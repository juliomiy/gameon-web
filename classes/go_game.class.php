<?php
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');

/* Author: Julio Hernandez-Miyares
   Date: May 9th 2010
   Purpose:Plain all Php object encapsulating game model 
   TODO: Return xml and other formats ie json 
   TODO: currently use normalized table necessitating joins - ok for now but won't scale properly,
         Not using Denormalized just yet
   Modified: August 19,2010 by Julio Hernandez-Miyares
     include .gobasicdefines and change how ini_path is set
     add query to database go_publicGames keyed by gameID to set the object based on row in table
     add getQuery function to return the necessary Sql to the function that will actually query the db
     the getQuery function will determine if it's a go_games or a go_publicgames query.
     The return set will be maintained identical regardless of which query is crafted
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
   private $pivotDate;
   private $pivotCondition;
   private $createdByUserID;
   private $createdByUserName;
/*Added for public Games - by JHM August 19,2010*/
   private $team1ID;
   private $team2ID;
   private $teamName1;
   private $teamName2;
/* end of additions */
   private $subscriptionCloseDate;  // date the subscription period for accepting wager lapses
   private $subscriptionOpen;   //true if open, false otherwise , if true, users can still assume/take the bet
   private $syndicationUrl;    // shortened syndication url to game/wager
   private $LOG;

   // dates will be assumed to be in DateTime object format with the local of the user specified
   // initially all date/time will be normalized for server time - normalization for GMT is a small step after that
   public static function getDefaultSubscriptionClose($typeName, $pivotDateStart, $pivotDateEnd=null) {

      if (get_class($pivotDateStart) != "DateTime") return null;
      $serverTZ = date_default_timezone_get();
      $pivotDateStart->setTimeZone(new DateTimeZone($serverTZ));
      $timeNow = new DateTime("now",new DateTimeZone($serverTZ));

      $timeNowTMZ= strtotime($timeNow->format("Y-m-dTH:i:s"));  //convert to int - unix epoch
      $pivotTMZ = strtotime($pivotDateStart->format("Y-m-dTH:i:s")); //convert to int - unix epoch
      $diffTMZ=  ($pivotTMZ-$timeNowTMZ)/2; //take half of the difference
      $closeDateTMZ = $pivotTMZ - $diffTMZ;   //calculate defaultCLoseDate in Unix epoch
      $closeDate = date("Y-m-dTH:i:s",$closeDateTMZ);
      return new DateTime($closeDate,new DateTimeZone($serverTZ));
   }

   public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
          $gameID = func_get_arg(0);
	  $this->getGame($gameID);
       } //if
   } //constructor    

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
   public function getSubscriptionOpem() {
       return $this->subscriptionOpen;
   }    
   public function setSubscriptionOpen($in) {
      $this->subscriptionOpen=$in;
   }    

   public function getSubscriptionCloseDate() {
       return $this->subscriptionCloseDate;
   }    
   public function setSubscriptionCloseDate($in) {
      $this->subscriptionCloseDate=$in;
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
   public function getSyndicationUrl() {
      return $this->syndicationUrl;
   }
   public function setSyndicationUrl($in) {
      $this->syndicationUrl = $in;
   }
   public function getPivotDate() {
      return $this->pivotDate;
   }
   public function setPivotDate($in) {
      $this->pivotDate = $in;
   }
   public function setPivotCondition($in) {
      $this->pivotCondition = $in;
   }
   public function getPivotCondition() {
      return $this->pivotCondition;
   }

/* Craft the necessary SQL depending on whether this is a game from publicGame Table or a defined game in go_games
*/  
   private  function getQuery($gameID,$type,$link) {

    if ($type == 'game') {
       $sql=sprintf("select g.*,t.typeName,s.sportName from go_games g LEFT JOIN go_types_lu t on g.type=t.id LEFT JOIN  go_sports_lu s on g.sportID=s.id LEFT JOIN go_wagerTypes_lu w on g.wagerTypeID = w.id  where g.gameID='%u'",
           mysqli_real_escape_string($link,$gameID));
    } else if ($type =='publicgame') {
       $sql = sprint("select g.*,t.typeName,s.sportName from go_publicgames g LEFT JOIN go_types_lu t on g.type=t.id LEFT JOIN  go_sports_lu s on g.sportID=s.id where g.gameID='%s'",
           mysqli_real_escape_string($link,$gameID));
    } //if
    return $sql;
   } //getQuery

/* Retrieve Game record from go_games table and populate object properties */
/* TODO - for expediency, joins prevail - not scalable, use denormalized table or in memory cache   
*/
   public function getGame($gameID,$type='game') {
   
      $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) mydie("Error connecting to Database");

      //added JHM 8/19/2010     
      $sql = $this->getQuery($gameID,$type, $link);
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
      $this->setCreatedByUserName($row['createdByUserName']);
      $this->setTitle($row['title']);
      $this->setSportID($row['sport']);
      $this->setSportName($row['sportName']);
      $this->setTypeID($row['type']);
      $this->setTypeName($row['typeName']);
      $this->setWagerUnits($row['wagerUnits']);
      $this->setWagerTypeID($row['wagerTypeID']);
      $this->setWagerType($row['wagerType']);
      $this->setSubscriptionCloseDate($row['subscriptionClose']);
      $this->setSubscriptionOpen($row['subscriptionOpen']);
      $this->setSyndicationUrl($row['syndicationUrl']);
      $this->setPivotDate($row['pivotDate']);
      $this->setPivotCondition($row['pivotCondition']);
/*Add JHM - public Games */
      $this->setHomeTeamID($row['team1ID']);
      $this->setVisitingTeamID($row['team2ID']);
      $this->setHomeTeam($row['teamName1']);
      $this->setVisitingTeam($row['teamName2']);
/*Finish add */
   }
   private function mydie($message,$link=null) {
      $this->LOG->log("$message",PEAR_LOG_ERR);
      if (isset($link)) $link->close();
      die($msg);
   }
}//class Game
?>
