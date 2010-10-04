<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: May 24, 2010
   Purpose: insert new  user and optional settings in go_user and go_userSettings
   using GET instead of POST even though these represent changes to the Database
   TODO: Change to POST 
   Modified: by Julio Hernandez-Miyares on August 22,2010
    make newUserName mandatory to be able to insert a new user record 
    check that newUserName is available
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
/* retrieve query parameters
   Required parameters are
   primaryNetworkName - currently either facebook, twitter or foursquare
*/
$userID = -1; //actual userID will be obtained from the insert into go_user

$LOG=Config::getLogObject();
//key of array is the Query parm and value is the field name in the database
/*
$_GET['foursquareid']="test";
$_GET['twitterid']="test2";
$_GET['facebookid']="test3";

$parmArray = array("foursquareid"=>"foursquareID",
                   "twitterid"=>"twitterID",
		   "facebookid"=>"facebookID",
                   "foursquaredefault"=>"foursquareDefault",
                   "twitterdefault"=>"twitterDefault",
		   "facebookdefault"=>"facebookDefault");
foreach($parmArray as $key => $value) {
    if (empty($_GET[$key])) {
       echo("$key is empty\n");
       $list .=$value .",";
    }
    else
       echo("$key is not empty\n");
}
echo("$list");
exit;
*/

$userName = $_GET['username'];
$newUserName = $_GET['newusername'];
$password= $_GET['password'];
$email = $_GET['email'];
$primaryNetworkName = $_GET['primarynetworkname'];
$primaryNetworkID= $_GET['primarynetworkid'];
$foursquareID = $_GET['foursquareid'];
$twitterID = $_GET['twitterid'];
$facebookID= $_GET['facebookid'];
$aimID= $_GET['aimid'];
$icqID= $_GET['icqid'];

$foursquareDefault = $_GET['foursquaredefault'];
$twitterDefault = $_GET['twitterdefault'];
$facebookDefault= $_GET['facebookdefault'];

$OAuthToken= $_GET['oauthtoken'];
//temporary for facebook
$OAuthToken= $_GET['access_token'];

$OAuthTokenSecret= $_GET['oauthtokensecret'];

//need to check what network these tokens are for to set the appropriate variables
if ($primaryNetworkName == "twitter") {
   $twitterOAuthToken = $OAuthToken;
   $twitterOAuthTokenSecret = $OAuthTokenSecret; 
} else if ($primaryNetworkName == "foursquare") {
   $foursquareOAuthToken= $OAuthToken;
   $foursquareOAuthTokenSecret= $OAuthTokenSecret; 
} else if ($primaryNetworkName="facebook") {
     $facebookOAuthToken= $OAuthToken;
}

$foursquareImageUrl= $_GET['foursquareimageurl'];
$twitterImageUrl= $_GET['twitterimageurl'];
$facebookImageUrl= $_GET['facebookimageurl'];

$trimCharlist="\x00..\x1F";

header("Content-Type: text/xml");
/* verify have enough to continue - set defaults for missing parameters as long as they are not
   mandatory
   At the moment, no required Fields - If all fields are null, a record will be inserted and
   the new userID returned.
*/
if (empty($primaryNetworkName)) {
   mydie("Query parameters missing");
}
//Implement transactional semantics - the insert into go_user and go_userSettings need to be atomic
//Perhaps a stored procedure is the best way to implement

/*echo '<?xml version="1.0" encoding="UTF-8"?>';
*/
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   mydie("Error connecting to Database");
}
//check if the userName is available
if (!userNameAvailable($newUserName,$link)) {
   mydie("UserName $newUserName not available",410);
} //if
 
//define insert sql  into go_user
$sql = sprintf("insert into go_user (userName,primaryNetworkID,primaryNetworkName,password) values ('%s','%u','%s','%s','%s')",
        mysqli_real_escape_string($link,$newUserName),
        mysqli_real_escape_string($link,$primaryNetworkID),
        mysqli_real_escape_string($link,$primaryNetworkName),
        mysqli_real_escape_string($link,$email),
        mysqli_real_escape_string($link,$password));


if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 
if (!$rc) {
   // Server error
   mydie(mysqli_error($link));
}
if (($tempuserID = mysqli_insert_id($link)) > 0) {
   $userID = $tempuserID;
} else {
   mydie("Error obtaining new userID");
}
//define and insert the go_userSettings record
$sql=sprintf("insert into go_userSettings (userID,facebookID,twitterID,foursquareID,facebookOAuthToken,twitterOAuthToken,foursquareOAuthToken,twitterOAuthTokenSecret,foursquareOAuthTokenSecret,facebookImageUrl,twitterImageUrl,foursquareImageUrl) values ('%u','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
        mysqli_real_escape_string($link,$userID),
        mysqli_real_escape_string($link,$facebookID),
        mysqli_real_escape_string($link,$twitterID),
        mysqli_real_escape_string($link,$foursquareID),
        mysqli_real_escape_string($link,$facebookOAuthToken),
        mysqli_real_escape_string($link,$twitterOAuthToken),
        mysqli_real_escape_string($link,$foursquareOAuthToken),
        mysqli_real_escape_string($link,$twitterOAuthTokenSecret),
        mysqli_real_escape_string($link,$foursquareOAuthTokenSecret),
        mysqli_real_escape_string($link,$facebookImageUrl),
        mysqli_real_escape_string($link,$twitterImageUrl),
        mysqli_real_escape_string($link,$foursquareImageUrl));
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_query($link,$sql); 
if (!$rc) {
   // Server error
   mydie(mysqli_error($link));
}

header('HTTP/1.1 200 OK');
$link->close();  /* Close Database */
//return xml 
Utility::emitXML("","insert_user",0);
Utility::emitXML("200","status_code");
Utility::emitXML("ok","status_message");
Utility::emitXML("$userID","userid");
Utility::emitXML("$userName","username");
Utility::emitXML("$primaryNetworkName","networkname");
Utility::emitXML("","insert_user",0);
ob_end_flush();

exit;
/* Check if the requested userName is available.
   Return True if the userName is available, false otherwise
*/
function userNameAvailable($userName,$link) {

   global $LOG;
   $userName = strtolower($userName);
//   $sql = sprintf("select count(*) as countOfUserName from go_user where userName = '%s'",
//        mysqli_real_escape_string($link,$userName));
   $sql = sprintf("select userName from go_user where userName = '%s'",
        mysqli_real_escape_string($link,$userName));
 
   if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
   $cursor = mysqli_query($link,$sql); 
   if (!$cursor) {
   // Server error
      mydie(mysqli_error($link) . "  executing sql $sql");
   } //if
   $countOfUserName = $cursor->num_rows; 
//   if ($row = @mysqli_fetch_assoc($cursor) == null) mydie(mysqli_error($link) . " executing sql $sql");
//   $countOfUserName = $row['countOfUserName'];
   $cursor->close();
   if ($countOfUserName > 0 ) return false;
   else return true;

} //userNameExists

function mydie($message,$statusCode=500) {
global $LOG;
global $link;
//   ob_end_clean();
   if ($link) $link->close();
   $LOG->log("$message",PEAR_LOG_ERR);

//   ob_start();
   header("Content-Type: text/xml");
   header('HTTP/1.1 $statusCode Internal Server Error');
   Utility::emitXML("","insert_user",0);
   Utility::emitXML("$statusCode","status_code");
   Utility::emitXML("$message","status_message");
   Utility::emitXML("","insert_user",0);
   ob_end_flush();

   exit;
} //mydie
?>
