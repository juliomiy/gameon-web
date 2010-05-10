<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

//Includes the foursquare-asyc library files
require_once('foursquare/EpiCurl.php');
require_once('foursquare/EpiOAuth.php');
require_once('foursquare/EpiFoursquare.php');

//Put in the key and secret for your Foursquare app
//Your values will be different than mine
$consumer_key = Config::getFoursquareConsumerKey();
$consumer_secret = Config::getFoursquareConsumerKeySecret();

session_start();
$foursquareObj = new EpiFoursquare($consumer_key, $consumer_secret);
$foursquareObj->setToken($_REQUEST['oauth_token'],$_SESSION['secret']);
$token = $foursquareObj->getAccessToken();
$foursquareObj->setToken($token->oauth_token, $token->oauth_token_secret);

try {
   //Making a call to the API
   $foursquareTest = $foursquareObj->get_user();
   print_r($foursquareTest->response);
 } catch (Exception $e) {
   echo "Error: " . $e;
 }

?>
