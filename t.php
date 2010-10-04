<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: retrive from games for a particular user and return xml
            retrive games that a user is subscribed to

   Update: by Julio Hernandez-Miyares
           August 17,2010
           added sort query parameter
           sorts results
   TODO: initially only retrieve those defined by userid, add those not created by userid but subscribed
   TODO: implement retrieval by various filters
              sport, leauge , date
   TODO: implement security - right now just knowing the userid is enough

   <games>
     <status_code>200</status_code>
     <status_message>Ok</status_message>
     <numberofgames>10</numberofgames>
     <game>
        <gameid>50</gameid>
        <createdbyuserid>1</createdbyuserid>
        <createdbyusername>jittrdev</createdbyusername>
        <publicgameid>2345</publicgameid>
        <eventname>Jets vs Chargers</eventname>
        <eventdatetime>2010-09-06 13:00:00</eventdatetime>
        <wagertype>duckets</wagertype>
        <wagerunits>12</wagerunits>
        <typeid>2</typeid>
        <typename>Team</typename>
        <sportid>3</sportid>
        <sportname>Football</sportname>
        <numberofsubscribers>5</numberofsubscribers>
     </game>
   </games>
*/
/* using GET instead of POST even though these represent changes to the Database
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
$LOG=Config::getLogObject();

$userID=$_GET['userid'];
$query =$_GET['query'];
$querySort = $_GET['sort'];
/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($userID)) {
   die("paramaters not complete");
}
if (!$query) $query="created";

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
function mydie($msg,$statusCode = "500", $link=null) {
global $LOG;
   ob_end_clean();
   $LOG->log("$sql",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   ob_start();
     header("Content-Type: text/xml");
//     header("HTTP/1.1" . $statusCode . " Internal Server Error");
     Utility::emitXML("",'games',0);
     Utility::emitXML("$statusCode",'status_code');
     Utility::emitXML("$msg",'status_message');
     Utility::emitXML("",'games',0);
   ob_end_flush();
   die();
}

?>
