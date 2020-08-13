<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$factcomp = $_POST['factcomp'];
$query="SELECT *, CONVERT(VARCHAR(10),fechacomp,101) fecha from MCompra where factcomp='$factcomp'  ";	

$estados = mssqlConn::Listados($query);
echo $estados;