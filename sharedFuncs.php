<?php

$logHandler = '';
$fLogPath = '';

function stripper($data) {
  $data = str_replace(array("\n","\r"," "), '', $data);
  return $data;
}

function input_cleanse($data) {
  $data = trim($data);
  $data = stripslashes($data);
  return $data;
}

function make_log(){
  global $logHandler;
  global $fLogPath;
  $logDir = "LOGS";
  if(!is_dir($logDir)){
    mkdir($logDir);
  }
  
  global $baseFileName;
  $logFileName = $baseFileName . date("ymdHis") . ".LOG";

  $fLogPath = $logDir . "\\" . $logFileName;
  if (!file_exists($fLogPath)) {
    touch($fLogPath);
  }

  $logHandler = fopen($fLogPath, 'w+');
  fwrite($logHandler,date("[Y-m-d H:i:s] ") . "Log file created\r\n\r\n");
}

function log_out($msg, $deleteMe = false){
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

function respondToRequest($title = "Help", $body = "Please contact the administrator for assistance.", $favicon = "favicon.ico", $fileType = "html") {
  $resFilename = "return";

  if (file_exists($resFilename . "." . strtolower($fileType))) {
    log_out("Filetype (" . $fileType . ") exists, using it for response.");
    $resFilename = $resFilename . "." . strtolower($fileType);
  } else {
    log_out("Filetype (" . $fileType . ") does not exist, using HTML for response.");
    $resFilename = $resFilename . ".html";
  }

  $resFileInfo = [];
  $resFilehandle = fopen($resFilename, "r");
  while(!feof($resFilehandle)){
    $resFileInfo[] = fgets($resFilehandle);
  }
  fclose($resFilehandle);

  log_out("Replacing default template strings");
  $resFileInfo = str_replace("[[favicon]]", $favicon, $resFileInfo);
  log_out("Favicon: " . $favicon);
  $resFileInfo = str_replace("[[title]]", $title, $resFileInfo);
  log_out("Title: ". $title);
  $resFileInfo = str_replace("[[body]]", $body, $resFileInfo);
  log_out("Body: ". $body);

  log_out("Returning template response");
  foreach($resFileInfo as $line) {
    echo $line;
  }
}

?>