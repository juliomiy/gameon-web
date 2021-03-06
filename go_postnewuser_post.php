<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: May 24, 2010
   Purpose: insert new  user and optional settings in go_user and go_userSettings
   using GET instead of POST even though these represent changes to the Database
   Modified: by Julio Hernandez-Miyares on August 22,2010
    make newUserName mandatory to be able to insert a new user record 
    check that newUserName is available
   Modified: by JHM August 29,2010
     added, firstname, lastname and email to the xml return on success
   Modified: by JHM September 1,2010
     added operation flag - update, 
   Modified: by JHM November 7,2010
     insert go_userBank record for the user with default bank Balance

   TODO - add delete user
   TODO - confirm not a duplicate email address
   TODO - deal password encrytion
   TODO - make password mandatory if using Betsquare name space for registration
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_userbank.class.php');
/* retrieve query parameters
   Required parameters are
   primaryNetworkName - currently either facebook, twitter or foursquare
*/
$userID = -1; //actual userID will be obtained from the insert into go_user

$LOG=Config::getLogObject();
//key of array is the Query parm and value is the field name in the database
/*
$parmArray = array("foursquareid"=>"foursquareID",
                   "twitterid"=>"twitterID",
		   "facebookid"=>"facebookID",
                   "foursquaredefault"=>"foursquareDefault",
                   "twitterdefault"=>"twitterDefault",
		   "facebookdefault"=>"facebookDefault");
foreach($parmArray as $key => $value) {
    if (empty($_POST[$key])) {
       echo("$key is empty\n");
       $list .=$value .",";
    }
    else
       echo("$key is not empty\n");
}
echo("$list");
exit;
*/

$operation = $_POST['operation'];
if (empty($operation)) $operation='insert'; //default to insert which is the current functionality
 
$userName = $_POST['username'];
$newUserName = $_POST['newusername'];
$password= $_POST['password'];
$firstName = $_POST['firstname'];
$lastName = $_POST['lastname'];
$email= $_POST['email'];
$primaryNetworkName = $_POST['primarynetworkname'];
$primaryNetworkID= $_POST['primarynetworkid'];
$foursquareID = $_POST['foursquareid'];
$twitterID = $_POST['twitterid'];
$facebookID= $_POST['facebookid'];
$aimID= $_POST['aimid'];
$icqID= $_POST['icqid'];
$avatarURL = $_REQUEST['avatarurl'];

$foursquareDefault = $_POST['foursquaredefault'];
$twitterDefault = $_POST['twitterdefault'];
$facebookDefault= $_POST['facebookdefault'];

$OAuthToken= $_POST['oauthtoken'];
//temporary for facebook
//$OAuthToken= $_POST['access_token'];

$OAuthTokenSecret= $_POST['oauthtokensecret'];

//need to check what network these tokens are for to set the appropriate variables
switch ($primaryNetworkID) {
   case TWITTER_NETWORK:
       $twitterOAuthToken = $OAuthToken;
       $twitterOAuthTokenSecret = $OAuthTokenSecret;
       $twitterImageUrl= $avatarURL;
       break; 
   case FOURSQUARE_NETWORK:
       $foursquareOAuthToken= $OAuthToken;
       $foursquareOAuthTokenSecret= $OAuthTokenSecret; 
       $foursquareImageUrl= $avatarURL;
       break;
   case FACEBOOK_NETWORK:
       $facebookOAuthToken= $OAuthToken;
       $facebookImageUrl= $avatarURL;
       break;
   case BETSQUARED_NETWORK:
       if (empty($password)) {
//          mydie("Password missing");
       };
       break;
} //switch



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
$userID = insertNewUser($newUserName,$password,$firstName, $lastName,$email,$primaryNetworkID, $primaryNetworkName,$link);
 
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
Utility::emitXML("$newUserName","username");
Utility::emitXML("$firstName","firstname");
Utility::emitXML("$lastName","lastname");
Utility::emitXML("$email","email");
Utility::emitXML("$primaryNetworkName","networkname");
Utility::emitXML("","insert_user",0);
ob_end_flush();

exit;

/*Add new User to the go_user table
  if successful return the userID
  insert new go_userBank record - the top level Bank Record
*/
function insertNewUser($newUserName, $password, $firstName, $lastName, $email, $primaryNetworkID,$primaryNetworkName,$link) {
   global $LOG;
//define insert sql  into go_user
   $sql = sprintf("insert into go_user (userName,firstName, lastName, email, primaryNetworkName,password) values ('%s','%s','%s','%s','%s','%s')",
           mysqli_real_escape_string($link,$newUserName),
           mysqli_real_escape_string($link,$firstName),
           mysqli_real_escape_string($link,$lastName),
           mysqli_real_escape_string($link,$email),
           mysqli_real_escape_string($link,$primaryNetworkName),
           mysqli_real_escape_string($link,$password));
   if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
   $rc = mysqli_query($link,$sql); 
   if (!$rc) {
   // Server error
      mydie(mysqli_error($link) .  " executing sql $sql");
   } //if
   if (($tempuserID = mysqli_insert_id($link)) > 0) {
      $userID = $tempuserID;
   } else {
      mydie("Error obtaining new userID");
   }
   
//insert UserBank Record - added JHM 11/7/2010
   $userBank = new UserBank();
   $userBank->insertUserBank($userID);

   return $userID;
} //insertNewUser

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
   ob_end_clean();
   if ($link) $link->close();
   $LOG->log("$message",PEAR_LOG_ERR);

   ob_start();
   header("Content-Type: text/xml");
//   header('HTTP/1.1 $statusCode Internal Server Error');
   Utility::emitXML("","insert_user",0);
   Utility::emitXML("$statusCode","status_code");
   Utility::emitXML("$message","status_message");
   Utility::emitXML("","insert_user",0);
   ob_end_flush();

   exit;
} //mydie
?>
