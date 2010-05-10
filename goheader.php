<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');

/* Author: Julio Hernandez-Miyares
   Date: May 2010
   Purpose: head section of Body
   Austere at this point - make sure to include on all .php files that will
   return a web page
*/
?>
<body>
<div id="goheader">
<p class="logo"><h2><a href="<?php echo(Config::getRootDomain());?>">GameOn</a></h2><img src="<?php echo(Config::getLogoURL());?>"/></p>
</div>

