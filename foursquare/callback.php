<?php

//Put in the key and secret for your Foursquare app
//Your values will be different than mine
$consumer_key = "AROV4OCBZMPAMSHLCF3LLYWQQ0W0F2WH1K1BDZDGBW1OFJSM";
$consumer_secret = "IT5NN1BSTKCORZ3YVC32BRAJD5O4201TCCAGAK2KAMUYHOQD";

//Includes the foursquare-asyc library files
require_once('EpiCurl.php');
require_once('EpiOAuth.php');
require_once('EpiFoursquare.php');

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
