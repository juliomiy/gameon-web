<?php
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: Set of convenience methods mostly static
*/   
class Utility {


/* emit xml */
/* TODO - add switch to return string instead of echo's to output buffer*/
public static $emitForm="";

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

} //class Utility
?>
