<?php

$default_page = 5;

function parse_filter( $fltStr ){  
	$arr3 = explode (',',$fltStr);
	//print_r( $arr3);
  $not = '';
  $flt = '';
  switch ($arr3[1]) {
		
		// "sw”: start with (string starts with value)
    case 'nsw':
      $not = 'not';
    case 'sw':
      $flt .= $arr3[0] . " $not like '" . $arr3[2] . "%' ";
      break;
		
		// “cs”: contain string (string contains value)
    case 'ncs':
      $not = 'not';
    case 'cs':
      $flt .= $arr3[0] . " $not like '%" . $arr3[2] . "%' ";
      break;
		
		// “ew”: end with (string end with value)
    case 'new':
      $not = 'not';
    case 'ew':
      $flt .= $arr3[0] . " $not like '%" . $arr3[2] . "' ";
      break;
		
		// “eq”: equal (string or number matches exactly)
    case 'neq':
      $not = '!';
    case 'eq':
      $flt .= $arr3[0] . " $not = '" . $arr3[2] . "'";
      break;
		
		// “lt”: lower than (number is lower than value)
    case 'lt':
      $flt .= $arr3[0] . " < '" . $arr3[2] . "'";
      break;
		
		// “le”: lower or equal (number is lower than or equal to value)
    case 'le':
      $flt .= $arr3[0] . " <= '" . $arr3[2] . "'";
      break;
		
		// "ge": greater or equal (number is higher than or equal to value)
    case 'ge':
      $flt .= $arr3[0] . " >= '" . $arr3[2] . "'";
      break;
 
		// "gt": greater than (number is higher than value)
    case 'gt':
      $flt .= $arr3[0] . " > '" . $arr3[2] . "'";
      break;
	
		// “bt”: between (number is between two comma separated values)
		case 'nbt':
      $not = 'not';
    case 'bt':
      $flt .= $not . '(' . $arr3[0] . " > '" . $arr3[2] . "' and " . $arr3[0] . " < '" . $arr3[3] . "' )";
			break;

		// “nbte”: not between or equal (number is between two comma separated values)
	  case 'nbte':
      $not = 'not';
    case 'bte':
      $flt .= $not . '(' . $arr3[0] . " >= '" . $arr3[2] . "' and " . $arr3[0] . " <= '" . $arr3[3] . "' )";
      break;
		
		// "in" in list number or string is in list separated by comas 
		case 'nin':
      $not = 'not';
    case 'in':
      $lst = implode(",", array_slice($arr3, 2));
      $flt .= $arr3[0] . " $not in ($lst)";
			break;
			
    // “is”: is null (field contains “NULL” value)
    case 'nis':
      $not = 'not';
    case 'is':
      $flt .= $arr3[0] . " is $not NULL ";
      break;
	}
	
  return $flt;
}

function build_filter($flt){
	if(is_string($flt)){
		return parse_filter($flt);
	}else{
		if(is_array($flt)){
			return "(". build_filter($flt[0]) . " $flt[1] " . build_filter($flt[2]) . ")";
		}
		else return '';
	}
}

function build_sql($input){

	$where = "";
	switch ($input->sqlStatement) {
	case 'sql':
		$nasty_words = ['delete', 'drop', 'insert', 'use', 'grant', 'user', 'update'];
		$sql = $input->sql;
		foreach ($nasty_words as $wrd) {
			if (stripos($sql, $wrd) !== false)
				die(json_encode(["OK" => false, "error" => " Bad Robot!!! "]));
		}
	break;
	case 'select': 
			$fieldArr = array();
			$whereArr = array();
			if (isset($input->fields))
				$fields = $input->fields;
			else
				$fields = ["*"];
			foreach ($fields as $fl)
				array_push($fieldArr, $fl);

			if (isset($input->keyData)) {
				foreach ($input->keyData as $key => $val)
					array_push($whereArr, "$key='$val'");
				$where = "WHERE " . implode(' and ', $whereArr);
			} elseif (isset($input->where))
				$where = "WHERE $input->where";
			elseif (isset($input->filter)){
				$where = "WHERE " . build_filter($input->filter);
			}
			else
				$where = "";

			$limit = "";
			if (isset($input->limit))
				if (is_array($input->limit))
					$limit = " limit " . implode(",", $input->limit);
				else
					$limit = " limit " . $input->limit;
			else {
				if (isset($input->page)) {
					if (is_array($input->page))
						$page = $input->page;
					else
						$page = explode(",", $input->page);
					if (isset($page[1]))
						$limit = " limit " . $page[0] * $page[1] . ',' . $page[1];
					else
						$limit = " limit " . $page[0] * $default_page . ',' . $default_page;
				}
			}

			$order = "";
			if (isset($input->order)) {
				if (is_array($input->order))
					$order = "ORDER BY " . implode(",", $input->order);
				else
					$order = "ORDER BY " . $input->order;
			}

			$sql = "SELECT " . implode(',', $fieldArr) . " FROM $input->table $where $order $limit;";
		break;
		case 'update':
			$whereArr = array();
			if (isset($input->keyData)) {
				foreach ($input->keyData as $key => $val)
					array_push($whereArr, "$key='$val'");
			}	
			$where = implode(' and ', $whereArr);

			$fieldArr = array();
			foreach ($input->data as $pl => $vl)
				array_push($fieldArr, "$pl=:$pl");

				$sql = "UPDATE $input->table SET " . implode(',', $fieldArr) . " WHERE $where ";
		break;
		case 'insert':
			$nms = array();
			$vls = array();
			foreach ($input->data as $nm => $vl) {
				array_push($nms, "`$nm`");
				array_push($vls, ":$nm");
			}
			$sql = "INSERT INTO `$input->table`(" . implode(',', $nms) . ") VALUES(" . implode(',', $vls) . ");";
		break;
		case 'delete':
			$whereArr = array();
			if (isset($input->keyData)) {
				foreach ($input->keyData as $key => $val)
					array_push($whereArr, "$key='$val'");
			}	
			$where = implode(' and ', $whereArr);

			$sql = "DELETE FROM `$input->table` WHERE $where;";
			//file_put_contents('inputDump.txt', $sql, FILE_APPEND);
			if (strlen(trim($where)) > 5) {
				return $sql;
			}	
		break;
		default:
		 $sql='';
	};
	return $sql;
};


// rss feed zdravstvo
function ParseV2 ($url) {
	$fileContents= file_get_contents($url);
	$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
	$fileContents = trim(str_replace('"', "'", $fileContents));
	$simpleXml = simplexml_load_string($fileContents);
	return $simpleXml;
}
function limit_text($text, $limit) {
	if (str_word_count($text, 0) > $limit) {
			$words = str_word_count($text, 2);
			$pos = array_keys($words);
			$text = substr($text, 0, $pos[$limit]) . '<br>[...]';
	}
	return $text;
}

function Parse($url){
	return simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
}
