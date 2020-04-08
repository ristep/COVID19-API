<?php

require_once "rapid_api.php";

$response = apiCall("worldstat.php");

$resp = (array) json_decode($response);
foreach ($resp as &$value) {
	$value = intval(preg_replace('/[^\d.]/', '', $value));
};

$conn = require "conn.php";

$sql = "INSERT INTO `world_total_log` (`total_cases`, `total_deaths`, `total_recovered`, `new_cases`, `new_deaths`, `statistic_taken_at`) 
						 VALUES ( $resp[total_cases], $resp[total_deaths], $resp[total_recovered], $resp[new_cases], $resp[new_deaths], $resp[statistic_taken_at]);";
$conn->exec($sql);

$conn->exec("TRUNCATE `corona_stat`.`world_total`");
$sql = "INSERT INTO `world_total` (`total_cases`, `total_deaths`, `total_recovered`, `new_cases`, `new_deaths`, `statistic_taken_at`) 
						 VALUES ( $resp[total_cases], $resp[total_deaths], $resp[total_recovered], $resp[new_cases], $resp[new_deaths], $resp[statistic_taken_at]);";
$conn->exec($sql);


echo 'WorldStat!', PHP_EOL;
