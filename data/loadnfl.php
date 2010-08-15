<?php
/* Author: Julio Hernandez-Miyares
   Date: August 3,2010
   Purpose: take a csv file containing NFL football schedule and
   transactionally load into gameOn publicGames table
*/
require_once('../classes/config.class.php');
$LOG = Config::getLogObject();
$programName = array_shift($argv);
if (count($argv) != 5)  {
   mydie("Usage: $programName inputFile, season, sportID, sportName, LeagueName\n");
}
define("ERROR_TOLERANCE",1);

$inputFile=array_shift($argv);  //input file
define("SEASON",array_shift($argv));
define("SPORT_ID",array_shift($argv)); //from go_sports_lu 
define("SPORT_NAME",array_shift($argv)); //from go_sports_lu 
define("LEAGUE_NAME",array_shift($argv)); //from go_leagues_lu 

//define constants
define("FIELD_DELIMITER",",");
define("FIELD_WEEK_NUMBER",0);
define("FIELD_GAME_DATE",1);
define("FIELD_GAME_TIME",2);
define("FIELD_GAME_VISITING_TEAM",3);
define("FIELD_GAME_HOME_TEAM",4);

define("TYPE_ID",2);  //from go_types_lu - team sport
$recordsProcessed=0;

//open input file
$fileHandle = fopen($inputFile, 'r');
$link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
//$link = @mysqli_connect('.',Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase(),null,'mysql');
if (!$link) mydie("Error connecting to Database \n" . "Error No:" . mysqli_connect_errno() .
             "\n Error = " . mysqli_connect_error() .
             "\n using database server = " . Config::getDatabaseServer() .
             "\n Database User = " . Config::getDatabaseUser() . 
             "\n Database Password = " . Config::getDatabasePassword() . 
             "\n Database = " . Config::getDatabase() . "\n"  );

//start by deleting existing records  first from go_publicgames_combatants then from go_publicgames
$sql = sprintf("delete from go_publicgames_combatants where gameID in (select gameID from go_publicgames where leagueName='%s' and season='%u')",
       LEAGUE_NAME,SEASON);
$rc = mysqli_query($link,$sql);
if (!$rc) {
   // Server error
 mydie(mysqli_error($link) . " executing sql $sql");
} //if
$sql = sprintf("delete from go_publicgames where leagueName='%s' and season='%u'",
       LEAGUE_NAME,SEASON);
$rc = mysqli_query($link,$sql);
if (!$rc) {
   // Server error
 mydie(mysqli_error($link) . " executing sql $sql");
} //if

//loop while records
while (!feof($fileHandle)) {
  $recordsProcessed++;
//read a record and parse into array
  $line = fgets($fileHandle,4096);
  $lineArray = split(FIELD_DELIMITER,$line);
  //echo $line;
  $weekNumber = trim($lineArray[FIELD_WEEK_NUMBER]);
  $gameDate   = trim($lineArray[FIELD_GAME_DATE]);
  $gameTime   = trim($lineArray[FIELD_GAME_TIME]);
  $visitingTeam = trim($lineArray[FIELD_GAME_VISITING_TEAM]);
  $homeTeam = trim($lineArray[FIELD_GAME_HOME_TEAM]);
// massage data
  $dateArray = split("-",$gameDate); //input format is dd-mm-yyy
  $timeArray = split(":",$gameTime); //input format is hh:mm AM/PM
  if (count($timeArray) > 1) {
     $tmp = split(" ",$timeArray[1]); //split up the minute and the AM/PM
     if (count($tmp) > 1) {
        $timeArray[1] = $tmp[0];
        $timeArray[0] = (($tmp[1] == "PM") ? $timeArray[0]+12 : $timeArray[0]);
     }  //if
  } else {
     $timeArray[0]="00"; $timeArray[1]="00";
  }//if 
  $gameDateTime = $dateArray[2] . "-" . $dateArray[1] . "-" . $dateArray[0] . " " . $timeArray[0] . ":" . $timeArray[1] . ":00";
  $title = $visitingTeam . " versus " . $homeTeam;
  $eventName = $title;
  $decription = $title;

// obtain team ids from go_teams_lu
  $visitingTeamID=0; $homeTeamID=0;   //reset for each loop
  $sql="select id from go_teams_lu where teamName like '%" . $visitingTeam . "%' and sportID=" . SPORT_ID;
  if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
  $cursor = mysqli_query($link,$sql);
  if (!$cursor) {
   // Server error
   mydie(mysqli_error($link) . " executing sql $sql");
  } //if
  $row = mysqli_fetch_assoc($cursor);
  if ($row) $visitingTeamID = $row['id'];
  if ($visitingTeamID==0) continue;
 
  $sql="select id from go_teams_lu where teamName like '%" . $homeTeam . "%' and sportID=" . SPORT_ID;
  if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
  $cursor = mysqli_query($link,$sql);
  if (!$cursor) {
   // Server error
   mydie(mysqli_error($link) . " executing sql $sql");
  } //if
  $row = mysqli_fetch_assoc($cursor);
  if ($row) $homeTeamID = $row['id'];
  if ($homeTeamID==0) continue;

//save the record into database
  $sql=sprintf("insert into go_publicgames (title,eventName,date,description,type,leagueName,sportID,sportName,season,seasonWeek) values ('%s','%s','%s','%s','%u','%s','%u','%s','%u','%u')",
                 $title,$eventName,$gameDateTime,$description,TYPE_ID,LEAGUE_NAME,SPORT_ID,SPORT_NAME,SEASON,$weekNumber);
  if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
  //continue;
  $rc = mysqli_query($link,$sql);
  if (!$rc) {
   // Server error
     if (!ERROR_TOLERANCE) {
        mydie(mysqli_error($link) . " Using sql = $sql\n");
     } else {
        echo(mysqli_error($link) . " Using Sql = $sql\n");
        $LOG->log(mysqli_error($link) . " Using Sql = $sql\n");
        continue;
     } //else
  } //if
  $gameID = mysqli_insert_id($link);
//insert visiting team
  $sql = sprintf("insert into go_publicgames_combatants (gameID,teamID,homeTeam) values ('%u','%u','%u')",
          $gameID,$visitingTeamID,0);
  if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
  $rc = mysqli_query($link,$sql);
  if (!$rc) {
   // Server error
     if (!ERROR_TOLERANCE) {
        mydie(mysqli_error($link) . " Using Sql = $sql\n");
     } else {
        echo(mysqli_error($link) . " Using Sql = $sql\n");
        $LOG->log(mysqli_error($link) . " Using Sql = $sql\n");
     } //else
  } //if
//insert home team
  $sql = sprintf("insert into go_publicgames_combatants (gameID,teamID,homeTeam) values ('%u','%u','%u')",
          $gameID,$homeTeamID,1);
  if (Config::getDebug()) $LOG->log($sql,PEAR_DEBUG);
  $rc = mysqli_query($link,$sql);
  if (!$rc) {
   // Server error
     if (!ERROR_TOLERANCE) {
        mydie(mysqli_error($link) . " Using Sql - $sql\n");
     } else {
        echo(mysqli_error($link) . " Using Sql = $sql\n");
        $LOG->log(mysqli_error($link) . " Using Sql = $sql\n");
     } //else
  } //if
} //while
//close 
fclose($fileHandle); //close input file
$link->close();      //close database
exit;


//handle Error
function myDie($message) {
global $LOG;
global $link;
if ($fileHandle) fclose($fileHandle); //close input file
if ($link) $link->close();      //close database
echo("$message");
$LOG->log($message,PEAR_ERROR);
exit;
} //myDie
?>
