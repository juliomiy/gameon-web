<?php
ob_start();
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');

/* Author: Julio Hernandez-Miyares
   Date: August 16,2010
   Purpose: retrieve the user dashboard given a userid
   Simple api because all of the summarized data will be in go_userDashboard and the api's function is solely
   to select the normalized row and return in xml (and eventually in json)
   Though not implemented for version 1, will require some form of authentication

   Total Number of Bets
   Total Number of bets initiated
   Total Wins/Loses
   Total $$$ wins/loses

   currently active bets


   xml response
   <userdashboard>
      <statuscode>200</statuscode>
      <statusmessage>OK</statusmessage>
      <userid>21</userid>
      <totalbets>20</totalbets>
      <totalbetsinitiated>16</totalbetsinitiated>
      <totalwins>5</totalwins>
      
   </userdashboard>

   GET query parameters
 
    userid   (betsquared id for user)
    foursquareid
    facebookid
    twitterid

*/
//cleanup/build return xml
/* xml returned

   <userdashboard>
     <statuscode>403</statuscode>
     <statusmessage>Forbidden</statusmessage>
   <userdashboard>
*/
$LOG=Config::getLogObject();

//parse query parameters , called via  HTTP GET
//Filtering
$params = Array();
$params['userid'] = $_GET['userid'];

//open connect to database
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link) {
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$sql = getQuery($params);
$LOG->log($sql,PEAR_DEBUG);
$cursor = mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link) . " executing $sql",$link);
}

header ("content-type: text/xml");
echo '<?xml version="1.0"?>';
Utility::emitXML("",'userdashboard',0);
Utility::emitXML("200",'statuscode');
Utility::emitXML("OK",'statusmessage');
$recordsEmitted=0;

$totalBets=0;
$totalBets=0;
$totalBetsInitiated=0;
$totalBetsAccepted=0;
$totalWins=0;
$totalLoses=0;

// while records to read/ retrieve and emit xml
if ( $row = mysqli_fetch_assoc($cursor) )  {
   $recordsEmitted++;
   $userID= $row['userID'];
   $totalBets= $row['totalBets'];
   $totalBetsInitiated= $row['totalBetsInitiated'];
   $totalBetsAccepted= $row['totalBetsAccepted'];
   $totalWins= $row['totalWins'];
   $totalLoses= $row['totalLoses'];
} //if
$cursor->close();

Utility::emitXML("$userID",'userid');
Utility::emitXML((isset($totalBets) ? $totalBets : 0 ),'totalbets');
Utility::emitXML("$totalBetsInitiated",'totalbetsinitiated');
Utility::emitXML("$totalBetsAccepted",'totalbetsaccepted');
Utility::emitXML("$totalWins",'totalwins');
Utility::emitXML("$totalLoses",'totalloses');

Utility::emitXML("",'userdashboard',0);

$link->close();  /* Close Database */
ob_end_flush();

exit;

/* Functions 
*/
function mydie($msg,$link=null) {
   global $LOG;
   ob_end_clean();
   $LOG->log("$msg",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   ob_start();

   header ("content-type: text/xml");
   Utility::emitXML("",'userdashboard',0);
   Utility::emitXML("500",'statuscode');
   Utility::emitXML("$msg",'statusmessage');
   Utility::emitXML("",'userdashboard',0);
   ob_end_flush();
   die();
}
function getQuery($params) {
   $sql = "select * from go_userDashboard ";
   if (!empty($params)) {
      foreach ($params as $key => $value) {
         switch($key) {
            case "userid":
              $where = " where userID = $value";
         } //switch
      } //foreach
   } //if
   $sql .=$where;
   return $sql; 
}
 
?>
