<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: insert new game/wager into go_games 
   using GET instead of POST even though these represent changes to the Database
   added call to set the subscriptionClose date/time in case theuser did not explicitly set one
   added code to syndicate to user's social network starting with twitter first - initially will be full broadcast to your followers
   TODO - add twitter direct tweet
   TODO - add twitter tweet to user list
*/
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_game.class.php');
require_once('go_usersettings.class.php');
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
$gameID = $_GET['gameid'];
$publicGameID=$_GET['publicgameid'];
$createdByUserName=$_GET['createdbyusername'];
$createdByUserID=$_GET['createdbyuserid'];
$title    = $_GET['title'];
$eventName= $_GET['eventname'];
$date=$_GET['date'];
$description=$_GET['description'];
$type=$_GET['type'];
$typeName=$_GET['typename'];
$sport=$_GET['sport'];
$subscriptionClose=$_GET['subscriptionclose'];
$wagerType=$_GET['wagertype'];
$wagerUnits=$_GET['wagerunits'];
$pivotDate = $_GET['pivotdate'];
$pivotCondition = $_GET['pivotcondition'];

/* general verification have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($createdByUserName,$createdByUserID,$title,$wagerType,$type,$typeName)) {
   $message .= (!isset($createdByUserName) ? " createdByUser" :  null); 
   $message .= (!isset($createdByUserID) ? " createdByUserID" :  null); 
   $message .= (!isset($title) ? " title" :  null); 
   $message .= (!isset($wagerType) ? " wagerType" :  null); 
   $message .= (!isset($type) ? " Type" :  null); 
   $message .= (!isset($typeName) ? " TypeName" :  null); 
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
//grab unique id for game
$gameID=Utility::generateUniqueIDKey('GAMEID');
//if not set or empty, calculare default subscriptionClose date/time
//TODO -currently defaulting to a Wager of type "date" driven - 
if (empty($subscriptionClose)) {
   $pivotDateObj = new DateTime($pivotDate,new DateTimeZone('America/New_York'));
   $subscriptionCloseObj = Game::getDefaultSubscriptionClose($typeName, $pivotDateObj);
   if ($subscriptionCloseObj) $subscriptionClose=$subscriptionCloseObj->format("Y-m-d:h:i:s");
}
//grab the shortened subscription url for the wager.
$rc = Utility::shortenUrl("http://jittr.com/jittr/gameon/gogame.php/" . urlencode("?gameid=" . $gameID . "&createdbyuserid=" . $createdByUserID));
if (Config::getDebug()) $LOG->log("status code " . $rc->status_code,PEAR_LOG_INFO);
if ($rc->status_code == 200) {
    $syndicationUrl= $rc->data->url;
}
$sql=sprintf("insert into go_games (gameID,createdByUserID,createdByUserName,title,eventName,date,description,type,typeName,pivotDate,pivotCondition,sport,subscriptionClose,syndicationUrl,wagerType,wagerUnits) values ('%s','%u','%s','%s','%s','%s','%s','%u','%s','%s','%s','%s','%s','%s','%s','%u')",
        mysqli_real_escape_string($link,$gameID),
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
        mysqli_real_escape_string($link,$subscriptionClose),
        mysqli_real_escape_string($link,$syndicationUrl),
        mysqli_real_escape_string($link,$wagerType),
        mysqli_real_escape_string($link,$wagerUnits));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 

if (!$rc) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link));
}
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

function mydie($message) {
global $LOG;
Utility::emitXML("","insert_game",0);
Utility::emitXML("500","status_code");
Utility::emitXML("$message","status_message");
Utility::emitXML("","insert_game",0);
ob_end_flush();
$LOG->log("$message",PEAR_LOG_ERR);
exit;
}
?>
