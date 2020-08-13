<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien	 = $_POST['codclien'];
$fecha 		 = $_POST['fecha'];
$citados 	 = $_POST['citados'];			
$fecha_cita  = $_POST['fecha_cita'];           
$codmedico   = $_POST['codmedico'];           
$usuario     = $_POST['usuario'];           
$fecreg      = $_POST['fecreg'];           
$observacion = $_POST['observacion'];           
$primera_control = $_POST['primera_control'];
$nocitados   = $_POST['nocitados'];
$confirmado  = $_POST['confirmado'];           
$citacontrol = $_POST['citacontrol'];
$servicios   = $_POST['servicios'];
$asistido    = $_POST['asistido'];           
$codconsulta = $_POST['codconsulta'];


//,hora
//ipaddress,
//workstation,
//,HoraRegistro
$query="insert into mconsultas  (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta) values ('$codclien','$fecha','$citados','$fecha_cita','$codmedico','$usuario','$fecreg','$observacion','$primera_control','$nocitados','$confirmado','$citacontrol'  ,'$servicios','$asistido','$codconsulta')" ;


$result = mssqlConn::insert($query);
echo $result;