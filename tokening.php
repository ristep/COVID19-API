<?php
require "./vendor/autoload.php";
use \Firebase\JWT\JWT;
//$skey = md5("FMyNTYiLCJ0eX5180112");
$skey = md5("FMyNTYiLCJ0eX5".date("ymd"));

if($_SERVER['REQUEST_METHOD']=='OPTIONS') die();

$head = getallheaders();
// file_put_contents('udaa.txt', print_r($head, true), FILE_APPEND );

if(isset($head['Authorization'])){
	$token = $head['Authorization'];
}
else{
	if(isset($_GET['au-token'])){
		$token = $_GET['au-token'];
	}
	else
		if(isset($_POST['au-token'])){
			$token = $_POST['au-token'];
		}
		else
			if(isset($input->au_token)){
				$token = $input->au_token;
			}	
			else{	
				echoErr(  (object)[ 'error' => 'tokenator', 'code' => 401, 'message' => 'Unauthorized, unknown user!!'  ] );
				die();
			}
}	

try{
	$decoded = JWT::decode($token	,$skey, ['HS256']);
}
catch(Exception $err) {
	echoErr(  (object)[ 'error' => 'tokenator', 'code' => 401, 'message' => 'Unauthorized, bad token!!'  ] );
	die();
}	
// print_r($decoded);
return $decoded;

function echoErr($e){
	//file_put_contents('sqlError.dump', print_r($e->errorInfo,true)."\n", FILE_APPEND );
	if(isset($e->code))
					http_response_code($e->code);
	else
					http_response_code('406');
	die(json_encode($e));}
?>

?>