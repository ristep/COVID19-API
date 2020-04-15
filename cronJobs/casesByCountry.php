<?php
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=file.csv");
	header("Pragma: no-cache");
	header('Access-Control-Allow-Origin: *');
	
	echo file_get_contents("/path/to/file");