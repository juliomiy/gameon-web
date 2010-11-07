<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: November 5,2010
   Purpose: update a user's response to a game invite
   Operations Permitted: approve , decline

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
$operation = (isset($_REQUEST['operation'])) ? strtolower($_REQUEST['operation']) : null;
$userID = $_REQUEST['userid'];
$gameID = $_REQUEST['gameid'];
$typeID = $_REQUEST['typeid'];
$publicGameID= $_REQUEST['publicgameid'];


// check for valid request data

/* general verification have enough to continue - set defaults for missing parameters as long as they are not
*/
$message=null;
if (!isset($operation,$gameID ,$typeID,$userID)) {
   $message .= (!isset($operation) ? " operation" :  null);
   $message .= (!isset($gameID) ? " gameID" :  null);
   $message .= (!isset($typeID) ? " typeID" :  null);
   $message .= (!isset($userID) ? " userID" :  null);
   if (!isset($message)) {
      $message .= (!is_numeric($userID) ? " Invalid dataType for userID" :  null);
      $message .= (!is_numeric($typeID) ? " Invalid dataType for typeID" :  null);
      $message .= (!is_numeric($gameID) ? " Invalid dataType for gameID" :  null);
      if (isset($pubicGameID)) $message .= (!is_numeric($publicGameID) ? " Invalid dataType for pubicGameID" :  null);
   } //if
}//if
if (!empty($message)) {
   mydie("$message - parameters not complete");
}
// check for supported operations
$pos = strpos("approve decline deny",$operation);
if($pos === false) {
 // string needle NOT found in haystack
   mydie("Invalid Operation specified");
}

//validate based on game Type. Types from go_types_lu table
switch ($typeID) {
   case GAME_TYPE_TEAM :
      $teamID = $_REQUEST['teamid'];
      $teamName = $_REQUEST['teamname'];
      if (!isset($teamID, $teamName)) {
         $rv = explode(GAMEON_DELIMITER,Utility::getTeamIDOrTeamName((isset($teamID) ? $teamID : $teamName) , (isset($teamID) ? "id" : "teamname")));
         print_r($rv);
         if ($rv[0] == GAMEON_OK) {
            if ($rv[1] == 'teamid') $teamID = $rv[2];
            else $teamName = $rv[2];
            $params['teamid']=$teamID;
            $params['teamname']=$teamName;
         } else { //if
           mydie("invalid teamID or teamName");
         } //else
      } //if
      break;
   case GAME_TYPE_TOURNAMENT:
      break;
   case GAME_TYPE_USER:
      break;
   case GAME_TYPE_DATE:
      break;
   default:
} //switch on typeID

//if set, retrieve publicGame Object passing in publicGameID
if (isset($publicGameID)) {
   require_once("go_publicgame.class.php");
   $publicGame = new goPublicGame($publicGameID);
   if (isset($publicGame)) {
      if ($publicGame->getStatusCode() != "200" ) $message .=" Invalid pubicGameID";
   } else $message .= " Invalid publicGameID";  
}
if (!empty($message)) {
   mydie("$message"); 
}
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   mydie("Error connecting to Database");
}
$params['operation']=$operation;
$params['gameid'] = $gameID;
$params['publicgameid']=$publicGameID;
$params['userid']=$userID;
$params['initiatorflag']=0;  //in this api, initiatorflag is always 0

print_r($params);
$sql = getQuery($params,$link);
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql);
if (!$rc) {
   // Server error
   mydie(mysqli_error($link),500,$link);
}

ob_end_flush();
exit;

function getQuery($params,$link) {

if ($params['operation'] == "approve") {
   $sql = sprintf("insert into go_gameSubscribers_Team (gameID, publicGameID, userID, initiatorFlag, teamID, teamName)  values (
              '%u','%u','%u','%u','%u','%s')",
        mysqli_real_escape_string($link,$params['gameid']),
        mysqli_real_escape_string($link,$params['publicgameid']),
        mysqli_real_escape_string($link,$params['userid']),
        mysqli_real_escape_string($link,$params['initiatorflag']),
        mysqli_real_escape_string($link,$params['teamid']),
        mysqli_real_escape_string($link,$params['teamname'])
        );
} //if       
return $sql;
}

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
} //mydie
?>
