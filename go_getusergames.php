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
   mydie("paramaters not complete");
}
if (!$query) $query="created";

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

// Sorting if set from query parameter 
if (!empty($querySort)) {
   if ('recent' == strtolower($querySort))
       $sql .= " order by createdDate desc ";
} //if

$sql .= " Limit 0,100";
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$cursor = mysqli_query($link,$sql); 
if (!$cursor) {
   // Server error
   mydie(mysqli_error($link),"500",$link);
}
$numberOfGames = $cursor->num_rows;
header('HTTP/1.1 200 OK');
//   Fetch the results of the query 
header("Cache-Control: no-cache, must-revalidate");
header ("content-type: text/xml");
echo '<?xml version="1.0"?>';
Utility::emitXML("",'games',0);
Utility::emitXML("200",'status_code');
Utility::emitXML("Ok",'status_message');
Utility::emitXML("$numberOfGames",'numberofgames');
$recordsEmitted=0;
while( $row = mysqli_fetch_assoc($cursor) )  {
   Utility::emitXML("",'game',0);
   Utility::emitXML($row['gameID'],"gameid");
   Utility::emitXML($row['publicGameID'],"publicgamegameid");
   Utility::emitXML($row['createdByUserID'],"createdByUserID");
   Utility::emitXML($row['createdByUserName'],"createdbyusername");
   Utility::emitXML($row['eventName'],"eventname");
   Utility::emitXML($row['date'],"eventdatetime");
   Utility::emitXML($row['title'],"title");
   Utility::emitXML($row['description'],"description");
   Utility::emitXML($row['wagerType'],"wagertype");  //temp
   Utility::emitXML($row['wagerUnits'],"wagerunits");
   Utility::emitXML($row['closeDateTime'],"subscriptionclose");  //temp
   Utility::emitXML($row['typeID'],"type"); 
   Utility::emitXML($row['typeName'],"typeName"); 
   Utility::emitXML($row['sport'],"sport"); 
   Utility::emitXML($row['sportName'],"sportname"); 
   Utility::emitXML($row['numberSubscribed'],"numbersubscribers");
   Utility::emitXML("",'game',0);
   $recordsEmitted++;
} //while
Utility::emitXML("","games",0);
if( isset($cursor)) $cursor->close();
if (isset($link))  $link->close();  //
ob_end_flush();
exit;

function mydie($msg,$statusCode = "500", $link=null) {
global $LOG;
   ob_end_clean();
   $LOG->log("$sql",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   ob_start();
     header("Content-Type: text/xml");
    // header("HTTP/1.1" . $statusCode . " Internal Server Error");
     Utility::emitXML("",'games',0);
     Utility::emitXML("$statusCode",'status_code');
     Utility::emitXML("$msg",'status_message');
     Utility::emitXML("",'games',0);
   ob_end_flush();
   die();
}
?>
