<?php
ob_start();
/*
  Author: Julio Hernandez-Miyares
  Date: May 2010
  Purpose: Log in - no security at the moment 
  Drops a cookie for userName and userID
  until security is enabled use the jittrdev account added by default
  Modified: By Julio Hernandez-Miyares
  Date: August 22,2010
   Clean up css to make it presentable
   allow registering for first time

*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('go_usersettings.class.php');
require_once('facebook.php');

$LOG=Config::getLogObject();
include('gohtmlhead.php');
include('goheader.php');
$username=$_GET['username'];
$register=strtolower($_GET['register']);

$login =$_GET['login'];
$oauth=$_GET['oauth'];
$access_token=$_GET['access_token'];

// Create our Application instance.
$facebook = new Facebook(array(
  'appId'  => '113817188649294',
  'secret' => 'd0e1c39b00814c1cb4819f5133338c89',
  'cookie' => true,
));

/* Set operation flag depending on user action, and grab the appropriate 
   query parameters
*/
if (!empty($register)) {
  $operation="register";
  $params['newusername'] = $_GET['newusername'];
  $newUserName = $params['newusername'];
  $params['newpassword'] = $_GET['newpassword'];
  $params['newfirstname']= $_GET['newfirstname'];
  $params['newlastname'] = $_GET['newlastname'];
  $network=strtolower($_GET['network']);
} else if (!empty($login)) {
  $operation="login";
  $userName=$_GET['username'];
  $password=$_GET['password'];
} else if (!empty($oauth))
  $operation="getoauthtoken";
else
  $operation="loginform";
if ($operation=="loginform") {
?>
<div id="userlogin">
   <form name="input" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="get">
   <p><strong>**USE jittrdev as a test account which has been setup in all of the Social Networks</strong></p> 
   Login ID:
   <input type="textbox" name="username"  />
   Password: (TODO-implement md5 hashing, currently, cleartext over the wire)
   <input type="password" name="password"  />
   <input type="submit" name="login" value="Login" />
   <br />
</div>
<div id="newuserregister">
   <p>Register as a new User of <strong>GameOn</strong> using the credentials of the networks below</p>
   New User Name:
   <input type="textbox" name="newusername" />
   Password:
   <input type="password" name="newpassword" />
   Verify Password:
   <input type="password" name="newpassword_verifier" />
   <br />
</div>
<div id="selectnetwork">
<label>None Leave for later:</label><input type="radio" name="network" value="none" CHECKED />
<br />
<label>Facebook:</label><input type="radio" name="network" value="facebook" />
<br />
<label>Twitter:</label><input type="radio" name="network" value="twitter" />
<br />
<label>Foursquare:</label><input type="radio" name="network" value="foursquare" />
<br />
</div>
<input type="submit" name="register" value="Register" />
</form>
<?php
   $errorMessage= $_GET['errormessage'];
   if (!empty($errorMessage)) {
     echo("<p id=errormessage>" . $errorMessage . "</p>");
   }
   include 'gofooter.php';
} else if ($operation == "login") {
/*Login using the credentials passed in returns userId if successful, -0- otherwise*/
   $rc = goUserSettings::login($userName,$password);
   if ($rc==0) {
      header("Location:" . $_SERVER['PHP_SELF'] . "?errormessage=" . urlencode("Invalid user name or password"));
      exit;
   }
   $userSettings = new goUserSettings();
   $rc = $userSettings->getUserSettings($userName,'Yes');
   /* TODO - add code to determine if the retrieval failed due to error or because the userid or username not defined. For now assume error */
   if (!rc) {  
   // Server error
      mydie("Error connecting to Database");
   }
   $userID = $userSettings->getuserID();
   setcookie('userid',$userID);
   setcookie('username',$userSettings->getUserName());
   header("Location: " .  Config::getRootDomain() );
} else if ($operation =="register") {
  // make sure we have the minimum necessary populated fields
  if (empty($newUserName) || empty($newPassword) ) {
  }
  
/* first take care of inserting new user into go_user table */
  $rc = insertNewUser($params);
   If (!$rc) {
      header("Location:" . $_SERVER['PHP_SELF'] . "?errormessage=" . urlencode("User name $userName not available"));
      exit;
   } // if
 
//the insert succeeded and the return is the newUserID 
  $newUserID=$rc;
  if ($network != 'none') {
      registerWithSocialNetwork($network);
  } 
  /* Temporary here */
  setcookie('userid',$newUserID);
  setcookie('username',$newUserName);
  header("Location: " . Config::getRootDomain() ); 
  exit;
} else if ($operation =="getoauthtoken") {
  $code = $_GET['code'];
  $access_token = $_GET['access_token'];
  if (Config::getDebug()) $LOG->log("in getoauthtoken operation - code=$code and access_token=$access_token");
}
  if (!empty($code) && empty($access_token)) {
     $url="https://graph.facebook.com/oauth/access_token?client_id=" . Config::getFacebookConsumerKey() . "&redirect_uri=http://jittr.com/jittr/gameon/gologin.php?oauth=yes&client_secret=" . Config::getFacebookConsumerKeySecret()  . "&code=" .$code;
  if (Config::getDebug()) $LOG->log("In oauth flow for FB calling url = " . $url);
  $curl = curl_init($url);
  $opts=Facebook::$CURL_OPTS;
  curl_setopt_array($curl, $opts);
  $result = curl_exec($curl);
  curl_close($curl);
  $oauth=$result;
  if (Config::getDebug()) $LOG->log("In oauth flow for FB value of result= " . $oauth);

  $url="https://graph.facebook.com/me?" . $result;
  if (Config::getDebug()) $LOG->log("Get FB user profile calling url = " . $url);
  $curl = curl_init($url);
  $opts=Facebook::$CURL_OPTS;
  curl_setopt_array($curl, $opts);
  $result = curl_exec($curl);
  curl_close($curl);

  if (Config::getDebug()) $LOG->log("Get FB user profile result= " . $result);
  $rc = insertUserFacebook($result,$oauth);
  header("Location: " .  Config::getRootDomain() ); 
}
ob_end_flush();
exit;
//functions
function registerWithSocialNetwork($network) {
   switch($network) {
     case "facebook":
        registerWithFacebook();
        break;
     case "twitter":
        registerWithTwitter();
        break;
     case "foursquare":
        registerWithFoursquare();
        break;
     default:
    }//switch
} //function

