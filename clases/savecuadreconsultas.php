<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$tiporeporte='false';
$cash      = $_POST['cash'];
$usuario   = $_POST['usuario'];
$Id_centro = $_POST['Id_centro'];
$tipo      = $_POST['tipo'];
$estacion  = $_POST['estacion'];
$fecha     = $_POST['fecha'];
if (isset($_POST['tiporeporte'])) {
   $tiporeporte= $_POST['tiporeporte'];
}

$len = sizeof($cash);


date_default_timezone_set("America/Puerto_Rico");
$hoy =date("m-d-Y");


$time = strtotime($fecha);
$newDate = date('m-d-Y',$time);

if ($newDate==$hoy) {
	
	//$query="Delete from cuadre WHERE fecha='$fecha' and id_centro='$Id_centro' and estacion='$estacion' and usuario='$usuario' ";
	//$query="Delete from cuadre WHERE fecha='$fecha' and id_centro='$Id_centro' and estacion='CMA' ";
	if ($tiporeporte=='true') {	
		$query="Delete from cuadre WHERE fecha='$fecha' and id_centro='$Id_centro' and usuario='$usuario' ";
	}
	$res = mssqlConn::insert($query);

	for ($i=0; $i <$len ; $i++) { 
		$valor=$cash[$i]['valor'];
		$monto=$cash[$i]['monto'];

		//$query="Insert into  Cuadre  (fecha,estacion,monto,valor,id_centro,usuario,tipo) VALUES('$fecha','$estacion','$monto','$valor','$Id_centro','$usuario','$tipo')  ";
		
		//$query="Insert into  Cuadre (fecha,estacion,monto,valor,id_centro,usuario,tipo) VALUES('$fecha','CMA','$monto','$valor','$Id_centro','$usuario','$tipo')  ";
		if ($tiporeporte=='true') {	
		    
		    $query="Insert into  Cuadre (fecha,monto,valor,id_centro,usuario,tipo,estacion) VALUES('$fecha','$monto','$valor','$Id_centro','$usuario','$tipo','CMA')  ";	
		}
		$res = mssqlConn::insert($query);
	} 	
}
// $query="SELECT *  FROM  Cuadre   WHERE fechA='$fecha' and usuario='$usuario' ";
// $res = mssqlConn::Listados($query);
// echo $res;