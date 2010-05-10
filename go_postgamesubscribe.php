<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: insert a new subscription to a wager 
   A few checks besides normal edits have to be performed before the record is inserted
      Game has to exist
      It must not be closed for subscriptions. Initially will be simple date/time check without regard to all of the 
      timing issues of locale/timezone
   using GET instead of POST even though these represent changes to the Database
*/
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
/* retrieve query parameters
   Required parameters are
   gameid 
   userid
   wagerUnits
*/
$LOG=Config::getLogObject();
$trimCharlist="\x00..\x1F";
$gameID = $_GET['gameid'];
$userID = $_GET['userid'];
$wagerUnits=$_GET['wagerunits'];

/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
*/
if (!isset($userID,$gameID,$wagerUnits)) {
   mydie("paramaters not complete");
}   
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
//check if the subscription is still open for this game- currently only based on time
//eventually, will also be based on network of users
//attempting to retrieve the record will also perform check for existense.
$sql=sprintf("select * from go_games where gameID='%s'",mysqli_real_escape_string($link,$gameID));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);

$cursor = mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link));
}
$row = mysqli_fetch_assoc($cursor);
if (!$row) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie("GameID $gameID does not exist");
}
$subscriptionClose=$row['subscriptionClose']; //is a mysql timestamp

$sql=sprintf("insert into go_gamesSubscribers (gameID,userID,wagerUnits) values ('%u','%u','%u')",
        mysqli_real_escape_string($link,$gameID),
        mysqli_real_escape_string($link,$userID),
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
Utility::emitXML("","insert_game_subscriber",0);
Utility::emitXML("200","status_code");
Utility::emitXML("ok","status_message");
Utility::emitXML("$gameID","gameid");
Utility::emitXML("$userID","userid");
Utility::emitXML("$numberOfSubscribers","numberofsubscribers");
Utility::emitXML("","insert_game_subscriber",0);
ob_end_flush();

exit;

function mydie($message,$statusCode=500) {
Utility::emitXML("","insert_game_subscriber",0);
Utility::emitXML("$statusCode","status_code");
Utility::emitXML("$message","status_message");
Utility::emitXML("","insert_game_subscriber",0);
$LOG->log("$message",PEAR_LOG_ERR);

ob_end_flush();
exit;
}
?>
