<?php
ob_start();
/*
  Author: Julio Hernandez-Miyares
  Date: May 2010
  Purpose: Log in - no security at the moment 
  Drops a cookie for userName and userID
  until security is enabled use the jittrdev account added by default
*/
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');

include('gohtmlhead.php');
include('goheader.php');
$username=$_GET['username'];
$register=$_GET['register'];
$login =$_GET['login'];
$oauth=$_GET['oauth'];

if (!empty($register)) 
  $operation="register";
else if (!empty($login))
  $operation="login";
else if (!empty($oauth))
  $operation="getoauthtoken";
else
  $operation="loginform";
if ($operation=="loginform") {
?>
<form name="input" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="get">
<p><strong>**USE jittrdev as a test account which has been setup in all of the Social Networks</strong></p> 
Login ID:
<input type="textbox" name="username"  />
<input type="submit" name="login" value="Login" />
<br />
<p>Register as a new User using the credentials of the networks below</p>

Facebook:<input type="radio" name="network" value="facebook" CHECKED />
<br />
Twitter:<input type="radio" name="network" value="twitter" />
<br />
Foursquare:<input type="radio" name="network" value="foursquare" />
<br />
<input type="submit" name="register" value="Register" />
</form>
<?php
   include 'gofooter.php';
} else if ($operation == "login") {
   $userName = $_GET['username'];
   $userSettings = new goUserSettings();
   $rc = $userSettings->getUserSettings($userName,'Yes');
   /* TODO - add code to determine if the retrieval failed due to error or because the userid or username not defined. For now assume error */
   if (!rc) {  
   // Server error
      header('HTTP/1.1 500 Internal Server Error');
      die("Error connecting to Database");
   }
   $userID = $userSettings->getuserID();
   setcookie('userid',$userID);
   setcookie('username',$userSettings->getUserName());
   header("Location:http://jittr.com/jittr/gameon");
} else if ($operation =="register") {
  $url="https://graph.facebook.com/oauth/authorize?client_id=" . Config::getFacebookConsumerKey() . "&redirect_uri=http://jittr.com/jittr/gameon/gologin.php?oauth=yes&scope=user_photos,user_videos";
  header("Location:" . $url);
} else if ($operation =="getoauthtoken") {
  $code = $_GET['code'];
  $access_token = $_GET['access_token'];

  if (!empty($code)) {
     $url="https://graph.facebook.com/oauth/access_token?client_id=" . Config::getFacebookConsumerKey() . "&redirect_uri=http://jittr.com/jittr/gameon/gologin.php?oauth=yes&client_secret=" . Config::getFacebookConsumerKeySecret()  . "& code=" .$code;
  header("Location:" . $url);
  } else if (!empty($access_token)) {
    $url="https://graph.facebook.com/me?access_token=" . $access_token;
    //$url= "http://jittr.com/jittr/gameon";
  }
  echo("In getoauthtoken operation - url = $url");
  //header("Location:" . $url);
}
ob_end_flush();
?>
