<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$coditems = $_POST['idprod'];
$activo   = $_POST['activo'];
$fecha    = date("Y-m-d");

$query="UPDATE MInventario SET activo = '$activo' where coditems='$coditems'";
$result = mssqlConn::insert($query);                  

echo $result;