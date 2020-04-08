<?php
/* Template function for defining custom procedures
** input parameter is usually data from post request
** returns PHP object 
*/
function phpTestPrintout($inp, $conn)
{
	$info =  (object) [
		'title' => "Test RPC",
		'desc' => "Dummy Function"
	];
	return (object) array_merge((array) $info, (array) $inp);
}

function getWorldTotal($inp, $conn, $tokenData)
{
	$sth = $conn->prepare("
	SELECT 
	sum(`cases`) cases,
    sum(`deaths`) `deaths`,
    sum(`cases`)-sum(`new_deaths`)-sum(`total_recovered`) `active_cases`, 
    sum(`total_recovered`) `total_recovered`, 
    sum(`new_deaths`) `new_deaths`, 
    sum(`new_cases`) `new_cases`,
    sum(`active_cases`) `critical_cases`,
    max(`statistic_taken_at`) `statistic_taken_at`
FROM `cases_by_country` WHERE `cases_by_country`.`country_name`!='World'
");
	
	// $sth->bindParam('userId', $inp->userId);
	try {
		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_OBJ);
		$ret = [
			'OK' => true,
			'dataSet' => $inp->dataSet,
			'message' => $sth->rowCount() == 1 ? 'World Total OK' : 'Agregation Error!',
			"data" => $result
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e
		];
	}
	return $ret;
};

function getCountry($inp, $conn, $tokenData)
{
	require_once "cronJobs/countryHistory.php";
	$sth = $conn->prepare("
	SELECT * from `cases_by_country` WHERE country_name=:country  
		");
	$cc = urldecode($inp->keyData->country_name);
	$sth->bindParam('country', $cc);
	try {
		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_OBJ);
		$history = countryHistory($result->code3);
		$ret = [
			'OK' => true,
			// 'cc' => urldecode($cc),
			'dataSet' => $inp->dataSet,
			"data" => $result,
			'history'=> $history
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e
		];
	}
	return $ret;
}

function getGlobalHistory($inp, $conn, $tokenData)
{
	require_once "cronJobs/globalHistory.php";
	try {
		$history = globalHistory();
		$ret = [
			'OK' => true,
			'dataSet' => $inp->dataSet,
			'history'=> $history
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e
		];
	}
	return $ret;
}

function getCountryNames($inp, $conn, $tokenData)
{
	$sth = $conn->prepare("
	SELECT country_name FROM `cases_by_country` WHERE 1  
	ORDER BY `cases_by_country`.`country_name` ASC
	");
	
	try {
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_COLUMN);
		$ret = [
			'OK' => true,
			'dataSet' => $inp->dataSet,
			'table' => 'users',
			"data" => $result
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e,
			"userData" => false
		];
	}
	return $ret;
}

function getUserData($inp, $conn, $tokenData)
{
	if (isset($inp->userId)) {
		$sth = $conn->prepare("SELECT * FROM `users` WHERE `id`=:userId");
		$sth->bindParam('userId', $inp->userId);
	} else {
		$sth = $conn->prepare("SELECT * FROM `users` WHERE `name`=:name");
		$sth->bindParam('name', $inp->userName);
	}
	try {
		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_OBJ);
		$sth2 = $conn->prepare('SHOW FIELDS FROM users');
		$sth2->execute();
		//			$fields = $sth2->fetchAll(PDO::FETCH_OBJ);
		$ret = [
			'OK' => true,
			'dataSet' => $inp->dataSet,
			'table' => 'users',
			'message' => $sth->rowCount() == 1 ? 'UserData OK' : 'User not Found!',
			"data" => $result
			//				"fileds" => $fields
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e,
			"userData" => false
		];
	}

	return $ret;
}

function changePassword($inp, $conn, $tokenData)
{
	if (isset($inp->userId)) {
		if ($tokenData->name != $inp->userName || $tokenData->id != $inp->userId) {
			return [
				'OK' => false,
				'errorType' => 'fakeRquest',
				'code' => 477,
				'message' => "Fake request Error!"
			];
		};
	};

	$sth = $conn->prepare("SELECT `password` FROM `users` WHERE `id`=:userId");
	$sth->bindParam('userId', $inp->userId);
	$sth->execute();
	$result = $sth->fetch(PDO::FETCH_OBJ);
	if ($inp->oldPassword != $result->password) {
		return [
			// 'oldp' =>  $inp->oldPassword,
			// 'dnOld' => $result->password,
			'OK' => false,
			'errorType' => 'oldPasswordErr',
			'code' => 401,
			'message' => "Current Password is Incorrect!"
		];
	}

	$sth2 = $conn->prepare("UPDATE `users` SET `password` = :userPassword WHERE `users`.`id` = :userId;");
	$sth2->bindParam('userPassword', $inp->newPassword);
	$sth2->bindParam('userId', $inp->userId);
	try {
		$sth2->execute();
		$ret = [
			'OK' => true,
			'message' => "Password changed!",
			//		"data" => $result
			// 		"fileds" => $fields
		];
	} catch (PDOException $e) {
		$ret = [
			'OK' => false,
			'errorType' => 'DataBase',
			'code' => 416,
			'message' => "Data Base Error!",
			'PDO' => $e,
			"userData" => false
		];
	}

	return $ret;
}


// rss feed zdravstvo
function ParseV2 ($url) {
	$fileContents= file_get_contents($url);
	$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
	$fileContents = trim(str_replace('"', "'", $fileContents));
	$simpleXml = simplexml_load_string($fileContents);
	return $simpleXml;
}

function Parse($url){
	return simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
}

function getZdravstvoRSS() {
		return Parse ('http://zdravstvo.gov.mk/feed/');
}

// $xml = simplexml_load_string($feed);
// return ($xml);


		// $fileContents = file_get_contents($feed_url);
		// $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
		// $fileContents = trim(str_replace('"', "'", $fileContents));
		// $simpleXml = simplexml_load_string($fileContents);
		// $json = json_encode($simpleXml);
		// return $json;
