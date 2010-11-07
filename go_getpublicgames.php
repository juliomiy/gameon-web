<?php
ob_start();
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
/* Author: Julio Hernandez-Miyares
   Date: April 2010
   Updated: August 7,2010
   Updated: August 11,2010
          added xml return for error
          improve search by team to allow partial matches 
          tighten getQuery which is query generator per the query paramters of webservice call
   Purpose: retrieve public games
*/
$LOG=Config::getLogObject();

//parse query parameters , called via  HTTP GET
//Filtering 
$params = Array();
$params['sport'] = $_GET['sport'];   //ie Footbal, Baseball
$params['league'] = $_GET['league']; //ie NFL, NCAA
$params['team'] = $_GET['team'];  //ie New York Mets
$params['latitude'] = $_GET['latitude'];  //near a location 
$params['longitude'] = $_GET['longitude']; //near a location
$params['timeframe'] = $_GET['timeframe']; //a positive integer value of days into the future - 0 means today
//Sorting

//open connect to database
$link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link) {
   header('HTTP/1.1 500 Internal Server Error');
   mydie("Error connecting to Database");
}
//prep hash lookup tables
$lookupHash = lookupHash($link);
//$t=$params['team'];
//echo("team " . $lookupHash[$t]); exit;
//$teamID = array_search($params['team'],$lookupHash);
//print_r($lookupHash); exit;
//exit;

$sql = getQuery($params,$lookupHash);
$cursor = mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   header('HTTP/1.1 500 Internal Server Error');
   mydie(mysqli_error($link) . " executing $sql",$link);
}
header ("content-type: text/xml");
echo '<?xml version="1.0"?>';
Utility::emitXML("",'publicgames',0);
Utility::emitXML("200",'statuscode');
Utility::emitXML("OK",'statusmessage');
$recordsEmitted=0;

// while records to read/ retrieve and emit xml 
while( $row = mysqli_fetch_assoc($cursor) )  {
   $recordsEmitted++;
   $gameID = $row['gameID'];
   $publicGameID = $row['gameID'];  //added JHM 11/7/2010 - set publicGameID Element 
   $combatants =  getGameCombatants($link,$gameID);
   $homeTeamID = $combatants['homeTeam'];
   $visitingTeamID = $combatants['visitingTeam'];
   $stadiumName = $lookupHash[$homeTeamID]['stadiumName'];
   $address = $lookupHash[$homeTeamID]['address'];
   $city= $lookupHash[$homeTeamID]['city'];
   $state= $lookupHash[$homeTeamID]['state'];
   $latitude= $lookupHash[$homeTeamID]['latitude'];
   $longitude= $lookupHash[$homeTeamID]['longitude'];
   Utility::emitXML("",'game',0);
   Utility::emitXML($gameID,'gameid');
   Utility::emitXML($publicGameID,'publicgameid'); //added JHM 11/7/2010 - add publicGameID element
   Utility::emitXML($row['sportName'],'sportname');
   Utility::emitXML($row['leagueName'],'leaguename');
   Utility::emitXML($row['seasonWeek'],'seasonweek');
   Utility::emitXML(htmlentities($row['eventName']),'eventname');
   Utility::emitXML($row['date'],'eventdatetime');
   Utility::emitXML("",'stadium',0);
   Utility::emitXML(htmlentities($stadiumName),'stadiumname');
   Utility::emitXML(htmlentities($address),'address');
   Utility::emitXML($city,'city');
   Utility::emitXML($state,'state');
   Utility::emitXML($row['fsVenueID'],'fsvenueid');
   Utility::emitXML($latitude,'latitude');
   Utility::emitXML($longitude,'longitude');
   Utility::emitXML("",'stadium',0);
   Utility::emitXML("",'teams',0);

       Utility::emitXML("",'team',0);
       Utility::emitXML(htmlentities($lookupHash[$homeTeamID]['teamName']),'teamname');
       Utility::emitXML(htmlentities($lookupHash[$homeTeamID]['teamLogoURL']),'teamlogo');
       Utility::emitXML("",'team',0);
       
       Utility::emitXML("",'team',0);
       Utility::emitXML(htmlentities($lookupHash[$visitingTeamID]['teamName']),'teamname');
       Utility::emitXML(htmlentities($lookupHash[$visitingTeamID]['teamLogoURL']),'teamlogo');
       Utility::emitXML("",'team',0);

   Utility::emitXML("",'teams',0);
   Utility::emitXML("",'game',0);
} //while
Utility::emitXML("",'publicgames',0);

// xml schema returned if successful
/*
   <publicgames>
     <game>
        <gameid>12345</gameid>
        <sport>Football</sport>
        <league>NFL</league>
        <seasonweek>5</seasonweek>
        <eventname>Miami Dolphins versus New York Jets</eventname>
        <eventdatetime>2010-09-03 01:00:00 PM</eventdatetime>
        <stadium>
           <stadiumname>Meadowlands</stadiumname>
           <address>1 Meadowland Drive, Fort Lee New Jersey</adress>
           <fsvenueid>12345</fsvenueid>
           <latitude>70.12</latitude>
           <longitude>70.12</longitue>
        </stadium>
        <teams>
           <team>
              <teamname>New York Jets</teamname>
              <teamlogo>http://gameon/jets.gif</teamlogo>
           </team>
           <team>
              <teamname>Miami Dolphins</teamname>
              <teamlogo>http://gameon/dolphins.gif</teamlogo>
           </team>
        </teams>
            
     </game>
   </publicgames>  
*/
$link->close();  /* Close Database */
ob_end_flush();
exit;

