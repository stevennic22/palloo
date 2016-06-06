<?php
//TO DO: In trimOnCallSet & trimOnCallCheck, if script errors out and can't log in, mark that in the PHP Transfer file. Also triple check home copies of all files.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo "<!DOCTYPE html><html><head>";
echo '<link rel="shortcut icon" type="image/x-icon" href="https://nealv4test.sleepex.com/favicon.ico" />';
//echo '<link rel="shortcut icon" type="image/x-icon" href="http://uptimerobot.com/assets/ico/favicon.ico" />';
//echo "<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />";
echo "<script type='text/javascript' src='https://code.jquery.com/jquery-1.11.2.min.js'></script>";
echo "<script type='text/javascript'>
$(document).ready(function() {
  $('.test').hover(function(){ $(this).toggleClass('cn'); });
});
</script>";
echo "<title>";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	//echo "GET <br>";
	if (count($_GET) == 0 || isset($_GET["help"])){
		echo "Palloo Help";
	} else if (isset($_GET["process"])) {
		if(strtolower($_GET["process"]) == "help"){
			echo "Palloo Help";
		} else if (!count($_GET) == 0) {
			if (isset($_GET["process"])) {
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
				}
			} else {
				echo "Palloo Help";
			}
		}
	}
}
echo "</title><style>.main {margin: auto;
		//margin-left: auto;
		//margin-right: auto;
		//width: 90%;
		text-align: center;}</style></head><body>";
/*
echo "</title><style>.main {margin: 20px 0;
		//margin-left: auto;
		//margin-right: auto;
		//width: 90%;
		text-align: center;}</style></head><body><div class='main'>";
*/
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
			"token" => "", //your application token here
			"user" => "", //your user token here
			"title" => $title,
			"message" => $msg,
			"sound" => "none",
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
		CURLOPT_HTTPHEADER  => array('Authorization: Bearer '), //Add your token here
		CURLOPT_POSTFIELDS => array(
			//Use email to send it to another PushBullet user
			"email" => "", //email of user to send to
			"type" => "note",
			"title" => $title,
			"body" => $msg,
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
	$number = str_replace(array("&","-"," ","(",")"), '', $number);
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
		case "google fi":
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
		return "Please provide a valid name to set.";
	} else {
		//Run Python script to get On Call #
		set_time_limit(360);
		shell_exec("trimOnCallCheck.py");
		$transferfilename = "Ext/phptransfer";
		
		if(file_exists($transferfilename)) {
			$pyCapture = fopen($transferfilename, "r");
			while(!feof($pyCapture)){
					$transferFileInfo[] = fgets($pyCapture);
			}
			fclose($pyCapture);
			unlink($transferfilename);
			$pyOutput = stripper($transferFileInfo[0]);
			
			if ($pyOutput == "Error logging in to Halloo" || $pyOutput == "Not a valid cellular service at this time") {
				return $pyOutput;
			}
		} else {
			return "Error running script.";
		}
		
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
				return "User is already On Call.";
			} else {
				//This should set the onCall User here
				$ochandle = fopen($ocfilename, "w");
				
				for($i=1;$i < count($line);$i++){
					fwrite($ochandle, $line[$i]);
				}
				fclose($ochandle);
				if($rType == 1){
					return "On Call file updated. User: " . strtoupper(substr($line[0],0,1)) . substr($line[0],1);
				} else {
					return $line[0];
				}
			}
		} else {
			set_time_limit(360);
			$command = escapeshellcmd('trimOnCallSet.py -s ');
			if (str_replace(array("\n","\r"), '', $line[3]) == "Google Fi") {
				$setService = strtolower(escapeshellcmd(stripper($line[2])));
			} else {
				$setService = strtolower(escapeshellcmd(str_replace(array("&","\n","\r","-"," "), '', $line[3])));
			}
			$setVars = escapeshellcmd(stripper($line[1]));
			shell_exec($command . '"' . $setService . '" -p "' . $setVars . '"');
			
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
					return "User's extension is now set on Halloo and On Call file is up to date. User: " . strtoupper(substr($line[0],0,1)) . substr($line[0],1);
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
					return "User's extension is now set on Halloo and On Call file updated. User: " . strtoupper(substr($line[0],0,1)) . substr($line[0],1);
				} else {
					return $line[0];
				}
			}
		}
	}
}

function checkUser($rType){
	//Run Python script to get On Call #
	set_time_limit(360);
	shell_exec("trimOnCallCheck.py");
	$transferfilename = "Ext/phptransfer";
	
	if(file_exists($transferfilename)) {
		$pyCapture = fopen($transferfilename, "r");
		while(!feof($pyCapture)){
				$transferFileInfo[] = fgets($pyCapture);
		}
		fclose($pyCapture);
		$pyOutput = stripper($transferFileInfo[0]);
		unlink($transferfilename);
			
		if ($pyCapture == "Error logging in to Halloo" || $pyCapture == "Not a valid cellular service at this time") {
			return $pyCapture;
		}
	} else {
		return "Error running script.";
	}
	
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
				return "On Call user is: " . strtoupper(substr($line[0],0,1)) . substr($line[0],1);
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
				return "On Call file updated. User: " . strtoupper(substr($line[0],0,1)) . substr($line[0],1);
			} else {
				return $line[0];
			}
		}
	}
}

