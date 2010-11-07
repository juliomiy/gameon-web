<?php
include('.gobasicdefines.php');
$include_path=ini_get('include_path');
ini_set('include_path',INI_PATH . ':' . $include_path);
require_once('config.class.php');
require_once('go_usersettings.class.php');
require_once('go_activityreward.class.php');

/* Author: Julio Hernandez-Miyares
   date: November 7,2010
   UserBank Class
   Manages User Bank operations
*/

class UserBank {
  private $userID;
  private $currentInPlayWager;
  private $bankBalance;
  private $overDraftLine;
  private $overDraftLineUsed;
  private $LOG;
  private $outputFlag;

  public function __construct() {
       $this->LOG = Config::getLogObject();
       if (func_num_args()) {
          $userID= func_get_arg(0);
          if (is_numeric($userID)) {
             $this->getUserBanK($userID);
          }
       } //if
   } //constructor

//get go_userBank Record for the passed in userID, populates the object if successful, returns GAMEON_ERROR if not
  public function getUserBank($userID) {
     $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) return GAMEON_ERROR;
 
      $sql  = sprintf("select * from go_userBank where userID = '%u'",
            mysqli_real_escape_string($link,$userID));

      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
      $cursor=@mysqli_query($link,$sql);
      if (isset($cursor)) {
          $row = @mysqli_fetch_assoc($cursor);
          if (isset($row) && count($row) > 0) {
             $this->setUserID($row['userID']);
             $this->setCurrentInPlayWager($row['currentInPlayWagers']);
             $this->setBankBalance($row['bankBalance']);
             $this->setOverDraftLine($row['overDraftLine']);
             $this->setOverDraftLineUsed($row['overDraftLineUsed']);
             $rv = GAMEON_OK;
          } else { 
             $rv = GAMEON_NORECORD;
          }

      } else  { //if
          $rv = GAMEON_NORECORD;
      } //else
      if (isset($cursor)) $cursor->close();
      if (isset($link)) $link->close();
      return $rv;
  } //getUserBank

//Insert UserBank Record for a given user identified by userID
  public function insertUserBank($userID,$activityName='register') {
     
      $bankBalance=0;
      if (!empty($activityName)) {
        $activityReward = new ActivityReward($activityName);
        $bankBalance = (isset($activityReward) ? $activityReward->getActivityRewardAmount() : 0);
      }
      $link = @mysqli_connect(Config::getDatabaseServer(),Config::getDatabaseUser(), Config::getDatabasePassword(),Config::getDatabase());
      if (!$link) return GAMEON_ERROR;

      $sql  = sprintf("insert into go_userBank (userID, bankBalance ) values ('%u','%u')", 
            mysqli_real_escape_string($link,$userID),
            mysqli_real_escape_string($link,$bankBalance));

      if (Config::getDebug()) $this->LOG->log("$sql",PEAR_LOG_INFO);
      $rc =@mysqli_query($link,$sql);
      $affectedRows = mysqli_affected_rows($link);
      if ($affectedRows > 0) return GAMEON_OK;
      else return GAMEON_ERROR;
  }  //insertUserBank

//setters/getters
  public function getUserID() {
     return $this->userID;
  }
  public function setUserID($in) {
     $this->userID=$in;
  }

  public function getBankBalance() {
     return $this->bankBalance;
  }
  public function setBankBalance($in) {
     $this->bankBalance=$in;
  }

  public function getOverDraftLine() {
     return $this->overDraftLine;
  }
  public function setOverDraftLine($in) {
     $this->overDraftLine=$in;
  }

  public function getOverDraftLineUsed() {
     return $this->overDraftLineUsed;
  }
  public function setOverDraftLineUsed($in) {
     $this->overDraftLineUsed=$in;
  }

  public function getCurrentInPlayWager() {
     return $this->currentInPlayWager;
  }
  public function setCurrentInPlayWager($in) {
     $this->currentInPlayWager=$in;
  }

  public function getAvailableToWager() {
      return ($this->getBankBalance() + $this->getOverDraftLine() - $this->getOverDraftLineUsed() - $this->getCurrentInPlayWager());
  }

  public function getLastCode() {
     return $this->statusCode;
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
     $xml .= Utility::emitXML("","userbank",0);

     $xml .=Utility::emitXML($this->getLastCode(),"status_code");
     $xml .=Utility::emitXML($this->getLastMessage(),"status_message");
     $xml .=Utility::emitXML($this->getUserID(),"userid");
     $xml .=Utility::emitXML($this->getBankBalance(),"bankbalance");
     $xml .=Utility::emitXML($this->getAvailableToWager(),"availabletowager");
     $xml .=Utility::emitXML($this->getCurrentInPlayWager(),"currentinplaywager");
     $xml .=Utility::emitXML($this->getOverDraftLine(),"overdraftline");
     $xml .=Utility::emitXML($this->getOverDraftLineUsed(),"overdraftlineused");

     $xml .= Utility::emitXML("","userbank",0);
     return $xml;
  }
} //class UserBank
?>
