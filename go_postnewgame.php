<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: insert new game/wager into go_games 
   using GET instead of POST even though these represent changes to the Database
   added call to set the subscriptionClose date/time in case theuser did not explicitly set one
   added code to syndicate to user's social network starting with twitter first - initially will be full broadcast to your followers
   Modified: August 16,2010 by Julio Hernandez-Miyares
      after successfully adding the new game in go_games , add the record(s) to the 
      go_gameInvite table. This table will manage game/bet invites
   Modified: JHM September 1,2010
      standard ini_set
      update xml return both for success and failure
      focus on getting publicGames to work to allow Bet functionality from publicgames of android/smartphone application to work
   Modified: JHM September 2,2010
      function inviteGame will be dev coded to automatically add Jittr employees to invite List 
      in go_GameInviteDetail
      This will be removed and will be under user control towards the end of the development cycle

   TODO - add twitter direct tweet
   TODO - add twitter tweet to user list
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_game.class.php');
require_once('go_usersettings.class.php');
require_once('go_publicgame.class.php');
require_once('EpiTwitter.php');
/* retrieve query parameters
   Required parameters are
   createdBy
   title
   wagertype
   Everything can have a default
*/
$LOG=Config::getLogObject();
$trimCharlist="\x00..\x1F";
$operation = $_REQUEST['operation'];
if (empty($operation)) $operation='insert';  //default behavior is to insert a record

if ($operation == 'insert') {
   $gameID  = Utility::generateUniqueIDKey('GAMEID');
} else if ($operation =='update') {
   $gameID = $_REQUEST['gameid'];
}
$publicGameID=$_REQUEST['publicgameid'];

$createdByUserName=$_REQUEST['createdbyusername'];
$createdByUserID=$_REQUEST['createdbyuserid'];
$title    = $_REQUEST['title'];
$eventName= $_REQUEST['eventname'];
$teamID = $_REQUEST['teamid'];
$teamName = $_REQUEST['teamname'];
$date=$_REQUEST['date'];
$description=$_REQUEST['description'];
$type=$_REQUEST['type'];
$typeName=$_REQUEST['typename'];
$sport=$_REQUEST['sport'];
$subscriptionClose=$_REQUEST['subscriptionclose'];
$expirationDateTime=$_REQUEST['expirationdatetime'];
$wagerType=$_REQUEST['wagertype'];
$wagerUnits=$_REQUEST['wagerunits'];
$pivotDate = $_REQUEST['pivotdate'];
$pivotCondition = $_REQUEST['pivotcondition'];

/* if publicGameID is not empty , instantiate  publicGame Record */
if (!empty($publicGameID)) {
  $publicGameSettings = new GoPublicGame($publicGameID);
   $leagueName = $publicGameSettings->getLeagueName();
   if (empty($eventName)) $eventName = $publicGameSettings->getEventName();
   if (empty($sport)) {
       $sport = $publicGameSettings->getSportID();
       $sportName = $publicGameSettings->getSportName();
   } //if
   if (empty($subscriptionClose)) {  //calculate default subscription Close if not explicitly set in query parameter
       $eventDateTime = $publicGameSettings->getEventDate();
       $epoch = strtotime($eventDateTime);
       $epoch -= 3600;
       $subscriptionClose = date('Y-m-d H:i:s',$epoch);
       if (empty($expirationDateTime)) $expirationDateTime = $subscriptionClose;

   } //if
   if (empty($teamID)) { //get TeamID by teamName
      $teamID = Utility::getTeamIDbyTeamName($teamName); 
   }
} else 
  $publicGameID = 0;
$title = (!empty($title) ? $title : $eventName);
$userSettings = new goUserSettings($createdByUserID); //grab user who created this wager - will need properties for syndication including oauth credentials
$createdByUserName = $userSettings->getUserName();

/* general verification have enough to continue - set defaults for missing parameters as long as they are not
*/
$message=null;
if (!isset($createdByUserName,$createdByUserID,$title,$wagerType,$type,$typeName)) {
   $message .= (empty($createdByUserName) ? " createdByUser" :  null); 
   $message .= (!isset($createdByUserID) ? " createdByUserID" :  null); 
   $message .= (!isset($title) ? " title" :  null); 
   $message .= (!isset($wagerType) ? " wagerType" :  null); 
   $message .= (!isset($type) ? " Type" :  null); 
   $message .= (!isset($typeName) ? " TypeName" :  null); 
}   
if ($typeName == 'Team' && !isset($teamID)) {
   $message .= " TeamID" ;
}
if (!empty($message)) {
   mydie("$message - parameters not complete");
}
   
/* more refined edits - depend on the type of wager */
/* initial prototype will have only date driven wagers*/
$type = strtolower($type);
if ($type == "date") {
// need the pivot date for the wager as well as the condition , before, on, after
   if (!isset($pivotDate,$pivotCondition)) {
      mydie("$message - parameters not complete");
   }
} //if

$createdBy=trim($createdBy,$trimCharlist);
$title=trim($title,$trimCharlist);
$wagerType=trim($wagerType,$trimCharlist);

if (is_null($eventName)) $eventName=$title;
if (is_null($description)) $description=$title;
if (is_null($wagerUnits)) $wagerUnits=1;

/* define the url to use for syndicating the bet*/
$syndicationUrl = "";

$userSettings = new goUserSettings($createdByUserID); //grab user who created this wager - will need properties for syndication including oauth credentials

