<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];


$query="Select modopago, SUM(monto) total, count(*) facturas from view_pagosgroupmss where fechapago='$fecha' group by modopago";
$res = mssqlConn::Listados($query);
echo $res;

