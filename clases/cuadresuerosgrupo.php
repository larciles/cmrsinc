<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
$tiporeporte = $_POST['tiporeporte'];


$query=" SELECT  sum(monto) monto, modopago,codforpa from VIEWpagosPRCMACST where fechapago='$fecha' and statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  and usuario='$usuario' group by modopago,codforpa order by codforpa ";

if ($tiporeporte=='false') {
	$query=" SELECT  sum(monto) monto, modopago,codforpa from VIEWpagosPRCMACST where fechapago='$fecha' and statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') group by modopago,codforpa order by codforpa ";	
}

$res = mssqlConn::Listados($query);
echo $res;