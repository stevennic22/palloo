<?php
$baseFileName = basename(__FILE__, '.php');
include 'sharedFuncs.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

log_out($_SERVER['REQUEST_URI']);
$headers = apache_request_headers();
log_out("SERVER Info: " . http_build_query($_SERVER,'',', '));
log_out("Headers: " . http_build_query($headers,'',', '));

$RESPONSE_TITLE = '400 - Bad Request';
log_out("Title: ". $RESPONSE_TITLE);
$RESPONSE_BODY = 'ERROR 400 - BAD REQUEST</br>';
log_out("Body: ". $RESPONSE_BODY);

if (!file_exists("return.html")) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
  exit;
} else {
  respondToRequest($RESPONSE_TITLE, $RESPONSE_BODY, "favicon.ico");
}
?>