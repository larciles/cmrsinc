<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];

$query=" SELECT d.numfactu, d.modopago From VIEWpagosPRCMA d WHERE d.fechapago between '$fecha' AND '$fecha' Order By d.modopago ";
$result = mssqlConn::Listados($query);

echo $result;