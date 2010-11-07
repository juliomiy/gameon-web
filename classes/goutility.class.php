<?php
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: Set of convenience methods mostly static
   Modified: by Julio Hernandez-Miyares
   Date: September 5,2010
   Purpose: Add utlity lookups for the LU tables, initially by simple mysql reads.
     Consider a less resource intensive manor for these lookups

*/   
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');

class Utility {
/* emit xml */
/* TODO - add switch to return string instead of echo's to output buffer*/
public static $emitForm="";

/* shortern url - used for urls that are syndication of 
    bets. The short utl is what is syndicated to a wager's social network
*/
public static function shortenUrl($url,$defaultFormat=null) {
  $shortenerConfig = Config::$BITLY;
  $format = (($defaultFormat) ? $defaultFormat : $shortenerConfig['DEFAULTFORMAT']);
  $api=$shortenerConfig['APIURL'] . 'login=' . $shortenerConfig['LOGIN'] . '&apiKey=' . $shortenerConfig['APIKEY'] . '&format=' . $format . '&longUrl=' . $url;
  if (Config::getDebug()) Config::getLogObject()->Log("ShortenURL Api call " . $api);
  $curl = @curl_init($api);
  $opts=Config::$CURL_OPTS;
  curl_setopt_array($curl,$opts);
  $result = @curl_exec($curl);
  if (Config::getDebug()) Config::getLogObject()->Log("ShortenURL Api call result " . $result);
  @curl_close($curl);
/* Parse the returned XML into php object */
   $document = simplexml_load_string($result);
  if (Config::getDebug()) Config::getLogObject()->Log("status code = " . $document->status_code);
  if (Config::getDebug()) Config::getLogObject()->Log("url= " . $document->data->url);
   return $document;
} //shortenURL

public static function emitXML($in,$tag,$close=1) {
static $levels=0;
static $level;
      
if ($close) 
   $str = "<$tag>$in</$tag>";
else {
        if (isset($level[$tag])) {
           $str = "</$tag>";
           unset($level[$tag]);
           $levels--;
        } else {
          $str="<$tag>";
          $level[$tag]=1;
          $levels++;
        }
     }//if
if (self::$emitForm != 'string') 
    echo($str);
else    
   return $str;
   
} //emitXML
/* Generate unique key of type - generally for mysql primary keys when auto increment is not
   enabled
   Want to avoid having to select/update for each key request.
   TODO- temporarily returning now which is timestamp based just to continue
*/
public static function generateUniqueIDKey($type) {

return time();
} //generateUnqiueIDKey

/* generate unique key into go_gameInvite which represents the key to an invite to a game/bet
   TODO - with prefix of time to sessionID should guarantee uniqueness but would want to make the key
          mean something more then just a unqiue reference to the record in the table
*/
public static function generateGameInvite() {
   $gameInviteKey = time() . session_id();
   return $gameInviteKey;
}
 //generateGameInvite

public static function getTeamIDbyTeamName($teamName) {
   $LOG = Config::getLogObject();
   $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
   if (!$link)
   {   
      // Server error
      mydie("Error connecting to Database");
   }
   $sql = sprintf("select id from go_teams_lu where teamName = '%s'",
      mysqli_real_escape_string($link,$teamName));
   if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
   $cursor = mysqli_query($link,$sql);
   if (!$cursor) {
      // Server error
      mydie(mysqli_error($link),500,$link);
   } //if
   $row = mysqli_fetch_assoc($cursor);
   $teamID = $row['id'];
   $cursor->close();
   $link->close();
   return $teamID;
}  //getTeamIDbyTeamName


public static function getUserIDOrName($param,$type='id') {

   $LOG = Config::getLogObject();
   $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
   if (!$link)
   {   
      // Server error
      mydie("Error connecting to Database");
   }
  if ($type=='id') {
     $sql=sprintf("select * from go_user where userID='%u'",
      mysqli_real_escape_string($link,$param));
  } else {
     $sql=sprintf("select * from go_user where userName='%s'",
      mysqli_real_escape_string($link,$param));
  }
  if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
   $cursor = mysqli_query($link,$sql);
   if (!$cursor) {
      // Server error
      mydie(mysqli_error($link),500,$link);
   } //if
   $row = mysqli_fetch_assoc($cursor);
   if (isset($cursor)) $cursor->close();
   if (isset($link)) $link->close();
   return ($type=='id' ? $row['userName'] : $row['userID']);
} //getUserIDOrName

public static function getTeamIDOrTeamName($param,$type='id') {
   $LOG = Config::getLogObject();
   $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
   if (!$link)
   {   
      // Server error
      // mydie("Error connecting to Database");
      return GAMEON_ERROR;
   }
  if ($type =='id') {
     $sql=sprintf("select * from go_teams_lu where id='%u'",
      mysqli_real_escape_string($link,$param));
  } else {
     $sql=sprintf("select * from go_teams_lu where teamName='%s'",
      mysqli_real_escape_string($link,$param));
  }
  if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
  $cursor = mysqli_query($link,$sql);
  if ($cursor) {
        $row = mysqli_fetch_assoc($cursor);
        $rv = GAMEON_OK . GAMEON_DELIMITER . ($type=='id' ?  'teamname' . GAMEON_DELIMITER . $row['teamName'] : 'teamid' . GAMEON_DELIMITER . $row['id']);
  } else $rv = GAMEON_NORECORD;
   if (isset($cursor)) $cursor->close();
   if (isset($link)) $link->close();
   return $rv;
} //getTeamIDOrTeamName
 
} //class Utility


?>
