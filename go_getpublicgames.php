<?php
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<games>';
for ($i=1;$i<7;$i++) {
echo '<game>';
echo '<id>1000</id>';
echo "<title>Super Bowl 201" . $i . '</title>';
echo '<eventname>Super Bowl</eventname>';
echo '<eventdate>2010-04-06 00:00:00</eventdate>';
echo '<description>NFL Super Bowl</description>';
echo '<type>team</type>';
echo '<sport>Football</sport>';
echo '<league>NFL</league>';
echo '<teams>';
   echo '<team1>New York Giants</team1>';
   echo '<team2>New York Jets</team2>';
echo '</teams>';
echo '<favorite>New York Giants</favorite>';
echo '<numbersubscribers>50</numbersubscribers>';
echo '</game>';
}
echo '</games>';
?>
