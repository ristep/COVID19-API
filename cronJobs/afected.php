<?php

require_once "rapid_api.php";

$response = apiCall("affected.php");

$resp =  json_decode($response);

$conn = require "conn.php";

foreach( $resp->affected_countries as $value ){
	$conn->exec ("INSERT IGNORE INTO `countries`(`name`) VALUES ('$value');");
}

echo 'Afected!', PHP_EOL;;