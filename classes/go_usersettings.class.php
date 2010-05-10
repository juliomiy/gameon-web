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

  public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
         //$userID = func_get_arg(0);
         $this->getUserSettings(1);
         //$this->getUserSettings($userID);
       }
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

  public function setFacebookID($id) {
     $this->facebookUserID=$id;
  }

  public function setTwitterID($id) {
     $this->twitterUserID=$id;
  }

  public function setFoursquareID($id) {
     $this->foursquareUserID=$id;
  }

  public function setFacebookDefault($default) {
     $this->facebookDefault=$default;
  }

  public function setTwitterDefault($default) {
     $this->twitterDefault=$default;
  }

  public function setFoursquareDefault($default) {
     $this->foursquareDefault=$default;
  }

  public function setTwitterOAuthTokenSecret($oauth) {
     $this->twitterOAuthTokenSecret = $oauth;
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
        $this->setFacebookID($row['facebookID']); 
        $this->setTwitterID($row['twitterID']); 
        $this->setFoursquareID($row['foursquareID']); 
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
     if (Config::getDebug()) $LOG->log("$sql",PEAR_LOG_INFO);
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

} //class goUserSettings