function getAlertVars($from) {
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
	function alertOutput($file,$comVars) {
		switch(strtolower(stripper($file[4]))){
			case "pushover":
				pushOver(stripper($file[5]), $comVars[0], $comVars[1]);
				break;
			case strtolower("SMS"):
				sendText($file[3], stripper($file[1]), $comVars[0], $comVars[1]);
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
	
	if($from == 1){
		if($_GET["alertType"] == 1) {
			$comVars[0] = "Monitor is UP: " . $_GET["monitorFriendlyName"];
			$upDown = "UP";
		} else if ($_GET["alertType"] == 2) {
			$comVars[0] = "Monitor is DOWN: " . $_GET["monitorFriendlyName"];
			$upDown = "DOWN";
		} else {
			$comVars[0] = "Monitor is in a superposition of both UP and DOWN: " . $_GET["monitorFriendlyName"];
			$upDown = "in a superposition of both UP and DOWN";
		}
		
		$comVars[1] = "The monitor '" . $_GET["monitorFriendlyName"] . "' (" . $_GET["monitorURL"] . ") is currently " . $upDown . " (" . $_GET["alertDetails"] . ").";
		
		return($comVars);
	} else {
		foreach($_GET as $query_string_variable => $value) {
			switch($query_string_variable) {
				case "title":
					$comVars[0] = input_cleanse($value);
					break;
				case "msg":
					$comVars[1] = input_cleanse($value);
					break;
			}
		}
		if(!isset($_GET["title"])){
			$comVars[0] = "Title was not set";
		}
		if(!isset($_GET["msg"])){
			$comVars[1] = "Message was not set";
		}
	}
	
	//Sets directory of extensions
	$dir = 'Ext';
	
	//Open On Call file and get all information
	$ocfilename = $dir . "/oncall";
	$ochandle = fopen($ocfilename, "r");
	
	while(!feof($ochandle)){
		$ocFileInfo[] = fgets($ochandle);
	}
	fclose($ochandle);
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
		if(stripper($ocFileInfo[0]) == stripper($line[1])) {
			break;
		}
		unset($line);
	}
	
	if(!isset($line)) {
		//Send information to Neal
		//Grabs all information from Neal's file.
		$nealfilename = $dir . "/neal";
		$nealFileInfo[] = "Neal";
		$nealhandle = fopen($nealfilename, "r");
		while(!feof($nealhandle)){
			$nealFileInfo[] = fgets($nealhandle);
		}
		fclose($nealhandle);
		alertOutput($nealFileInfo,$comVars);
	} else {
	//$line[0] == filename
	//$line[1] == phone number
	//$line[2] == email address
	//$line[3] == cell service
	//$line[4] == method
	//$line[5] == extra information
	//If method is phone - 3 is carrier
	//If method is pushover - 5 is device ID
	//If method is email - 5 would not be needed (unless personal email)
	//If method is pushbullet - 5 would be personal user email address (if blank, use work email)
		if (stripper($line[0]) == strtolower("neal")) {
			alertOutput($line,$comVars);
		} else {
			alertOutput($line,$comVars);
			$nealfilename = $dir . "/neal";
			$nealFileInfo[] = "Neal";
			$nealhandle = fopen($nealfilename, "r");
			while(!feof($nealhandle)){
				$nealFileInfo[] = fgets($nealhandle);
			}
			fclose($nealhandle);
			alertOutput($nealFileInfo,$comVars);
		}
	}
	return $comVars;
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
	if (count($_GET) == 0 || isset($_GET["help"])){
		echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
	} else if (isset($_GET["process"])) {
		if(strtolower($_GET["process"]) == "help"){
			echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
		} else if (isset($_GET["process"])) {
			$procVar = input_cleanse(strtolower($_GET["process"]));
			switch($procVar){
				case "check":
					$OCUser = checkUser(1);
					
					if (is_String($OCUser)) {
						if ($OCUser == "Error logging in to Halloo" || $OCUser == "Not a valid cellular service at this time") {
							echo "<div class='main' style='text-align: center;'>There was an error while checking the onCall user. Please contact the administrator.</div>";
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
							if ($funcUpdate == "Error logging in to Halloo" || $funcUpdate == "Not a valid cellular service at this time" || $funcUpdate == "Error running script.") {
								echo "<div class='main' style='text-align: center;'>There was an error while setting the onCall user. Please contact the administrator.</div>";
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
					//Get information from URL
					//Find On Call information (from file)
					//If Neal
					//	Use preferred contact method to alert
					//If not Neal
					//	Contact Neal through preferred method
					//	Contact On Call user through preferred method
					if (isset($_GET["from"]) && $_GET["from"]=="uptime") {
						$alertVars = getAlertVars(1);
					} else {
						$alertVars = getAlertVars(0);
					}
					//echo $alertVars[0];
					for($i=0;$i < count($alertVars);$i++){
						if ($i == 0){
							echo "<div class='main' style='text-align: center;'>Title: " . $alertVars[$i] . "</div>";
						} else if ($i == 1){
							echo "<div class='main' style='text-align: center;'>Message: " . $alertVars[$i] . "</div>";
						} else {
							echo "<div class='main' style='text-align: center;'>" . $alertVars[$i] . "</div>";
						}
					}
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
						//Should probably send notification...
					} else {
						echo "<div class='main' style='text-align: center;'>The next user in the rotation (" . $pyOutput .") has been set.</div>";
					}
					break;
			}
		} else {
			echo "<div class='main' style='text-align: center;'>Available functions:<br><br>&bull;Check<br>&bull;Set<br>&bull;Alert<br>&bull;Auto<br></div>";
		}
	}
}
echo "</body></html>";
?>
