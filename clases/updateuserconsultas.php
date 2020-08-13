<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';


$codclien  = $_POST['codclien']; 
$codconsulta= $_POST['codconsulta'];
$fieLd    = $_POST['fieLd'];
$fecha_cita= $_POST['fecha_cita'];
$typefield= $_POST['typefield'];

if ($codconsulta=='07') {
	$table ="MconsultaSS";
}else{
	$table ="Mconsultas";
}

if ($typefield=="m") {
	$campo="codmedico";
}elseif ($typefield=="u") {
	$campo="usuario";
}

$dbmsql = mssqlConn::getConnection();

$query="Update ".$table." Set ".$campo."="."'".$fieLd."'"." where   codclien='$codclien' and codconsulta='$codconsulta' and fecha_cita='$fecha_cita' ";



$result = mssqlConn::insert($query);
echo json_encode($result);