header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$gameInviteKey = Utility::generateGameInvite('GAMEID');

//if not set or empty, calculare default subscriptionClose date/time
//TODO -currently defaulting to a Wager of type "date" driven - 
if (empty($subscriptionClose)) {
   $pivotDateObj = new DateTime($pivotDate,new DateTimeZone('America/New_York'));
   $subscriptionCloseObj = Game::getDefaultSubscriptionClose($typeName, $pivotDateObj);
   if ($subscriptionCloseObj) $subscriptionClose=$subscriptionCloseObj->format("Y-m-d h:i:s");
}
//grab the shortened subscription url for the wager.
$rc = Utility::shortenUrl("http://jittr.com/jittr/gameon/gogame.php/" . urlencode("?gameid=" . $gameID . "&createdbyuserid=" . $createdByUserID));
if (Config::getDebug()) $LOG->log("status code " . $rc->status_code,PEAR_LOG_INFO);
if ($rc->status_code == 200) {
    $syndicationUrl= $rc->data->url;
}
$sql=sprintf("insert into go_games (gameID,publicGameID,createdByUserID,createdByUserName,title,eventName,date,description,type,typeName,pivotDate,pivotCondition,sportID,sportName,leagueName,closeDateTime,expirationDateTime,syndicationUrl,wagerType,wagerUnits) values ('%s','%u','%u','%s','%s','%s','%s','%s','%u','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%u')",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$publicGameID),
        mysqli_real_escape_string($link,$createdByUserID),
        mysqli_real_escape_string($link,$createdByUserName),
        mysqli_real_escape_string($link,$title),
        mysqli_real_escape_string($link,$eventName),
        mysqli_real_escape_string($link,$date),
        mysqli_real_escape_string($link,$description),
        mysqli_real_escape_string($link,$type),
        mysqli_real_escape_string($link,$typeName),
        mysqli_real_escape_string($link,$pivotDate),
        mysqli_real_escape_string($link,$pivotCondition),
        mysqli_real_escape_string($link,$sport),
        mysqli_real_escape_string($link,$sportName),
        mysqli_real_escape_string($link,$leagueName),
        mysqli_real_escape_string($link,$subscriptionClose),
        mysqli_real_escape_string($link,$expirationDateTime),
        mysqli_real_escape_string($link,$syndicationUrl),
        mysqli_real_escape_string($link,$wagerType),
        mysqli_real_escape_string($link,$wagerUnits));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 
if (!$rc) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link),500,$link);
}
/* Assume Team Sport - drop record by initiator into go_gameSubscriber_Team table */
$sql = sprintf("insert into go_gameSubscribers_Team (gameID,publicGameID, userID, initiatorFlag, position,teamID, teamName) values ('%s','%u','%u',1,1,'%u','%s')",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$publicGameID),
        mysqli_real_escape_string($link,$createdByUserID),
        mysqli_real_escape_string($link,$teamID),
        mysqli_real_escape_string($link,$teamName));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 
if (!$rc) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link),500,$link);
}

insertGameInvites($link,$gameID,$gameInviteKey,$createdByUserID,$createdByUserName);

//Twitter syndication
$twitter = new EpiTwitter(Config::getTwitterConsumerKey(),Config::getTwitterConsumerKeySecret(), $userSettings->getTwitterOAuthToken(), $userSettings->getTwitterOAuthTokenSecret());
if ($twitter) {
   $response = $twitter->post_statusesUpdate(array('status' => $title . " " . $syndicationUrl ));
   if (Config::getDebug()) $LOG->log("$response",PEAR_LOG_INFO);
}
header('HTTP/1.1 200 OK');
$link->close();  /* Close Database */
//return xml 
Utility::emitXML("","insert_game",0);
Utility::emitXML("200","status_code");
Utility::emitXML("ok","status_message");
Utility::emitXML("$gameID","gameid");
Utility::emitXML("","insert_game",0);
ob_end_flush();

exit;
/* insert gameInvites into go_gameInvite table
*/
function insertGameInvites($link,$gameID, $inviteKey, $createdByUserID, $createdByUserName) {
  global $LOG;
  $sql=sprintf("insert into go_gameInvite (gameID,inviteKey) values ('%s','%s')",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$inviteKey)
    );
  if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
  $rc = mysqli_query($link,$sql); 
  if (!$rc) {
   // Server error
     header('HTTP/1.1 500 Internal Server Error');
     mydie(mysqli_error($link),500,$link);
  } //if
//TODO - Remove temporary automatic insert into invite Detail

  $sql = sprintf("insert into go_gameInviteDetail (gameID, inviteKey, createdByUserID,createdByUserName, inviteeUserID) select '%s','%s','%u','%s',userID from go_user where userID in (110,111,112)",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$inviteKey),
        mysqli_real_escape_string($link,$createdByUserID),
        mysqli_real_escape_string($link,$createdByUserName));
  if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
  $rc = mysqli_query($link,$sql); 
 
} // insert GameInvites

function mydie($message,$statusCode=500,$link=null) {
  global $LOG;
  $LOG->log("$message",PEAR_LOG_ERR);
  ob_end_clean();
  if (isset($link)) $link->close();
  
  ob_start();
  header("Content-Type: text/xml");

  Utility::emitXML("","insert_game",0);
   Utility::emitXML($statusCode,"status_code");
   Utility::emitXML("$message","status_message");
  Utility::emitXML("","insert_game",0);
  ob_end_flush();
  exit;
}
?>
