<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('myFirstDatabase','sqlite:'.$myPath);
define('COOKIE_FILE', 'cookie.txt');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');
$myPath = realpath(__DIR__ . '/../../extensions.db');

$RESPONSE_TITLE = 'Palloo';
$RESPONSE_BODY = 'Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (count($_GET) == 0) {
    $RESPONSE_TITLE = "Palloo Help";
  } else if(isset($_GET["help"])){
    $RESPONSE_TITLE = "Palloo Help";
  } else if (isset($_GET["process"])) {
    $procVar = strtolower($_GET["process"]);
    switch($procVar){
      case "check":
        $RESPONSE_TITLE = "Palloo Check";
        break;
      case "set";
        $RESPONSE_TITLE = "Palloo Set";
        break;
      case "alert":
        $RESPONSE_TITLE = "Palloo Alert";
        break;
      case "auto":
        $RESPONSE_TITLE = "Palloo Auto-Rotate";
        break;
      case "numswap":
        $RESPONSE_TITLE = "Palloo Swapping";
        break;
      case "avail":
        $RESPONSE_TITLE = "Palloo Availability";
        break;
      default:
        $RESPONSE_TITLE = "Palloo Help";
        break;
    }
  } else {
    $RESPONSE_TITLE = "Palloo Help";
  }
}

function dbSetup() {
  try {
    $file_db = new PDO(myFirstDatabase);
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    foreach ($file_db->query('SELECT * from PASSES', PDO::FETCH_ASSOC) as $row) {
      if($row['SERVICE'] == "PUSHOVER"){
        define('PUSHOVER_API_TOKEN',$row['API_TOKEN']);
      } else if ($row['SERVICE'] == "PUSHBULLET"){
        define('PUSHBULLET_API_TOKEN',$row['API_TOKEN']);
      }
    }
    $result = $file_db->query("SELECT COUNT(*) from EXTENSIONS WHERE NAME ='steven';", PDO::FETCH_ASSOC);
    if((int)$result->fetchColumn() == 1) {
      foreach ($file_db->query("SELECT * from EXTENSIONS WHERE NAME ='steven';", PDO::FETCH_ASSOC) as $row) {
        define('USERNAME', $row['EMAIL']);
        define('PASSWORD', $row['PASS']);
      }
    }
    $file_db = null;
  } catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
}

function stripper($data) {
  $data = str_replace(array("\n","\r"," "), '', $data);
  return $data;
}

function input_cleanse($data) {
  $data = trim($data);
  $data = stripslashes($data);
  return $data;
}

function pushOver($user, $title, $msg) {
  curl_setopt_array($ch = curl_init(), array(
    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
    CURLOPT_POSTFIELDS => array(
      "token" => PUSHOVER_API_TOKEN,
      "user" => $user,
      "title" => $title,
      "message" => $msg,
      "sound" => "bugle",
      "priority" => 0
    ),
    CURLOPT_SAFE_UPLOAD => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true
  ));
  curl_exec($ch);
  curl_close($ch);
}

function pushBullet($email, $title, $msg) {
  curl_setopt_array($ch = curl_init(), array(
    CURLOPT_URL => 'https://api.pushbullet.com/v2/pushes',
    CURLOPT_HTTPHEADER  => array('Authorization: Bearer '. PUSHBULLET_API_TOKEN),
    CURLOPT_POSTFIELDS => array(
      "email" => $email,
      "type" => "note",
      "title" => $title,
      "body" => $msg
    ),
    CURLOPT_SAFE_UPLOAD => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true
  ));
  curl_exec($ch);
  curl_close($ch);
}

function sendText($service, $number, $title, $msg) {
  $service = stripper(str_replace(array("&","-"," "), '', $service));
  $number = stripper(str_replace(array("&","-"," ","(",")"), '', $number));
  if (strlen($number) == 11) {
    $number = substr($number,1);
  }
  switch(strtolower($service)){
    case "att":
      $to = $number . "@txt.att.net";
      $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
      $execution = str_replace(array("'"), '"', $execution);
      shell_exec($execution);
      break;
    case "verizon":
      $to = $number . "@vtext.com";
      $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
      $execution = str_replace(array("'"), '"', $execution);
      shell_exec($execution);
      break;
    case "tmobile":
      $to = $number . "@tmomail.net";
      $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
      $execution = str_replace(array("'"), '"', $execution);
      shell_exec($execution);
      break;
    case "sprint":
      $to = $number . "@messaging.sprintpcs.com";
      $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
      $execution = str_replace(array("'"), '"', $execution);
      shell_exec($execution);
      break;
    case "googlefi":
      $to = $number . "@msg.fi.google.com";
      $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
      $execution = str_replace(array("'"), '"', $execution);
      shell_exec($execution);
      break;
  }
  return;
}

