<?php
//Pushover proof of concept to reference in PHP
curl_setopt_array($ch = curl_init(), array(
	CURLOPT_URL => "https://api.pushover.net/1/messages.json",
	CURLOPT_POSTFIELDS => array(
		"token" => "", //your application token here
		"user" => "", //your user token here
		"title" => "title",
		"message" => "msg",
		"sound" => "none",
	),
	CURLOPT_SAFE_UPLOAD => true,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_RETURNTRANSFER => true
));
curl_exec($ch);
curl_close($ch);
?>