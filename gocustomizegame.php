<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');
require_once('go_game.class.php');

$LOG = Config::getLogObject();

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
          echo('<li><a href="' . Config::getRootDomain() . '/goprofile.php?userid=' . $game->userid . '">' . $game->username . '</a></li>');
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
<?php generateTypeListBox($game->getTypeName());?>
<input type="hidden" name="typeid" value="<?php echo($game->getTypeID());?>"/>
<br />
Pivot Date:
<input type="text" name="type" value="<?php echo($game->getPivotDate());?>"/>
Pivot Condition:
<?php generatePivotCondition($game->getPivotCondition());?>
<br />
Wager Type:
<input type="text" name="wagertype" value="<?php echo($game->getWagerType());?>"/>
<br />
Wager Units:
<input type="text" name="wagerunits" value="<?php echo($game->getWagerUnits());?>" />
<br />
Subscription Close Date:
<input type="text" name="subscriptionclose" value="<?php echo($game->getSubscriptionCloseDate());?>" />
<br />
Created By:
<input type="text" name="createdby" value="<?php echo($game->getCreatedByUserName());?>" />
<br />
<input type="hidden" name="createdbyid" value="<?php echo($game->getCreatedByUserID());?>" />
Syndication Url:
<input type="text" name="syndicationurl" value="<?php echo($game->getSyndicationUrl());?>" />
<br />
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

function generatePivotCondition($pivotCondition) {
   echo('<select name="conditionbox">');

       $selected = (strtolower($pivotCondition) == strtolower('after')) ?   "SELECTED" : null;
       echo('<option value="after"'  . $selected . '>After</option>');

       $selected = (strtolower($pivotCondition) == strtolower('between')) ?  "SELECTED" : null;
       echo('<option value="between"'  . $selected . '>Between</option>');

       $selected = (strtolower($pivotCondition) == strtolower('before')) ?  "SELECTED" : null;
       echo('<option value="before"'  . $selected . '>Before</option>');

       $selected = (strtolower($pivotCondition) == strtolower('on')) ?  "SELECTED" : null;
       echo('<option value="on"'  . $selected . '>On</option>');
   echo('</select>');

}
function generateTypeListbox($typeName) {
    global $LOG;
    $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
    if (!$link) mydie("Error connecting to Database");
    $sql="select * from go_types_lu order by typeName";
    if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
    $cursor=@mysqli_query($link,$sql);
    if (!$cursor) $this->mydie(mysqli_error($link),$link);
    echo('<select name="typebox">');
    while (($row = @mysqli_fetch_assoc($cursor)) != null) {
       $selected = (strtoupper($typeName) == strtoupper($row['typeName'])) ?  ' SELECTED' : null;
       echo('<option value="' . $row['id'] . '"' . $selected . '>' . $row['typeName'] .  '</option>');
    } 
   $link->close();
   echo('</select>');
}
?>

