<?php
/* Author: Julio Hernandez-Miyares
   Date: August 11,2010
   Purpose:  using google mapping apis, calculate the lat/long of all team venues 
    and store in go_teams_lu associated with team name
*/
require_once('../classes/config.class.php');
$LOG = Config::getLogObject();
$programName = array_shift($argv);
/*i//if (count($argv) != 4)  {
   mydie("Usage: $programName inputFile, , sportID, sportName, LeagueName\n");
}
*/
$recordsProcessed=0;

$link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link) mydie("Error connecting to Database \n" . "Error No:" . mysqli_connect_errno() .
             "\n Error = " . mysqli_connect_error() .
             "\n using database server = " . Config::getDatabaseServer() .
             "\n Database User = " . Config::getDatabaseUser() .
             "\n Database Password = " . Config::getDatabasePassword() .
             "\n Database = " . Config::getDatabase() . "\n"  );
$sql="select * from go_teams_lu where stadiumAddress is not null and stadiumCity is not null";
$cursor= mysqli_query($link,$sql);
if (!$cursor) {
   // Server error
   mydie(mysqli_error($link) . " executing sql $sql");
} //if

$apiUrl = "http://maps.google.com/maps/api/geocode/xml?address=";
while ($row = mysqli_fetch_assoc($cursor)) {
   $teamID=$row['id'];
   $address = $row['stadiumName'] . " " . $row['stadiumAddress'] . " " . $row['stadiumCity'] . " " . $row['stadiumState'];
   $address = urlencode(htmlentities($address));
   $url = $apiUrl . $address . "&sensor=false";
   //echo $url . "\n"; 
   $curl = curl_init($url);
   curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
   $result = curl_exec($curl);
   curl_close($curl);
 
   $document=simplexml_load_string($result);
   if (!$document) continue;
//   print_r($document);
   $location = $document->result->geometry->location;
   $lat = $location->lat;
   $lng = $location->lng;
   $sql = "update go_teams_lu set latitude = $lat, longitude=$lng where id = $teamID";
   echo("$sql" . ";\n");
} //while

$link->close();      //close database
exit;

/* Saved image will be in the following format
   [IMAGE_RAW_DIRECTORY]/[NormalizedteamName].[extension]
*/
function saveImage($teamName,$img){
    $normalizedTeamName= strtolower(str_replace(' ','',$teamName));
    $fullSavePath=null;
   
    $baseName = basename($img);
    $baseNameArray = split('\.',$baseName);  //grab the extension
//    print_r( $baseNameArray); return;
    $extension = $baseNameArray[1];
    $fullSavePath = IMAGE_RAW_DIRECTORY . "/" . $normalizedTeamName . "." . $extension;
  //  return $fullSavePath;
    $ch = curl_init ($img);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $rawdata=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($fullSavePath)){
        unlink($fullSavePath);
    }
    $fp = fopen($fullSavePath,'x');
    fwrite($fp, $rawdata);
    fclose($fp);

    return $fullSavePath;
} //saveImage

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

