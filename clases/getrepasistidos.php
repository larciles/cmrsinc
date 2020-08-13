<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha  = $_POST['fecha_cita'];
$fecha2  = $_POST['fecha_cita2'];
$usuario= $_POST['usuario'];
$codperfil=$_POST['codperfil'];  //NO ES codperfil ES controlcita
$codperfil=$_SESSION['controlcita'];//$_GET['codperfil'];  


       $query="SELECT "
       ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.fecha_cita, a.activa, a.asistido, a.usuario, convert(varchar(10), cast(a.ProximaCita as date), 101)  ProximaCita, a.observacion, "
       ."b.NumeroCitas "
       ."FROM "
       ."VIEW_repconsultas3 a "
       ."INNER JOIN VIEW_repconsultasNCitas b  ON  a.codclien = b.codclien "
       ."Where "
       ."(a.fecha_cita between '$fecha' and '$fecha2') and a.asistido=3 and a.activa<>'0' and a.usuario='$usuario' ";

if ($codperfil=='1') {
	$query="SELECT "
       ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.fecha_cita, a.activa, a.asistido, a.usuario, convert(varchar(10), cast(a.ProximaCita as date), 101)  ProximaCita, a.observacion, "
       ."b.NumeroCitas "
       ."FROM "
       ."VIEW_repconsultas3 a "
       ."INNER JOIN VIEW_repconsultasNCitas b  ON  a.codclien = b.codclien "
       ."Where "
       ."(a.fecha_cita between '$fecha' and '$fecha2') and a.asistido=3 and a.activa<>'0' and a.usuario='$usuario' ";
} else {
	$query="SELECT "
       ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.fecha_cita, a.activa, a.asistido, a.usuario, convert(varchar(10), cast(a.ProximaCita as date), 101)  ProximaCita, a.observacion, "
       ."b.NumeroCitas "
       ."FROM "
       ."VIEW_repconsultas3 a "
       ."INNER JOIN VIEW_repconsultasNCitas b  ON  a.codclien = b.codclien "
       ."Where "
       ."(a.fecha_cita between '$fecha' and '$fecha2') and a.asistido=3 and a.activa<>'0' ";
}




$result = mssqlConn::Listados($query);
echo $result;