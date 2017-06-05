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

function countryIPCheck($IPAddress, $pubIP = False) {
  if (isset($_GET["c"])) {
    return($_GET["c"]);
  }

  if (!$pubIP) {
    $publicIP = @file_get_contents('https://api.ipify.org?format=json');
    if (!$publicIP === False) {
      $pubIP = json_decode($publicIP, true)["ip"];
    } else {
      $pubIP = $_SERVER["SERVER_ADDR"];
    }
  }

  if ((strpos($IPAddress, '192.168') === FALSE) && (strpos($IPAddress, '127.0.0.1') === FALSE) && (strpos($IPAddress, '10.0.0') === FALSE) && (strpos($IPAddress, $pubIP) === FALSE)) {
    $ip_check_1 = @file_get_contents("http://ipinfo.io/" . $IPAddress . "/json");
    
    if (!$ip_check_1 === False) {
      $returned_country = json_decode($ip_check_1, true);
      log_out("IP Check 1 (ipinfo.io): ");
      log_out($ip_check_1);
      return($returned_country["country"]);
    } else {
      $ip_check_2 = @file_get_contents("http://freegeoip.net/json" . $IPAddress);
      
      if (!ip_check_2 === False) {
        $returned_country = json_decode($ip_check_2, true);
        log_out("IP Check 2 (freegeoip.net): ");
        log_out($ip_check_2);
        return($returned_country["country_code"]);
      } else {
        return("US");
      }
    }
  } else {
    return("US");
  }
}

function isInternational($input_country, $non_int_country_codes = array("US", "CA")) {
  $int_country = True;

  foreach ($non_int_country_codes as $country) {
    if (strtolower($input_country) == strtolower($country)) {
      $int_country = False;
      break;
    }
  }

  return($int_country);
}

function respondToRequest($title = "Help", $body = "Please contact the administrator for assistance.", $favicon = "favicon.ico", $acceptType = "text/html", $exitOnEnd = true) {
  $resFilename = "return";

  if(strpos(strtolower($acceptType), 'html') !== False) {
    log_out("Filetype found in: (" . $acceptType . "), using it for response.");
    $resFilename = $resFilename . ".html";
    if(gettype($body) == "array") {
      $interimBody = $body["name"];
      $count = count($body["values"]);
      $i = 1;
      foreach($body["values"] as $item) {
        if ($i == $count) {
          $interimBody .= $item;
        } else {
          $interimBody .= $item . "</br>";
        }
        $i++;
      }
      $body = $interimBody;
    }
  } else if(strpos(strtolower($acceptType), 'json') !== False) {
    log_out("Filetype found in: (" . $acceptType . "), using it for response.");
    $resFilename = $resFilename . ".json";
    if(gettype($body) == "string") {
      $body = '"' . $body . '"';
    } else if(gettype($body) == "array") {
      $body["name"] = str_replace(array("&bull; ", "&bull;","</br>","<br>"), "", $body["name"]);
      foreach($body["values"] as &$item) {
        $item = str_replace(array("&bull; ", "&bull;","</br>","<br>"), "", $item);
      }
      $body = json_encode($body);
    }
  } else if(strpos(strtolower($acceptType), 'xml') !== False) {
    log_out("Filetype found in: (" . $acceptType . "), using it for response.");
    $resFilename = $resFilename . ".xml";
    if(gettype($body) == "array") {
      $interimBody = "\n   <name>" . $body["name"] . "</name>\n";
      foreach($body["values"] as $item) {
        $interimBody .= "    <value>" . $item . "</value>\n";
      }
      $body = $interimBody;
    }
    $body = str_replace(array("&bull; ", "&bull;"), "", $body);
    $body = str_replace(array("</br>","<br>"), "", $body);
  } else if(strpos(strtolower($acceptType), 'text') !== False) {
    log_out("Filetype found in: (" . $acceptType . "), using it for response.");
    $resFilename = $resFilename . ".txt";
    if(gettype($body) == "array") {
      $interimBody = $body["name"];
      $count = count($body["values"]);
      $i = 1;
      foreach($body["values"] as $item) {
        if ($i == $count) {
          $interimBody .= $item;
        } else {
          $interimBody .= $item . "\n";
        }
        $i++;
      }
      $body = $interimBody;
    }
    $body = str_replace("&bull;", "•", $body);
    $body = str_replace("</br>", "\n", $body);
  } else {
    log_out("Filetype (" . $acceptType . ") does not match existing return types, using HTML for response.");
    $resFilename = $resFilename . ".html";
    if(gettype($body) == "array") {
      $interimBody = $body["name"];
      $count = count($body["values"]);
      $i = 1;
      foreach($body["values"] as $item) {
        if ($i == $count) {
          $interimBody .= $item;
        } else {
          $interimBody .= $item . "</br>";
        }
        $i++;
      }
      $body = $interimBody;
    }
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
  if ($exitOnEnd) {
    exit();
  }
}

?>