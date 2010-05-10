<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');
require_once('go_game.class.php');

//retrieve users subscribed to this game  
function getGameSubscribers($gameID) {
  $url= Config::getAPIDomain() . "/go_getgamesubscribers.php?query=gamesubscribers&gameid=" . urlencode($gameID);
  if (Config::getDebug()) Config::getLogObject()->log("$url",PEAR_LOG_DEBUG);
  $curl = @curl_init($url);
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  $xml =  @curl_exec($curl);
  curl_close($curl);
  if ($xml) {
      $document=@simplexml_load_string($xml);
      echo("<h3>Users subscribed to Game</h3>");
      if (!$document) return false;
      echo("<ul>");
      foreach($document->game as $game) {
          //echo('<li><a href="gocustomizegame.php?gameid=' . $game->gameid . '">' . $game->title . '</a></li>');
          echo('<li>' . $game->username . '</li>');
      }
   }
   echo("</ul>");
   return;
}// getGameSubcribers
$userID = $_COOKIE['userid'];
$userName = $_COOKIE['username'];
$gameID = $_GET['gameid'];
if (!isset($userID,$userName)) {
   ob_end_clean();
   header("Location: http://jittr.com/jittr/gameon/gologin.php");
   exit;
}
include('gohtmlhead.php');
include('goheader.php');
$game=new Game($gameID);
?>
<div id="personal">
<h2><?php echo("Welcome user $userName - you have ID of $userID"); ?></h2>
</div>
<br />
<div id="updategame">
<h3>Customize Game <span><?php echo($game->getTitle());?></span></h3>
<form name="input" action="<?php echo($_SERVER['PHP_SELF'] . "?query=update&gameid=" . urlencode($game->getGameID())); ?>"  method="get">
Game Title:
<input type="text" name="title" value="<?php echo($game->getTitle());?>" />
<br />
Game Description:
<input type="textarea" name="description" value="<?php echo($game->getDescription());?>"/>
<br />
Game Type:
<input type="text" name="type" value="<?php echo($game->getTypeName());?>"/>
<input type="hidden" name="typeid" value="<?php echo($game->getTypeID());?>"/>
<br />
Wager Type:
<input type="text" name="wagertype" value="<?php echo($game->getWagerType());?>"/>
<br />
Wager Units:
<input type="text" name="wagerunits" value="<?php echo($game->getWagerUnits());?>" />
<br />
Subscription Close Date:
<input type="text" name="subscriptionclose" value="<?php echo($game->getSubscriptionClose());?>" />
<br />
Created By:
<input type="text" name="createdby" value="<?php echo($game->getCreatedByUserName());?>" />
<br />
<input type="hidden" name="createdbyid" value="<?php echo($game->getCreatedByUserID());?>" />
<input type="submit" value="Update" name="update" />
<div id="subscribers">
 <?php getGameSubscribers($gameID);?>
</div>   
</form>
</div>
<?php
include('gofooter.php');
ob_end_flush();
exit;


?>

