<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$medios = $_POST['medios'];
$numfactu = $_POST['facturaNum'];


$query  = "update mfactura set medios='$medios' where numfactu='$numfactu'";
 
$result = mssqlConn::insert($query);
echo $result;