<?php
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Purpose: Set of convenience methods mostly static
*/   
class Utility {

/* emit xml */
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
echo($str);
return;
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
