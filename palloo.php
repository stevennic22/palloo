<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('COOKIE_FILE', 'cookie.txt');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');

$headers = apache_request_headers();

$baseFileName = basename(__FILE__, '.php');
include 'sharedFuncs.php';

if(!file_exists(COOKIE_FILE)) {
  $cookieFile = fopen(COOKIE_FILE, "w");
  fclose($cookieFile);
}

$RESPONSE_TITLE = 'Palloo';
$RESPONSE_BODY = 'Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>';

log_out("Pulling contents of extensions.json");
$extensionsJson = json_decode(file_get_contents("extensions.json"), true);

log_out("Defining Halloo settings");
define('USERNAME', $extensionsJson["palloo"]["admin"]["email"]);
define('PASSWORD', $extensionsJson["palloo"]["admin"]["pass"]);

log_out("Defining push settings");
foreach($extensionsJson["palloo"]["creds"] as $credentials) {
  if( $credentials["service"] == "PUSHOVER" ) {
    define('PUSHOVER_API_TOKEN', $credentials['token']);
  } else if ( $credentials["service"] == "PUSHBULLET" ) {
    define('PUSHBULLET_API_TOKEN', $credentials['token']);
  }
}
if ( !defined('USERNAME') || !defined('PASSWORD') ) {
  print "Unable to find username";
  log_out("Unable to find username. Exiting...");
  exit();
} else if ( !defined("PUSHOVER_API_TOKEN") || !defined("PUSHBULLET_API_TOKEN") ) {
  print "Unable to find pushing credentials";
  log_out("Unable to find pushing credentials. Exiting...");
  exit();
}

