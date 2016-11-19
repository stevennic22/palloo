<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
define('COOKIE_FILE', 'cookie.txt');

define('USERNAME', ''); //Insert Halloo Email address here
define('PASSWORD', ''); //Insert Halloo password here
define('PUSHOVER_API_TOKEN',''); //Insert Pushover API Token here
define('PUSHBULLET_API_TOKEN',''); //Insert Pushbullet API Token here
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');
echo "<!DOCTYPE html><html><head>";
echo '<link rel="shortcut icon" type="image/x-icon" href="https://stevenv4test.sleepex.com/favicon.ico" />';
echo "<title>";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (count($_GET) == 0) {
    echo "Palloo Help";
  } else if(isset($_GET["help"])){
    echo "Palloo Help";
  } else if (isset($_GET["process"])) {
    $procVar = strtolower($_GET["process"]);
    switch($procVar){
      case "check":
        echo "Palloo Check";
        break;
      case "set";
        echo "Palloo Set";
        break;
      case "alert":
        echo "Palloo Alert";
        break;
      case "auto":
        echo "Palloo Auto-Rotate";
        break;
      case "numswap":
        echo "Palloo Swapping";
        break;
      case "avail":
        echo "Palloo Availability";
        break;
      default:
        echo "Palloo Help";
        break;
    }
  } else {
    echo "Palloo Help";
  }
}
echo "</title><style>.main {margin: auto;
    //margin-left: auto;
    //margin-right: auto;
    //width: 90%;
    text-align: center;}</style></head><body>";

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
  $dir = 'Ext';
  $files = array_diff(scandir($dir), array('..', '.','oncall','rotation','phptransfer'));
  $line = [];
  $loo = false;
  while (list($key,$val) = each($files)){
    if($val == $userToSet) {
      $line[] = $val;
      $filePath = $dir . "/" . $val;
      $handle = fopen($filePath, "r");
      while(!feof($handle)){
        $line[] = fgets($handle);
      }
      $loo = true;
    }
    if($loo) {
      break;
    } else {
      unset($line);
    }
  }
  
  if(!isset($line)) {
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
    
    if ($pyOutput == stripper($line[1])) {
      //Open On Call file and get all information
      $ocfilename = "Ext/oncall";
      //$ocFileInfo[] = substr($ocfilename,4);
      $ochandle = fopen($ocfilename, "r");
      
      while(!feof($ochandle)){
        $ocFileInfo[] = fgets($ochandle);
      }
      fclose($ochandle);
      
      //Strip line breaks and weird stuff from phone number
      $ocFileInfo[0] = stripper($ocFileInfo[0]);
      
      if ($ocFileInfo[0] == stripper($line[1])) {
        if($rType == 1){
          curl_close($ch);
          return "User is already On Call.";
        } else {
          curl_close($ch);
          return $line[0];
        }
      } else {
        //This should set the onCall User here
        $ochandle = fopen($ocfilename, "w");
        
        for($i=1;$i < count($line);$i++){
          fwrite($ochandle, $line[$i]);
        }
        fclose($ochandle);
        if($rType == 1){
          curl_close($ch);
          return "On Call file updated. User: " . ucfirst($line[0]);
        } else {
          curl_close($ch);
          return $line[0];
        }
      }
    } else {
      $onCallBoundary = "---------------------------" . randomKey(16);
      $tsEmerBoundary = "---------------------------" . randomKey(16);
      $postonCallForHeaders = array("Host: my.halloo.com","Origin: $OCForwardingURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: $OCForwardingURL","Cache-Control: no-cache");
      $postonCallGenHeaders = array("Host: my.halloo.com","Origin: $OCGeneralURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=$onCallBoundary","Referer: $OCGeneralURL","Cache-Control: no-cache");
      
      $posttsEmerForHeaders = array("Host: my.halloo.com","Origin: $TSForwardingURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: $TSForwardingURL","Cache-Control: no-cache");
      $posttsEmerGenHeaders = array("Host: my.halloo.com","Origin: $TSGeneralURL","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=$tsEmerBoundary","Referer: $TSGeneralURL","Cache-Control: no-cache");
      
      $onCallStrings = $onCallHomeField.'='.stripper($line[1]).'&'.$onCallOfficeField.'='.stripper($line[1]).'&'.$onCallMobileField.'='.stripper($line[1]).'&Submit=Save+Changes';
      $phoMail = phoMail($line[1],$line[3]);
      
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
      $tsEmerStrings = $tsEmerHomeField.'='.stripper($line[1]).'&'.$tsEmerOfficeField.'='.stripper($line[1]).'&'.$tsEmerMobileField.'='.stripper($line[1]).'&Submit=Save+Changes';
      
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
      
      //Open On Call file and get all information
      $ocfilename = "Ext/oncall";
      //$ocFileInfo[] = substr($ocfilename,4);
      $ochandle = fopen($ocfilename, "r");
      
      while(!feof($ochandle)){
        $ocFileInfo[] = fgets($ochandle);
      }
      fclose($ochandle);
      
      //Strip line breaks and weird stuff from phone number
      $ocFileInfo[0] = stripper($ocFileInfo[0]);
      
      if ($ocFileInfo[0] == $line[1]) {
        if($rType == 1){
          return "User's extension is now set on Halloo and On Call file is up to date. User: " . ucfirst($line[0]);
        } else {
          return $line[0];
        }
      } else {
        //This should set the onCall User here
        $ochandle = fopen($ocfilename, "w");
        
        for($i=1;$i < count($line);$i++){
          fwrite($ochandle, $line[$i]);
        }
        fclose($ochandle);
        if($rType == 1){
          return "User's extension is now set on Halloo and On Call file updated. User: " . ucfirst($line[0]);
        } else {
          return $line[0];
        }
      }
    }
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
  
  //Open On Call file and get all information
  $ocfilename = "Ext/oncall";
  //$ocFileInfo[] = substr($ocfilename,4);
  $ochandle = fopen($ocfilename, "r");
  
  while(!feof($ochandle)){
    $ocFileInfo[] = fgets($ochandle);
  }
  fclose($ochandle);
  
  //Strip line breaks and weird stuff from phone number
  $ocFileInfo[0] = stripper($ocFileInfo[0]);
  
  //If numbers match, send information to screen "On Call user is this:"
  //Else, Set information in On Call file and update screen to inform update
  if ($pyOutput == $ocFileInfo[0]) {  
    //Sets directory of extensions
    $dir = 'Ext';
    //Creates array with file names, excluding On Call and extraneous information
    $dirFiles = array_diff(scandir($dir), array('..', '.','oncall','rotation','phptransfer'));
    $line = [];
    //Loops through key/val array of files to determine which user has the phone number that matches the On Call number
    while (list($key,$val) = each($dirFiles)){
      $line[] = $val;
      $filePath = $dir . "/" . $val;
      $handle = fopen($filePath, "r");
      while(!feof($handle)){
        $line[] = fgets($handle);
      }
      if($ocFileInfo[0] == stripper($line[1])) {
        break;
      }
      unset($line);
    }
    if(!isset($line)) {
      return "There was an error gathering On Call information. On Call information online does not match any users phone number on file locally.";
    } else {
      if($rType == 1){
        return "On Call user is: " . ucfirst($line[0]);
      } else {
        return $line[0];
      }
    }
  } else {
    //Sets directory of extensions
    $dir = 'Ext';
    //Creates array with file names, excluding On Call and extraneous information
    $dirFiles = array_diff(scandir($dir), array('..', '.','oncall'));
    $line = [];
    //Loops through key/val array of files to determine which user has the phone number that matches the On Call number
    while (list($key,$val) = each($dirFiles)){
      $line[] = $val;
      $filePath = $dir . "/" . $val;
      $handle = fopen($filePath, "r");
      while(!feof($handle)){
        $line[] = fgets($handle);
      }
      if($pyOutput == stripper($line[1])) {
        break;
      }
      unset($line);
    }
    if(empty($line)) {
      return "There was an error gathering On Call information. On Call information online does not match any users phone number on file locally.";
    } else {
      //This should set the On Call User here
      $ochandle = fopen($ocfilename, "w");
      
      for($i=1;$i < count($line);$i++){
        fwrite($ochandle, $line[$i]);
      }
      fclose($ochandle);
      if($rType == 1){
        return "On Call file updated. User: " . ucfirst($line[0]);
      } else {
        return $line[0];
      }
    }
  }
}

