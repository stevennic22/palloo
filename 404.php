<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$RESPONSE_TITLE = '404 - FILE NOT FOUND';
$RESPONSE_BODY = 'ERROR 404 - FILE NOT FOUND</br>';

$retfilename = "return.html";
if (!file_exists($retfilename)) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 File Not Found', true, 404);
  exit;
}
$retFileInfo = [];
$rethandle = fopen($retfilename, "r");
while(!feof($rethandle)){
  $retFileInfo[] = fgets($rethandle);
}
fclose($rethandle);

$retFileInfo = str_replace("[[title]]", $RESPONSE_TITLE, $retFileInfo);
$retFileInfo = str_replace("[[body]]", $RESPONSE_BODY, $retFileInfo);

foreach($retFileInfo as $line) {
  echo $line;
}
?>