<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');

/* Author: Julio Hernandez-Miyares
   Date: May 9th 2010
   Purpose:Plain all Php object encapsulating game model 
   TODO: Return xml and other formats ie json 
   TODO: currently use normalized table necessitating joins - ok for now but won't scale properly,
         Not using Denormalized just yet
*/
class Game {
   private $gameID;
   private $publicGameID;
   private $title;
   private $description;
   private $typeID;
   private $type;
   private $sportID;
   private $sport;
   private $wagerUnits;
   private $wagerType;
   private $wagerTypeID;
   private $createdByUserID;
   private $createdByUserName;
   private $subscriptionClose;
   private $LOG;

   public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
          $gameID = func_get_arg(0);
	  $this->getGame($gameID);
       } //if
   } //constructor    

   public function getGameID() {
      return $this->gameID;
   }
   public function getPublicGameID() {
      return $this->publicGameID;
   }
   public function getTitle() {
      return $this->title;
   }
   public function getDescription() {
      return $this->description;
   }
   public function getCreatedByUserID() {
       return $this->createdByUserID;
   }    
   public function getCreatedByUserName() {
       return $this->createdByUserName;
   }    
   public function getTypeID() {
      return $this->typeID;
   }
   public function getTypeName() {
      return $this->type;
   }
   public function getSportID() {
      return $this->sportID;
   }
   public function getSportName() {
      return $this->sport;
   }

   public function getWagerUnits() {
       return $this->wagerUnits;
   }

   public function getWagerTypeID() {
       return $this->wagerTypeID;
   }
   public function getWagerType() {
       return $this->wagerType;
   }
   public function getSubscriptionClose() {
       return $this->subscriptionClose;
   }    
   public function setSubscriptionClose($in) {
      $this->subscriptionClose=$in;
   }    

   public function setGameID($in) {
      $this->gameID=$in;
   }
   public function setPublicGameID($in) {
      $this->publicGameID=$in;
   }
   public function setTitle($in) {
      $this->title=$in;
   }
   public function setDescription($in) {
      $this->description=$in;
   }
   public function setCreatedByUserID($in) {
      $this->createdByUserID=$in;
   }    
   public function setCreatedByUserName($in) {
      $this->createdByUserName=$in;
   }    
   public function setTypeID($in) {
      $this->typeID=$in;
   }
   public function setTypeName($in) {
      $this->type=$in;
   }
   public function setSportID($in) {
      $this->sportID=$in;
   }
   public function setSportName($in) {
      $this->sport=$in;
   }
   public function setWagerType($in) {
      $this->wagerType=$in;
   }
   public function setWagerTypeID($in) {
      $this->wagerTypeID=$in;
   }
   public function setWagerUnits($in) {
      $this->wagerUnits=$in;
   }


/* Retrieve Game record from go_games table and populate object properties */
/* TODO - for expediency, joins prevail - not scalable, use denormalized table or in memory cache   
*/
   public function getGame($gameID) {
   
      $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) mydie("Error connecting to Database");

      $sql=sprintf("select g.*,t.typeName,s.sportName from go_games g LEFT JOIN go_types_lu t on g.type=t.id LEFT JOIN  go_sports_lu s on g.sport=s.id LEFT JOIN go_wagerTypes_lu w on g.wagerTypeID = w.id  where g.gameID='%u'",
           mysqli_real_escape_string($link,$gameID));
      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);

      $cursor=@mysqli_query($link,$sql);
      if (!$cursor) $this->mydie(mysqli_error($link),$link);
      $row = @mysqli_fetch_assoc($cursor);
      if (Config::getDebug()) $this->LOG->log("$row",PEAR_LOG_DEBUG);
      if (!row) return false;

      $this->setGameID($row['gameID']);
      $this->setPublicGameID($row['publicGameID']);
      $this->setDescription($row['description']);
      $this->setCreatedByUserID($row['createdByUserID']);
      $this->setCreatedByUserID($row['createdByUserName']);
      $this->setTitle($row['title']);
      $this->setSportID($row['sport']);
      $this->setSportName($row['sportName']);
      $this->setTypeID($row['type']);
      $this->setTypeName($row['typeName']);
      $this->setWagerUnits($row['wagerUnits']);
      $this->setWagerTypeID($row['wagerTypeID']);
      $this->setWagerType($row['wagerType']);
      $this->setSubscriptionClose($row['subscriptionClose']);
   }
   private function mydie($message,$link=null) {
      $this->LOG->log("$message",PEAR_LOG_ERR);
      if (isset($link)) $link->close();
      die($msg);
   }
}//class Game
?>
