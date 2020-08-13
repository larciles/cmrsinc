<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
$query="select sum(Porcentaje) tax from Impuestos where Activo =1";
$result = mssqlConn::Listados($query);
echo $result;