<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id = $_POST['q'];

$query="Select TOP 25 * from Mclientes where  Historia ='$id'  ";

$result = mssqlConn::Listados($query);
echo $result;


function isChar($val){
  if (is_numeric($val)) {
    return $val + 0;
  }
  return 0;
} 