function sendAnEmail($to, $title, $msg) {
  $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
  $execution = str_replace(array("'"), '"', $execution);
  shell_exec($execution);
  return;
}

function swappa($line) {
  $headers = array("Host" => "my.halloo.com","Origin" => "https://my.halloo.com","User-Agent" => USER_AGENT,"Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language" => "en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection" => "keep-alive","Content-Type" => "application/x-www-form-urlencoded","Cache-Control" => "no-cache");

  $fields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In');

  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$fields["ucomp"].'&upass='.$fields["upass"].'&submit='.$fields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  
  curl_reset($ch);
  $headers = array("Host" => "my.halloo.com","User-Agent" => USER_AGENT,"Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language" => "en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection" => "keep-alive","Cache-Control" => "no-cache");
  $myFirstURL = 'http://my.halloo.com/console/miniproxy?method=setForward&forward=' . $line;
  
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $myFirstURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  $result = curl_exec($ch);
  
  curl_close($ch);
  $xml = simplexml_load_string($result);
  return($xml->response[0]->agent[0]['forward']);
}

function randomKey($length) {
    $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
  $key = "";
    for($i=0; $i < $length; $i++) {
        $key .= $pool[mt_rand(0, count($pool) - 1)];
    }
    return $key;
}

function resultsHandler($result,$phemail,$boundary) {
  $html = new DOMDocument();
  $internalErrors = libxml_use_internal_errors(true);
  $html->loadHtml($result);
  
  $xpath = new DOMXpath($html);
  $name = $xpath->query("//*[contains(@name, 'name')]");
  $submitted = $xpath->query("//*[contains(@name, 'submitted')]");
  $isAdmin = $xpath->query("//*[contains(@name, 'admin')]");
  $userID = $xpath->query("//*[contains(@name, 'userid')]");
  $pins = $xpath->query("//*[contains(@name, 'newpin')]");
  $email = $xpath->query("//*[contains(@name, 'email')]");
  $street = $xpath->query("//*[contains(@name, 'street')]");
  $city = $xpath->query("//*[contains(@name, 'city')]");
  $postcode = $xpath->query("//*[contains(@name, 'postcode')]");
  $mytz = $xpath->query("//*[contains(@name, 'mytz')]");
  $dst = $xpath->query("//*[contains(@name, 'dst')]");
  $submit = $xpath->query("//*[contains(@name, 'Submit')]");
  
  $POSTINFO = [];
  $POSTINFO[$submitted->item(0)->getAttribute('name')] = $submitted->item(0)->getAttribute('value');
  
  $POSTINFO[$isAdmin->item(0)->getAttribute('name')] = $isAdmin->item(0)->getAttribute('value');
  
  foreach ($name as $node) {
    if($node->getAttribute('name') == "firstname") {
      $POSTINFO[$node->getAttribute('name')] = $node->getAttribute('value');
    }
  }
  
  $POSTINFO[$userID->item(0)->getAttribute('name')] = $userID->item(0)->getAttribute('value');
  
  foreach ($name as $node) {
    if($node->getAttribute('name') == "lastname") {
      $POSTINFO[$node->getAttribute('name')] = $node->getAttribute('value');
    }
  }
  
  foreach ($pins as $node) {
    $POSTINFO[$node->getAttribute('name')] = $node->getAttribute('value');
  }
  
  $POSTINFO["mugshot"] = "";
  
  foreach ($email as $node) {
    if($node->getAttribute('name') == "email") {
      $POSTINFO[$node->getAttribute('name')] = $node->getAttribute('value');
    } else if ($node->getAttribute('name') == "memail") {
      $POSTINFO[$node->getAttribute('name')] = $phemail;
    }
  }
  
  if ($street->item(0)->getAttribute('value') == "") {
    $POSTINFO["street"] = "";
  } else {
    $POSTINFO[$street->item(0)->getAttribute('name')] = $street->item(0)->getAttribute('value');
  }
  
  if ($city->item(0)->getAttribute('value') == "") {
    $POSTINFO["city"] = "";
  } else {
    $POSTINFO[$city->item(0)->getAttribute('name')] = $city->item(0)->getAttribute('value');
  }
  
  if ($postcode->item(0)->getAttribute('value') == "") {
    $POSTINFO["postcode"] = "";
  } else {
    $POSTINFO[$postcode->item(0)->getAttribute('name')] = $postcode->item(0)->getAttribute('value');
  }
  foreach($mytz->item(0)->getElementsByTagName("option") as $option) {
    if($option->getAttribute('selected') != "") {
      $POSTINFO[$mytz->item(0)->getAttribute('name')] = $option->getAttribute('value');
    }
  }
  $POSTINFO[$dst->item(0)->getAttribute('name')] = $dst->item(0)->getAttribute('value');
  $POSTINFO[$submit->item(0)->getAttribute('name')] = $submit->item(0)->getAttribute('value');
  
  $body = multipart_build_query($POSTINFO,$boundary);
  return $body;
}

