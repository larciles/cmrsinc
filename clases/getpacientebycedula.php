<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id = $_GET['q'];
$field = null;
if (isset($_GET['field'])) {
	 $field =$_GET['field'];
}


$query="Select TOP 200 nombre,apellido,nombres from Mclientes where Cedula  = '$id' ";
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
