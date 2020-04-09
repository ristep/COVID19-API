<?php

function countryHistory($param)
{
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://covidapi.info/api/v1/country/$param",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
	)); 

	$response = json_decode( curl_exec($curl) );
	$err = curl_error($curl);
	
	curl_close($curl);

	$reto = new stdClass();
	$i=0;
	foreach( $response->result as $key => $value ){
		if( $value->confirmed >0 || $value->deaths || $value->recovered ){
			if(!$reto->begin) $reto->begin = $key;
			$reto->end = $key;
			$reto->labels[$i] = $key;
			$reto->confirmed[$i] = $value->confirmed;
			$reto->deaths[$i] =  $value->deaths;
			$reto->recovered[$i] = $value->recovered;
			$reto->active[$i] =  $value->confirmed - $value->recovered; - $value->deaths;
			$i++;
		}
	} 

	if ($err) {
		 die("cURL Error #:" . $err);
	} else {
		return  $reto;
	}
}
