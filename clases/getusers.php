<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$usuario = $_POST['q'];

$query="Select *  from loginpass   where activo ='1'  /* amd login ='$usuario' */ ";    

$result = mssqlConn::Listados($query);
echo $result;
