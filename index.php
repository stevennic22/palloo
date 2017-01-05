<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
    $uri .= $_SERVER['HTTP_HOST'];
    header('Location: '.$uri);
	} else {
		$uri = 'http://';
	}

	$RESPONSE_TITLE = 'Index';
	$RESPONSE_BODY = '<a href="/Halloo/palloo.php">Palloo</a>';

	$retfilename = "return.html";
	if (!file_exists($retfilename)) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 File Not Found', true, 404);
		exit;
	}
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
