<?
/* Author: Julio Hernandez-Miyares
   Date: August 19,2010
   Purpose: Define some basic Constants
    generally used when preferring or not being able to define via .htaccess, php.ini or 
    apache config.
    Initially will be used to set the ini path for finding php objects and being able to change in one place
    if the underlying directory structure on host changes
*/
DEFINE("INI_PATH","/home/juliomiyares/jittr.com/jittr/gameon/classes");
//a pain but make sure to keep in sync with the device android code
DEFINE("TWITTER_NETWORK",2);
DEFINE("FACEBOOK_NETWORK",1);
DEFINE("BETSQUARED_NETWORK",4);
DEFINE("FOURSQUARE_NETWORK",3);
?>
