<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fecha_cita'];
$codclien   = $_POST['codclien'];

$query="SELECT e.codclien,sum(e.terapias) terapias  "
."from VIEW_mconsultas_02 e "
."where e.fecha_cita >='$fecha_cita' and e.codconsulta='07' and e.ASISTIDOS='Asistido' and e.coditems like 'TD%' and e.terapias is not null and  e.codclien = '$codclien' group by e.codclien  ";
$medicos = mssqlConn::Listados($query);
echo $medicos;

