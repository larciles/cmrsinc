<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fecha_cita'];


$query=" Select Medico,COUNT(*) nAsistidos from VIEW_mconsultas_02 where  fecha_cita='$fecha_cita' and codmedico<>'000' and ASISTIDOS='Asistido' and codconsulta<>'07' group by Medico ";


$result = mssqlConn::Listados($query);
echo $result;