//produce query string to database based on input parameters
//for now, assume Team sports - 2 teams paring off
function getQuery($params,$lookup) {
   global $LOG;
   $where = null;  //declare
   $league = strtoupper($params['league']); //leauge query parameter
   if (!empty($league)) {
      $where = " where leagueName='" . $league . "'";
   }
   $team = $params['team'];    //team query parameter
   if (!empty($team)) { //team passed as parameter , need to lookup TeamID
      $teamID = $lookup[$team];
      if (empty($teamID)) {  //try more advanced search for team - using normalizedTeamName and nickName fields
         
      } //if
      if (!empty($teamID)) {
        $where .= (!empty($where) ? "and" : " where " ) . " gameID in (select gameID from go_publicgames_combatants where teamID = $teamID)";
      } //if 
   } //if
   $timeFrame = $params['timeframe'];
   if (!empty($timeFrame)) {
        $today= date('Y-m-d G:i:s');
        $endDateEpoch = strtotime(date("Y-m-d G:i:s", strtotime($today)) .  "+" . $timeFrame .  "day");
        $endDate = date('Y-m-d G:i:s',$endDateEpoch);
       // $endDate="2010-09-01 23:59:59";
        $where .= (!empty($where) ? "and" : " where " ) . "  date between '$today' and '$endDate' ";
   } else {
        $where .= (!empty($where)) ? " and date > now()" : " where date > now() ";
   }//if
   $basesql = "select * from go_publicgames g "; 
   $sql = $basesql . $where;
   if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
   return $sql;
} //getQuery

// to prevent difficult to process joins, will take gameID and return the combatants
//expect result of 0 or 2
function getGameCombatants($link,$gameID) {
   $sql="select teamID,homeTeam from go_publicgames_combatants where gameID=$gameID";
   $cursor = mysqli_query($link,$sql);
   if (!$cursor) {
   // Server error
      mydie(mysqli_error($link) . " executing $sql",$link);
   }
   $combatants=array();
   while( $row = mysqli_fetch_assoc($cursor) )  {
      $teamID=$row['teamID'];
      $homeTeam = $row['homeTeam'];
      
      if ($homeTeam) 
         $combatants['homeTeam'] = $teamID;
      else
         $combatants['visitingTeam'] = $teamID;
 
   } //while
   return $combatants;
}
/* to minimize joins , prep some lookup arrays from _lu tables
*/
function lookupHash($link) {
   $lookup = array();
   $sql = "select * from go_teams_lu";
   $cursor = mysqli_query($link,$sql);
   if (!$cursor) {
   // Server error
      mydie(mysqli_error($link) . " executing $sql",$link);
   }
   // while records to read/ retrieve and emit xml
   while( $row = mysqli_fetch_assoc($cursor) )  {
     $keyID=$row['id'];
     $teamName=$row['teamName'];
     $nickName=$row['nickName'];
     $teamNameNormalized=$row['teamNameNormalized'];
     $teamLogoURL=$row['teamLogoURL'];
     $stadiumName=$row['stadiumName'];
     $address=$row['stadiumAddress'];
     $city=$row['stadiumCity'];
     $state=$row['stadiumState'];
     $latitude=$row['latitude'];
     $longitude=$row['longitude'];
     $fsvenueID=$row['foursquareVenueID'];
     $arr =   array( 
                                    'teamName' => $teamName,
                                    'teamNameNormalized' => $teamNameNormalized,
                                    'teamLogoURL' => $teamLogoURL,
                                    'stadiumName' => $stadiumName,
                                    'address' => $address,
                                    'city' => $city,
                                    'state' =>$state,
                                    'latitude' =>$latitude,
                                    'longitude' => $longitude,
                                    'fsvenueID' => $fsvenueID
     );
     $lookup[$keyID] = $arr;
     $lookup[$teamName] = $keyID;
     $lookup[$nickName] = $keyID;
   } //while
   return $lookup;
}//lookupHash

//cleanup/build return xml 
/* xml returned

   <publicgames>
     <statuscode>403</statuscode>
     <statusmessage>Forbidden</statusmessage>
   <publicgames>
*/
function mydie($msg,$link=null) {
   global $LOG;
   ob_end_clean();
   $LOG->log("$msg",PEAR_LOG_ERR);
   if (isset($link)) $link->close();
   ob_start();

   header ("content-type: text/xml");
   Utility::emitXML("",'publicgames',0);
   Utility::emitXML("500",'statuscode');
   Utility::emitXML("$msg",'statusmessage');
   Utility::emitXML("",'publicgames',0);
   ob_end_flush();
   die();
}

?>
