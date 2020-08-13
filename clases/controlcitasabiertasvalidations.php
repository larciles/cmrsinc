<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien = $_POST['q'];

$query="Select * from mconsultas where codclien='$codclien ' and asistido=0 ";
$result = mssqlConn::Listados($query);
echo $result;
