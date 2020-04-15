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
	$value->active_cases = intval(preg_replace('/[^\d.]/', '', $value->active_cases));
	$value->total_cases_per_1m_population = intval(preg_replace('/[^\d.]/', '', $value->total_cases_per_1m_population));
};

// echo $statistic_taken_at;
// print_r($countries_stat);
// echo "cases_by_country!", PHP_EOL;
