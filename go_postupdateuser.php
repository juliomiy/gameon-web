<?php
ob_start();
/* Author: Julio Hernandez-Miyares
   Date: October 25,2010
   Purpose: update user settings and bank balance
   TODO - do I really want bank balance transaction in this webservice
   For convenience nd quick development, yes for now
 
   After update (Post) will call and repopulate userSettings class and return via xml as the response to the post
  
   TODO - add delete user
   TODO - confirm not a duplicate email address
   TODO - deal password encrytion
   TODO - make password mandatory if using Betsquare name space for registration
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

/* retrieve query parameters
   Required parameters are
   primaryNetworkName - currently either facebook, twitter or foursquare
*/
$userID = -1; //actual userID will be obtained from the insert into go_user

$LOG=Config::getLogObject();

$operation = strtolower($_REQUEST['operation']);
$userID = $_REQUEST['userid'];

if (empty($operation)) $operation='insert'; //default to insert which is the current functionality

/* transactionID  - the transactionID from the service provider
   transactionType - purchase , transfer, etc - currently only purchase supported
   transactionProvider - the ecommerce solution used - currently only paypal supported

*/
if ($operation == 'updatebankbalance') {
   $transactionID = $_REQUEST['transactionid'];
   $transactionType = $_REQUEST['transactiontype'];
   $transactionProvider = $_REQUEST['transactionprovider'];

} 
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

$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link)
{
   // Server error
   mydie("Error connecting to Database");
}
$sql = getQuery($operation,$link,$_REQUEST);
if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
$rc = mysqli_multi_query($link,$sql);
if (!$rc) {
}
if ($operation == 'updatebankbalance') {
} else {
}

header('HTTP/1.1 200 OK');
$link->close();  /* Close Database */

//return xml 
$userSettings = new goUserSettings($userID);
if (isset($userSettings)) {
    $userSettings->setOutputFlag('xml');
    echo($userSettings);
}
ob_end_flush();
exit;

/*Add new User to the go_user table
 return sql based on operation
*/
function getQuery($operation,$link,$params) {

$userID = $params['userid'];
$transactionID = $params['transactionid'];
$transactionTypeID = $params['transactiontypeid'];
$transactionTypeName = $params['transactiontypename'];
$transactionAmountCurrency= $params['transactionamountcurrency'];
$transactionAmountDucketts= $params['transactionamountducketts'];
$transactionProvider = $params['transactionprovider'];

$sql = sprintf("update go_userBank b set b.bankBalance = b.bankBalance + '%u' where b.userID = '%u';",
           mysqli_real_escape_string($link,$transactionAmountDucketts),
           mysqli_real_escape_string($link,$userID)
);

$sql .= sprintf("insert into go_userBankDetail (userID, transactionID,transactionTypeID,transactionTypeName,transactionAmount) 
           values ('%u','%s','%u','%s','%u')",
           mysqli_real_escape_string($link,$userID),
           mysqli_real_escape_string($link,$transactionID),
           mysqli_real_escape_string($link,$transactionTypeID),
           mysqli_real_escape_string($link,$transactionTypeName),
           mysqli_real_escape_string($link,$transactionAmountCurrency)
);
return $sql;
} //getQuery

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
