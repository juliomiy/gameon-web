<?php
require_once('classes/go_game.class.php');

if (ini_get('date.timezone')) {
    echo 'date.timezone: ' . ini_get('date.timezone');
}
echo("Currently set Server timezone " . date_default_timezone_get() . "\n");
$serverTZ = date_default_timezone_get();
$serverTZObj=new DateTimeZone($serverTZ);
$gmtTZObj = new DateTimeZone('Europe/London');
$nycTZObj = new DateTimeZone('America/New_York');
$serverDateTime = new DateTime("now",$serverTZObj);
$gmtDateTime = new DateTime("now",$gmtTZObj);
$nycDateTime = new DateTime("now",$nycTZObj);
echo("Class name is " . get_class($nycDateTime) . "\n");
echo("NYC Time = " . $nycDateTime->format('Y-m-d H:i:s') . "\n");
echo("Server Time = " . $serverDateTime->format('Y-m-d H:i:s') . "\n");
echo("GMT Time = " . $gmtDateTime->format('Y-m-d H:i:s') . "\n");
$offset = $serverTZObj->getOffset($nycDateTime);
echo("Offset = $offset" ."\n");
//$nycDateTime->add(new DateInterval('P10D'));
$nycDateTime = "2010-06-02";
$d = Game::getDefaultSubscriptionClose('Date', $nycDateTime);
print_r($d);
exit ; 
//$dd=date_add( $nycDateTime , new DateInterval('P10D' ));
//print_r($dd);
if ($d)
echo("NYC Default CLose Time = " . $d->format('Y-m-d H:i:s') . "\n");
echo(get_class($nycDateTime));
//echo($serverTZObj->getOffset( $serverDateTime));
//echo($nyTZObj->getOffset( $serverDateTime));

exit;
echo("Current time in NYC is " . date("d/m/y : H:i:s", time()));
date_default_timezone_set ("America/Denver");
echo("Current time after setting in NYC is " . date("d/m/y : H:i:s", time()));

echo date_default_timezone_get();
$gmtTimezone = new DateTimeZone('GMT');
$usaTimezone =  new DateTimeZone('America/New_York');
print_r($gmtTimezone);
var_dump($gmtTimezone);
echo($usaTimezone->getOffset( $usaTimezone));
?>
