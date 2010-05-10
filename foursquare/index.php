<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('utility.class.php');
require_once('go_usersettings.class.php');

//Put in the key and secret for your Foursquare app
//Your values will be different than mine
$consumer_key = "AROV4OCBZMPAMSHLCF3LLYWQQ0W0F2WH1K1BDZDGBW1OFJSM";
$consumer_secret = "IT5NN1BSTKCORZ3YVC32BRAJD5O4201TCCAGAK2KAMUYHOQD";
$loginurl = "";

//Includes the foursquare-asyc library files
require_once('foursquare/EpiCurl.php');
require_once('foursquare/EpiOAuth.php');
require_once('foursquare/EpiFoursquare.php');

session_start();
try{
  $foursquareObj = new EpiFoursquare($consumer_key, $consumer_secret);
  $results = $foursquareObj->getAuthorizeUrl();
  $loginurl = $results['url'] . "?oauth_token=" . $results['oauth_token'];
  $_SESSION['secret'] = $results['oauth_token_secret'];
} catch (Execption $e) {
  //If there is a problem throw an exception
}

echo "<a href='" . $loginurl . "'>Login Via Foursquare</a>";  //Display the Foursquare login link
echo "<br>";
//This is your OAuth token and secret generated above
//The OAuth token is part of the Foursquare link above
//They are dynamic and will change each time you refresh the page
//If everything is working correctly both of these will show up when you open index.php
var_dump($results['oauth_token']);
echo "<br>";
var_dump($_SESSION['secret']);

?>
