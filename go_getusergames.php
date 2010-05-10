<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: retrive from games for a particular user and return xml
            retrive games that a user is subscribed to  
	   
   TODO: initially only retrieve those defined by userid, add those not created by userid but subscribed 
   TODO: implement retrieval by various filters
              sport, leauge , date
   TODO: implement security - right now just knowing the userid is enough

*/	      
/* using GET instead of POST even though these represent changes to the Database
*/
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
$LOG=Config::getLogObject();

$userID=$_GET['userid'];
$query =$_GET['query'];
/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($userID)) {
   mydie("paramaters not complete");
}
if (!$query) $query="created";

header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: text/xml");
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
if ($query == "created")
   $sql = sprintf("select * from go_games where createdByUserID='%u'",mysqli_real_escape_string($link,$userID));
else if ($query == "subscribed")
   $sql = sprintf("select g.* from go_games g, go_gamesSubscribers s where s.userID='%u' and s.gameID = g.gameID",mysqli_real_escape_string($link,$userID));
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

Utility::emitXML("",'games',0);
$recordsEmitted=0;

while( $row = mysqli_fetch_assoc($cursor) )  {
   Utility::emitXML("",'game',0);
   Utility::emitXML($row['gameID'],"gameid");
   Utility::emitXML($row['publicGameID'],"publicgamegameid");
   Utility::emitXML($row['title'],"title");
   Utility::emitXML($row['description'],"description");
   Utility::emitXML($row['wagerType'],"wagertype");  //temp
   Utility::emitXML($row['wagerUnits'],"wagerunits");
   Utility::emitXML($row['subscriptionClose'],"subscriptionclose");  //temp
   Utility::emitXML($row['type'],"type"); 
   Utility::emitXML($row['sport'],"sport"); 
   Utility::emitXML($row['numberSubscribers'],"numbersubscribers");
   Utility::emitXML("",'game',0);
   $recordsEmitted++;
} //while
Utility::emitXML("","games",0);
$cursor->close();
$link->close();  /* Close Database */
ob_end_flush();
exit;


function mydie($msg,$link=null) {
   ob_end_clean();
   $LOG->log("$sql",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   die($msg);
}
?>
