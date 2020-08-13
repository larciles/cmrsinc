<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codconsulta = $_POST['codconsulta'];
$codclien    = $_POST['codclien'];
$coditems    = $_POST['coditems'];
$prioridad    = $_POST['prioridad'];


  date_default_timezone_set("America/Puerto_Rico");
  $fecha_cita =date("Y-m-d");
  

$clcod = explode("-",$codclien );
$codclien =$clcod[0];
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
        $query="UPDATE mconsultasS SET prioridad = '$prioridad' where codclien='$codclien' and fecha_cita='$fecha_cita' and CODITEMS ='$coditems' ";
	} else {
        $query="UPDATE mconsultas  SET prioridad = '$prioridad' where codclien='$codclien' and fecha_cita='$fecha_cita' ";
	}

	$result = mssqlConn::insert($query);                  
 }
echo $result;