<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$extensionsFile = file_get_contents("extensions.json");
$extensionsJson = json_decode($extensionsFile, true);
define('COOKIE_FILE', 'cookie.txt');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');

$RESPONSE_TITLE = 'Palloo';
$RESPONSE_BODY = 'Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br>';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET["process"])) {
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

function jsonSetup() {
  global $extensionsJson;
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

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET["process"])) {
    $procVar = input_cleanse(strtolower($_GET["process"]));
    switch($procVar){
      case "auto":
        jsonSetup();
        global $extensionsJson;
        //Gather information from rotation file
        shell_exec("cal.py");
        
        #If oncall user does not match Google Calendar, set the GCal user to oncall
        break;
      case "set":
        jsonSetup();
        #Log into Halloo and set someone on call
        #If that user is on call already, do nothing
        #If that user wasn't, update on call
        break;
      case "check":
        jsonSetup();
        #Log into Halloo and see who is currently set to On Call
        #If wrong person is set to OnCall, update the local file
        break;
      case "avail":
        jsonSetup();
        #Set whether a user is available or not
        break;
      case "swappa":
        jsonSetup();
        #Swap current phone line
        break;
      case "alert":
        jsonSetup();
        #Send an alert to the currently on call user
        #Alert is likely going to come from Uptime Robot
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