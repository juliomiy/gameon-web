<?php
require_once('classes/go_game.class.php');
//get server timezone
$serverTZ = date_default_timezone_get();
//sample date/time - default to New York
 $dateSrc = '2010-06-19 12:50'; 
 $timeZone = 'America/New_York';  // +2 hours     
 $dateTime = new DateTime($dateSrc, new DateTimeZone($timeZone));
 echo($dateTime->format("M:d:Y:H:i:s") . "\n");
 
 $newDateTime = Game::getDefaultSubscriptionClose('Date',$dateTime);
 echo 'DateTime::format(): '.$newDateTime->format('M:d:Y:H:i:s'); 
 exit;

 function getDefaultSubscriptionClose($typeName, $pivotDateStart, $pivotDateEnd=null) {
      
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

?>
