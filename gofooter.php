<?php
$include_path=ini_get('include_path');
ini_set('include_path','/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
/* Author: Julio Hernandez-Miyares
   Date: May 2010
   Purpose: footer section of Body
   Austere at this point - make sure to include on all .php files that will
   return a web page
   May 29,2010 - added javascript includes to the bottom just before closing body tag
   using jquery library as the base. The files configuration stored in the 
   central COnfig class
*/
?>
<div id="gofooter">
<p class="logofooter"><a href="<?php echo(Config::getRootDomain());?>">GameOn</a></p>
<p> * Copyright &copy; 2010 Jittr, Inc. All rights reserved. Registration on or use of this site constitutes acceptance of our <a href="<?php echo(Config::getRootDomain());?>/gotos.php" target="_blank">Terms of Service</a> and <a href="<?php echo(Config::getRootDomain());?>/goprivacypolicy.php">Privacy Policy</a>. <span class="pipe">|</span> <a href="<?php echo(Config::getRootDomain());?>/godisclaimer.php" target="_blank">Disclaimer</a>
</p>
<p class="contributions">
   Design by <a href="http://www.jittr.com/about" target="_blank">Jittr, Inc.</a> <span class="pipe">|</span> Powered by <a href="http://www.mongodb.org">MongoDB</a> <span class="pipe">|</span> Hosted by <a href="http://www.dreamhost.com" target="_blank">Dreamhost</a> <span class="pipe">|</span> Web analytics by  <a href="http://www.empiricalpath.com/offer?utm_source=bi&utm_medium=partner&utm_content=footer&utm_campaign=audit">Google Analytics</a>
</p>
</div>
<?php
echo ('<script type="text/javascript" src="' . Config::getJSlib() . '"/>');
echo ('<script type="text/javascript" src="' . Config::getJSlibApp() . '"/>');
?>
</body>
</html>

