<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
/* Author: Julio Hernandez-Miyares
   Date: May 2010
   Purpose: footer section of Body
   Austere at this point - make sure to include on all .php files that will
   return a web page
*/
?>
<div id="gofooter">
<p class="logofooter"><a href="<?php echo(Config::getRootDomain());?>">GameOn</a></p>
</div>
</body>
</html>

