<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$record = $_POST['record'];


$query="SELECT * from MClientes A where A.Historia ='$record' ";
$res = mssqlConn::Listados($query);
echo $res;

