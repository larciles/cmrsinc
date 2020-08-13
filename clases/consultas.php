<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query="select * from VIEW_SoloConsulta where codcons in ('01','03') ";
$result = mssqlConn::Listados($query);
echo $result;