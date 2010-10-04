<?php
/* Author: Julio Hernandez-Miyares
   Date: August 19,2010
   Purpose: select public games from 
            go_publicGames table based on various query parameter filters
            provide an interface to select a particular game to initiate a bet/wager on
*/
ob_start();
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');
require_once('go_game.class.php');
$LOG = Config::getLogObject();

/* is this a fresh invoke or based on user selection */
$selected = $_GET['selected'];
if (!empty($selected)) {
   $params['league'] = $_GET['leaguename'];
   $params['team'] = $_GET['teamname'];
   $params['timefilter'] = $_GET['timefilter'];
} //if
   
/*
print_r( $_GET);
exit;
*/
/*$logout = $_GET['logout'];
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
   header("Location: " . Config::getRootDomain() . "/gologin.php");
   exit;
}
*/
include('gohtmlhead.php');
include('goheader.php');
if (!isset($_GET['title'])) {
   $userSettings = new goUserSettings($userID);
}
?>
 <form name="input" action="<?php echo($_SERVER['PHP_SELF']); ?>" method="get">
<?php
echo('<div id="selectbets">');
// search selection filters via listbox
   populateTimeFilter($params['timefilter']);
   getLeagues('nfl');
   getTeams($params['league'],$params['team']);    // Has lot's of data - have to think how to make decipherable and efficient
   echo('<input type="hidden" value="selected" name="selected" />');
   echo('<input type="submit" value="go" />');
echo('</div>');
echo('</form>');

echo('<div id="gameresults">');
   getPublicGames($params);
echo('</div>');
include('gofooter.php');
ob_end_flush();
exit;

function getPublicGames($params) {
   if (count($params) > 0) {
      foreach ($params as $key => $value) {
          $value=urlencode($value);
          $queryParameters .=  (!empty($queryParameters)) ? "&$key=$value" :  "?$key=$value"; 
      } //foreach      
   } //if
   $url= Config::getAPIDomain() . "/go_getpublicgames.php" . $queryParameters;
echo($url);
   $curl = @curl_init($url);
   curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
   $xml = @curl_exec($curl);
   curl_close($curl);
   if ($xml) {
      $document=simplexml_load_string($xml);
      echo('<ul>');
      foreach($document->game as $game) {
          $recordcnt++;
          $gameID = $game->gameid;
          $eventName = $game->eventname;
          $stadiumName = $game->stadium->stadiumname;
          $stadiumCity= $game->stadium->city;
          $latitude = $game->stadium->latitude;
          $longitude= $game->stadium->longitude;
          $eventDateTime = $game->eventdatetime;
          $tmpDateTime = new DateTime($eventDateTime);
          $formatedEventDateTime = $tmpDateTime->format('F d,Y g:i A');
          $customizeUrlParameter= "?publicgameid=$gameID&action=customize";
          echo('<li><a href="gocustomizebet.php' . $customizeUrlParameter . '">'. $eventName . '</a> at ' . '<a href="gowhatsnearby.php">' . $stadiumName . ',' . $stadiumCity .  '</a> on ' . $formatedEventDateTime . '</li>');
      }
      echo('</ul>');
   } //if
} //getPublicGames

/* Populate listbox with the various time filters to search for public games
*/
function populateTimeFilter($selected=null) {
    echo('<select id="timelistbox" name="timefilter">');
    echo('<option value="today" ' .   ($selected=='today' ? "SELECTED" : null) . '>Today</option>');
    echo('<option value="week" ' .    ($selected=='week' ? "SELECTED" : null) . '>This Week</option>');
    echo('<option value="weekend" ' . ($selected=='weekend' ? "SELECTED" : null) . '>This WeekEnd</option>');
    echo('<option value="month" ' .   ($selected=='month' ? "SELECTED" : null) . '>This Month</option>');
    echo('<option value="all" ' .     ($selected=='all' ? "SELECTED" : null) . '>All</option>');
    echo('</select>');
}

function getLeagues($selected=null) {
    global $LOG;
    if (empty($selected)) $selected='nfl';
    $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
    if (!$link) mydie("Error connecting to Database");
    $sql="select * from go_leagues_lu order by leagueName";
    if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
    $cursor=@mysqli_query($link,$sql);
    if (!$cursor) mydie(mysqli_error($link),$link);
    echo('<select id="leaguelistbox" name="leaguename">');
    while (($row = @mysqli_fetch_assoc($cursor)) != null) {
       $selectedFlag = (strtolower($selected) == strtolower($row['leagueName'])) ?  ' SELECTED' : null;
       echo('<option value="' . $row['leagueName'] . '"' . $selectedFlag . '>' . $row['leagueName'] .  '</option>');
    }
   $link->close();
   echo('</select>');
}

function getTeams($sportID=null,$selected=null) {
    global $LOG;
    $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
    if (!$link) mydie("Error connecting to Database");
    $sql="select * from go_teams_lu ";
    if (!empty($sportID)) {
      if (is_numeric($sportID)) 
         $where = " where sportID = $sportID";
      else
         $where = ' where leagueName ="' . $sportID . '"';
    } //if
    $sql .= $where . " order by teamName";
    if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
    $cursor=@mysqli_query($link,$sql);
    if (!$cursor) mydie(mysqli_error($link),$link);
    echo('<select id="teamlistbox" name="teamname">');
    while (($row = @mysqli_fetch_assoc($cursor)) != null) {
       $selectedFlag = (strtolower($selected) == strtolower($row['teamName'])) ?  ' SELECTED' : null;
       echo('<option value="' . $row['teamName'] . '"' . $selectedFlag . '>' . $row['teamName'] .  '</option>');
    }
   $link->close();
   echo('</select>');
}
function mydie($msg, $link = null) {
   global $LOG;
   if (!empty($link)) $link->close();
   $LOG->log($msg,PEAR_ERROR);  
   die($msg);
}
?>
