<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha  = $_POST['fecha_cita'];
$fecha2  = $_POST['fecha_cita2'];
$usuario= $_POST['usuario'];
$codperfil=$_SESSION['controlcita'];  //NO ES codperfil ES controlcita


    $query="SELECT "
             ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast(a.UltimaAsistida as date), 101)   UltimaAsistida, a.observacion, a.fallecido "
             ."FROM "
             ."VIEW_repconsultas4 a "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados=1 and a.usuario='$usuario' and a.fallecido<>'1'  and a.inactivo<>'1'  order by Medicos";


if ($codperfil=='1') {
	$query="SELECT "
        	 ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast(a.UltimaAsistida as date), 101)   UltimaAsistida, a.observacion, a.fallecido "
             ."FROM "
             ."VIEW_repconsultas4 a "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados=1 and a.usuario='$usuario' and a.fallecido<>'1'  and a.inactivo<>'1'  order by Medicos";

} else {
	$query="SELECT "
        	 ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast(a.UltimaAsistida as date), 101)   UltimaAsistida, a.observacion, a.fallecido "
             ."FROM "
             ."VIEW_repconsultas4 a "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados=1  and a.fallecido<>'1'  and a.inactivo<>'1'  order by Medicos";
}


$result = mssqlConn::Listados($query);
echo $result;