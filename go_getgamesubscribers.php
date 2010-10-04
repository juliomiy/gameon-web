<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: retrieve subscribers to a game
*/	      
/* using GET instead of POST even though these represent changes to the Database
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
$LOG=Config::getLogObject();

$gameID=$_GET['gameid'];
$LOG->log("GameID - $gameID",PEAR_LOG_DEBUG);
$query =$_GET['query'];
/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (empty($gameID)) {
   mydie("paramaters not complete");
}
if (!$query) $query="gamesubscribers";

header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: text/xml");
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
if ($query == "gamesubscribers")
   $sql = sprintf("select s.*,u.userName from go_gamesSubscribers s LEFT JOIN go_user u on s.userID = u.userID  where s.gameID = '%u' ",mysqli_real_escape_string($link,$gameID));
else
    mydie("invalid query - $query");

$sql .= " Limit 0,100";
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$cursor = mysqli_query($link,$sql); 
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link),$link);
}
header('HTTP/1.1 200 OK');
 /* Fetch the results of the query */
echo '<?xml version="1.0"?>'; 

Utility::emitXML("",'gamesubscribers',0);
$recordsEmitted=0;

while( $row = mysqli_fetch_assoc($cursor) )  {
   Utility::emitXML("",'game',0);
   Utility::emitXML($row['gameID'],"gameid");
   Utility::emitXML($row['userID'],"userid");
   Utility::emitXML($row['userName'],"username");
   //Utility::emitXML($row['position'],"position");  //temp
   Utility::emitXML($row['wagerUnits'],"wagerunits");
   Utility::emitXML("",'game',0);
   $recordsEmitted++;
} //while
Utility::emitXML("","gamesubscribers",0);
$cursor->close();
$link->close();  /* Close Database */
ob_end_flush();
exit;

function mydie($msg,$link=null) {
   global $LOG;
   ob_end_clean();
   $LOG->log("$sql",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   die($msg);
}
?>
