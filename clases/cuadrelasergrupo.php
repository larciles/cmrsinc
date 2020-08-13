<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
$tiporeporte = $_POST['tiporeporte'];



$query=" SELECT "
." sum(a.monto) monto, a.modopago,a.codforpa "
." FROM "
."  VIEWpagosPRMSS_W7 a  "
." WHERE "
."  a.statfact <> '2' AND "
."  a.id_centro = '3' AND "
."  a.fechapago = '$fecha' AND "
."  a.userfac = '$usuario' "
." group by  a.modopago,a.codforpa " 
."	order by a.modopago ";

if ($tiporeporte=='false') {
	$query=" SELECT "
	." sum(a.monto) monto, a.modopago,a.codforpa "
	." FROM "
	."  VIEWpagosPRMSS_W7 a  "
	." WHERE "
	."  a.statfact <> '2' AND "
	."  a.id_centro = '3' AND "
	."  a.fechapago = '$fecha'  "
	." group by  a.modopago,a.codforpa " 
	."	order by a.modopago ";	
}

$res = mssqlConn::Listados($query);
echo $res;