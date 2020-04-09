<?php

require_once "rapid_api.php";

$response = apiCall("cases_by_country.php");

$resp =  json_decode($response);

$statistic_taken_at = intval(preg_replace('/[^\d.]/', '', $resp->statistic_taken_at));
$countries_stat = $resp->countries_stat;

foreach ($countries_stat as $key => &$value) {
	$value->cases = intval(preg_replace('/[^\d.]/', '', $value->cases));
	$value->deaths = intval(preg_replace('/[^\d.]/', '', $value->deaths));
	$value->region = intval(preg_replace('/[^\d.]/', '', $value->region));
	$value->total_recovered = intval(preg_replace('/[^\d.]/', '', $value->total_recovered));
	$value->new_deaths = intval(preg_replace('/[^\d.]/', '', $value->new_deaths));
	$value->new_cases = intval(preg_replace('/[^\d.]/', '', $value->new_cases));
	$value->serious_critical = intval(preg_replace('/[^\d.]/', '', $value->serious_critical));

	$sqll[$key] = "
		INSERT INTO `cases_by_country` (`country_name`,`cases`, `deaths`, `critical`,`total_recovered`, `new_cases`, `new_deaths`, `active_cases`, `statistic_taken_at`) 
		VALUES( '$value->country_name',$value->cases, $value->deaths, $value->region, $value->total_recovered, $value->new_cases, $value->new_deaths, $value->serious_critical, $statistic_taken_at)
		ON DUPLICATE KEY UPDATE 
		`cases` = $value->cases,
		`deaths`=  $value->deaths,
		`critical`=  $value->region,
		`total_recovered`= $value->total_recovered,
		`new_cases`= $value->new_cases,
		`new_deaths`= $value->new_deaths,
		`statistic_taken_at`=$statistic_taken_at
		";

	$sql[$key] = "
		INSERT ignore INTO `cases_by_country_log` (`country_name`,`cases`, `deaths`, `critical`,`total_recovered`, `new_cases`, `new_deaths`, `active_cases`, `statistic_taken_at`) 
		VALUES( '$value->country_name',$value->cases, $value->deaths, $value->region, $value->total_recovered, $value->new_cases, $value->new_deaths, $value->serious_critical, $statistic_taken_at);
		";
};

echo $statistic_taken_at;
//  print_r($countries_stat);

$conn = require "conn.php";

// try {
// 	$conn->beginTransaction();
// 	$conn->exec("TRUNCATE `corona_stat`.`cases_by_country`");
// 	$conn->commit();
// 	echo "truncate OK", PHP_EOL;
// } catch (PDOException $e) {
// 	print_r($e);
// }

try {
	$conn->beginTransaction();
	foreach ($sqll as $key => $value) {
		$conn->exec($value);
//		echo $value;
	}
	$conn->commit();
} catch (PDOException $e) {
	print_r($e);
}

try {
	$conn->beginTransaction();
	foreach ($sql as $key => $value) {
		$conn->exec($value);
		// echo $value;
	}
	$conn->commit();
} catch (PDOException $e) {
	print_r($e);
}

echo "cases_by_country!", PHP_EOL;
