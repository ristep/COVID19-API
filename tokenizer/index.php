<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:  POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-Requested-With');
//header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

require "../vendor/autoload.php";
use \Firebase\JWT\JWT;
$skey = md5("FMyNTYiLCJ0eX5".date("ymd"));

if($_SERVER['REQUEST_METHOD']=='OPTIONS') die();

if( $_SERVER['REQUEST_METHOD'] != 'POST'){
	require_once "../echoErr.php";
	echoErr(  (object)[ 'error' => 'inifile', 'code' => 400, 'message' => 'Bad Request'  ] );
}

$cn = require "../conn.php";
if(isset($_POST['name']) && $_POST['password']){
	$username = $_POST['name'];
	$password = $_POST['password'];
}
else{
	$input = json_decode(file_get_contents("php://input"));
	$username = $input->name;
	$password = $input->password;
}

// clear
file_put_contents('inputDump.txt', $username . ' ' . $password . "\n", FILE_APPEND );

try { 
	$sql = "select * from users where name=:name";
	$sth = $cn->prepare($sql);
  $sth->bindParam(':name', $username, PDO::PARAM_STR);
	$sth->execute();
	if( $sth->rowCount() < 1 ) {
		require_once "../echoErr.php";
		echoErr(  (object)[ 'error' => 'inifile', 'code' => 401, 'message' => 'Unauthorized nema go'  ] );
	}
	$result = $sth->fetch(PDO::FETCH_OBJ);

	// hashenbashen na passwordot
	if($password===$result->password){
		$token = array(
			"id" => $result->id,
			"name" => $result->name,
			"email" => $result->email,
      "first_name" => $result->first_name,
      "second_name" => $result->second_name,
			// "address" => $result->address,
			// "state" => $result->state,
			// "place" => $result->place,
			"role" => $result->role,
			// 'time' => date("ymdHms"),
			"jti" => 'deca-meca-'.date("ymdhms").'-jade-'.mt_rand().'-nogu'
		);
		$jwt = JWT::encode($token, $skey);
		// sleep(2);
		$token['jti'] = date("y-m-d H:m:s");
		$token['auToken'] = $jwt;
		echo json_encode($token);	  
		// sleep(2);	
		// print_r( JWT::decode($jwt, md5("FMyNTYiLCJ0eX5".date("Y-m-d")), array('HS256')) );
	 	die();
	}
	else{
		require_once "../echoErr.php";
		echoErr(  (object)[ 'error' => 'inifile', 'code' => 401, 'message' => 'Unauthorized, de be daa..'  ] );
	}
} 
catch (PDOException $e) { 
	echoErr( $e ); 
}
