<?php

$baseFileName = basename(__FILE__, '.php');
include 'sharedFuncs.php';

log_out($_SERVER['REQUEST_URI']);
$headers = apache_request_headers();
log_out(http_build_query($headers,'',', '));

log_out("Redirecting to HTTPS if server has it enabled.");
if (empty($_SERVER['HTTPS'])) {
	$uri = 'https://';
	$uri .= $_SERVER['HTTP_HOST'];
	header('Location: '.$uri);
}

$RESPONSE_TITLE = 'Index';
log_out("Title: ". $RESPONSE_TITLE);
$RESPONSE_BODY = '<a href="/Halloo/palloo.php">Palloo</a>';
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

log_out("Replacing favicon placeholder");
$retFileInfo = str_replace("[[favicon]]", "favicon.ico", $retFileInfo);
log_out("Replacing default template strings");
$retFileInfo = str_replace("[[title]]", $RESPONSE_TITLE, $retFileInfo);
log_out("Title: ". $RESPONSE_TITLE);
$retFileInfo = str_replace("[[body]]", $RESPONSE_BODY, $retFileInfo);
log_out("Body: ". $RESPONSE_BODY);

log_out("Returning template response...");
foreach($retFileInfo as $line) {
  echo $line;
}

if (isset($headers["User-Agent"]) && strpos(strtolower($headers["User-Agent"]), 'uptime') !== False) {
  log_out("Deleting file",true);
}
?>
