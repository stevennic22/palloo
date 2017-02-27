<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$baseFileName = basename(__FILE__, '.php');
include 'sharedFuncs.php';

$RESPONSE_TITLE = 'INTERNAL SERVER ERROR - 500';
log_out("Title: ". $RESPONSE_TITLE);
$RESPONSE_BODY = 'ERROR 500 - INTERNAL SERVER ERROR</br>';
log_out("Body: ". $RESPONSE_BODY);

$retfilename = "return.html";
if (!file_exists($retfilename)) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
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