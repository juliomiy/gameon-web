<?php
//Put in the key and secret for your Foursquare app
//Your values will be different than mine
$consumer_key='AROV4OCBZMPAMSHLCF3LLYWQQ0W0F2WH1K1BDZDGBW1OFJSM';
$consumer_secret='IT5NN1BSTKCORZ3YVC32BRAJD5O4201TCCAGAK2KAMUYHOQD';

//$consumer_key = "XB1NE31CJ4U22EF2GA53C4ULR3SL2BG21G1M5VTRCZ3K1XW5";
//$consumer_secret = "3RHRD1KJLGFFHKDMD4SCE11NHNDCFUPOIPOQW4VGKLADFKC1";
$loginurl = "";

//Includes the foursquare-asyc library files
require_once('EpiCurl.php');
require_once('EpiOAuth.php');
require_once('EpiFoursquare.php');

//session_start();
try{
  $foursquareObj = new EpiFoursquare($consumer_key, $consumer_secret);
    $results = $foursquareObj->getAuthorizeUrl();
      $loginurl = $results['url'] . "?oauth_token=" . $results['oauth_token'];
	echo($loginurl);
        $_SESSION['secret'] = $results['oauth_token_secret'];
	} catch (Execption $e) {
	  echo($e);
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

