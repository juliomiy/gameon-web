<?php
$include_path=ini_get('include_path');
ini_set('include_path','.:/home/juliomiyares/jittr.com/jittr/gameon/classes' . ':' . $include_path);
require_once('config.class.php');
require_once('goutility.class.php');

/* Author: Julio Hernandez-Miyares
   Date: MAy 7th 2010
   Purpose: class object for userSettings 
*/
class goUserSettings {
  private $userID;
  private $userName;
  private $facebookUserID;
  private $twitterUserID;
  private $foursquareUserID;
  private $facebookDefault;
  private $twitterDefault;
  private $foursquareDefault;
  private $twitterOAuthToken;
  private $twitterOAuthTokenSecret;
  private $twitterProfileImageUrl;
  private $foursquareOAuthToken;
  private $foursquareOAuthTokenSecret;
  private $foursquareProfileImageUrl;
  private $facebookOAuthToken;
  private $facebookOAuthTokenSecret;
  private $facebookProfileImageUrl;
  private $LOG;
  private $outputFlag;

  public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
         $userID = func_get_arg(0);
         $this->getUserSettings($userID);
       }
  }

/* determines the type of output returned by _toString. currently one supported is xml
*/
  public function setOutputFlag($in) {
      $this->outputFlag=$in;
  }

  public function setUserID($userID) {
     $this->userID = $userID;
  }   

  public function getUserID() {
      return $this->userID;
  }   

  public function setUserName($userName) {
     $this->userName = $userName;
  }   

  public function getUserName() {
      return $this->userName;
  }   

  public function isDefaultFacebook() {
     return $this->facebookDefault;
  }

  public function isDefaultTwitter() {
     return $this->twitterDefault;
  }

  public function isDefaultFoursquare() {
     return $this->foursquareDefault;
  }

  public function hasFaceBook() {
     return !empty($this->facebookUserID);
  }

  public function hasTwitter() {
     return !(empty($this->twitterUserID) || empty($this->twitterOAuthToken) || empty($this->twitterOAuthTokenSecret));
  }

  public function hasFoursquare() {
     return !(empty($this->foursquareUserID));
  }
  public function getFacebookUserID() {
     return $this->facebookUserID;
  }

  public function setFacebookUserID($id) {
     $this->facebookUserID=$id;
  }

  public function setTwitterUserID($id) {
     $this->twitterUserID=$id;
  }

  public function getTwitterUserID() {
     return $this->twitterUserID;
  }

  public function setFoursquareUserID($id) {
     $this->foursquareUserID=$id;
  }

  public function getFoursquareUserID() {
     return $this->foursquareUserID;
  }

  public function setFacebookDefault($default) {
     $this->facebookDefault=$default;
  }

  public function getFacebookDefault() {
     return $this->facebookDefault;
  }

  public function getFacebookOAuthToken() {
     return $this->facebookOAuthToken;
  }

  public function getFacebookOAuthTokenSecret() {
     return $this->facebookOAuthTokenSecret;
  }

  public function setTwitterDefault($default) {
     $this->twitterDefault=$default;
  }

  public function getTwitterDefault() {
     return $this->twitterDefault;
  }

  public function setFoursquareDefault($default) {
     $this->foursquareDefault=$default;
  }

  public function getFoursquareDefault() {
     return $this->foursquareDefault;
  }

  public function getTwitterOAuthTokenSecret() {
     return $this->twitterOAuthTokenSecret;
  }
  public function setTwitterOAuthTokenSecret($oauth) {
     $this->twitterOAuthTokenSecret = $oauth;
  }
  
  public function getTwitterOAuthToken() {
     return $this->twitterOAuthToken;
  }
  public function setTwitterOAuthToken($oauth) {
     $this->twitterOAuthToken = $oauth;
  }
  
  public function getTwitterProfileImageUrl() {
     return $this->twitterProfileImageUrl; 
  }
  
  public function setTwitterProfileImageUrl($url) {
     $this->twitterProfileImageUrl = $url;
  }
  
  public function setFoursquareOAuthTokenSecret($oauth) {
     $this->foursquareOAuthTokenSecret = $oauth;
  }
  
  public function setFoursquareOAuthToken($oauth) {
     $this->foursquareOAuthToken = $oauth;
  }
  
  public function getFoursquareOAuthTokenSecret() {
     return $this->foursquareOAuthTokenSecret;
  }
  
  public function getFoursquareOAuthToken() {
     return $this->foursquareOAuthToken;
  }

  public function getFoursquareProfileImageUrl() {
     return $this->foursquareProfileImageUrl; 
  }
  
  public function setFoursquareProfileImageUrl($url) {
     $this->foursquareProfileImageUrl = $url;
  }
  public function setFacebookOAuthTokenSecret($oauth) {
     $this->facebookOAuthTokenSecret = $oauth;
  }
  
  public function setFacebookOAuthToken($oauth) {
     $this->facebookOAuthToken = $oauth;
  }
  
  public function getFacebookProfileImageUrl() {
     return $this->facebookProfileImageUrl; 
  }
  
  public function setFacebookProfileImageUrl($url) {
     $this->facebookProfileImageUrl = $url;
  }
  /* checks the credentials passed in to log in
     returns > 0 (userID) if the login is successful
     0 otherwise
  */
  public static function login($userName, $password) {
     $userID=0;
     $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
     if (!$link) mydie("Error connecting to Database");
   
     $sql=sprintf("select userID from go_user where userName='%s' and password='%s'",
             mysqli_real_escape_string($link,$userName),
             mysqli_real_escape_string($link,$password));

     if (Config::getDebug()) Config::getLogObject()->log("$sql",PEAR_LOG_INFO);
     $cursor=@mysqli_query($link,$sql);
     if (!$cursor) die(mysqli_error($link));
     $row = @mysqli_fetch_assoc($cursor);
     if ($row) {
        $userID = $row['userID'];
     }
     $link->close(); 
     
     if (Config::getDebug()) Config::getLogObject()->log("Value of userID = $userID");
     return $userID;
  }
  /* retrieve GameOn user by userID or userName, default is by userID
  */
  public function getUserSettings($user,$isName=null) {

     $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
     if (!$link) die("Error connecting to Database");
       
     if (empty($isName)) //retrieve by userID
        $sql=sprintf("select g.userName,s.* from go_user g, go_userSettings s where g.userID='%s' and g.userID=s.userID ",mysqli_real_escape_string($link,$user));
     else  //retrieve by userName
        $sql=sprintf("select g.userName,s.* from go_user g, go_userSettings s where g.userName='%s' and g.userID=s.userID ",mysqli_real_escape_string($link,$user));
  
     if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
     $cursor=mysqli_query($link,$sql);
     if (!$cursor) die(mysqli_error($link));
     $row = mysqli_fetch_assoc($cursor);
     if ($row) {
        $this->setUserID($row['userID']); 
        $this->setUserName($row['userName']); 
        $this->setFacebookUserID($row['facebookID']); 
        $this->setTwitterUserID($row['twitterID']); 
        $this->setFoursquareUserID($row['foursquareID']); 
        $this->setFacebookDefault($row['facebookDefault']); 
        $this->setTwitterDefault($row['twitterDefault']); 
        $this->setFoursquareDefault($row['foursquareDefault']); 
	$this->setTwitterOAuthToken($row['twitterOAuthToken']);
	$this->setTwitterOAuthTokenSecret($row['twitterOAuthTokenSecret']);
	$this->setTwitterProfileImageUrl($row['twitterImageUrl']);
	$this->setFoursquareOAuthToken($row['foursquareOAuthToken']);
	$this->setFoursquareOAuthTokenSecret($row['foursquareOAuthTokenSecret']);
	$this->setFoursquareProfileImageUrl($row['foursquareImageUrl']);
	$this->setFacebookOAuthToken($row['facebookOAuthToken']);
	$this->setFacebookOAuthTokenSecret($row['facebookOAuthTokenSecret']);
	$this->setFacebookProfileImageUrl($row['facebookImageUrl']);
     } 
     $link->close();
     return true;
  }

  public function updateTwitterOAuth($userID, $twitterID, $imageUrl, $sec, $tok) {

     $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
     if (!$link) die("Error connecting to Database");
       
     $sql=sprintf("update go_userSettings set twitterID='%s',twitterImageUrl='%s',twitterOAuthToken='%s',twitterOAuthTokenSecret='%s' where userID='%s'",
             mysqli_real_escape_string($link,$twitterID),
             mysqli_real_escape_string($link,$imageUrl),
             mysqli_real_escape_string($link,$tok),
             mysqli_real_escape_string($link,$sec),
             mysqli_real_escape_string($link,$userID));
     if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
     $cursor=mysqli_query($link,$sql);
     if (!$cursor) die(mysqli_error($link));
     $link->close();
     return true;
  }
  public function updateFoursquareOAuth($userID, $foursquareID, $imageUrl, $sec, $tok) {

     $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
     if (!$link) die("Error connecting to Database");
       
     $sql=sprintf("update go_userSettings set foursquareID='%s',foursquareImageUrl='%s',foursquareOAuthToken='%s',foursquareOAuthTokenSecret='%s' where userID='%s'",
             mysqli_real_escape_string($link,$foursquareID),
             mysqli_real_escape_string($link,$imageUrl),
             mysqli_real_escape_string($link,$tok),
             mysqli_real_escape_string($link,$sec),
             mysqli_real_escape_string($link,$userID));
     if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
     $cursor=mysqli_query($link,$sql);
     if (!$cursor) die(mysqli_error($link));
     $link->close();
     return true;
  }
  public function updateFacebookOAuth($userID, $facebookID, $imageUrl, $sec, $tok) {

     $link = mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
     if (!$link) die("Error connecting to Database");
     if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
     $cursor=mysqli_query($link,$sql);
     if (!$cursor) die(mysqli_error($link));
     $link->close();
     return true;
  }

  /* return XML formatted output = for use when being called by webservice */ 
  public function __toString() {
      if ($this->outputFlag=='xml') 
         return $this->toStringXML();
      return "";
  }
  /* return XML formatted output = for use when being called by webservice */ 
  private function toStringXML() {
     $xml="";

     Utility::$emitForm="string";
     $xml .= Utility::emitXML("","usersettings",0);

     $xml .=Utility::emitXML($this->getUserID(),"userid");
     $xml .=Utility::emitXML($this->getUserName(),"username");
     $xml .=Utility::emitXML($this->getFacebookUserID(),"facebookuserid");
     $xml .=Utility::emitXML($this->getFacebookProfileImageURL(),"facebookprofileimageurl");
     $xml .=Utility::emitXML($this->getTwitterUserID(),"twitteruserid");
     $xml .=Utility::emitXML($this->getTwitterProfileImageURL(),"twitterprofileimageurl");
     $xml .=Utility::emitXML($this->getFoursquareUserID(),"foursquareuserid");
     $xml .=Utility::emitXML($this->getFoursquareProfileImageURL(),"foursquareprofileimageurl");

     $xml .= Utility::emitXML("","settings",0);
     $xml .= Utility::emitXML($this->getFacebookDefault(),"facebookdefault");
     $xml .= Utility::emitXML($this->getTwitterDefault(),"twitterdefault");
     $xml .= Utility::emitXML($this->getFoursquareDefault(),"foursquaredefault");
     $xml .= Utility::emitXML("","settings",0);

     $xml .=Utility::emitXML("","usersettings",0);
     return $xml;
  }

} //class goUserSettings
