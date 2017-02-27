<?php
$baseFileName = basename(__FILE__, '.php');
include 'sharedFuncs.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

log_out($_SERVER['REQUEST_URI']);
$headers = apache_request_headers();
log_out(http_build_query($headers,'',', '));

$RESPONSE_TITLE = '404 - FILE NOT FOUND';
log_out("Title: ". $RESPONSE_TITLE);
$RESPONSE_BODY = 'ERROR 404 - FILE NOT FOUND</br>';
log_out("Body: ". $RESPONSE_BODY);

$retfilename = "return.html";
if (!file_exists($retfilename)) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 File Not Found', true, 404);
  exit;
}

log_out("Opening template...");
$retFileInfo = [];
$rethandle = fopen($retfilename, "r");
while(!feof($rethandle)){
  $retFileInfo[] = fgets($rethandle);
}
fclose($rethandle);

log_out("Replacing default template strings...");
$retFileInfo = str_replace("[[title]]", $RESPONSE_TITLE, $retFileInfo);
$retFileInfo = str_replace("[[body]]", $RESPONSE_BODY, $retFileInfo);

log_out("Returning template information...");
foreach($retFileInfo as $line) {
  echo $line;
}
?>