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

$twitterObj->setToken($_GET['oauth_token']);
$token = $twitterObj->getAccessToken();
$twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);
$twitterInfo= $twitterObj->get_accountVerify_credentials();
$twitterInfo->response;
//echo "Your twitter username is {$twitterInfo->screen_name} and your profile picture is <img src=\"{$twitterInfo->profile_image_url}\">";
//$tok = file_put_contents('tok', $token->oauth_token);
//$sec = file_put_contents('sec', $token->oauth_token_secret);
$tok = $token->oauth_token;
$sec = $token->oauth_token_secret;
$twitterID = $twitterInfo->screen_name;
$imageUrl= $twitterInfo->profile_image_url;
$userSettings = new goUserSettings();
$userID=1;
$userSettings->updateTwitterOAuth($userID,$twitterID,$imageUrl, $sec,$tok);

echo "Your twitter username is {$twitterInfo->screen_name} and your profile picture is <img src=\"{$twitterInfo->profile_image_url}\">";
/*echo("<twitter><oauth_token>" . $token->oauth_token . "</oauth_token");
echo("<oauth_token_secret>" . $token->oauth_token_secret . "</oauth_token_secret>");
echo("<twitter_userid>" . $twitterInfo->screen_name . "</twitter_userid>");
echo("</twitter>");
*/
?>