function alertOutput($file,$comVars) {
  switch(strtolower(stripper($file[4]))){
    case "pushover":
      pushOver(stripper($file[5]), $comVars[0], $comVars[1]);
      break;
    case "sms":
      if(stripper($file[3]) != 'GoogleFi' && stripper($file[3]) != 'AT&T' && stripper($file[3]) != 'Sprint' && stripper($file[3]) != 'T-Mobile' && stripper($file[3]) != 'Verizon') {
        sendAnEmail(stripper($file[2]), $comVars[0], $comVars[1]);
      } else {
        sendText($file[3], stripper($file[1]), $comVars[0], $comVars[1]);
      }
      break;
    case "pushbullet":
      if (isset($file[5])) {
        pushBullet($file[5], $comVars[0], $comVars[1]);
      } else {
        pushBullet($file[2], $comVars[0], $comVars[1]);
      }
      break;
    case "email":
      if (isset($file[5])) {
        sendAnEmail(stripper($file[5]), $comVars[0], $comVars[1]);
      } else {
        sendAnEmail(stripper($file[2]), $comVars[0], $comVars[1]);
      }
      break;
  }
}

function getAlertVars($from,$to = "oncall",$storage = array()) {
  //$file[0] == filename
  //$file[1] == phone number
  //$file[2] == email address
  //$file[3] == cell service
  //$file[4] == method
  //$file[5] == extra information
  //If method is sms - 3 is carrier
  //If method is pushover - 5 is device ID
  //If method is email - 5 would not be needed (unless personal email)
  //If method is pushbullet - 5 would be personal user email address (if blank, use work email)
  
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
    $line[] = "oncall";
    $filepath = 'Ext/oncall';
    $handle = fopen($filepath,"r");
    while(!feof($handle)){
      $line[] = fgets($handle);
    }
  } else {
    //Sets directory of extensions
    $dir = 'Ext';
    
    //Creates array with file names, excluding On Call and extraneous information
    $dirFiles = array_diff(scandir($dir), array('..', '.','oncall','rotation','phptransfer'));
    $line = [];
    //Loops through key/val array of files to determine which user has the phone number that matches the On Call number
    while (list($key,$val) = each($dirFiles)){
      $line[] = $val;
      $filePath = $dir . "/" . $val;
      $handle = fopen($filePath, "r");
      while(!feof($handle)){
        $line[] = fgets($handle);
      }
      if(stripper($comVars[2]) == stripper($line[0])) {
        break;
      }
      unset($line);
    }
  }
  
  if(!isset($line)) {
    //Send information to Steven
    //Grabs all information from Steven's file.
    $stevenfilename = $dir . "/steven";
    $stevenFileInfo[] = "Steven";
    $stevenhandle = fopen($stevenfilename, "r");
    while(!feof($stevenhandle)){
      $stevenFileInfo[] = fgets($stevenhandle);
    }
    fclose($stevenhandle);
    alertOutput($stevenFileInfo,$comVars);
  } else {
    alertOutput($line,$comVars);
  }
  return $comVars;
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
    echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
  } else if(isset($_GET["help"])){
    echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
  } else if (isset($_GET["process"])) {
    $procVar = input_cleanse(strtolower($_GET["process"]));
    switch($procVar){
      case "check":
        $OCUser = checkUser(1);
        
        if (is_String($OCUser)) {
          if (strpos($OCUser,'Error') !== False || strpos($OCUser,'error') !== False) {
            echo "<div class='main' style='text-align: center;'>There was an error while checking the onCall user. Please contact the administrator.</div>";
            $alertVars = getAlertVars(0,"steven",array("title" => "Error while checking the OnCall user","msg" => "There was an error while checking the onCall user."));
          } else {
            echo "<div class='main' style='text-align: center;'>" . $OCUser . "</div>";
          }
        } else {
          echo "<div class='main' style='text-align: center;'>Unknown return type.</div>";
        }
        break;
      case "set":
        if (isset($_GET["name"])) {
          $setVars = input_cleanse(strtolower($_GET["name"]));
          $funcUpdate = setUser($setVars, 1);
          if (is_String($funcUpdate)) {
            if (strpos($funcUpdate,'Error') !== False || strpos($funcUpdate,'error') !== False) {
              echo "<div class='main' style='text-align: center;'>There was an error while setting the onCall user. Please contact the administrator.</div>";
              $alertVars = getAlertVars(0,"steven",array("title" => "Error while setting a user OnCall","msg" => "There was an error while setting the onCall user."));
            } else {
              echo "<div class='main' style='text-align: center;'>" . $funcUpdate . "</div>";
            }
          } else {
            echo "<div class='main' style='text-align: center;'>Unknown return type.</div>";
          }
        } else {
          echo "<div class='main' style='text-align: center;'>Please provide a valid name.</div>";
        }
        break;
      case "alert":
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
            echo "<div class='main' style='text-align: center;'>Title: " . $alertVars[$i] . "</div>";
          } else if ($i == 1){
            echo "<div class='main' style='text-align: center;'>Message: " . $alertVars[$i] . "</div>";
          } else {
            //echo "<div class='main' style='text-align: center;'>" . $alertVars[$i] . "</div>";
          }
        }
        break;
      case "numswap":
        if(isset($_GET["line"])){
          if(strtolower($_GET["line"]) == "office" || strtolower($_GET["line"]) == "voicemail" || strtolower($_GET["line"]) == "mobile" || strtolower($_GET["line"]) == "home") {
            $numSwapLine = swappa(ucfirst(strtolower($_GET["line"])));
            echo "<div class='main' style='text-align: center;'>Current Line: " . $numSwapLine . "</div>";
          } else {
            echo "<div class='main' style='text-align: center;'>Please supply a valid phone line to swap to.</div>";
          }
        } else {
          echo "<div class='main' style='text-align: center;'>Please supply a valid phone line to swap to.</div>";
        }
        break;
      case "avail":
        if(isset($_GET["set"])){
          $toggleRes = availabilityToggling(stripper(strtolower($_GET["set"])));
        } else {
          $toggleRes = availabilityToggling();
        }
        echo "<div class='main' style='text-align: center;'>" . $toggleRes . "</div>";
        break;
      case "auto":
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
          echo "<div class='main' style='text-align: center;'>There was an error gathering on call username. Please try again.</div>";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run.","msg" => "There was an error gathering on call username. Please try again."));
          break;
        }
        
        $OCUser = checkUser(0);
        
        if(stripper($pyOutput) == stripper($OCUser) && strpos($OCUser,'Error') === False && strpos($OCUser,'error') === False) {
          echo "<div class='main' style='text-align: center;'>The currently scheduled on-call user (" . ucfirst($pyOutput) .") is already set.</div>";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => ucfirst($pyOutput)));
          break;
        } else if (strpos($OCUser,'Error') !== False || strpos($OCUser,'error') !== False) {
          echo "<div class='main' style='text-align: center;'>There was an error while setting the onCall user. Please contact the administrator.</div>";
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
          echo "<div class='main' style='text-align: center;'>There was an error and something didn't update. Please try again.</div>";
          $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => "There was an error and something didn't update. Please try again."));
        } else {
          $alertVars = getAlertVars(0,"oncall",array("title" => "You are now on call","msg" => "You have been automatically set to on call based on a schedule. Good luck!"));
          echo "<div class='main' style='text-align: center;'>The currently scheduled on-call user (" . ucfirst($pyOutput) .") has been set.</div>";
          if(strtolower($pyOutput) != 'steven') {
            $alertVars = getAlertVars(0,"steven",array("title" => "OnCall Script has run","msg" => ucfirst($pyOutput)));
          }
        }
        break;
      default:
        echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
        break;
    }
  } else {
    echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
  }
}
echo "</body></html>";
?>