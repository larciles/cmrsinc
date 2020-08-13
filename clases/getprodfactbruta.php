<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
$tiporeporte='false';

if (isset($_POST['tiporeporte'])) {
   $tiporeporte= $_POST['tiporeporte'];
}

$query="SELECT sum(total+monto_flete) monto from MFactura where fechafac='$fecha' and  statfact=3  ";
if ($tiporeporte=='true') {
	$query="SELECT sum(total+monto_flete) monto from MFactura where fechafac='$fecha'  and usuario='$usuario' and  statfact=3  ";
}
$res = mssqlConn::Listados($query);
echo $res;
//