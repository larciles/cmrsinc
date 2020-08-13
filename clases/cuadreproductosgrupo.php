<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
$tiporeporte = $_POST['tiporeporte'];


$query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWPagosPR_W7  where fechapago='$fecha' and usuario='$usuario' and statfact=3  group by modopago,codforpa  order by modopago";

if ($tiporeporte=='false') {	
$query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWPagosPR_W7  where fechapago='$fecha' and statfact=3  group by modopago,codforpa  order by modopago";
}

$res = mssqlConn::Listados($query);
echo $res;