<?php
$query=$_GET['query'];
$access_token =$_GET['access_token'];

print_r( $_GET);
exit;

if ($query == 'authorize') {
   header("Location:https://graph.facebook.com/oauth/authorize?client_id=113817188649294&redirect_uri=http://jittr.com/jittr/gameon/testfb.php&type=user_agent&display=popup");
}
if (!empty($access_token)) {
  echo("Access token - " .$access_token);
  }
  ?>


