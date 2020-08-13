<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");


require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien = $_POST['codclien'];
$fecha=date("Y-m-d");
$idempresa= $_POST['idempresa'];

if ($idempresa=='2') {
	$query="Select * from cma_MFactura where fechafac='$fecha' and codclien='$codclien' "; 
} else {
	# code...
}


$result = mssqlConn::Listados($query);
echo $result;

//checkfacturahoy.php