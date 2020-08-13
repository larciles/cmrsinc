<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha  = $_POST['fecha_cita'];
$fecha2  = $_POST['fecha_cita2'];
$usuario= $_POST['usuario'];
$codperfil=$_POST['codperfil']; //NO ES codperfil ES controlcita
$codperfil=$_SESSION['controlcita'];//$_GET['codperfil'];  

    $query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and b.usuario='$usuario'  and  b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 



if ($codperfil=='1') {
	$query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and b.usuario='$usuario'  and  b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 

} else {
	$query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and  b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 

}


  


$result = mssqlConn::Listados($query);
echo $result;