<?php
ob_start();
include('htmlhead.php');
echo ("<body>");
if (!isset($_GET['title'])) {
?>
<br />
Facebook:
<input type="textbox" name="network" value="facebook" />
<br />
Twitter:
<input type="textbox" name="network" value="twitter" />
<br />
FourSquare
<input type="textbook" name="network" value="foursquare" />
<br />


