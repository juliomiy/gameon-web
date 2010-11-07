<?php
ob_start();
/*
   Author: Julio Hernandez-Miyares
   Date: September 2,2010
   Purpose:
     retrieve game invites for a particular user

    query parameters 
      mandatory - userid
      optional  - sportID/sportName
                - createdByUserID
   <game_invites>
     <status_code>200</status_code>
     <status_message>Ok</status_message>
     <userid>1</userid>
     <openinvites>10</openinvites>
     <gameinvite>
         <gameid>12345
         <createdByUserID>1</createdByUserID<
         <createdByUserName>1</createdByUserName<
         <eventname>NY Giants versus NY Jets</eventname>
         <numbersubscribed>10</numbersubscribed>
         <eventdatetime>2010-12-03 13:00:00</eventdatetime>
         <closedatetime>2010-12-03 13:00:00</closedatetime>
         <wagertype>Donuts</wagertype</wagertype>
         <wagerunits>12</wagerunits>
     </gameinvite>
   </game_invites>
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
$LOG=Config::getLogObject();

//only userid is mandatory
$userID = $_REQUEST['userid'];
$userName = $_REQUEST['username'];
$sportID= $_REQUEST['sportid'];
$sort = $_REQUEST['sort'];

if (empty($userID)  && !empty($userName)) {
    $userID = Utility::getUserIDOrName($userName,'username');
} else 
if (!empty($userID)  && empty($userName)) {
    $userName = Utility::getUserIDOrName($userID,'id');
}
if (empty($userID)) {
   mydie("Incomplete Parameters",500);
} //if


$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$sql = getQuery($link,$userID);
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$cursor = mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link),500,$link);
}
header('HTTP/1.1 200 OK');
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
 /* Fetch the results of the query */

Utility::emitXML("",'game_invites',0);
Utility::emitXML('200','status_code');
Utility::emitXML('Ok','status_message');
Utility::emitXML("$userID",'userID');
Utility::emitXML("$userName",'username');
Utility::emitXML($cursor->num_rows,'openinvites');
$recordsEmitted=0;

while( $row = mysqli_fetch_assoc($cursor) )  {
   $recordsEmitted++;
   Utility::emitXML("",'gameinvite',0);
   Utility::emitXML($row['gameID'],"gameid");
   Utility::emitXML($row['createdByUserID'],"createdbyuserid");
   Utility::emitXML($row['createdByUserName'],"createdbyusername");
   Utility::emitXML($row['eventName'],"eventname");
   Utility::emitXML($row['numberSubscribed'],"numbersubscribed");
   Utility::emitXML($row['eventDateTime'],"eventdatetime");
   Utility::emitXML($row['closeDateTime'],"closedatetime");
   Utility::emitXML($row['wagerType'],"wagertype");
   Utility::emitXML($row['wagerUnits'],"wagerunits");
   Utility::emitXML($row['type'],"type");
   Utility::emitXML($row['sportID'],"sportid");
   Utility::emitXML($row['sportName'],"sportname");
   Utility::emitXML($row['leagueID'],"leagueid");
   Utility::emitXML($row['leagueName'],"leaguename");
   Utility::emitXML("",'gameinvite',0);
} //while
$cursor->close();
Utility::emitXML("",'game_invites',0);
ob_end_flush();
exit;

// returns a sql statement to execute
function getQuery($link,$userID) {
 $sql = sprintf("select i.gameID, i.inviteKey, i.createdByUserID , i.createdByUserName, g.eventName, g.numberSubscribed, g.date as eventDateTime, g.closeDateTime,g.wagerUnits,g.wagerType , g.type, g.sportID,g.sportName, g.leagueID, g.leagueName from go_gameInviteDetail i join go_games g on i.gameID = g.gameID  where i.inviteeUserID='%u' and i.inviteStatusID not in (" . FRIEND_INVITE_DECLINED . "," .  FRIEND_INVITE_APPROVED . ")  and i.closeDateTime > now()",
           mysqli_real_escape_string($link,$userID));
$sql .=(empty($sort) ? " order by i.closeDateTime asc" : " order by i.closeDateTime $sort");
return $sql;
} //getQuery

//mydie function in case of critical error
function mydie($message,$statusCode=500,$link=null) {
  global $LOG;
  $LOG->log("$message",PEAR_LOG_ERR);
  ob_end_clean();
  if (isset($link)) $link->close();

  ob_start();
  header("Content-Type: text/xml");

  Utility::emitXML("","game_invites",0);
   Utility::emitXML($statusCode,"status_code");
   Utility::emitXML("$message","status_message");
  Utility::emitXML("","game_invites",0);
  ob_end_flush();
  exit;
}

?>
