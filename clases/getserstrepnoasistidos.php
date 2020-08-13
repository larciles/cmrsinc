<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha  = $_POST['fecha_cita'];
$fecha2 = $_POST['fecha_cita2'];
$usuario= $_POST['usuario'];

$query="SELECT a.codclien, 
               a.nombres, 
               a.telfhabit, 
               a.Medicos, 
               a.Historia, 
               a.asistido, 
               convert(varchar(10), cast(a.fecha_cita as date), 101) fecha_cita, 
               a.observacion, 
               a.coditems FROM  viewSSNoasistidos a Where (a.fecha_cita  between  '$fecha' and '$fecha2' ) and a.asistido=0 and   a.coditems like '%ST' ";

$result = mssqlConn::Listados($query);
echo $result;