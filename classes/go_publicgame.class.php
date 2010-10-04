<?php
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);

require_once('config.class.php');
require_once('go_usersettings.class.php');

/* Author: Julio Hernandez-Miyares
   Date: May 13th 2010
   Purpose:Plain all Php object encapsulating publicgame model 
   TODO: Return xml and other formats ie json 
   TODO: currently use normalized table necessitating joins - ok for now but won't scale properly,
         Not using Denormalized just yet
   TODO: update toXML return to include new fields added August 19,2010
   Modified: by Julio Hernandez-Miyares August 19,2010
    add additional fields for public Games
*/
class goPublicGame {
   private $gameID;
   private $title;
   private $description;
   private $eventName;
   private $eventDate;
/*Added for public Games - by JHM August 19,2010*/
   private $team1ID;
   private $team2ID;
   private $teamName1;
   private $teamName2;
/* end of additions */
   private $typeID;
   private $type;
   private $sportID;
   private $leagueName;
   private $sport;
   private $favorite;
   private $numberofSubscribers;
   private $subscriptionClose;
   private $LOG;
   private $outputFlag;

   public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
          $gameID = func_get_arg(0);
	  $this->getGame($gameID);
       } //if
   } //constructor    

   /* determines the type of output returned by _toString. currently one supported is xml
   */
     public function setOutputFlag($in) {
           $this->outputFlag=$in;
     }

   /* sql for getting a list of games -the caller will determine the where condtions*/
   public static function getSql() {
      $sql="select g.*,t.typeName,s.sportName,l.leagueName from go_publicgames g LEFT JOIN go_types_lu t on g.type=t.id LEFT JOIN  go_sports_lu s on g.sport=s.id LEFT JOIN go_leagues_lu l on g.leagueID=l.id"; 
      return $sql;
   }
   public function getGameID() {
      return $this->gameID;
   }
  public function getHomeTeamID() {
       return $this->team1ID;
   }
   public function getVisitingTeamID() {
       return $this->team2ID;
   }

   public function getHomeTeam() {
       return $this->teamName1;
   }
   public function getVisitingTeam() {
       return $this->teamName2;
   }

   public function setHomeTeamID($in) {
       $this->team1ID=$in;
   }
   public function setVisitingTeamID($in) {
       $this->team2ID=$in;
   }

   public function setHomeTeam($in) {
       $this->teamName1=$in;
   }
   public function setVisitingTeam($in) {
       $this->teamName2=$in;
   }

   public function getTitle() {
      return $this->title;
   }
   public function getEventName() {
       return $this->eventName;
   }
   public function setEventName($in) {
       $this->eventName=$in;
   }

   public function getEventDate() {
       return $this->eventDate;
   }
   public function setEventDate($in) {
       $this->eventDate=$in;
   }
   public function getDescription() {
      return $this->description;
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
   public function getLeagueName() {
      return $this->leagueName;
   }
   public function setLeagueName($in) {
      $this->leagueName=$in;
   }

   public function getFavorite() {
      return $this->favorite;
   }
   public function setFavorite($in) {
      $this->favorite=$in;
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
   public function setTitle($in) {
      $this->title=$in;
   }
   public function setDescription($in) {
      $this->description=$in;
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
   public function getNumberofSubscribers() {
      return $this->numberofSubscribers;
   }

   public function setNumberofSubscribers($in) {
       $this->numberofSubscribers=$in;
   }

/* Retrieve Game record from go_publicgames table and populate object properties */
/* TODO - for expediency, joins prevail - not scalable, use denormalized table or in memory cache   
*/
   public function getGame($gameID) {
   
      $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) mydie("Error connecting to Database");

      $sql=sprintf("select g.*,t.typeName,s.id,s.sportName, l.leagueName from go_publicgames g LEFT JOIN go_types_lu t on g.type=t.id LEFT JOIN  go_sports_lu s on g.sportID=s.id LEFT JOIN go_leagues_lu l on g.leagueID = l.id where g.gameID='%u'", mysqli_real_escape_string($link,$gameID));
      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);

      $cursor=@mysqli_query($link,$sql);
      if (!$cursor) $this->mydie(mysqli_error($link),$link);
      if (Config::getDebug()) $this->LOG->log($row,PEAR_LOG_DEBUG);
      $row = @mysqli_fetch_assoc($cursor);
      if (!$row) { 
         return false;
      } //if
      $this->setGameID($row['gameID']);
      $this->setDescription($row['description']);
      $this->setTitle($row['title']);
      $this->setEventName($row['eventName']);
      //$this->setEventDate($row['eventDate']);
      $this->setEventDate($row['date']);
      $this->setSportID($row['id']);
      $this->setSportName($row['sportName']);
      $this->setLeagueName($row['leagueName']);
      $this->setFavorite($row['favorite']);
      $this->setTypeID($row['type']);
      $this->setTypeName($row['typeName']);
 //     $this->setSubscriptionClose($row['subscriptionClose']);
   //a little cleanup for properties that may be empty
      if (empty($this->eventName)) $this->setEventName($this->getTitle());
      if (empty($this->description)) $this->setDescription($this->getTitle());
      $rc = $this->getPublicGameCombatants($gameID,$link);
      return $rc;
   }

/* for now, assume 2 team games */
   private function getPublicGameCombatants($gameID,$link=null) {
      if (empty($link)) return false;
      $sql = "select g.teamID,t.teamName, g.homeTeam from go_publicgames_combatants g LEFT JOIN go_teams_lu t on g.teamID = t.id where gameID = $gameID order by g.homeTeam desc";
      if (Config::getDebug()) $this->LOG->log($sql,PEAR_LOG_DEBUG);
      $cursor=@mysqli_query($link,$sql);
      if (!$cursor) $this->mydie(mysqli_error($link),$link);
      if (mysqli_num_rows($cursor) != 2) return false;
      $row = @mysqli_fetch_assoc($cursor);
/*Add JHM - public Games */
      $this->setHomeTeamID($row['teamID']);
      $this->setHomeTeam($row['teamName']);
      $row = @mysqli_fetch_assoc($cursor);
      $this->setVisitingTeamID($row['teamID']);
      $this->setVisitingTeam($row['teamName']);
/*Finish add */
      return true;
   } //getPublicGameCombatants

   private function mydie($message,$link=null) {
      $this->LOG->log("$message",PEAR_LOG_ERR);
      if (isset($link)) $link->close();
      die($msg);
   } //function

     /* return XML formatted output = for use when being called by webservice */
   public function __toString() {
         if ($this->outputFlag=='xml') {
            $xml = "<publicgames>";
            $xml .= $this->toStringXML();
            $xml .= "</publicgames>";
            return $xml;
	 }
	 return "";
   } //function

   private function toStringXML() {
     $xml="";
     Utility::$emitForm="string";
     $xml .=Utility::emitXML("","publicgame",0);
     $xml .=Utility::emitXML($this->getGameID(),"gameid");
     $xml .=Utility::emitXML($this->getTitle(),"title");
     $xml .=Utility::emitXML($this->getEventName(),"eventname");
     $xml .=Utility::emitXML($this->getEventDate(),"eventdate");
     $xml .=Utility::emitXML($this->getDescription(),"description");
     $xml .=Utility::emitXML($this->getHomeTeam(),"hometeam");
     $xml .=Utility::emitXML($this->getVisitingTeam(),"visitingteam");
     $xml .=Utility::emitXML($this->getTypeName(),"type");
     $xml .=Utility::emitXML($this->getSportName(),"sport");
     $xml .=Utility::emitXML($this->getLeagueName(),"league");
     $xml .=Utility::emitXML($this->getFavorite(),"favorite");
     $xml .=Utility::emitXML($this->getNumberofSubscribers(),"numbersubscribers");
     $xml .=Utility::emitXML("","publicgame",0);
   return $xml;
   } //function

}//class Game
?>
