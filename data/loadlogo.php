<?php
/* Author: Julio Hernandez-Miyares
   Date: August 10,2010
   Purpose: take a csv file containing teamName Logo Url  and
   transactionally load into gameOn go_teams_lu table, store the uri to images/raw
   and (FUTURE) resize the images to various sizes
*/
require_once('../classes/config.class.php');
$LOG = Config::getLogObject();
$programName = array_shift($argv);
if (count($argv) != 4)  {
   mydie("Usage: $programName inputFile, , sportID, sportName, LeagueName\n");
}
$inputFile = array_shift($argv);
define("ERROR_TOLERANCE",1);
define("IMAGE_RAW_DIRECTORY","../images/raw");
define("FIELD_DELIMITER",",");

$recordsProcessed=0;

//open input file
$fileHandle = fopen($inputFile, 'r');
$link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
if (!$link) mydie("Error connecting to Database \n" . "Error No:" . mysqli_connect_errno() .
             "\n Error = " . mysqli_connect_error() .
             "\n using database server = " . Config::getDatabaseServer() .
             "\n Database User = " . Config::getDatabaseUser() .
             "\n Database Password = " . Config::getDatabasePassword() .
             "\n Database = " . Config::getDatabase() . "\n"  );

while (!feof($fileHandle)) {
  $recordsProcessed++;
//read a record and parse into array
  $line = fgets($fileHandle,4096);
  $lineArray = split(FIELD_DELIMITER,$line);
  $teamName = $lineArray[0];
  $imageURL = $lineArray[1];
  
  $fullSavePath = saveImage($teamName, $imageURL);
  //echo $line;
}

fclose($fileHandle); //close input file
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

