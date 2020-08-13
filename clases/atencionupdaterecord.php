<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$codclien = $_POST['codclien'];
$record = $_POST['record'];
$old_str = $record;
$record  = str_replace(' ', '', $old_str);
if (isset($_POST['tipo'])  && $_POST['tipo']=='save') {
	$query="UPDATE MClientes Set  Historia='$record' where codclien='$codclien' ";
	$result = mssqlConn::insert($query);
} elseif (isset($_POST['tipo'])  && $_POST['tipo']=='check') {
	$query="Select * From MClientes Where  Historia='$record' ";
	$result = mssqlConn::Listados($query);
}



echo $result;