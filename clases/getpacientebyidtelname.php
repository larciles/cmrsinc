<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id = $_POST['q'];

$query="Select TOP 200  nombres,codclien,Cedula,Historia from MClientes where  concat(Historia,nombres,Cedula) like '%$id%'  Order  by nombres ";


$result = mssqlConn::Listados($query);
echo $result;


function isChar($val){
  if (is_numeric($val)) {
    return $val + 0;
  }
  return 0;
} 
