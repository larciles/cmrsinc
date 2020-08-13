<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];
$view  = $_POST['view'];
$fecha2 = $_POST['fecha2'];
$laser_type = $_POST['laser_type'];

$horai = $_POST['horai'];
$horaf = $_POST['horaf'];


if ($view=='day') {

	$camposConsulta = "cedula,nombres,CONVERT(varchar,fecha_cita,120 ) AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems,fecha_cita as FOrder,codconfirm,horain,horaout,id,llegada,terapias,mls,hilt,endtime,enddate";
	$query          = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where fecha_cita='$fecha' and activa='1' and (   coditems like 'TD%' and   codconsulta  in ('07') )  and $laser_type >0 "; 	

	if ($horai!=null) {
		$query          = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where fecha_cita='$fecha' and activa='1' and (   coditems like 'TD%' and   codconsulta  in ('07') )  and $laser_type >0   and hora between '$horai' and '$horaf'"; 	
	}


}else if ($view=='month') {
	$query  = "SELECT fecha_cita start, COUNT(*) title from VIEW_mconsultas_02 where fecha_cita between '$fecha' and '$fecha2' and activa='1' and (   coditems like 'TD%' and   codconsulta  in ('07') ) and $laser_type >0   group by fecha_cita";

}

$result = mssqlConn::Listados($query);
echo $result;