function registerWithFacebook() {
   global $LOG;
   $url="https://graph.facebook.com/oauth/authorize?client_id=" . Config::getFacebookConsumerKey() . "&redirect_uri=http://jittr.com/jittr/gameon/gologin.php?oauth=yes&scope=user_photos,user_videos";
   if (Config::getDebug()) $LOG->log("in registerWithFacebook - calling url = " . $url);
   header("Location:" . $url);
} //registerWithFacebook

function registerWithTwitter() {
}
function registerWithFoursquare() {
}
/* $me is the result from the facebook graph api - in json format
*/
function insertUserFacebook($me, $oauth) {
   global $LOG;
   $me_array = json_decode($me,true); //convert to associative array
   $userName=$me_array['name'];
   $primaryNetworkName="facebook";
   $facebookID=$me_array['id'];
   $OAuthToken=$oauth;
   $url="http://jittr.com/jittr/gameon/go_postnewuser.php?".
               "facebookid=$facebookID" .
               "&primarynetworkname=$primaryNetworkName".
               "&newusername=" . urlencode($userName).
               "&" . $OAuthToken;
   if (Config::getDebug()) $LOG->log("calling from insertUserFacebook using url = " . $url);
   $curl = curl_init($url);
   $opts=Facebook::$CURL_OPTS;
   curl_setopt_array($curl, $opts);
   $result = curl_exec($curl);
   $LOG->log("$result");
   curl_close($curl);
}
/* Insert new record for new user in go_user table 
   returns the userID a unique long
   Modified JHM August 22,2010 
   deal with various errors/issues that can arise from posting
   new userID
   TODO - change to post from get
   TODO - encode the password
*/
function insertNewUser($params) {
   global $LOG;
   $userID = 0;
   $userName = $params['newusername'];
   $password= $params['newpassword'];
   
   $url= Config::getAPIDomain() . "/go_postnewuser.php?" .
               "newusername=" . urlencode($userName) .
               "&password=" . urlencode($password);
   if (Config::getDebug()) $LOG->log("calling insertNewUser using url = " . $url);
   $curl = @curl_init($url);
   $opts=Facebook::$CURL_OPTS;
   curl_setopt_array($curl, $opts);
   $result = @curl_exec($curl);
   $LOG->log("$result");
   @curl_close($curl);
/* Parse the returned XML */
   $document = @simplexml_load_string($result);
   if ($document) {
   //check the return code
      if ($document->status_code == 200) {
          $userID = $document->userid;
      } else return false;
   } //
   return $userID;
}
function mydie($message,$statusCode=500) {
global $LOG;
global $link;

//   ob_end_clean();
   if ($link) $link->close();
   $LOG->log("$message",PEAR_LOG_ERR);
   ob_end_flush();
}
?>
