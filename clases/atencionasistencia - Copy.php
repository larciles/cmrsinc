<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codconsulta = $_POST['codconsulta'];
$codclien = $_POST['codclien'];
$fecha_cita = $_POST['fecha_cita'];
$coditems = $_POST['coditems'];
$asistido = $_POST['asistido'];
$noAsistido = $_POST['noAsistido'];
$usuario = $_POST['usuario'];

if ($codconsulta=='07') {
	$query="SELECT * from mconsultasS where codclien='$codclien' and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems' ";
} else {
	$query="SELECT * from mconsultas  where codclien='$codclien' and fecha_cita='$fecha_cita' ";
}

 $result = mssqlConn::Listados($query);

 $haycitas = json_decode($result, true);
 $nin=sizeof($haycitas);
 if ($nin>0) {
	 if ($codconsulta=='07') {
		//$query="UPDATE mconsultasS  SET asistido = '$asistido', noasistido = '$noAsistido',regusuario = '$usuario' where codclien='$codclien' and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems' ";
		$query="UPDATE mconsultasS  SET asistido = '$asistido', noasistido = '$noAsistido' where codclien='$codclien' and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems' ";
	} else {
		//$query="UPDATE mconsultas   SET asistido = '$asistido', noasistido = '$noAsistido',regusuario = '$usuario' where codclien='$codclien' and fecha_cita='$fecha_cita' ";
		$query="UPDATE mconsultas   SET asistido = '$asistido', noasistido = '$noAsistido' where codclien='$codclien' and fecha_cita='$fecha_cita' ";
	}

	$result = mssqlConn::insert($query);                  
 }
echo $result;