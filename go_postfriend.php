<?php
function callback($buffer) {
 global $failureCount, $successCount;
 $numberOfInvites = $failureCount+$successCount;
 if ($failureCount ==0) {
    $statusCode="200";
    $statusMessage="Ok";
 } else {
    $statusMessage = "$failureCount invites failed";
    $statusCode="403";
 }
 $buffer = str_replace("^STATUS_CODE",$statusCode,$buffer);
 $buffer = str_replace("^STATUS_MESSAGE",$statusMessage,$buffer);
 $buffer = str_replace("^NUMBEROFINVITES",$numberOfInvites,$buffer);
 return $buffer;
}
ob_start("callback");
/*
  Author: Julio Hernandez-Miyares
  Date: September 17,2010
  Purpose: perform Invites of users to Betsquare
   operations supported: invite users (operation= invite
   invitee accept/decline an invite operation=accept, operation=decline           
Uses POST
 
   uppaded: Julio Hernandez-Miyares
   date: October 17,2010
   implement approve/decline of invites


Response
   <friend_invite>
     <numberofinvites>10</numberofinvites>
     <status_code>200</status_code>           //200 and OK will only occur if all transaction succeed
     <status_message>Ok</status_message>
     <invite>
        <status_message>Ok</status_message>
        <username>Clay</username>
        <socialnetwork>Facebook</socialnetwork>
        <socialnetworkid>2<socialnetworkid>
        
     </invite>
     <invite>   repeat for each individual invite
      ...
    </invite>
  </friend_invite>
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');

$LOG=Config::getLogObject();

$invitetorUserName=$_REQUEST['username']; // user doing the inviting 
$invitetorUserID=$_REQUEST['invitetoruserid']; //userid doing the inviting
$operation= strtolower($_REQUEST['operation']);  
$socialNetworkName = $_REQUEST['socialnetworkname'];
$socialNetworkID= $_REQUEST['socialnetworkid'];

//added to implement invite approve/decline - will be passed along in the header part of the post if operation is approve/decline
$inviteeBSUserID = $_REQUEST['inviteeuserid'];  
$inviteeBSUserName = $_REQUEST['inviteeusername'];
 
$errorMsg = null; //set to null, will test at end of block - if still null, passed test
if (empty($operation) || strrpos("invite approve deny decline",$operation) === FALSE || empty($socialNetworkID))  {
    $errorMsg = "Operation Parameter  missing ";
}  //if
// confirm parameters are passed depending on the operation requested
if ($operation =='invite') {
   $errorMsg .= (empty($invitetorUserID)) ? "invitetorUserID missing " : null;
} else {
   $errorMsg .= (empty($inviteeBSUserID)) ? "inviteeUserID missing " : null;
}  //if
//if (isset($errorMsg)) mydie($errorMsg);

if (Config::getDebug()) $LOG->log($_REQUEST['invites'],PEAR_LOG_INFO);
if (Config::getDebug()) $LOG->log($_SERVER['QUERY_STRING'],PEAR_LOG_INFO);

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}//if

header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: text/xml");
Utility::emitXML("",'friend_invite',0);
Utility::emitXML("^STATUS_CODE",'status_code');
Utility::emitXML("^STATUS_MESSAGE",'status_message');
Utility::emitXML("^NUMBEROFINVITES",'numberofinvites');

$invitesArr = explode('^',$_REQUEST['invites']); //invites records are separated by ^ (caret)
$noOfInvites = count($invitesArr);

$failureCount=0;
$successCount=0;
for ($index=0; $index < $noOfInvites; $index++) {
   $params=null;  //for safety's sake
   if ($operation =='invite') // header parm is the user Inviting
      $params['invitetoruserid'] = $invitetorUserID;
   else       //header parm is the person invited
      $params['inviteebsuserid'] = $inviteeBSUserID;
   $params['socialnetworkname'] = $socialNetworkName;
   $params['socialnetworkid'] = $socialNetworkID;
   $params['operation'] = $operation;
   $inviteArr = explode('|',$invitesArr[$index]);

   //print_r($inviteArr);
   for ($x = 0; $x < count($inviteArr); $x++) {
       $values = explode("=",$inviteArr[$x]);
       $key = $values[0];
       $value = $values[1];
       $params[$key]=$value;

     //print_r($params);
   } //for
   $sql = getQuery($params,$link);
   //echo("$sql \n");
   if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
   Utility::emitXML("",'invite',0);
   $rc = mysqli_multi_query($link,$sql);
   if (!$rc) {
      $statusCode="";
      $statusMessage="Failed to invite";
      $failureCount++;
   } else { //if
      $statusCode="200";
      $statusMessage="Ok";
      $successCount++;
   } //else

   Utility::emitXML("$statusMessage",'status_message');
   Utility::emitXML($params['inviteeusername'],'username');
   Utility::emitXML("$socialNetworkName",'socialnetworkname');
   Utility::emitXML("$socialNetworkID",'socialnetworkid');
   Utility::emitXML("",'invite',0);
} //for
Utility::emitXML("",'friend_invite',0);

if (isset($link)) $link->close();
ob_end_flush();
exit;

/* Takes array of parameters and returns sql to execute
   supports 3 operations, invite, approve, deny
*/
function getQuery($params,$link) {

   $operation = $params['operation'];
   $invitetorUserID = $params['invitetoruserid'];
   $socialNetworkID = $params['socialnetworkid'];
   $inviteeBSUserID = $params['inviteebsuserid'];
   $inviteeUserName= $params['inviteeusername'];
   $socialNetworkName = $params['socialnetworkname'];
   
   if ($operation =='invite')  { 
   $sql = sprintf("insert into go_friendInvites (invitetorUserID,inviteeBSUserID,inviteeUserName, inviteNetworkID,inviteNetworkName) values ('%u','%u','%s','%u','%s')",
             mysqli_real_escape_string($link,$invitetorUserID),
             mysqli_real_escape_string($link,$inviteeBSUserID),
             mysqli_real_escape_string($link,$inviteeUserName),
             mysqli_real_escape_string($link,$socialNetworkID),
             mysqli_real_escape_string($link,$socialNetworkName));
   } else if ($operation =='approve') {
         $sql = sprintf("insert into go_userFriends (userID, friendUserID, friendUserName, friendName) select '%u',u.userID, u.userName,u.name from go_user u  where u.userID = '%u';update go_friendInvites set inviteStatus='approved' , inviteStatusID='%u' where invitetorUserID='%u' and inviteeBSUserID='%u'",
             mysqli_real_escape_string($link,$invitetorUserID),
             mysqli_real_escape_string($link,$inviteeBSUserID),
             mysqli_real_escape_string($link,FRIEND_INVITE_APPROVED),
             mysqli_real_escape_string($link,$invitetorUserID),
             mysqli_real_escape_string($link,$inviteeBSUserID));
   } else if ($operation =='decline' ) {
         $sql = sprintf("update go_friendInvites set inviteStatus='decline' ,inviteStatusID = '%u' where invitetorUserID='%u' and inviteeBSUserID='%u'",
             mysqli_real_escape_string($link,FRIEND_INVITE_DECLINED),
             mysqli_real_escape_string($link,$invitetorUserID),
             mysqli_real_escape_string($link,$inviteeBSUserID));
   } //else
   return $sql;
} //getQuery

/*takes delimited string and returns array with the embedded invites to process
*/
function parseInvites($params) {

  
}
function mydie($message,$statusCode=500,$link=null) {
  global $LOG;
  ob_end_clean();
  header("Cache-Control: no-cache, must-revalidate");
  header("Content-Type: text/xml");
  $LOG->log("$message",PEAR_LOG_ERR);

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

