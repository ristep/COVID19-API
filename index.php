<?php
/* 
** Headers are not tested quite well yet
*/
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
// Only post request is valid
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-Requested-With');

//
// this prevent errors from some browsers preflight OPTIONS request
// some illuminations here https://smanzary.sman.cloud/cors-nightmare-in-spa-applications/
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') die();
 
require_once "functions.php";
$cn = require "conn.php";

$method = $_SERVER['REQUEST_METHOD'];

$input  = file_get_contents("php://input");
$input = json_decode($input);
//file_put_contents('inputDump.txt', $input->phpFunction, FILE_APPEND); // uncomment for debugging

//$tokenData = require_once('tokening.php'); // for user validation uncomment

switch ($method) {
	case 'POST': // update, insert, delete and select 
		//file_put_contents('inputDump.txt', 'In post method'.$input->phpFunction, FILE_APPEND);
		
		if(isset($input->phpFunction)){ // RPC funcion call
			require_once "phpFunctions.php";
			//file_put_contents('inputDump.txt', 'In if isset', FILE_APPEND); 
			if(function_exists($input->phpFunction)) 
				$ret = ($input->phpFunction)($input,$cn,$tokenData);
			else{
				$ret = (object)[
					'OK' => false,
					'error' => true,
					'rpcName' => $input->phpFunction,
					'message' => "RPC call error! function $input->phpFunction  doesn't  exist! ",
					'data' => false
				];
			}
		}else
			if(isset($input->sqlStatement))
				try{
					$sql = build_sql($input);  // imported function from functions.php
					$sth = $cn->prepare($sql);
					$sth->execute((array)$input->data);
					if($input->sqlStatement == 'select' ){
						$result = $sth->fetchAll(PDO::FETCH_ASSOC);
					}else 
						$result = (object)[];	
					$ret = [
						'OK' => true,
						'error' => false,
						'table' => $input->table,
						'message' => "$input->cmd successfully!",
						'SQL' => $sql,
						'count' => $sth->rowCount()
						];
						if($input->sqlStatement == "select")
							$ret["data"] = $result;
				} catch (PDOException $e) {
					$ret = (object)[
						'error' => 'DataBase',
						'code' => 416,
						'message' => "$input->cmd error!?!?",
						'PDO' => $e,
						'SQL' => $sql
					];
				}
  break;
  case 'PUT':
	case 'GET':
  case 'DELETE':
  case 'PATCH':
  default:
  		http_response_code(417);
			$ret = ((object)[
	    	'error' => "$method method forbidden",
      	'code' => 417,
      	'message' => "$method - Not implemented use POST",
				'Info' => "This is test endpoint for RP_JSON-PHP-API. If you don't know what is this? You are here probably by mistake",
      	'InputData' => $input
    ]);
}
echo json_encode($ret, JSON_NUMERIC_CHECK + JSON_PRESERVE_ZERO_FRACTION);
