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

//Friend inviteStatusID - mapped to table go_friendInvites
DEFINE("FRIEND_INVITE_CREATED",1);
DEFINE("FRIEND_INVITE_DECLINED",6);
DEFINE("FRIEND_INVITE_APPROVED",7);

//Transacions
DEFINE("TRANSACTION_WAGER",1);
DEFINE("TRANSACTION_PURCHASE_DUCKETTS",2);
DEFINE("TRANSACTION_TRANSFER_DUCKETTS",3);
DEFINE("TRANSACTION_GIFT_DUCKETTS",4);

//Game Types - from go_types_lu table
DEFINE("GAME_TYPE_UNDEFINED",1);
DEFINE("GAME_TYPE_TEAM",2);
DEFINE("GAME_TYPE_TOURNAMENT",3);
DEFINE("GAME_TYPE_DATE",4);
DEFINE("GAME_TYPE_USER",5);

DEFINE("GAMEON_OK",1);
DEFINE("GAMEON_ERROR",-1);
DEFINE("GAMEON_NORECORD",-2);
DEFINE("GAMEON_DELIMITER",'|');

?>
