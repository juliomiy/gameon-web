<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');

/* Author: Julio Hernandez-Miyares
   Date: 2009
   Purpose: Head section of the html Document 
   TODO: callback function for output buffering is not currently activated 
*/

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">';
   echo '<html lang="en">';
   echo '<head>';
   echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
   echo '<meta name="revist-after" content="3 days">';
   echo '<meta name="Language" content="en-us">';
   echo '<meta name="robots" content="all">';
   echo '<meta name="msnbot" content="index, follow">';
   echo '<meta name="googlebot" content="index, follow">';
   echo '<meta name="allow-search" content="yes">';
   echo '<meta name="revisit-after" content="3 Days">';
   echo '<meta name="Rating" content="General">';
   echo '<meta name="language" content="en">';
   echo '<meta name="distribution" content="Global">';
   echo '<title>%_PAGETITLE_%</title>';
   echo '<link rel="shortcut icon" type="image/x-icon" href="' . Config::getRootDomain() . '/' . Config::getFaviconURL() . '">';
   echo '<link rel=StyleSheet href="' . Config::getCommonCSS() . '" type="text/css" media=screen>';
   echo '</head>';
  
  function ob_callback($buffer) {
      global $OB_KEY,$OB_REPLACE;
      $new_buffer = str_replace($OB_KEY,$OB_REPLACE, $buffer);
    return $new_buffer;
    }
?>
