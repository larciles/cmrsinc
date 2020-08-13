<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$idempresa     = $_POST['idempresa'];
$monto_abonado = $_POST['monto'];
$numdoc        = $_POST['numdoc'];
$tipo          = $_POST['tipo'];

if ($idempresa =='3' and $tipo=='04') {
	$dbmaster='MSSMDev';
	$fieldFilter="numnotcre";
} elseif ($idempresa =='2' and $tipo=='04') {
	$dbmaster='CMA_Mnotacredito';
	$fieldFilter="numnotcre";
}


$query="UPDATE ".$dbmaster." Set  monto_abonado='$monto_abonado', statnc='3' , cancelado='1' where ".$fieldFilter."='$numdoc' ";
$result = mssqlConn::insert($query);

echo $result;

//updatemasterpagosdev.php