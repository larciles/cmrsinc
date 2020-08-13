<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query="Select COD,PAIS from Pais order by PAIS";
$paises = mssqlConn::Listados($query);
echo $paises;