<?php
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: Set of convenience methods mostly static
*/   
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
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

} //class Utility
?>
