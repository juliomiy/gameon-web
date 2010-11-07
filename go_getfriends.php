<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date:  September 13,2010
   Purpose: retrive user friends from go_userFriends table 
      if operation = friends
      if operation = invites will instead
      retrieve open invites from go_inviteDetails
   modified October 19,2010 by Julio H Miyares
   added search betsquare users feature 
   currently searches will hit an isam version of go_users named go_users_search

*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
$LOG=Config::getLogObject();

$userID=$_GET['userid'];
$query =$_GET['query'];
$operation = $_GET['operation'];
$querySort = $_GET['sort'];

//default if empty
if (empty($operation)) $operation='friends';

/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($userID)) {
   mydie("paramaters not complete");
}

$params['userid'] = $userID;
$params['operation'] = strtolower($operation);
$params['query'] = $query;

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$sql = getQuery($params,$link);
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$cursor = mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   mydie(mysqli_error($link),"500",$link);
}
$numberOfFriends = $cursor->num_rows;
header('HTTP/1.1 200 OK');
//   Fetch the results of the query
header("Cache-Control: no-cache, must-revalidate");
header ("content-type: text/xml");
echo '<?xml version="1.0"?>';

Utility::emitXML("",'go_friends',0);
Utility::emitXML("200",'status_code');
Utility::emitXML("Ok",'status_message');
Utility::emitXML($userID,'userid');
Utility::emitXML($numberOfFriends,'numberoffriends');
$recordsEmitted=0;
while( $row = mysqli_fetch_assoc($cursor) )  {
   Utility::emitXML("",'friend',0);
   Utility::emitXML($row['friendUserID'],'frienduserid');
   Utility::emitXML($row['friendUserName'],'friendusername');
   Utility::emitXML($row['friendName'],'friendname');
   Utility::emitXML($row['friendImageUrl'],'friendImageUrl');
   Utility::emitXML($row['numberOfBets'],'numberofbets');
   Utility::emitXML("",'friend',0);
} //while records to read
Utility::emitXML("",'go_friends',0);

if( isset($cursor)) $cursor->close();
if (isset($link))  $link->close();  //
ob_end_flush();
exit;

function getQuery($params, $link) {
  $operation = $params['operation'];

  if ($operation =='friends') {  //get my friends
      $sql = "select f.friendUserID, u.userName as friendUserName, f.friendName ,'test.png'  as friendImageUrl, 5 as numberOfBets from go_userFriends f JOIN go_user u on f.friendUserID = u.userID " ;
      $where = " where f.userID = '%u'";
  } else if ($operation == 'invites') {  //get list of invitees user has sent out
      $sql = "select i.inviteeBSUserID as friendUserID, i.inviteeUserName, 'testname' as friendName, '' as friendImageUrl, 0 as numberOfBets from
              go_friendInvites i LEFT JOIN go_user u on i.inviteeBSUserID = u.userID";
      $where = " where i.invitetorUserID = '%u'";
  } else if ($operation == 'invitee') {  //get where I am being invited
      $sql = "select i.invitetorUserID as friendUserID, u.userName as friendUserName, u.name as friendName, 'test.png' as friendImageUrl, 0 as numberOfBets from go_friendInvites i LEFT JOIN go_user u on i.invitetorUserID = u.userID ";
      $where = " where i.inviteeBSUserID = '%u' and i.inviteStatusID not in (" . FRIEND_INVITE_DECLINED . ',' . FRIEND_INVITE_APPROVED . ")" ;
  }  else if ($operation == 'search') {
// select those betsquared users that match the query string and that are NOT already my friends
      $sql ="select u.userID as friendUserID, u.userName as friendUserName, u.name as friendName, 'test.png' as friendImageUrl, 0 as numberOfBets from go_user u";
     $where = "  where u.userID in (select userID from go_user_search where match(body) against ('%s')) and u.userID  not in (select friendUserID from go_userFriends f where f.userID = '%u')"; 
     $sql = sprintf($sql . $where , 
    mysqli_real_escape_string($link,$params['query']),
    //mysqli_real_escape_string($link,$params['userid']),
    mysqli_real_escape_string($link,$params['userid']));
    return $sql;
  } //if

  
  $sql = sprintf($sql . $where , 
    mysqli_real_escape_string($link,$params['userid']));

  $order .= (!empty($params['sort']) ? " order by " . $params['sort'] : "");
  $sql .=$order; 
  return $sql;
} //getQuery

function mydie($msg,$statusCode = "500", $link=null) {
global $LOG;
   ob_end_clean();
   $LOG->log("$sql",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   ob_start();
     header("Content-Type: text/xml");
    // header("HTTP/1.1" . $statusCode . " Internal Server Error");
     Utility::emitXML("",'go_friends',0);
     Utility::emitXML("$statusCode",'status_code');
     Utility::emitXML("$msg",'status_message');
     Utility::emitXML("",'go_friends',0);
   ob_end_flush();
   die();
}//myDie
?>
