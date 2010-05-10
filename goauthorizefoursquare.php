<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

//Put in the key and secret for your Foursquare app
//Your values will be different than mine
$consumer_key = Config::getFoursquareConsumerKey(); 
$consumer_secret = Config::getFoursquareConsumerKeySecret();
$loginurl = "";

//Includes the foursquare-asyc library files
require_once('foursquare/EpiCurl.php');
require_once('foursquare/EpiOAuth.php');
require_once('foursquare/EpiFoursquare.php');

$query = $_GET['query'];
if ($query=="fbauthorize") {
session_start();
try{
  $foursquareObj = new EpiFoursquare($consumer_key, $consumer_secret);
  $results = $foursquareObj->getAuthorizeUrl();
  $loginurl = $results['url'] . "?oauth_token=" . $results['oauth_token'];
  $_SESSION['secret'] = $results['oauth_token_secret'];
} catch (Execption $e) {
  //If there is a problem throw an exception
}
header("Location:" . $loginurl);
exit;
}

$oauth_token=$_GET['oauth_token'];
if (!empty($oauth_token)) {
session_start();
$foursquareObj = new EpiFoursquare($consumer_key, $consumer_secret);
$foursquareObj->setToken($_REQUEST['oauth_token'],$_SESSION['secret']);
$token = $foursquareObj->getAccessToken();
$foursquareObj->setToken($token->oauth_token, $token->oauth_token_secret);
try {
   //Making a call to the API
      $foursquareTest = $foursquareObj->get_user();
      $userSettings = new goUserSettings();
      $responseUserArray = $foursquareTest->response['user'];
      $fquserID =  $responseUserArray['id'];
      $fqphoto = $responseUserArray['photo'];
      $userSettings->updateFoursquareOAuth(1,$fquserID,$fqphoto,$token->oauth_token,$token->oauth_token_secret);
      print_r($foursquareTest->response);
    echo('working with array');
  $a=$foursquareTest->response['user'];
   echo("user id " . $a['id']);
} catch (Exception $e) {
     echo "Error: " . $e;
}
header("Location:http://jittr.com/jittr/gameon");
}

/*
echo "<a href='" . $loginurl . "'>Login Via Foursquare</a>";  //Display the Foursquare login link
echo "<br>";
//This is your OAuth token and secret generated above
//The OAuth token is part of the Foursquare link above
//They are dynamic and will change each time you refresh the page
//If everything is working correctly both of these will show up when you open index.php
var_dump($results['oauth_token']);
echo "<br>";
var_dump($_SESSION['secret']);
*/
?>
