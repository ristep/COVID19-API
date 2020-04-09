<?php

function apiCall($param)
{
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://coronavirus-monitor.p.rapidapi.com/coronavirus/$param",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"x-rapidapi-host: coronavirus-monitor.p.rapidapi.com",
			"x-rapidapi-key: e730727e85msh4eb11bdb97a8ae6p12dedcjsna700ed539aeb"
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		 die("cURL Error #:" . $err);
	} else {
		return $response;
	}
}
