<?php
ob_start();
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');
header("Content-Type: text/xml");

/* author: Julio Hernandez-Miyares
   date: May 2010
   purpose - get user Information - default is for go_user
   style is different - instantiates an object which is then echo'ed 
   to  stdout with xml output as the style
   modified by Julio Hernandez-Miyares
   date: October 23,2010
   goUserSettings now contains ducketts balance and will be returned via xml
   this script will just echo it and no other changes are necessary
   TODO - add signing for this api as it has user/personal data
   Modified; Julio Hernandez-Miyares
   Date: October 27,2010
   purpose: added login via password 

   Allowable operations - getuserinfo & login
   both return a full goUserSettings xml if successful
*/
$userID    = $_REQUEST['userid'];
$userName  = $_REQUEST['username'];
$operation = strtolower($_REQUEST['operation']);

if (empty($operation)) $operation = 'getuserinfo';
if ($operation == 'login') {
   $password=$_REQUEST['password'];
   $params['password']= $_REQUEST['password'];
   $userID = goUserSettings::login($userName, $password);
   if ($userID <=0) {
      $userSettings = new goUserSettings();
      $userSettings->setOutputFlag('xml');
      header("Content-Type: text/xml");
      $userSettings->setStatusMessage("Not authorized");
      $userSettings->setStatusCode("403");
      echo($userSettings);
      ob_flush();
      exit;
   } 
} //if

$params = array();
$params['operation']=$operation;
if (!empty($userID)) 
  $params['userid'] = $userID;
else if (!empty($userName)) 
  $params['username'] = $userName;

$userSettings = new goUserSettings($params);
if (isset($userSettings)) {
    $userSettings->setOutputFlag('xml');
    header("Content-Type: text/xml");
    echo($userSettings);
}
ob_end_flush();
exit;
?>

