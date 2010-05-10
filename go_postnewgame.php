<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: insert new game/wager into go_games 
   using GET instead of POST even though these represent changes to the Database
*/
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
/* retrieve query parameters
   Required parameters are
   createdBy
   title
   wagertype
   Everything can have a default
*/
$LOG=Config::getLogObject();
$trimCharlist="\x00..\x1F";
$gameID = $_GET['gameid'];
$publicGameID=$_GET['publicgameid'];
$createdByUserName=$_GET['createdbyusername'];
$createdByUserID=$_GET['createdbyuserid'];
$title    = $_GET['title'];
$eventName= $_GET['eventname'];
$date=$_GET['date'];
$description=$_GET['description'];
$type=$_GET['type'];
$sport=$_GET['sport'];
$subscriptionClose=$_GET['subscriptionclose'];
$wagerType=$_GET['wagertype'];
$wagerUnits=$_GET['wagerunits'];

/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($createdByUserName,$createdByUserID,$title,$wagerType)) {
   mydie("parameters not complete");
}   

$createdBy=trim($createdBy,$trimCharlist);
$title=trim($title,$trimCharlist);
$wagerType=trim($wagerType,$trimCharlist);

if (is_null($eventName)) $eventName=$title;
if (is_null($description)) $description=$title;
if (is_null($wagerUnits)) $wagerUnits=1;

header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
$gameID=Utility::generateUniqueIDKey('GAMEID');
$sql=sprintf("insert into go_games (gameID,createdByUserID,createdByUserName,title,eventName,date,description,type,sport,subscriptionClose,wagerType,wagerUnits) values ('%s','%u','%s','%s','%s','%s','%s','%u','%u','%s','%s','%u')",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$createdByUserID),
        mysqli_real_escape_string($link,$createdByUserName),
        mysqli_real_escape_string($link,$title),
        mysqli_real_escape_string($link,$eventName),
        mysqli_real_escape_string($link,$date),
        mysqli_real_escape_string($link,$description),
        mysqli_real_escape_string($link,$type),
        mysqli_real_escape_string($link,$sport),
        mysqli_real_escape_string($link,$subscriptionClose),
        mysqli_real_escape_string($link,$wagerType),
        mysqli_real_escape_string($link,$wagerUnits));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 

if (!$rc) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link));
}
header('HTTP/1.1 200 OK');
$link->close();  /* Close Database */
//return xml 
Utility::emitXML("","insert_game",0);
Utility::emitXML("200","status_code");
Utility::emitXML("ok","status_message");
Utility::emitXML("$gameID","gameid");
Utility::emitXML("","insert_game",0);
ob_end_flush();

exit;

function mydie($message) {
Utility::emitXML("","insert_game",0);
Utility::emitXML("500","status_code");
Utility::emitXML("$message","status_message");
Utility::emitXML("","insert_game",0);
ob_end_flush();
$LOG->log("$message",PEAR_LOG_ERR);

exit;
}
?>
