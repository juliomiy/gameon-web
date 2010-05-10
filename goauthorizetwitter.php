<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

require_once('EpiCurl.php');
require_once('EpiOAuth.php');
require_once('EpiTwitter.php');
require_once('secret.php');

$twitterObj = new EpiTwitter($consumer_key, $consumer_secret);
$return = $twitterObj->getAuthorizationUrl();
header("Location:" . $twitterObj->getAuthorizationUrl());
?>

