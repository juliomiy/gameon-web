<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');
$LOG = Config::getLogObject();

$logout = $_GET['logout'];
if ($logout=="true") {
   setcookie("username", "", time()-3600); 
   setcookie("userid", "", time()-3600); 
   header("Location:" . $_SERVER['PHP_SELF']);
   exit;
}
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
   <h2><?php echo('Welcome user <a href="goprofile.php?userid=' . $userID . '">'  .  $userName . '</a>' . " - you have ID of $userID"); ?></h2>
   <a href="<?php echo($_SERVER['PHP_SELF'] . "?logout=true"); ?>">Logout</a>
</div>
<br />
<div id="quickcreategame">
   <form name="input" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="get">
   <h3>Initiate Quick Bet or <a href="<?php echo(Config::getRootDomain());?>/gopublicgames.php">Find an Event to bet</a></h3>
   Game Title: <input type="text" name="title" />
   <br />
   Wager Type: <input type="text" name="wagertype" />
   <br />
   Wager Units: <input type="text" name="wagerunits" />
   <br />
   Select Game Type: <?php generateTypelistbox(); ?>
   <br />
   <div id="datewagerinput">
      Pivot Date: <input type="text" name="pivotdate" />
      <?php generatePivotCondition(); ?>
      <br />
     </div>
<div id="socialnetworkinput">
   <h3>Customize Syndication or <a href="<?php echo(Config::getRootDomain());?>/goprofile.php">Go to Profile Settings</a></h3>
   Facebook: <?php if ($userSettings->hasFacebook()) { 
   echo '<input type="checkbox" name="network" value="facebook" ' . ($userSettings->isDefaultFacebook() ? "checked": null)  . '/>';
      } else {
   echo '<a href="goauthorizefacebook.php" >Authorize FaceBook</a>';
   }
   ?>   
   <br />
   Twitter: <?php if ($userSettings->hasTwitter()) { 
   echo '<input type="checkbox" name="network" value="twitter"  ' . ($userSettings->isDefaultTwitter() ? "checked": null)  . '/>';
   $url= $userSettings->getTwitterProfileImageUrl();
   if (!empty($url))
      echo('<img src="' . $url . '" height="25px" width="25px"  />');
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
         echo('<img src="' . $url . '" height="25px" width="25px"/>');
      } else {
        echo '<a href="goauthorizefoursquare.php?query=fbauthorize" >Authorize Foursquare</a>';
      }
   ?>   
   <br />
   </div>
<input type="submit" value="Submit" />
<input type="hidden" value="<?php echo($userID);?>" name="createdbyuserid" />
<input type="hidden" value="<?php echo($userName);?>" name="createdbyusername" />
</form>
</div>
<?php
//get User defined games
echo('<div id="userinitiatedbets">');
getUserDefinedGames($userID, $userName);
echo('</div>');

//get games subscribed to
echo('<div id="useracceptedbets">');
getUserSubscribedGames($userID);
echo('</div>');
?>
<?php
} else {
  $title = $_GET['title'];
  $wagerType = $_GET['wagertype'];
  $wagerUnits = $_GET['wagerunits'];
  $typeID=$_GET['type'];
  $pivotDate=$_GET['pivotdate'];
  $pivotCondition=$_GET['pivotcondition'];
//temporarily hardcoded TODO - remove hardcoding
  $typeName='date';
  $typeID=4;

  
  $url = "http://jittr.com/jittr/gameon/go_postnewgame.php?title=" . urlencode($title) . "&wagertype=" . urlencode($wagerType) . "&wagerunits=" . $wagerUnits . "&createdbyusername=$userName" . "&createdbyuserid=$userID&typename=$typeName&pivotdate=$pivotDate&pivotcondition=$pivotCondition&type=$typeID";
  if (Config::getDebug()) $LOG->log("$url",PEAR_LOG_INFO);
  $curl = curl_init($url);
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  $result = curl_exec($curl);
  curl_close($curl);
  if (Config::getDebug()) $LOG->log("$result",PEAR_LOG_INFO);
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
 $url= Config::getAPIDomain() . "/go_getusergames.php?query=created&userid=" . urlencode($userID) . "&sort=recent";
 $curl = @curl_init($url);
 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
 $xml = @curl_exec($curl);
 curl_close($curl);
 if ($xml) {
    $document=simplexml_load_string($xml);
    echo("<h3>Recent Bets you have initiated</h3>");
    echo("<ul>");
    $recordcnt=0;
    foreach($document->game as $game) {
       $recordcnt++;
       if ($recordcnt <= 5) {  
          echo('<li><a href="gocustomizegame.php?gameid=' . $game->gameid . '">' . $game->title . '</a></li>');
       } else {   
          echo('<li><a href="godashboard.php">See All your Initiated Bets</a></li>');
          break;
       }  //if  
    } //for
    echo("</ul>");
    return;
 }
} //function


//retrieves games that the user has accepted/subscribed to 
function getUserSubscribedGames($userID) {
 $url= Config::getAPIDomain() . "/go_getusergames.php?query=subscribed&userid=" . urlencode($userID) . "&sort=recent"; 
 $curl = @curl_init($url);
 curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
 $xml =  @curl_exec($curl);
 curl_close($curl);
 if ($xml) {
    $document=simplexml_load_string($xml);
    echo("<h3>Recent Bets Accepted</h3>");
    echo("<ul>");
    $recordcnt=0;
    foreach($document->game as $game) {
       $recordcnt++;
       if ($recordcnt <= 5) {  
          echo('<li><a href="gocustomizegame.php?gameid=' . $game->gameid . '">' . $game->title . '</a></li>');
       } else {
          echo('<li><a href="godashboard.php">See All your Accepted Bets</a></li>');
          break;
       } //if
    } //foreach
  } //if
  echo("</ul>");
  return;
 }// getUserSubcribedGames

//retrieve games that user has an invite for. This is a special case where the invites are direct peer to peer instead of peer to network
//TODO - define database table storing this information before fleshing out webservice and this function
function getOpenInvites($userID) {
} //getOpenInvites
function generatePivotCondition($pivotCondition=null) {
   echo('<select name="pivotcondition">');

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
function generateTypeListbox($typeName=null) {
    global $LOG;
    $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
    if (!$link) mydie("Error connecting to Database");
    $sql="select * from go_types_lu order by typeName";
    if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
    $cursor=@mysqli_query($link,$sql);
    if (!$cursor) $this->mydie(mysqli_error($link),$link);
    echo('<select id="typelistbox" name="typename">');
    while (($row = @mysqli_fetch_assoc($cursor)) != null) {
       $selected = (strtoupper($typeName) == strtoupper($row['typeName'])) ?  ' SELECTED' : null;
       echo('<option value="' . $row['id'] . '"' . $selected . '>' . $row['typeName'] .  '</option>');
    }
   $link->close();
   echo('</select>');
}
?>
