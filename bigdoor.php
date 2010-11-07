<?php
/* Test BigDoor API 
*/
$id="9782";
$id="julio@jittr.com";
$applicationKey = "4933aa1974624d7fb30dea6910c082db";
$secretKey = "f00bb881812a49bda9513d8ca3a6483b";
$format="json";
$urlTranSummary="http://api.bigdoor.com/api/publisher/$applicationKey/transaction_summary";
$urlAwardSummary="http://api.bigdoor.com/api/publisher/$applicationKey/award_summary";

$urlCurrencyType="http://api.bigdoor.com/api/publisher/$applicationKey/currency_type";

$urlEndUser="http://api.bigdoor.com/api/publisher/$applicationKey/end_user";
$urlEndUserID = "http://api.bigdoor.com/api/publisher/$applicationKey/end_user/$id?format=$format";

$url = $urlEndUserID;

$curl = curl_init($url);
curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
$result = curl_exec($curl);
curl_close($curl);
echo($result);

?>