function multipart_build_query($fields, $boundary){
  $retVal = '';
  foreach($fields as $key => $value){
    if($key == "mugshot"){
      $retVal = $retVal . "--" . $boundary . "\nContent-Disposition: form-data; name=" . $key . "; filename=\"\" \r\nContent-Type: application/octet-stream\r\n\r\n".$value."\r\n";
    } else {
      $retVal .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
    }
  }
  $retVal .= "--$boundary--";
  return $retVal;
}

function phoMail ($number, $service) {
  $service = stripper(str_replace(array("&","-"," "), '', $service));
  $number = stripper(str_replace(array("&","-"," ","(",")"), '', $number));
  if (strlen($number) == 11) {
    $number = substr($number,1);
  }
  switch(strtolower($service)){
    case "att":
      $to = $number . "@txt.att.net";
      break;
    case "verizon":
      $to = $number . "@vtext.com";
      break;
    case "tmobile":
      $to = $number . "@tmomail.net";
      break;
    case "googlefi":
      $to = $number . "@msg.fi.google.com";
      break;
    case "sprint":
      $to = $number . "@messaging.sprintpcs.com";
      break;
  }
  return $to;   
}

function setUser($userToSet, $rType) {
  try {
    $file_db = new PDO(myFirstDatabase);
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $line = [];
    $result = $file_db->query("SELECT COUNT(*) from EXTENSIONS WHERE NAME ='$userToSet';", PDO::FETCH_ASSOC);
    if((int)$result->fetchColumn() == 1) {
      foreach ($file_db->query("SELECT * from EXTENSIONS WHERE NAME = '$userToSet'", PDO::FETCH_ASSOC) as $row) {
        foreach($row as $key => $value) {
          $line[$key] = $value;
        }
      }
    }
    if(!isset($line["NAME"])) {
      $file_db = Null;
      return "Error: Please provide a valid name to set.";
    } else {
      $OCForwardingURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=Forwarding";
      $OCGeneralURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=General";
      
      $TSForwardingURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=TSEmergency&tab=Forwarding";
      $TSGeneralURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=TSEmergency&tab=General";
      
      $signinHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
      $getterHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");
      $loginFields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In');
      
      //Sign-In Post set up and requested
      curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$loginFields["ucomp"].'&upass='.$loginFields["upass"].'&submit='.$loginFields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $signinHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      $result = curl_exec($ch);
      
      curl_reset($ch);
      
      //On-Call Forwarding page set up and requested
      curl_setopt_array($ch, array(CURLOPT_URL => $OCForwardingURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getterHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      $result = curl_exec($ch);
      
      $html = new DOMDocument();
      $internalErrors = libxml_use_internal_errors(true);
      $html->loadHtml($result);
      
      $xpath = new DOMXpath($html);
      $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
      $pyOutput = $fwd->item(2)->getAttribute('value');
      
      $onCallHomeField = $fwd->item(0)->getAttribute('name');
      $onCallOfficeField = $fwd->item(1)->getAttribute('name');
      $onCallMobileField = $fwd->item(2)->getAttribute('name');
      if ($pyOutput == $line["PHONE"]) {
        $ocFileInfo = [];
        
        $result = $file_db->query("SELECT COUNT(*) from ONCALL;", PDO::FETCH_ASSOC);
        if((int)$result->fetchColumn() == 1) {
          foreach ($file_db->query("SELECT * from ONCALL", PDO::FETCH_ASSOC) as $row) {
            foreach($row as $key => $value) {
              $ocFileInfo[$key] = $value;
            }
          }
        }
        
        if ($ocFileInfo["PHONE"] == $line["PHONE"]) {
          if($rType == 1){
            curl_close($ch);
            $file_db = null;
            return "User is already On Call.";
          } else {
            curl_close($ch);
            $file_db = null;
            return $line["PHONE"];
          }
        } else {
          $fullQuery = "UPDATE ONCALL SET ";
          foreach($line as $key => $value){
            if($key != "ID") {
              if($value != NULL) {
                $fullQuery = $fullQuery .  $key . "='" . $value . "', ";
              } else {
                $fullQuery = $fullQuery .  $key . "=NULL, ";
              }
            }
          }
          $fullQuery = rtrim($fullQuery, ", ") . " WHERE ID=1";
          $file_db->query($fullQuery);
          
          if($rType == 1){
            curl_close($ch);
            $file_db = null;
            return "On Call file updated. User: " . ucfirst($line["NAME"]);
          } else {
            curl_close($ch);
            $file_db = null;
            return $line["NAME"];
          }
        }
      } else {
        $onCallBoundary = "---------------------------" . randomKey(16);
        $tsEmerBoundary = "---------------------------" . randomKey(16);
        $postonCallForHeaders = array("Host: my.halloo.com","Origin: $OCForwardingURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: $OCForwardingURL","Cache-Control: no-cache");
        $postonCallGenHeaders = array("Host: my.halloo.com","Origin: $OCGeneralURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=$onCallBoundary","Referer: $OCGeneralURL","Cache-Control: no-cache");
        
        $posttsEmerForHeaders = array("Host: my.halloo.com","Origin: $TSForwardingURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: $TSForwardingURL","Cache-Control: no-cache");
        $posttsEmerGenHeaders = array("Host: my.halloo.com","Origin: $TSGeneralURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=$tsEmerBoundary","Referer: $TSGeneralURL","Cache-Control: no-cache");
        
        $onCallStrings = $onCallHomeField.'='.stripper($line["PHONE"]).'&'.$onCallOfficeField.'='.stripper($line["PHONE"]).'&'.$onCallMobileField.'='.stripper($line["PHONE"]).'&Submit=Save+Changes';
        $phoMail = phoMail($line["PHONE"],$line["SERVICE"]);
        
        curl_reset($ch);
        
        //On-Call Forwarding page Post set up and executed
        curl_setopt_array($ch, array(CURLOPT_URL => $OCForwardingURL,CURLOPT_POSTFIELDS => $onCallStrings,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $postonCallForHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        $result = curl_exec($ch);
        
        curl_reset($ch);
        
        //On-Call General page Get set up and executed
        curl_setopt_array($ch, array(CURLOPT_URL => $OCGeneralURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getterHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        
        $result = curl_exec($ch);
        $genPostFields = resultsHandler($result,$phoMail,$onCallBoundary);
        curl_reset($ch);
        
        //On-Call General page POST set up and executed
        curl_setopt_array($ch, array(CURLOPT_URL => $OCGeneralURL,CURLOPT_POST => 1,CURLOPT_POSTFIELDS => $genPostFields,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $postonCallGenHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        
        $result = curl_exec($ch);
        
        curl_reset($ch);
        
        //tsEmer Forwarding page GET set up and requested
        curl_setopt_array($ch, array(CURLOPT_URL => $TSForwardingURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getterHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        $result = curl_exec($ch);
        
        $html = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $html->loadHtml($result);
        
        $xpath = new DOMXpath($html);
        $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
        
        $tsEmerHomeField = $fwd->item(0)->getAttribute('name');
        $tsEmerOfficeField = $fwd->item(1)->getAttribute('name');
        $tsEmerMobileField = $fwd->item(2)->getAttribute('name');
        $tsEmerStrings = $tsEmerHomeField.'='.stripper($line["PHONE"]).'&'.$tsEmerOfficeField.'='.stripper($line["PHONE"]).'&'.$tsEmerMobileField.'='.stripper($line["PHONE"]).'&Submit=Save+Changes';
        
        curl_reset($ch);
        
        //tsEmer Forwarding page Post set up and executed
        curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $TSForwardingURL,CURLOPT_POSTFIELDS => $tsEmerStrings,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $posttsEmerForHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        $result = curl_exec($ch);
        
        curl_reset($ch);
        
        //tsEmer General page Get set up and executed
        curl_setopt_array($ch, array(CURLOPT_URL => $TSGeneralURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getterHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        
        $result = curl_exec($ch);
        $tsEmerPostFields = resultsHandler($result,$phoMail,$tsEmerBoundary);
        curl_reset($ch);
        
        //tsEmer General page POST set up and executed
        curl_setopt_array($ch, array(CURLOPT_URL => $TSGeneralURL,CURLOPT_POST => 1,CURLOPT_POSTFIELDS => $tsEmerPostFields,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $posttsEmerGenHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
        
        $result = curl_exec($ch);
        
        curl_close($ch);
        
        $ocFileInfo = [];
        
        $result = $file_db->query("SELECT COUNT(*) from ONCALL;", PDO::FETCH_ASSOC);
        if((int)$result->fetchColumn() == 1) {
          foreach ($file_db->query("SELECT * from ONCALL", PDO::FETCH_ASSOC) as $row) {
            foreach($row as $key => $value) {
              $ocFileInfo[$key] = $value;
            }
          }
        }
        
        if ($ocFileInfo["PHONE"] == $line["PHONE"]) {
          if($rType == 1){
            $file_db = null;
            return "User's extension is now set on Halloo and On Call file is up to date. User: " . ucfirst($line["NAME"]);
          } else {
            $file_db = null;
            return $line["NAME"];
          }
        } else {
          $fullQuery = "UPDATE ONCALL SET ";
          foreach($line as $key => $value){
            if($key != "ID") {
              if($value != NULL) {
                $fullQuery = $fullQuery .  $key . "='" . $value . "', ";
              } else {
                $fullQuery = $fullQuery .  $key . "=NULL, ";
              }
            }
          }
          $fullQuery = rtrim($fullQuery, ", ") . " WHERE ID=1";
          $file_db->query($fullQuery);
          
          if($rType == 1){
            $file_db = null;
            return "User's extension is now set on Halloo and On Call file updated. User: " . ucfirst($line["NAME"]);
          } else {
            $file_db = null;
            return $line["NAME"];
          }
        }
      }
    }
  } catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
}

function checkUser($rType){
  $postHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
  $getHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");
  
  $fields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In');
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$fields["ucomp"].'&upass='.$fields["upass"].'&submit='.$fields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $postHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  
  curl_reset($ch);
  
  curl_setopt_array($ch, array(CURLOPT_URL => 'http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=Forwarding',CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  $result = curl_exec($ch);
  $html = new DOMDocument();
  $internalErrors = libxml_use_internal_errors(true);
  $html->loadHtml($result);
  
  $xpath = new DOMXpath($html);
  $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
  $pyOutput = $fwd->item(2)->getAttribute('value');
  curl_close($ch);
  
  try {
    $file_db = new PDO(myFirstDatabase);
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($file_db->query('SELECT * from ONCALL', PDO::FETCH_ASSOC) as $row) {
      if(stripper($row['PHONE']) == stripper($pyOutput)){
        if($rType == 1){
          $file_db = null;
          return "On Call user is: " . ucfirst($row["NAME"]);
        } else {
          $file_db = null;
          return $row["NAME"];
        }
      } else {
        $line = [];
        $fullQuery = "UPDATE ONCALL SET ";
        foreach ($file_db->query("SELECT * from EXTENSIONS WHERE PHONE ='$pyOutput';", PDO::FETCH_ASSOC) as $row) {
          if(stripper($row['PHONE']) == stripper($pyOutput)){
            foreach($row as $key => $value) {
              if($key != "ID") {
                if($value != NULL) {
                  $fullQuery = $fullQuery . $key . " ='" . $value . "', ";
                } else {
                  $fullQuery = $fullQuery . $key . " =NULL, ";
                }
              }
              sleep(2);
            }
          }
          $fullQuery = rtrim($fullQuery, ", ") . " WHERE ID=1";
          $file_db->query($fullQuery);
          break;
        }
        if($rType == 1){
          $file_db = null;
          return "On Call file updated. User: " . ucfirst($row["NAME"]);
        } else {
          $file_db = null;
          return $row["NAME"];
        }
      }
    }
    $file_db = null;
  } catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
}

function alertOutput($file,$comVars) {
  switch(strtolower(stripper($file["METHOD"]))){
    case "pushover":
      pushOver(stripper($file["TOKEN"]), $comVars[0], $comVars[1]);
      break;
    case "sms":
      if(stripper($file["SERVICE"]) != 'GoogleFi' && stripper($file["SERVICE"]) != 'AT&T' && stripper($file["SERVICE"]) != 'Sprint' && stripper($file["SERVICE"]) != 'T-Mobile' && stripper($file["SERVICE"]) != 'Verizon') {
        sendAnEmail(stripper($file["EMAIL"]), $comVars[0], $comVars[1]);
      } else {
        sendText($file["SERVICE"], stripper($file["PHONE"]), $comVars[0], $comVars[1]);
      }
      break;
    case "pushbullet":
      if ($file["TOKEN"] != Null) {
        pushBullet($file["TOKEN"], $comVars[0], $comVars[1]);
      } else {
        pushBullet($file["EMAIL"], $comVars[0], $comVars[1]);
      }
      break;
    case "email":
      if ($file["TOKEN"] != Null) {
        sendAnEmail(stripper($file["TOKEN"]), $comVars[0], $comVars[1]);
      } else {
        sendAnEmail(stripper($file["EMAIL"]), $comVars[0], $comVars[1]);
      }
      break;
  }
}

function getAlertVars($from,$to = "oncall",$storage = array()) {
  
  if($from == 1){
    if($storage["alertType"] == 2) {
      $comVars[0] = "Monitor is UP: " . urldecode($storage["monitorFriendlyName"]);
      $upDown = "UP";
    } else if ($storage["alertType"] == 1) {
      $comVars[0] = "Monitor is DOWN: " . urldecode($storage["monitorFriendlyName"]);
      $upDown = "DOWN";
    } else {
      $comVars[0] = "Monitor is in a superposition of both UP and DOWN: " . urldecode($storage["monitorFriendlyName"]);
      $upDown = "in a superposition of both UP and DOWN";
    }
    
    $comVars[1] = urldecode("The monitor `" . $storage["monitorFriendlyName"] . "` (" . $storage["monitorURL"] . ") is currently " . $upDown . " (" . $storage["alertDetails"] . ").");
    
    if (isset($storage["name"])) {
      $comVars[2] = input_cleanse($storage["name"]);
    } else {
      $comVars[2] = $to;
    }
  } else {
    if (!empty($storage)) {
      foreach($storage as $query_string_variable => $value) {
        switch($query_string_variable) {
          case "title":
            $comVars[0] = input_cleanse($value);
            break;
          case "msg":
            $comVars[1] = input_cleanse($value);
            break;
          case "name":
            $comVars[2] = input_cleanse($value);
            break;
        }
      }
    }
    
    for ($x = 0; $x < 3; $x++) {
      if(!isset($comVars[$x])) {
        if ($x == 0) {
          $comVars[0] = "Title was not set";
        } else if ($x == 1) {
          $comVars[1] = "Message was not set";
        } else if ($x == 2) {
          $comVars[2] = $to;
        }
      }
    }
  }
  
  if(strtolower(stripper($comVars[2])) == "oncall") {
    try {
      $file_db = new PDO(myFirstDatabase);
      $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $line = [];
      $result = $file_db->query('SELECT COUNT(*) from ONCALL;', PDO::FETCH_ASSOC);
      if((int)$result->fetchColumn() == 1) {
        foreach ($file_db->query('SELECT * from ONCALL;', PDO::FETCH_ASSOC) as $row) {
          foreach($row as $key => $value) {
            $line[$key] = $value;
          }
        }
      }
      alertOutput($line,$comVars);
      $file_db = null;
      return $comVars;
    } catch(PDOException $e) {
      // Print PDOException message
      echo "here";
      echo $e->getMessage();
    }
  } else {
    try {
      $file_db = new PDO(myFirstDatabase);
      $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $line = [];
      
      $result = $file_db->query("SELECT COUNT(*) from EXTENSIONS WHERE NAME = '$comVars[2]';", PDO::FETCH_ASSOC);
      if((int)$result->fetchColumn() == 1) {
        foreach ($file_db->query("SELECT * from EXTENSIONS WHERE NAME = '$comVars[2]';", PDO::FETCH_ASSOC) as $row) {
          foreach($row as $key => $value) {
            $line[$key] = $value;
          }
        }
      }
      alertOutput($line,$comVars);
      $file_db = null;
      return $comVars;
    } catch(PDOException $e) {
      // Print PDOException message
      echo $e->getMessage();
    }
  }
}

function availabilityToggling($togVal=true) {
  $headers = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
  $fields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In'); 

  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$fields["ucomp"].'&upass='.$fields["upass"].'&submit='.$fields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => COOKIE_FILE,CURLOPT_COOKIEJAR => COOKIE_FILE,CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  
  if($togVal === true || $togVal == "on") {
    $togVal = "AVAIL";
  } else {
    $togVal = "UNAVAIL";
  }
  curl_reset($ch);
  $data = "_METHOD=PUT&qstatus=".$togVal;
  $availHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
    
  curl_setopt_array($ch, array(CURLOPT_URL => 'http://my.halloo.com/console/Extensions/steven',CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $availHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  $responseCode = curl_getinfo($ch);
  curl_close($ch);

  if ($responseCode["http_code"] == 200) {
    return("Availability updated to " . $togVal);
  } else {
    return("Error updating Halloo availability.");
  }
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (count($_GET) == 0) {
    $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
  } else if(isset($_GET["help"])){
    $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
  } else if (isset($_GET["process"])) {
    $procVar = input_cleanse(strtolower($_GET["process"]));
    switch($procVar){
      case "check":
        dbSetup();
        $OCUser = checkUser(1);
        if (is_String($OCUser)) {
          if (strpos($OCUser,'Error') !== False || strpos($OCUser,'error') !== False) {
            $RESPONSE_BODY = "There was an error while checking the onCall user. Please contact the administrator.";
            $alertVars = getAlertVars(0,"steven",array("title" => "Error while checking the OnCall user","msg" => "There was an error while checking the onCall user."));
          } else {
            $RESPONSE_BODY = $OCUser;
          }
        } else {
          $RESPONSE_BODY = "Unknown return type.";
        }
        break;
      case "set":
        dbSetup();
        if (isset($_GET["name"])) {
          $setVars = input_cleanse(strtolower($_GET["name"]));
          $funcUpdate = setUser($setVars, 1);
          if (is_String($funcUpdate)) {
            if (strpos($funcUpdate,'Error') !== False || strpos($funcUpdate,'error') !== False) {
              $RESPONSE_BODY = "There was an error while setting the onCall user. Please contact the administrator.";
              $alertVars = getAlertVars(0,"steven",array("title" => "Error while setting a user OnCall","msg" => "There was an error while setting the onCall user."));
            } else {
              $RESPONSE_BODY = $funcUpdate;
            }
          } else {
            $RESPONSE_BODY = "Unknown return type.";
          }
        } else {
          $RESPONSE_BODY = "Please provide a valid name.";
        }
        break;
      case "alert":
        dbSetup();
        $getStorage = $_GET;
        if (isset($getStorage["from"]) && input_cleanse($getStorage["from"]) == "uptime") {
          $alertVars = getAlertVars(1,"oncall",$getStorage);
        } else if (isset($getStorage["name"])){
          $alertVars = getAlertVars(0,$getStorage["name"],$getStorage);
        } else {
          $alertVars = getAlertVars(0,"oncall",$getStorage);
        }
        
        for($i=0;$i < count($alertVars);$i++){
          if ($i == 0){
            $RESPONSE_BODY .= "Title: " . $alertVars[$i] . "</br>";
          } else if ($i == 1){
            $RESPONSE_BODY .= "Message: " . $alertVars[$i] . "</br>";
          } else {
            //$RESPONSE_BODY .= "$alertVars[$i] . "</br>";
          }
        }
        break;
      case "numswap":
        dbSetup();
        if(isset($_GET["line"])){
          if(strtolower($_GET["line"]) == "office" || strtolower($_GET["line"]) == "voicemail" || strtolower($_GET["line"]) == "mobile" || strtolower($_GET["line"]) == "home") {
            $numSwapLine = swappa(ucfirst(strtolower($_GET["line"])));
            $RESPONSE_BODY = "Current Line: " . $numSwapLine;
          } else {
            $RESPONSE_BODY = "Please supply a valid phone line to swap to.";
          }
        } else {
          $RESPONSE_BODY = "Please supply a valid phone line to swap to.";
        }
        break;
      case "avail":
        dbSetup();
        if(isset($_GET["set"])){
          $toggleRes = availabilityToggling(stripper(strtolower($_GET["set"])));
        } else {
          $toggleRes = availabilityToggling();
        }
        $RESPONSE_BODY = $toggleRes;
        break;
      case "auto":
        dbSetup();
        //Gather information from rotation file
        shell_exec("cal.py");
        $transferfilename = "Ext/phptransfer";
        
        if(file_exists($transferfilename)) {
          $pyCapture = fopen($transferfilename, "r");
          while(!feof($pyCapture)){
              $transferFileInfo[] = fgets($pyCapture);
          }
          fclose($pyCapture);
          $pyOutput = stripper($transferFileInfo[0]);
          unlink($transferfilename);
        } else {
          $RESPONSE_BODY = "There was an error gathering on call username. Please try again.";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run.","msg" => "There was an error gathering on call username. Please try again."));
          break;
        }
        
        $OCUser = checkUser(0);
        
        if(stripper($pyOutput) == stripper($OCUser) && strpos($OCUser,'Error') === False && strpos($OCUser,'error') === False) {
          $RESPONSE_BODY = "The currently scheduled on-call user (" . ucfirst($pyOutput) .") is already set.";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => ucfirst($pyOutput)));
          break;
        } else if (strpos($OCUser,'Error') !== False || strpos($OCUser,'error') !== False) {
          $RESPONSE_BODY = "There was an error while setting the onCall user. Please contact the administrator.";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => "There was an error while setting the onCall user."));
          break;
        }
        
        //Get ready to call setUser() function
        $setVars = input_cleanse(strtolower($pyOutput));
        $funcUpdate = setUser($setVars,0);
        
        //Once setting is over, check result
        $OCUser = checkUser(0);
        
        
        //If response from setting is a string and it isn't yelling about a bad name, finishing rotating in rotation file
        if ($funcUpdate != $OCUser) {
          //If the response from setting the value is yelling about a bad name, this happens.
          $RESPONSE_BODY = "There was an error and something didn't update. Please try again.";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => "There was an error and something didn't update. Please try again."));
        } else {
          $alertVars = getAlertVars(0,"oncall",array("title" => "You are now on call","msg" => "You have been automatically set to on call based on a schedule. Good luck!"));
          $RESPONSE_BODY = "The currently scheduled on-call user (" . ucfirst($pyOutput) .") has been set.";
          if(strtolower($pyOutput) != 'steven') {
            $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => ucfirst($pyOutput)));
          }
        }
        break;
      default:
        $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
        break;
    }
  } else {
    $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
  }
}

$htmlfilename = "return.html";
$htmlFileInfo = [];
$htmlhandle = fopen($htmlfilename, "r");
while(!feof($htmlhandle)){
  $htmlFileInfo[] = fgets($htmlhandle);
}
fclose($htmlhandle);

$htmlFileInfo = str_replace("{{title}}", $RESPONSE_TITLE, $htmlFileInfo);
$htmlFileInfo = str_replace("{{body}}", $RESPONSE_BODY, $htmlFileInfo);

foreach($htmlFileInfo as $line) {
  echo $line;
}
?>