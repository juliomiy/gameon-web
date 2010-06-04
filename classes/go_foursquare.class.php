<?php
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/foursquare' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');
require_once('go_usersettings.class.php');

//Includes the foursquare-asyc library files
require_once('EpiCurl.php');
require_once('EpiOAuth.php');
require_once('EpiFoursquare.php');


/* Author: Julio Hernandez-Miyares
   Date: MAy 7th 2010
   Purpose: class object for Foursquare 
*/
class goFoursquare extends EpiFoursquare {

   private $LOG;
   private $userID;
   private $userSettings;

   protected $consumerKey = "XB1NE31CJ4U22EF2GA53C4ULR3SL2BG21G1M5VTRCZ3K1XW5";
   protected  $consumerSecret = "3RHRD1KJLGFFHKDMD4SCE11NHNDCFUPOIPOQW4VGKLADFKC1";

   public $FOURSQUARE_APIS = array( 
         NEARVENUE => 'http://api.foursquare.com/v1/venues',
         VENUE => 'http://api.foursquare.com/v1/venue',
   );

   /* assume argument is userID (userSetting) 
   */
   public function __construct() {
       parent::__construct($consumerKey,$consumerSecret);
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
         $userID = func_get_arg(0);
         $this->userSettings = new goUserSettings($userID);
         $this->setToken($this->userSettings->getFoursquareOAuthToken(), $this->userSettings->getFoursquareOAuthTokenSecret());
         echo($this->userSettings->getFoursquareOAuthToken()." " . $this->userSettings->getFoursquareOAuthTokenSecret());
        
       }//if
   } //construct

   public function getNearVenues($geolat, $geolong,$returnFormat="xml", $authentication=false) {

      $url = $this->FOURSQUARE_APIS['NEARVENUE'] . "." . $returnFormat ;
      $url  .=   "?geolat=" . $geolat . "&geolong=" . $geolong;
      if (Config::getDebug()) Config::getLogObject()->log("$url",PEAR_LOG_INFO);
      $curl = @curl_init($url);
      curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
      $result = @curl_exec($curl);
      @curl_close($curl);
      echo($result);

   }
   public function getVenue($venueID,$returnFormat="xml",$authentication=false) {

      $url = $this->FOURSQUARE_APIS['VENUE'] . "." . $returnFormat ;
      $url .="?vid=" . $venueID;
      if (Config::getDebug()) Config::getLogObject()->log("$url",PEAR_LOG_INFO);
      $curl = @curl_init($url);
      curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
      $result = @curl_exec($curl);
      @curl_close($curl);
      echo($result);
   }

} //class goFoursquare
?>
