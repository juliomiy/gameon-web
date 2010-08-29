<?php
ob_start();
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');
header("Content-Type: text/xml");

$userID    = $_GET['userid'];
$userName  = $_GET['username'];

$params = array();
if (!empty($userID)) 
  $params['userid'] = $userID;
else if (!empty($userName)) 
  $params['username'] = $userName;

$userSettings = new goUserSettings($params);
if (isset($userSettings)) {
    $userSettings->setOutputFlag('xml');
    echo($userSettings);
}
ob_end_flush();
?>

