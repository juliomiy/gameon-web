<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: retrive from go_pubicGames table and return xml
   Public Games are those that represent games of the public nature. Prime example are NCAA and Pro Sports
   TODO: implement retrieval by various filters
              sport, leauge , date
*/	      
/* using GET instead of POST even though these represent changes to the Database
*/
require_once('config.class.php');
require_once('goutility.class.php');

$sport=$_GET['sport'];

header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: text/xml");
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$sql = "select * from go_publicgames_dn";
if (!is_null($sport)) {
  $sql = sprintf("$sql where sportName='%s'",mysqli_real_escape_string($link,$sport)); 
} 
$sql .= " Limit 0,100";
//echo $sql;

$cursor = mysqli_query($link,$sql); 
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link));
}
header('HTTP/1.1 200 OK');
 /* Fetch the results of the query */
echo '<?xml version="1.0" encoding="UTF-8"?>';

Utility::emitXML("",'games',0);
$recordsEmitted=0;

while( $row = mysqli_fetch_assoc($cursor) )  {
   Utility::emitXML("",'game',0);
   Utility::emitXML($row['id'],"id");
   Utility::emitXML($row['title'],"title");
   Utility::emitXML($row['title'],"eventname");  //temp
   Utility::emitXML($row['eventdate'],"eventdate");
   Utility::emitXML($row['title'],"description");  //temp
   Utility::emitXML($row['type'],"type"); 
   Utility::emitXML($row['sport'],"sport"); 
   Utility::emitXML($row['league'],"league");
   Utility::emitXML("","teams",0);
   Utility::emitXML($row['team1'],"team1");
   Utility::emitXML($row['team2'],"team2");
   Utility::emitXML("","teams",0);
   Utility::emitXML($row['numbersubscribers'],"numbersubscribers");
   Utility::emitXML("",'game',0);
   $recordsEmitted++;
} //while
Utility::emitXML("","games",0);
$cursor->close();
$link->close();  /* Close Database */
ob_end_flush();
exit;


function mydie($msg) {
   ob_end_clean();
   die($msg);
}
?>
