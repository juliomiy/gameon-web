<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('Log.php');
require_once('config.class.php');
/* Author:Julio Hernandez-Miyares
   date: May 11,2010
   Purpose: view user's profile (go_user/go_userSettings)
   TODO - enable security to only allow editing by the authenticated user
*/
include('gohtmlhead.php');
include('goheader.php');
//if (!isset($_GET['title'])) {
?>
<div id="personal">
<h2><?php echo("User $userName - you have ID of $userID"); ?></h2>
</div>
<br />
Facebook:
<input type="textbox" name="network" value="facebook" />
<br />
Twitter:
<input type="textbox" name="network" value="twitter" />
<br />
FourSquare
<input type="textbook" name="network" value="foursquare" />
<br />
<?php
include('goheader.php');
ob_end_flush();
?>
