<?php
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');

/* Author: Julio Hernandez-Miyares
   date: November 7,2010
   ActivityReward Class
   dependent on go_activityRewards table
*/

class ActivityReward {
  private $activityReward;
  private $activityRewardID;
  private $activityRewardAmount;
  private $activityRewardType;
  private $statusCode;
  private $statusMessage;

  public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
          $activityReward= func_get_arg(0);
          $activityReward=trim(strtolower($activityReward));
          $this->getActivityReward($activityReward);
       } //if
   } //constructor

  public function getActivityReward($activityReward) {
     $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) return GAMEON_ERROR;

      $sql  = sprintf("select * from go_activityRewards  where activityName= '%s'",
            mysqli_real_escape_string($link,$activityReward));

      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
      $cursor=@mysqli_query($link,$sql);
      if (isset($cursor)) {
          $row = @mysqli_fetch_assoc($cursor);
          if (isset($row) && count($row) > 0) {
             $this->setActivityRewardID($row['activityID']);
             $this->setActivityRewardName($row['activityName']);
             $this->setActivityRewardType($row['rewardType']);
             $this->setActivityRewardAmount($row['amount']);
             $rv = GAMEON_OK;
             $this->setStatusCode(GAMEON_OK);
          } else {
             $rv = GAMEON_NORECORD;
          }
      }  else  { //if
          $rv = GAMEON_ERROR;
      } //else

      if (isset($cursor)) $cursor->close();
      if (isset($link)) $link->close();
      return $rv;

  } //getActivityReward

//getters/setter
 public function getActivityRewardName() {
     return $this->activityReward;
 }
 public function setActivityRewardName($in) {
     $this->activityReward=$in;
 }

 public function getActivityRewardID() {
     return $this->activityRewardID;
 }
 public function setActivityRewardID($in) {
     $this->activityRewardID=$in;
 }

 public function getActivityRewardType() {
     return $this->activityRewardType;
 }
 public function setActivityRewardType($in) {
     $this->activityRewardType=$in;
 }
 public function getActivityRewardAmount() {
     return $this->activityRewardAmount;
 }
 public function setActivityRewardAmount($in) {
     $this->activityRewardAmount=$in;
 }
 public function getLastCode() {
     return $this->statusCode;
 }
 public function setStatusCode($in) {
     $this->statusCode=$in;
 }

  public function getLastMessage() {
     return $this->statusMessage;
  }

 public function setOutputFlag($in) {
      $this->outputFlag=strtolower($in);
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
     $xml .= Utility::emitXML("","activityreward",0);

     $xml .=Utility::emitXML($this->getLastCode(),"status_code");
     $xml .=Utility::emitXML($this->getLastMessage(),"status_message");
     $xml .=Utility::emitXML($this->getActivityRewardID(),"activityrewardid");
     $xml .=Utility::emitXML($this->getActivityRewardName(),"activityrewardname");
     $xml .=Utility::emitXML($this->getActivityRewardAmount(),"activityrewardamount");
     $xml .=Utility::emitXML($this->getActivityRewardType(),"activityrewardtype");
               
     $xml .= Utility::emitXML("","activityreward",0);
     return $xml;
  }
}
?>