function woops($curlHandler){
  log_out("Cookie file was empty. Refilling cookie jar");

  $GetHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");
  curl_reset($curlHandler);
  $wooPostHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");

  $fields = array("ucomp" => USERNAME,"upass" => PASSWORD,"submit" => 'Sign-In');
  curl_setopt_array($curlHandler = curl_init(), array(CURLOPT_URL => "https://my.halloo.com/sign-in/",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => 'ucomp='.$fields["ucomp"].'&upass='.$fields["upass"].'&submit='.$fields["submit"],CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $wooPostHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  log_out("Logging into Halloo");
  $result = curl_exec($curlHandler);

  if (curl_getinfo($curlHandler)["http_code"] == 302 || curl_getinfo($curlHandler)["http_code"] == 500) {
    log_out("Following redirect");
    $redirectURL = curl_getinfo($curlHandler)["redirect_url"];
    curl_reset($curlHandler);

    curl_setopt_array($curlHandler, array(CURLOPT_URL => $redirectURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $GetHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($curlHandler);

    curl_reset($curlHandler);
  }
  
  log_out("Returning to caller");
  return($curlHandler);
}

function pushOver($user, $title, $msg) {
  log_out("Sending pushover notification");
  log_out("To: " . $user);
  log_out("Title: " . $title);
  log_out("Msg: " . $msg);
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
  log_out("Push sent");
}

function pushBullet($email, $title, $msg) {
  log_out("Sending pushbullet notification");
  log_out("To: " . $email);
  log_out("Title: " . $title);
  log_out("Msg: " . $msg);
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
  log_out("Push sent");
}

function sendAnEmail($to, $title, $msg) {
  log_out("Sending an email");
  log_out("To: " . $to);
  log_out("Title: " . $title);
  log_out("Msg: " . $msg);
  $execution = "sendEmail.py -r " . $to . " -m '" . $msg . "' -t '" . $title . "'";
  $execution = str_replace(array("'"), '"', $execution);
  shell_exec($execution);
  log_out("Email sent");
  return;
}

function phoMail ($number, $service) {
  log_out("Creating email from phone number");
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
    default:
      $to = "Error: Not a supported service";
      break;
  }
  log_out("Phomail: " . $to);
  return $to;   
}

function multipart_build_query($fields, $boundary){
  log_out("Building multipart query");
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

function resultsHandler($result,$phemail,$boundary) {
  log_out("Parsing HTML results");
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

function randomKey($length) {
    log_out("Generating random key for boundary");
    $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
    $key = "";
    for($i=0; $i < $length; $i++) {
        $key .= $pool[mt_rand(0, count($pool) - 1)];
    }
    return $key;
}

function checkUser($rtype) {
  $extJson = json_decode(file_get_contents("extensions.json"), true);
  $getHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");
  
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => 'http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=Forwarding',CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  log_out("Getting OnCall Forwarding tab");
  $result = curl_exec($ch);

  if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
    $ch = woops($ch);
    curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => 'http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=Forwarding',CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($ch);

    if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
      log_out("Returning: Error: Cannot log into Halloo");
      return "Error: Cannot log into Halloo.";
    }
  }

  log_out("Parsing Oncall forwarding");

  $html = new DOMDocument();
  $internalErrors = libxml_use_internal_errors(true);
  $html->loadHtml($result);
  
  $xpath = new DOMXpath($html);
  $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
  $hallooResponse = $fwd->item(2)->getAttribute('value');
  curl_close($ch);

  #If phone number matches that of the oncall user
  #Else, search for that matching number
  if (stripper($hallooResponse) == stripper($extJson["palloo"]["oncall"]["phone"])) {
    if ($rtype == 1) {
      log_out("Returning: On Call user is " . ucfirst($extJson["palloo"]["oncall"]["name"]) . ".");
      return "On Call user is " . ucfirst($extJson["palloo"]["oncall"]["name"]) . ".";
    } else {
      log_out("Returning: " . $extJson["palloo"]["oncall"]["name"]);
      return $extJson["palloo"]["oncall"]["name"];
    }
  } else {
    foreach($extJson["palloo"]["extensions"] as $user) {
      if(stripper($hallooResponse) == stripper($user["phone"])) {
        if ($rtype == 1) {
          #Update on call user in json file
          foreach($user as $key => $value) {
            $extJson["palloo"]["oncall"][$key] = $value;
          }
          log_out("Returning: On Call file updated. User: " . ucfirst($extJson["palloo"]["oncall"]["name"]));
          file_put_contents('extensions.json', json_encode($extJson,TRUE));
          return("On Call file updated. User: " . ucfirst($extJson["palloo"]["oncall"]["name"]));
        } else {
          #Find name of user on call in Halloo, return that
          foreach($extJson["palloo"]["extensions"] as $user) {
            if(stripper($hallooResponse) == stripper($user["phone"])) {
              log_out("Returning: " . $extJson["palloo"]["oncall"]["name"]);
              return($extJson["palloo"]["oncall"]["name"]);
            }
          }
        }
      }
    }
    log_out("Returning: There was an error gathering On Call information. On Call information online does not match any users phone number on file.");
    return "There was an error gathering On Call information. On Call information online does not match any users phone number on file.";
  }
}

function setUser($userToSet, $rType) {
  $extJson = json_decode(file_get_contents("extensions.json"), true);
  $userBeingSet = [];
  foreach($extJson["palloo"]["extensions"] as $user) {
    if(strtolower(stripper($userToSet)) == strtolower(stripper($user["name"]))) {
      foreach($user as $key => $value) {
        $userBeingSet[$key] = $value;
      }
      break;
    }
  }
  
  if(empty($userBeingSet)) {
    log_out("No valid user found");
    return "Error: Please provide a valid name to set.";
  } else {
    log_out("User grabbed from json");
    $OCURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=oncall&tab=";
    $TSURL = "http://my.halloo.com/ext/?view=User%20Settings&extn=TSEmergency&tab=";
    
    $getHeaders = array("Host: my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection: keep-alive","Cache-Control: no-cache");
    
    curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $OCURL . "Forwarding",CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($ch);

    if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
      $ch = woops($ch);
      curl_setopt_array($ch, array(CURLOPT_URL => $OCURL . "Forwarding",CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      $result = curl_exec($ch);

      if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
        return "Error: Cannot log into Halloo.";
      }
    }
    
    $html = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $html->loadHtml($result);
    
    $xpath = new DOMXpath($html);
    $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
    
    $hallooResponse = $fwd->item(2)->getAttribute('value');
    
    $OCHome = $fwd->item(0)->getAttribute('name');
    $OCOffice = $fwd->item(1)->getAttribute('name');
    $OCMobile = $fwd->item(2)->getAttribute('name');

    log_out("Phone numbers and field names gathered from Halloo");
    
    if (stripper($hallooResponse) == stripper($userBeingSet["phone"])) {
      if (strtolower(stripper($userBeingSet["name"])) == stripper(strtolower($extJson["palloo"]["oncall"]["name"]))) {
        if($rType == 1) {
          log_out("User is already set OnCall");
          return("User is already OnCall.");
        } else {
          log_out("User is already set OnCall: " . $extJson["palloo"]["oncall"]["name"]);
          return($extJson["palloo"]["oncall"]["name"]);
        }
      }
    } else {
      $OCBoundary = "---------------------------" . randomKey(16);
      $TSBoundary = "---------------------------" . randomKey(16);
      log_out("Boundaries created");
      
      $pOCForHead = array("Host: my.halloo.com","Origin: " . $OCURL . "Forwarding","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: " . $OCURL . "Forwarding","Cache-Control: no-cache");
      $pOCGenHead = array("Host: my.halloo.com","Origin: " . $OCURL . "General","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=" . $OCBoundary,"Referer: " . $OCURL . "General","Cache-Control: no-cache");
      
      $pTSForHead = array("Host: my.halloo.com","Origin: " . $TSURL . "Forwarding","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Referer: " . $TSURL . "Forwarding","Cache-Control: no-cache");
      $pTSGenHead = array("Host: my.halloo.com","Origin: " . $TSURL . "General","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: multipart/form-data; boundary=" . $TSBoundary,"Referer: " . $TSURL . "General","Cache-Control: no-cache");
      
      $OCString = $OCHome.'='.stripper($userBeingSet["phone"]).'&'.$OCOffice.'='.stripper($userBeingSet["phone"]).'&'.$OCMobile.'='.stripper($userBeingSet["phone"]).'&Submit=Save+Changes';
      $phoMail = phoMail($userBeingSet["phone"],$userBeingSet["service"]);
      log_out("OnCall string query generated and phone/email created");
      
      curl_reset($ch);
      
      //On-Call Forwarding page Post set up and executed
      curl_setopt_array($ch, array(CURLOPT_URL => $OCURL . "Forwarding",CURLOPT_POSTFIELDS => $OCString,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $pOCForHead,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      log_out("Setting information on OC Forwarding page");
      $result = curl_exec($ch);
      
      curl_reset($ch);
      
      //On-Call General page Get set up and executed
      curl_setopt_array($ch, array(CURLOPT_URL => $OCURL . "General",CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      
      $result = curl_exec($ch);
      $OCPost = resultsHandler($result,$phoMail,$OCBoundary);
      curl_reset($ch);
      
      //On-Call General page POST set up and executed
      curl_setopt_array($ch, array(CURLOPT_URL => $OCURL . "General",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => $OCPost,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $pOCGenHead,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      
      log_out("Setting information on OC General page");
      $result = curl_exec($ch);
      
      curl_reset($ch);
      
      //tsEmer Forwarding page GET set up and requested
      curl_setopt_array($ch, array(CURLOPT_URL => $TSURL . "Forwarding",CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      log_out("Gathering fields and phone numbers from TS Forwarding page");
      $result = curl_exec($ch);
      
      $html = new DOMDocument();
      $internalErrors = libxml_use_internal_errors(true);
      $html->loadHtml($result);
      
      $xpath = new DOMXpath($html);
      $fwd = $xpath->query("//*[contains(@name, 'fwd_')]");
      
      $TSHome = $fwd->item(0)->getAttribute('name');
      $TSOffice = $fwd->item(1)->getAttribute('name');
      $TSMobile = $fwd->item(2)->getAttribute('name');
      $TSString = $TSHome.'='.stripper($userBeingSet["phone"]).'&'.$TSOffice.'='.stripper($userBeingSet["phone"]).'&'.$TSMobile.'='.stripper($userBeingSet["phone"]).'&Submit=Save+Changes';

      log_out("TSEmergency string query generated (previous phone/email being used)");

      curl_reset($ch);
      
      //tsEmer Forwarding page Post set up and executed
      curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $TSURL . "Forwarding",CURLOPT_POSTFIELDS => $TSString,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $pTSForHead,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      log_out("Setting information on TS Forwarding page");
      $result = curl_exec($ch);
      
      curl_reset($ch);
      
      //tsEmer General page Get set up and executed
      curl_setopt_array($ch, array(CURLOPT_URL => $TSURL . "General",CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $getHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      
      $result = curl_exec($ch);
      $TSPost = resultsHandler($result,$phoMail,$TSBoundary);
      curl_reset($ch);
      
      //tsEmer General page POST set up and executed
      curl_setopt_array($ch, array(CURLOPT_URL => $TSURL . "General",CURLOPT_POST => 1,CURLOPT_POSTFIELDS => $TSPost,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $pTSGenHead,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
      
      log_out("Setting information on TS General page");
      $result = curl_exec($ch);
      
      curl_close($ch);

      foreach($userBeingSet as $key => $value) {
        $extJson["palloo"]["oncall"][$key] = $value;
      }
      log_out("Updating extensions file");
      file_put_contents('extensions.json', json_encode($extJson,TRUE));
      
      if($rType == 1){
        log_out("Returning: User's extension is now set on Halloo and On Call file is up to date. User: " . $extJson["palloo"]["oncall"]["name"]);
        return "User's extension is now set on Halloo and On Call file is up to date. User: " . $extJson["palloo"]["oncall"]["name"];
      } else {
        log_out("Returning: " . $extJson["palloo"]["oncall"]["name"]);
        return $extJson["palloo"]["oncall"]["name"];
      }
    }
  }
}

function setAvailability($togVal="true") {
  
  if($togVal == "true" || $togVal == strtolower("on") || $togVal == strtolower("avail")) {
    $togVal = "AVAIL";
  } else if ($togVal == "false" || $togVal == strtolower("off") || $togVal == strtolower("unavail")) {
    $togVal = "UNAVAIL";
  } else {
    log_out("Invalid option: " . $togVal);
    return("Invalid option: " . $togVal);
  }

  log_out("Updating Halloo availability to: " . $togVal);

  $data = "_METHOD=PUT&qstatus=".$togVal;
  $availHeaders = array("Host: my.halloo.com","Origin: https://my.halloo.com","User-Agent: ".USER_AGENT,"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: en-US,en;q=0.5","Accept-Encoding: gzip, deflate","Connection: keep-alive","Content-Type: application/x-www-form-urlencoded","Cache-Control: no-cache");
    
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => 'http://my.halloo.com/console/Extensions/steven',CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $availHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  $result = curl_exec($ch);
  $responseCode = curl_getinfo($ch);


  if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
    $ch = woops($ch);
  
    curl_setopt_array($ch, array(CURLOPT_URL => 'http://my.halloo.com/console/Extensions/steven',CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $availHeaders,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch);

    if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      exit();
    }
  }
  curl_close($ch);

  if ($responseCode["http_code"] == 200) {
    log_out("Availability updated to " . $togVal);
    return("Availability updated to " . $togVal);
  } else {
    log_out("Error updating Halloo availability.");
    return("Error updating Halloo availability.");
  }
}

function swapNumbers($line) {
  log_out("Swapping numbers to: " . $line);
  $headers = array("Host" => "my.halloo.com","User-Agent" => USER_AGENT,"Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language" => "en-US,en;q=0.5","Accept-Encoding" => "gzip, deflate","Connection" => "keep-alive","Cache-Control" => "no-cache");
  $myFirstURL = 'http://my.halloo.com/console/miniproxy?method=setForward&forward=' . $line;
  
  curl_setopt_array($ch = curl_init(), array(CURLOPT_URL => $myFirstURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
  
  $result = curl_exec($ch);

  if(curl_getinfo($ch)["http_code"] == 302 || curl_getinfo($ch)["http_code"] == 500 || curl_getinfo($ch)["redirect_url"] == "http://my.halloo.com/") {
    $ch = woops($ch);
    
    curl_setopt_array($ch, array(CURLOPT_URL => $myFirstURL,CURLOPT_FRESH_CONNECT => true,CURLOPT_TIMEOUT => 10,CURLOPT_COOKIEFILE => realpath(COOKIE_FILE),CURLOPT_COOKIEJAR => realpath(COOKIE_FILE),CURLOPT_HTTPHEADER => $headers,CURLOPT_SAFE_UPLOAD => true,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_RETURNTRANSFER => 1));
    
    $result = curl_exec($ch);
  }

  curl_close($ch);
  $xml = simplexml_load_string($result);
  log_out("Line swapped to: " . $xml->response[0]->agent[0]['forward']);
  return($xml->response[0]->agent[0]['forward']);
}

function alertOutput($file,$comVars) {
  #Check if field response is blank
  switch(strtolower(stripper($file["method"]))){
    case "pushover":
      pushOver(stripper($file["token"]), $comVars[0], $comVars[1]);
      break;
    case "sms":
      $smsMail = phoMail(stripper($file["phone"]),$file["service"]);
      
      if(strpos(strtolower($smsMail), 'error') !== False) {
        sendAnEmail(stripper($file["email"]), $comVars[0], $comVars[1]);
      } else {
        sendAnEmail($smsMail, $comVars[0], $comVars[1]);
      }
      break;
    case "pushbullet":
      if ($file["token"] != "") {
        pushBullet($file["token"], $comVars[0], $comVars[1]);
      } else {
        pushBullet($file["token"], $comVars[0], $comVars[1]);
      }
      break;
    case "email":
      if ($file["token"] != "") {
        sendAnEmail(stripper($file["token"]), $comVars[0], $comVars[1]);
      } else {
        sendAnEmail(stripper($file["email"]), $comVars[0], $comVars[1]);
      }
      break;
  }
}

function sendAlert($from,$to = "oncall",$storage = array()) {
  $extJson = json_decode(file_get_contents("extensions.json"), true);
  
  if ($to == "oncall") {
    if ($extJson["palloo"]["oncall"]["alert"] == "false") {
      log_out("Alert sending to this user is unavailable at this time.");
      return "Alert sending to this user is unavailable at this time.";
    }
  } else if ($to == "admin") {
    log_out("Message to admin, ignore protocols.");
  } else {
    foreach($extJson["palloo"]["extensions"] as $user) {
      if(input_cleanse(strtolower($to)) == input_cleanse(strtolower($user["name"]))) {
        if ($user["alert"] == "false") {
          log_out("Alert sending to this user is unavailable at this time.");
          return "Alert sending to this user is unavailable at this time.";
        }
        break;
      }
    }
  }

  if($from == 1) {
    if($storage["alertType"] == 1) {
      $comVars[0] = "Monitor is DOWN: " . urldecode($storage["monitorFriendlyName"]);
      $upDown = "DOWN";
    } else if ($storage["alertType"] == 2) {
      $comVars[0] = "Monitor is UP: " . urldecode($storage["monitorFriendlyName"]);
      $upDown = "UP";
    }
    
    $comVars[1] = urldecode("The monitor `" . $storage["monitorFriendlyName"] . "` (" . $storage["monitorURL"] . ") is currently " . $upDown . ".");
    
    if (isset($storage["alertDetails"])) {
      $comVars[1] = $comVars[1] . " (" . $storage["alertDetails"] . ")."; 
    }
    
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

  if (strtolower(stripper($to)) == "oncall" || strtolower(stripper($to)) == "admin") {
    alertOutput($extJson["palloo"][$to],$comVars);
    return $comVars;
  } else {
    foreach($extJson["palloo"]["extensions"] as $user) {
      if (input_cleanse(strtolower($user["name"])) == input_cleanse(strtolower($storage["name"]))) {
        alertOutput($user,$comVars);
        break 1;
      }
    }
    return $comVars;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET["process"])) {
    $procVar = input_cleanse(strtolower($_GET["process"]));
    switch($procVar){
      case "auto":
        log_out("Process: Auto");
        $RESPONSE_TITLE = "Palloo Auto-Rotate";
        if ($extensionsJson["palloo"]["checks"]["auto"] == "true") {
          shell_exec("cal.py");

          $HOCUser = checkUser(0);

          if(strpos(strtolower($HOCUser), 'error') !== False) {
            $RESPONSE_BODY = "There was an error while checking the onCall user. Administrator has been contacted.";
            sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => ucfirst($HOCUser)));
            break;
          } else if (stripper($extensionsJson["palloo"]["oncall"]["name"]) == stripper($HOCUser)) {
            $RESPONSE_BODY = "The currently scheduled on-call user (" . ucfirst($HOCUser) .") is already set.";
            sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => ucfirst($HOCUser)));
            break;
          }

          $nameToSet = input_cleanse(strtolower($HOCUser));
          $setRes = setUser($nameToSet,0);

          $HOCUser = checkUser(0);

          if(strpos(strtolower($HOCUser), 'error') !== False) {
            $RESPONSE_BODY = "There was an error while checking the onCall user. Administrator has been contacted.";
            sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => ucfirst($HOCUser)));
            break;
          } else if ($setRes != $HOCUser) {
            $RESPONSE_BODY = "There was an error and something didn't update. Please try again.";
            sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => "There was an error and something didn't update. Please try again."));
            break;
          } else {
            unset($extensionsJson);
            $extensionsJson = json_decode(file_get_contents("extensions.json"), true);
            if ($extensionsJson["palloo"]["oncall"]["alert"] == true) {
              sendAlert(0,"oncall",array("title" => "You are now on call","msg" => "You have been automatically set to on call based on a schedule. Good luck!"));
            }
            $RESPONSE_BODY = "The currently scheduled on-call user (" . ucfirst($HOCUser) .") has been set.";
            if(strtolower($HOCUser) != strtolower($extensionsJson["palloo"]["admin"]["name"])) {
              sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => ucfirst($HOCUser)));
            }
            break;
          }
        } else {
          log_out("Auto process is disabled");
          $RESPONSE_BODY = "Auto is unavailable at this time.";
        }
        break;
      case "check":
        log_out("Process: Check");
        $RESPONSE_TITLE = "Palloo Check";
        $checkRes = checkUser(1);
        if (strpos(strtolower($checkRes), 'error') !== False) {
          $RESPONSE_BODY = "There was an error while checking the onCall user. Administrator has been contacted.";
          log_out("General error when checking onCall user");
          sendAlert(0,"admin",array("title" => "OnCall Script has run","msg" => "There was an error checking the OnCall user. Please try again."));
        } else {
          $RESPONSE_BODY = $checkRes;
        }
        break;
      case "set":
        log_out("Process: Set");
        $RESPONSE_TITLE = "Palloo Set";
        if (isset($_GET["name"])) {
          $nameToSet = input_cleanse(strtolower($_GET["name"]));
          $setRes = setUser($nameToSet, 1);
          if (strpos(strtolower($setRes), 'error') !== False) {
            log_out("General error with setting.");
            $RESPONSE_BODY = "There was an error while setting the onCall user. Please contact the administrator.";
            log_out("Contacting administrator");
            sendAlert(0,"admin",array("title" => "Error while setting a user OnCall","msg" => "There was an error while setting the onCall user."));
          } else {
            $RESPONSE_BODY = $setRes;
          }
        } else {
          log_out("Not a valid name for setting");
          $RESPONSE_BODY = "Please provide a valid name.";
        }
        break;
      case "avail":
        log_out("Process: Avail");
        $RESPONSE_TITLE = "Palloo Availability";
        if ($extensionsJson["palloo"]["checks"]["avail"] == "true") {
          if(isset($_GET["set"])){
            $toggleRes = setAvailability(stripper(strtolower($_GET["set"])));
          } else {
            $toggleRes = setAvailability();
          }
          $RESPONSE_BODY = $toggleRes;
        } else {
          log_out("Availability swapping is disabled at this time.");
          $RESPONSE_BODY = "Availability switching is unavailable at this time.";
        }
        break;
      case "swappa":
      case "numswap":
        log_out("Process: Swappa");
        $RESPONSE_TITLE = "Palloo Swapping";
        if ($extensionsJson["palloo"]["checks"]["swap"] == "true") {
          if(isset($_GET["line"])){
            if(strtolower($_GET["line"]) == "office" || strtolower($_GET["line"]) == "voicemail" || strtolower($_GET["line"]) == "mobile" || strtolower($_GET["line"]) == "home") {
              $numSwapLine = swapNumbers(ucfirst(strtolower($_GET["line"])));
              $RESPONSE_BODY = "Current Line: " . $numSwapLine;
            } else {
              log_out("Valid phone line not supplied");
              $RESPONSE_BODY = "Please supply a valid phone line to swap to.";
            }
          } else {
            log_out("Valid phone line not supplied");
            $RESPONSE_BODY = "Please supply a valid phone line to swap to.";
          }
        } else {
          log_out("Extension swapping disabled.");
          $RESPONSE_BODY = "Extension swapping is unavailable at this time.";
        }
        break;
      case "alert":
        log_out("Process: Alert");
        $RESPONSE_TITLE = "Palloo Alert";

        if ($extensionsJson["palloo"]["checks"]["alert"] == "true") {
          $getStorage = $_GET;

          if (isset($getStorage["from"]) && input_cleanse($getStorage["from"]) == "uptime") {
            log_out("Uptime Robot notification");
            if ($extensionsJson["palloo"]["oncall"]["alert"] == "true") {
              $alertResponse = sendAlert(1,"oncall",$getStorage);
            } else {
              log_out("Alerts are disabled for this user");
              $RESPONSE_BODY = "Alert sending to the oncall user is unavailable at this time.";
            }
          } else if (isset($getStorage["name"])) {
            if (input_cleanse(strtolower($getStorage["name"] != "oncall")) && input_cleanse(strtolower($getStorage["name"] != "admin"))) {
              foreach($extensionsJson["palloo"]["extensions"] as $user) {
                if(input_cleanse(strtolower($getStorage["name"])) == input_cleanse(strtolower($user["name"]))) {
                  if ($user["alert"] == true) {
                    $alertResponse = sendAlert(0,$getStorage["name"],$getStorage);
                  }
                  break;
                }
              }
              log_out("Alerts are disabled for this user");
              $RESPONSE_BODY = "Alert sending to that user is unavailable at this time.";
            } else {
              $alertResponse = sendAlert(0,$getStorage["name"],$getStorage);
            }
          } else {
            if ($extensionsJson["palloo"]["oncall"]["alert"] == true) {
              $alertResponse = sendAlert(0,"oncall",getStorage);
            }
          }
        } else {
          log_out("Sending alerts is disabled");
          $RESPONSE_BODY = "Alert sending is unavailable at this time.";
        }

        if(gettype($RESPONSE_BODY) != 'string' || $RESPONSE_BODY == "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>") {
          $RESPONSE_BODY = "";
          for($i=0;$i < count($alertResponse);$i++) {
            if ($i == 0){
              $RESPONSE_BODY .= "Title: " . $alertResponse[$i] . "</br>";
            } else if ($i == 1){
              $RESPONSE_BODY .= "Message: " . $alertResponse[$i] . "</br>";
            } else {
              //$RESPONSE_BODY .= "$alertResponse[$i] . "</br>";
            }
          }
        }
        break;
      default:
        log_out("Process: Help");
        $RESPONSE_TITLE = "Palloo Help";
        $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
        break;
    }
  } else {
    log_out("Process not set");
    $RESPONSE_TITLE = "Palloo Help";
    $RESPONSE_BODY = "Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>";
  }
}

#Do check to see whether the response should be html or json
log_out("Opening template");
$retfilename = "return";

if (isset($headers['responsetype'])) {
  log_out(strtolower($headers['responsetype']) . " template chosen instead of HTML.");

  if (file_exists($retfilename . "." . strtolower($headers['responsetype']))) {
    log_out("Filetype exists, using it for response.");
    $retfilename = $retfilename . "." . strtolower($headers['responsetype']);
  } else {
    log_out("Filetype does not exist, using HTML for response.");
    $retfilename = $retfilename . ".html";
  }
} else {
  $retfilename = $retfilename . ".html";
}

$retFileInfo = [];
$rethandle = fopen($retfilename, "r");
while(!feof($rethandle)){
  $retFileInfo[] = fgets($rethandle);
}
fclose($rethandle);

log_out("Replacing favicon placeholder");
$retFileInfo = str_replace("[[favicon]]", "pfavicon.ico", $retFileInfo);
log_out("Replacing default template strings");
$retFileInfo = str_replace("[[title]]", $RESPONSE_TITLE, $retFileInfo);
log_out("Title: ". $RESPONSE_TITLE);
$retFileInfo = str_replace("[[body]]", $RESPONSE_BODY, $retFileInfo);
log_out("Body: ". $RESPONSE_BODY);

log_out("Returning template response");
foreach($retFileInfo as $line) {
  echo $line;
}
if ($RESPONSE_TITLE == "Palloo Help") {
  log_out("Deleting file",true);
}
?>