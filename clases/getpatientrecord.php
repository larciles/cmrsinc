<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$codclien = $_POST['codclien'];

 $query="SELECT cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), '', '-') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder  from VIEW_mconsultas_02  where codclien='$codclien'  and activa='1' order by forder desc  ";
$result = mssqlConn::Listados($query);

echo $result;