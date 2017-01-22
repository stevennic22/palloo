<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$logHandler = '';
$fLogPath = '';

function make_log(){
  global $logHandler;
  global $fLogPath;
  $logDir = "LOGS";
  if(!is_dir($logDir)){
    mkdir($logDir);
  }

  $logFileName = "403Error" . date("ymdHis") . ".LOG";

  $fLogPath = $logDir . "\\" . $logFileName;
  if (!file_exists($fLogPath)) {
    touch($fLogPath);
  }

  $logHandler = fopen($fLogPath, 'w+');
  fwrite($logHandler,date("[Y-m-d H:i:s] ") . "Log file created\r\n\r\n");
}

function log_out($msg, $deleteMe = false){
  echo $deleteMe;
  global $logHandler;

  if ($deleteMe === false) {
    if ($logHandler == '') {
      make_log();
    }

    $msg = date("[Y-m-d H:i:s] ").$msg."\r\n\r\n";

    try {
      flock($logHandler, LOCK_EX);
      fwrite($logHandler, $msg);
      flock($logHandler, LOCK_UN);
    } catch (Exception $e) {
      echo "Error: " . $e->getMessage() . "</br>";
    }
  } else {
    global $fLogPath;
    fclose($logHandler);
    unlink($fLogPath);
  }
}

$RESPONSE_TITLE = '403 - FORBIDDEN';
log_out("Title: ". $RESPONSE_TITLE);
$RESPONSE_BODY = 'ERROR 403 - FORBIDDEN</br>';
log_out("Body: ". $RESPONSE_BODY);

$retfilename = "return.html";
if (!file_exists($retfilename)) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
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