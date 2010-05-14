<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');
require_once('go_game.class.php');
require_once('go_publicgame.class.php');

/* Author: Julio Hernandez-Miyares
   Date: May 13th 2010
   Purpose: Collection of go_games and go_publicgames model 
   TODO: Return xml and other formats ie json
   TODO: would prefer a more abstract way to accomplish collection in php but have not yet come across
          a good library to mimic the collection patterns available in java.
          perhaps building it myself will provide me insight to arrive at a more generalized approach
*/
class goGameCollection {
   private $games;   //will contain collection of go_games
   private $publicGames; //will contain collection go_publicgames
   private $LOG;

   public function __construct() {
      $this->games = array();
      $this->publicGames = array();
      $this->LOG=Config::getLogObject();
   }
   
/* add game object to collection */
   public function add($in) {
   }
/* delete object from collection - NOT IMPLEMENTED AND LEFT PRIVATE FOR NOW */   
   private function delete($in) {
   }

   public function getSizeOfPublicGames() {
       return count($this->publicGames);
   }    

   /* retrieve Game records via sql query*/
   public function getGames() {
   }

   /* retrieve publicGame records via sql query*/
   public function getPublicGames() {

      $sql = goPublicGame::getSql();
      $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) mydie("Error connecting to Database");
      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
      $cursor=@mysqli_query($link,$sql);
      if (!$cursor) $this->mydie(mysqli_error($link),$link);
      $record=0;
      while ($row = @mysqli_fetch_assoc($cursor)) {
         $game=new goPublicgame();
	       //$game->setGameID($row['gameID']);
	       $game->setDescription($row['description']);
	       $game->setTitle($row['title']);
	       $game->setEventName($row['eventName']);
  	       $game->setEventDate($row['eventDate']);
	       $game->setSportID($row['sport']);
	       $game->setSportName($row['sportName']);
	       $game->setLeague($row['league']);
	       $game->setFavorite($row['favorite']);
	       $game->setTypeID($row['type']);
	       $game->setTypeName($row['typeName']);
      /*** temp explicit treatment as array storage***/
               $this->publicGames[$record]=$game;
               $record++;
      /*** end temp ***/	       
      } //while          
      $link->close();
   } //getPublicGames

   public function __toString() {
      $xml="";
      $records = $this->getSizeOfPublicGames();
      for ($x=0;$x < $records; $x++) {
          $game = $this->publicGames[$x];
	  $game->setOutputFlag('xml');
	  //echo($game);
	  $xml .= $game->__toString();
      }//for
      return $xml;
          
   } //function

   private function _toStringXML() {

   }

   private function mydie($message,$link=null) {
      $this->LOG->log("$message",PEAR_LOG_ERR);
      if (isset($link)) $link->close();
            die($msg);
   }  //mydie
} //class

