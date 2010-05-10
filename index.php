<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

$userID = $_COOKIE['userid'];
$userName = $_COOKIE['username'];
if (!isset($userID,$userName)) {
   ob_end_clean();
   header("Location: http://jittr.com/jittr/gameon/gologin.php");
   exit;
}
include('gohtmlhead.php');
include('goheader.php');
if (!isset($_GET['title'])) {
   $userSettings = new goUserSettings($userID);
?>
<div id="personal">
<h2><?php echo("Welcome user $userName - you have ID of $userID"); ?></h2>
</div>
<br />
<div id="creategame">
<form name="input" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="get">
Game Title:
<input type="text" name="title" />
<br />
Wager Type:
<input type="text" name="wagertype" />
<br />
Wager Units:
<input type="text" name="wagerunits" />
<br />
Facebook:
<?php if ($userSettings->hasFacebook()) { 
   echo '<input type="checkbox" name="network" value="facebook" ' . ($userSettings->isDefaultFacebook() ? "checked": null)  . '/>';
      } else {
   echo '<a href="goauthorizefacebook.php" >Authorize FaceBook</a>';
   }
?>   
<br />
Twitter:
<?php if ($userSettings->hasTwitter()) { 
   echo '<input type="checkbox" name="network" value="twitter"  ' . ($userSettings->isDefaultTwitter() ? "checked": null)  . '/>';
   $url= $userSettings->getTwitterProfileImageUrl();
   if (!empty($url))
      echo('<img src="' . $url . '" />');
   } else {
   echo '<a href="goauthorizetwitter.php" >Authorize Twitter</a>';
   }
?>   
<br />
FourSquare:
<?php if ($userSettings->hasFoursquare()) { 
   echo '<input type="checkbox" name="network" value="foursquare" ' . ($userSettings->isDefaultFoursquare() ? "checked": null)  . '/>';
   $url= $userSettings->getFoursquareProfileImageUrl();
   if (!empty($url))
      echo('<img src="' . $url . '" />');
   } else {
   echo '<a href="goauthorizefoursquare.php?query=fbauthorize" >Authorize Foursquare</a>';
   }
?>   
<br />
<input type="submit" value="Submit" />
<input type="hidden" value="<?php echo($userID);?>" name="createdbyuserid" />
<input type="hidden" value="<?php echo($userName);?>" name="createdbyusername" />
</form>
</div>
<?php
//get User defined games
echo('<div id="userdefinedgames">');
getUserDefinedGames($userID, $userName);
echo('</div>');

//get games subscribed to
echo('<div id="usersubscribedgames">');
getUserSubscribedGames($userID);
echo('</div>');
?>
<?php
} else {
  $title = $_GET['title'];
  $wagerType = $_GET['wagertype'];
  $wagerUnits = $_GET['wagerunits'];

  $url = "http://jittr.com/jittr/gameon/go_postnewgame.php?title=" . urlencode($title) . "&wagertype=" . urlencode($wagerType) . "&wagerunits=" . $wagerUnits . "&createdbyusername=$userName" . "&createdbyuserid=$userID";
  $curl = curl_init($url);
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  $result = curl_exec($curl);
  curl_close($curl);
  ob_end_clean();
  header("Location:".$_SERVER['PHP_SELF']);
} //else  
include 'gofooter.php';
ob_end_flush();
exit;

//------some functions
//retrieves games that the user has created
//*** You need to set options of CURLOPT_RETURNTRANSFER to actually return the xml from the webservice otherwise you get a true/false in the return value
function getUserDefinedGames($userID, $userName) {
 $url= Config::getAPIDomain() . "/go_getusergames.php?query=created&userid=" . urlencode($userID);
 $curl = @curl_init($url);
 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
 $xml = @curl_exec($curl);
 curl_close($curl);
 if ($xml) {
    $document=simplexml_load_string($xml);
    echo("<h3>Wagers you have created</h3>");
    echo("<ul>");
    foreach($document->game as $game) {
       echo('<li><a href="gocustomizegame.php?gameid=' . $game->gameid . '">' . $game->title . '</a></li>');
    }  
    echo("</ul>");
    return;
 }
} //function


//retrieves games that the user has subscribed to 
function getUserSubscribedGames($userID) {
 $url= Config::getAPIDomain() . "/go_getusergames.php?query=subscribed&userid=" . urlencode($userID); 
 $curl = @curl_init($url);
 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
 $xml =  @curl_exec($curl);
 curl_close($curl);
 if ($xml) {
    $document=simplexml_load_string($xml);
    echo("<h3>Wagers you have subscribed to not including those you created</h3>");
    echo("<ul>");
    foreach($document->game as $game) {
       echo('<li><a href="gocustomizegame.php?gameid=' . $game->gameid . '">' . $game->title . '</a></li>');
    }  
  }
  echo("</ul>");
  return;
 }// getUserSubcribedGames

//retrieve games that user has an invite for. This is a special case where the invites are direct peer to peer instead of peer to network
//TODO - define database table storing this information before fleshing out webservice and this function
function getOpenInvites($userID) {
} //getOpenInvites
?>

