<?php
 require_once "cases_by_country_array.php";

 $fp = fopen('cases_by_country.csv', 'w');

 fprintf($fp,"'country_name','cases','deaths','region','total_recovered','new_deaths','new_cases','serious_critical','active_cases','total_cases_per_1m_population'");
 fprintf($fp,"\r\n");

 foreach ($countries_stat as $key => $value) {
	fprintf($fp,"'$value->country_name'," ); 
	fprintf($fp,"'$value->cases'," ); 
	fprintf($fp,"'$value->deaths'," ); 
	fprintf($fp,"'$value->region'," ); 
	fprintf($fp,"'$value->total_recovered'," ); 
	fprintf($fp,"'$value->new_deaths'," ); 
	fprintf($fp,"'$value->new_cases'," ); 
	fprintf($fp,"'$value->serious_critical'," ); 
	fprintf($fp,"'$value->active_cases'," ); 
	fprintf($fp,"'$value->total_cases_per_1m_population'" ); 
	
	fprintf($fp,"\r\n");
 }

 print_r($countries_stat);

  