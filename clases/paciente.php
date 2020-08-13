<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id = $_POST['q'];
$field = null;
if (isset($_POST['field'])) {
	 $field =$_POST['field'];
}

$id =ltrim(rtrim($id)) ;
$arr = explode(" ",$id);
$len = count($arr);
if ($len>1) {
   $id = str_replace(" ","%",$id);
} 

$result=is_numeric($id); 

if ($result) {
	$query="Select TOP 200 * from Mclientes where  Historia = '$id'  Order  by nombres ";
}else{
	$query="Select TOP 200 * from Mclientes where  CONCAT(nombres,Cedula) like '%$id%'  Order  by nombres ";
}

if ( $field!==null) {
	$query="Select TOP 100 * from Mclientes where  codclien = '$id' ";
}


$result = mssqlConn::Listados($query);
echo $result;


function isChar($val){
  if (is_numeric($val)) {
    return $val + 0;
  }
  return 0;
} 
