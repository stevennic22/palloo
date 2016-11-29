<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('COOKIE_FILE', 'cookie.txt');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');

if(!file_exists(realpath(COOKIE_FILE))) {
  $cookieFile = fopen(realpath(COOKIE_FILE), "w");
  fclose($cookieFile);
}

$RESPONSE_TITLE = 'Palloo';
$RESPONSE_BODY = 'Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>';

$extensionsFile = file_get_contents("extensions.json");
$extensionsJson = json_decode($extensionsFile, true);

foreach($extensionsJson["palloo"]["extensions"] as $user){
  if( $user["name"] == "Steven" ) {
    define('USERNAME', $user['email']);
    define('PASSWORD', $user['pass']);
  }
}
foreach($extensionsJson["palloo"]["creds"] as $credentials){
  if( $credentials["service"] == "PUSHOVER" ) {
    define('PUSHOVER_API_TOKEN', $credentials['token']);
  } else if ( $credentials["service"] == "PUSHBULLET" ) {
    define('PUSHBULLET_API_TOKEN', $credentials['token']);
  }
}
if ( !defined('USERNAME') || !defined('PASSWORD') ) {
  print "Unable to find username";
  exit();
} else if ( !defined("PUSHOVER_API_TOKEN") || !defined("PUSHBULLET_API_TOKEN") ) {
  print "Unable to find pushing credentials";
  exit();
}

function woops($curlHandler){
  curl_reset($curlHandler);
  $wooPostHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
  $wooGetHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");

  $fields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In');
  curl_setopt_array($curlHandler = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$fields["ucomp"].'&upass='.$fields["upass"].'&submit='.$fields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $wooPostHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($curlHandler);

  if (curl_getinfo($curlHandler)["http_code"] == 302) {
    $redirectURL = curl_getinfo($curlHandler)["redirect_url"];
    curl_reset($curlHandler);

    curl_setopt_array($curlHandler, array(CURLOPT_URL => $redirectURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $wooGetHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($curlHandler);

    curl_reset($curlHandler);
  }
  
  return($curlHandler);
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

function checkUser($rtype) {
  #Check user set to oncall by the file
  #If it doesn't match what is on Halloo, set the person in the file on call
  
}

function setUser() {

}

function setAvailability($togVal=true) {
  $data = "_METHOD=PUT&qstatus=".$togVal;
  $availHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
    
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => 'http://my.halloo.com/console/Extensions/steven',CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $availHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  $responseCode = curl_getinfo($ch);


  if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
    $ch = woops($ch);
  
    curl_setopt_array($ch, array(CURLOPT_URL => 'http://my.halloo.com/console/Extensions/steven',CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $availHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch);

    if($getInfo["http_code"] == 302 || $getInfo["redirect_url"] == "http://my.halloo.com/") {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      exit();
    }
  }
  curl_close($ch);

  if ($responseCode["http_code"] == 200) {
    return("Availability updated to " . $togVal);
  } else {
    return("Error updating Halloo availability.");
  }
}

function swapNumbers($line) {
  $headers = array("Host" => "my.halloo.com","User-Agent" => USER_AGENT,"Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language" => "en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection" => "keep-alive","Cache-Control" => "no-cache");
  $myFirstURL = 'http://my.halloo.com/console/miniproxy?method=setForward&forward=' . $line;
  
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $myFirstURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  $result = curl_exec($ch);

  if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
    $ch = woops($ch);
    
    curl_setopt_array($ch, array(CURLOPT_URL => $myFirstURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    
    $result = curl_exec($ch);
  }

  curl_close($ch);
  $xml = simplexml_load_string($result);
  return($xml->response[0]->agent[0]['forward']);
}

function sendAlert() {

}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET["process"])) {
    $procVar = input_cleanse(strtolower($_GET["process"]));
    switch($procVar){
      case "auto":
        $RESPONSE_TITLE = "Palloo Auto-Rotate";
        if ($extensionsJson["palloo"]["checks"]["auto"] == false) {
          #Check trigger in json file to see whether to continue or not
          //Gather information from rotation file
          shell_exec("cal.py");
          
          #If oncall user does not match Google Calendar, set the GCal user to oncall

        } else {
          $RESPONSE_BODY = "Auto is unavailable at this time.";
        }
        break;
      case "check":
        $RESPONSE_TITLE = "Palloo Check";
        #Log into Halloo and see who is currently set to On Call
        #If wrong person is set to OnCall, update the local file
        break;
      case "set":
        $RESPONSE_TITLE = "Palloo Set";
        #Log into Halloo and set someone on call
        #If that user is on call already, do nothing
        #If that user wasn't, update on call
        break;
      case "avail":
        $RESPONSE_TITLE = "Palloo Availability";
        if ($extensionsJson["palloo"]["checks"]["avail"] == false) {
          #Set whether a user is available or not
        } else {
          $RESPONSE_BODY = "Availability switching is unavailable at this time.";
        }
        break;
      case "swappa":
        $RESPONSE_TITLE = "Palloo Swapping";
        if ($extensionsJson["palloo"]["checks"]["swap"] == false) {
          #Swap current phone line
        } else {
          $RESPONSE_BODY = "Extension switching is unavailable at this time.";
        }
        break;
      case "alert":
        $RESPONSE_TITLE = "Palloo Alert";
        if ($extensionsJson["palloo"]["checks"]["alert"] == false) {
          #Send an alert to the currently on call user
          #Alert is likely going to come from Uptime Robot
        } else {
          $RESPONSE_BODY = "Alert sending is unavailable at this time.";
        }
        break;
      default:
        $RESPONSE_TITLE = "Palloo Help";
        $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
        break;
    }
  } else {
    $RESPONSE_TITLE = "Palloo Help";
    $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
  }
}

#Do check to see whether the response should be html or json
$retfilename = "return." . input_cleanse(strtolower($_GET["return"]));
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