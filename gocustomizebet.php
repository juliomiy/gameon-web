<?php
/* Author: Julio Hernandez-Miyares
   Date: August 19,2010
   Purpose: customize a bet on a game - a team sport from the
            go_publicGames table
*/
ob_start();
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');
require_once('go_game.class.php');
require_once('go_publicgame.class.php');
$LOG = Config::getLogObject();

/* is this a fresh invoke or based on user selection */
$action= $_GET['action'];
if (!empty($action)) {
   $params['publicgameid'] = $_GET['publicgameid'];
   $publicGameID  = $_GET['publicgameid'];
} //if

/*
print_r( $_GET);
*/
include('gohtmlhead.php');
include('goheader.php');

$gameObject = new goPublicGame($publicGameID);
$gameObject->setOutputFlag('xml');
//echo($gameObject);
// present page to customize the game
if ($action = 'customize') {
}
echo('<div id="gameblock">');
echo('<p>Event Name :' .  $gameObject->getEventName() . '</p>');
echo('<p>Event Date:' . $gameObject->getEventDate()  . '</p>');
echo('<input type="radio" name="teams" value="' .  $gameObject->getVisitingTeamID() . '"/>' . $gameObject->getVisitingTeam());
echo('<input type="radio" name="teams" value="' .  $gameObject->getHomeTeamID() . '"/>' . $gameObject->getHomeTeam());
echo('</div>');
include('gofooter.php');
ob_end_flush();

exit;
?>
