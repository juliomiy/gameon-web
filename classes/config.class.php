<?php
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('Log.php');
/* 
 * Author: Julio Hernandez-Miyares
 * April 2010 
 * Only properties that matter at the moment are the Database settings 
 * Class with static methods to return configuration information
 * TODO - remove hardcoding and use file system/db for settings
 * TODO - modify .htaccess to prevent any of these files being listed
 * TODO - set php.ini for class path
 * TODO - review need for Paypal configuration - right now not used
*/

/**
 * Description of config
 *
 * @author juliomiyares
 */
class Config {
    //put your code here
    const FAVICONURL="favicon-1.ico";
    const LOGOURL="jittr_logo.png";
    const  JSLIB = "http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js";
    const COMMONCSS = "common.css";
    const SCRIPTDIR =  "scripts";
    const IMAGEDIR = "images";
    private static $copyright;
    private static $termsofservice;
//database configuration
    const DATABASE ="jittrgameon";
    const DBPASSWORD="android1";
    const DBUSER="jittr";
    const DBSERVER="jittrgameon.juliomiyares.com";
    const DEBUG=true;
//End Database configuration    
    private static $log;
    static private $payPalBusinessAccount;
    static private $payPalBusinessPW;
    static private $payPalApiSignature;
//API settings
    const API_DOMAIN="http://jittr.com/jittr/gameon";
    const ROOT_DOMAIN="http://jittr.com/jittr/gameon";
    const SITEKEY ="GAMEON";
//Oauth Settings for various Social Networks
    const FOURSQUARE_CONSUMERKEY="AROV4OCBZMPAMSHLCF3LLYWQQ0W0F2WH1K1BDZDGBW1OFJSM";
    const FOURSQUARE_CONSUMERKEYSECRET="IT5NN1BSTKCORZ3YVC32BRAJD5O4201TCCAGAK2KAMUYHOQD";

    const FACEBOOK_CONSUMERKEY="113817188649294";
    const FACEBOOK_CONSUMERKEYSECRET="d0e1c39b00814c1cb4819f5133338c89";

    const TWITTER_CONSUMERKEY="";
    const TWITTER_CONSUMERKEYSECRET="";
 
    private function  __construct() {

    } //constructor

    public static function getRootDomain() {
       return self::ROOT_DOMAIN;
    }

    public static function getFoursquareConsumerKey() {
       return self::FOURSQUARE_CONSUMERKEY;
    }

    public static function getFoursquareConsumerKeySecret() {
       return self::FOURSQUARE_CONSUMERKEYSECRET;
    }

    public static function getFacebookConsumerKey() {
       return self::FACEBOOK_CONSUMERKEY;
    }

    public static function getFacebookConsumerKeySecret() {
       return self::FACEBOOK_CONSUMERKEYSECRET;
    }

    public static function getAPIDomain() {
       return self::API_DOMAIN;
    }

    public static function getPayPalBusinessAccount() {
        if (!isset(self::$payPalBusinessAccount)) {
            self::getSiteConfiguration();
        } //if
        return self::$payPalBusinessAccount;
    }
    public static function getPayPalBusinessPW() {
         if (!isset(self::$payPalBusinessPW)) {
            self::getSiteConfiguration();
        } //if
        return self::$payPalBusinessPW;
    }

    public static function getPayPalApiSignature() {
         if (!isset(self::$payPalApiSignature)) {
            //self::$payPalBusinessAccount = "julio._1183208236_biz_api1.gmail.com";
            self::getSiteConfiguration();
        } //if
        return self::$payPalApiSignature;
    }
    public static function getDebug() {
        return self::DEBUG;
    }

    public static function getDatabase() {
        return self::DATABASE;
    }
    public static function getDatabaseUser() {
        return self::DBUSER;
    }
    public static function getDatabasePassword() {
        return self::DBPASSWORD;
    }
    public static function getDatabaseServer() {
        return self::DBSERVER;
    }
    public static function getFaviconURL() {
        return self::IMAGEDIR . "/" . self::FAVICONURL;
    }
    
    public static function getLogoURL() {
        return self::IMAGEDIR . "/" . self::LOGOURL;
    }
    
    public static function getJSlib() {
        return  self::JSLIB;
    }
    public static function getCommonCSS() {
        return self::SCRIPTDIR . "/" . self::COMMONCSS;
    }

    public static function getScriptDir() {
        return self::SCRIPTDIR;
    }

    public static function getImageDir() {
        return self::IMAGEDIR;
    }

    public static function setFavison($favicon) {
        //return self:
    }

    public static function setJSlib($jslib) {
        $this->jslib = $jslib;
    }
    public static function setCommonCSS($css) {
        $this->commoncss = $css;
    }
    public static function setScriptDir($scriptdir) {
        $this->scriptdir=$scriptdir;
    }
    public static function setImageDir($imagedir) {
        $this->imagedir=$imagedir;
    }
    public static function getCopyRight() {
        return "<p><span id=GF_footer_copyright>&copy; 2010 Miyares Web Solution, LLC All Rights Reserved </span></p>";
    }
    public static function getLogObject() {
        if (!isset(self::$log)) {
        // create Log object
	   $ident=$conf=$level=null;
           self::$log = &Log::singleton("file", "/home/juliomiyares/jittr.com/jittr/gameon/log/gameon.log","GAMEON");
        } //if
        return self::$log;
    }
    /*
     * @abstract - retrieve site configuration values primarily from database
     * @version 1.0
     * @author Julio Hernandez-Miyares
     */
    private function getSiteConfiguration() {

        try {
            $db = new db();
            $sql = "select * from siteConfiguration where site='" . Config::SITEKEY . "'";
            $result = $db->query($sql);
            if ($result) { /* Fetch the results of the query */
               while( $row = mysqli_fetch_assoc($result) )  {
                  self::$payPalBusinessAccount = $row['payPalBusinessAccount'];
                  self::$payPalBusinessPW = $row['payPalBusinessPW'];
                  self::$payPalApiSignature = $row['payPalApiSignature'];
               }  //while
            } //if
        } catch (Exception $ex) {

        }  //try/catch
    }
    public static function getPayPal() {

    }  //getPayPal
} //config
?>
