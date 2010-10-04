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

$invitetorUserName=$_REQUEST['username'];
$invitetorUserID=$_REQUEST['invitetoruserid'];
$operation= strtolower($_REQUEST['operation']);

if (empty($operation) || strrpos("invite approve deny",$operation) === FALSE)  {
    mydie("Parameter(s) missing");
}  //if

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

$invitesArr = explode('^',$_REQUEST['invites']);
$noOfInvites = count($invitesArr);
$failureCount=0;
$successCount=0;
for ($index=0; $index < $noOfInvites; $index++) {
   $params=null;  //for safety's sake
   $params['invitetoruserid'] = $invitetorUserID;
   $params['socialnetworkname'] = 'BET_SQUARED';
   $params['socialnetworkid'] = 4;
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
         $sql = sprintf("insert into go_userFriends (userID, friendUserID, values ()");    
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

