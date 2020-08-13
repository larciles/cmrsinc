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
             ."a.nombres, a.telfhabit,concat( b.nombre,' ', b.apellido) Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora,  ''  UltimaAsistida, a.observacion  "
             ."FROM "
             ."VIEW_mconsultas_02 a  inner join Mmedicos b On a.codmedico=b.Codmedico "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados='Citado' and a.usuario='$usuario' and a.descons='CEL MADRE'  order by Medicos";


if ($codperfil=='1') {
	$query="SELECT "
        	 ."a.nombres, a.telfhabit,concat( b.nombre,' ', b.apellido) Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora,  convert(varchar(10), cast( (SELECT TOP (1) PERCENT  MAX(c.fecha_cita) AS UltimaAsistida FROM dbo.Mconsultass c WHERE (c.asistido = 3 and c.coditems='CMKCINTRON')  and c.codclien=a.codclien GROUP BY c.codclien) as date), 101)   UltimaAsistida, a.observacion  "
             ."FROM "
             ."VIEW_mconsultas_02 a   inner join Mmedicos b On a.codmedico=b.Codmedico"
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados='Citado' and a.usuario='$usuario' and a.descons='CEL MADRE'    order by Medicos";

} else {
	$query="SELECT "
        	 ."a.nombres, a.telfhabit,concat( b.nombre,' ', b.apellido) Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast( (SELECT TOP (1) PERCENT  MAX(c.fecha_cita) AS UltimaAsistida FROM dbo.Mconsultass c WHERE (c.asistido = 3 and c.coditems='CMKCINTRON')  and c.codclien=a.codclien GROUP BY c.codclien) as date), 101)   UltimaAsistida, a.observacion "
             ."FROM "
             ."VIEW_mconsultas_02 a  inner join Mmedicos b On a.codmedico=b.Codmedico "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados='Citado'  and a.descons='CEL MADRE' order by Medicos";
}


$result = mssqlConn::Listados($query);
echo $result;