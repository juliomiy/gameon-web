<?php
ob_start();
/* goprofile.php
   Author: Julio Hernandez-Miyares
   modified: August 24,2010

   present user profile - go_user and go_userSettings 
   keyed by userID , userName (Jittr)
   TODO - allow lookup via social network ids
*/
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');
/* Author:Julio Hernandez-Miyares
   date: May 11,2010
   Purpose: view user's profile (go_user/go_userSettings)
   TODO - enable security to only allow editing by the authenticated user
*/
include('gohtmlhead.php');
include('goheader.php');
$userID = $_REQUEST['userid'];
$operation = strtolower($_REQUEST['bsupdateuser']);

/* updating user profile */
if ($operation == 'update') {
}

if (!empty($userID)) {
   $userSettings = new goUserSettings($userID);
   $userName = $userSettings->getUserName();
} else {
  header("location: " . Config::getErrorPage() . "/" . "user_not_found");
  exit;
}
echo('<form name="input" action="' . $_SERVER['PHP_SELF'] . '" method="POST">');
echo('<div id="personal">');
echo("<h2>User $userName - you have ID of $userID</h2>");
echo('First Name: <input type="textbox" name="bsfirstname" value="' . $userSettings->getFirstName() . '" />');
echo('Last Name: <input type="textbox" name="bslastname" value="' . $userSettings->getLastName() . '" />');
echo('Email: <input type="textbox" name="bsemail" value="' . $userSettings->getEmail() . '" />');
echo('<input type="submit" name="bsupdateuser" value="Update" />');
echo("</div></br>");
echo("</form>");
echo('<div id="socialnetwork">');
echo("<h3>Facebook:");
echo('ID: <input type="textbox" name="facebookid" value="' . $userSettings->getFacebookUserID() . '" />');
echo('<input type="textbox" name="facebookoauthtoken" value="' . $userSettings->getFacebookOAuthToken() . '" />');
echo('<input type="textbox" name="facebookoauthtokensecret" value="' . $userSettings->getFacebookOAuthTokenSecret() . '" />');
echo('Image URL: <input type="textbox" name="facebookimageurl" value="' . $userSettings->getFacebookProfileImageUrl() . '"/></h3>');
echo('<h3>Twitter:');
echo('ID: <input type="textbox" name="twitterid" value="' . $userSettings->getTwitterUserID() . '" />');
echo('<input type="textbox" name="twitteroauthtoken" value="' . $userSettings->getTwitterOAuthToken() . '" />');
echo('<input type="textbox" name="twitteroauthtokensecret" value="' . $userSettings->getTwitterOAuthTokenSecret() . '" /');
echo('Image Url: <input type="textbox" name="twittermageurl" value="' . $userSettings->getTwitterProfileImageUrl() . '" /></h3>');
echo('<h3>FourSquare:');
echo('ID: <input type="textbox" name="foursquareid" value="' . $userSettings->getFoursquareUserID() . '" />');
echo('<input type="textbox" name="foursquareoauthtoken" value="' . $userSettings->getFoursquareOAuthToken() . '" />');
echo('<input type="textbox" name="foursquareoauthtokensecret" value="' . $userSettings->getFoursquareOAuthTokenSecret() . '" />');
echo('Image Url: <input type="textbox" name="foursquareimageurl" value="' . $userSettings->getFoursquareProfileImageUrl() . '" /></h3>');
echo("</div></br>");
include('gofooter.php');
ob_end_flush();
?>
