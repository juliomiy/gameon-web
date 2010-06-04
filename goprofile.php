<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('Log.php');
require_once('config.class.php');
require_once('go_usersettings.class.php');
/* Author:Julio Hernandez-Miyares
   date: May 11,2010
   Purpose: view user's profile (go_user/go_userSettings)
   TODO - enable security to only allow editing by the authenticated user
*/
include('gohtmlhead.php');
include('goheader.php');
$userID = $_GET['userid'];
if (!empty($userID)) {
   $userSettings = new goUserSettings($userID);
   $userName = $userSettings->getUserName();
}
echo('<div id="personal">');
echo("<h2>User $userName - you have ID of $userID</h2>");
echo("</div></br>");
echo("<p>Facebook:");
echo('<input type="textbox" name="facebookid" value="' . $userSettings->getFacebookUserID() . '" />');
echo('<input type="textbox" name="facebookoauthtoken" value="' . $userSettings->getFacebookOAuthToken() . '" />');
echo('<input type="textbox" name="facebookoauthtokensecret" value="' . $userSettings->getFacebookOAuthTokenSecret() . '" />');
echo('<input type="textbox" name="facebookimageurl" value="' . $userSettings->getFacebookProfileImageUrl() . '"/></p>');
echo('<p>Twitter:');
echo('<input type="textbox" name="twitterid" value="' . $userSettings->getTwitterUserID() . '" />');
echo('<input type="textbox" name="twitteroauthtoken" value="' . $userSettings->getTwitterOAuthToken() . '" />');
echo('<input type="textbox" name="twitteroauthtokensecret" value="' . $userSettings->getTwitterOAuthTokenSecret() . '" /');
echo('<input type="textbox" name="twittermageurl" value="' . $userSettings->getTwitterProfileImageUrl() . '" /></p>');
echo('<p>FourSquare:');
echo('<input type="textbox" name="foursquareid" value="' . $userSettings->getFoursquareUserID() . '" />');
echo('<input type="textbox" name="foursquareoauthtoken" value="' . $userSettings->getFoursquareOAuthToken() . '" />');
echo('<input type="textbox" name="foursquareoauthtokensecret" value="' . $userSettings->getFoursquareOAuthTokenSecret() . '" />');
echo('<input type="textbox" name="foursquareimageurl" value="' . $userSettings->getFoursquareProfileImageUrl() . '" /></p>');
include('goheader.php');
ob_end_flush();
?>
