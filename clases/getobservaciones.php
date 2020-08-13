<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id = $_POST['id'];
$codconsulta = $_POST['codconsulta'];
$fechacita = $_POST['fechacita'];
$tipo = $_POST['tipo'];

if ($tipo=='1') {
	if ($codconsulta!='07') {
	 
		$query="Select max(convert(varchar(10), cast(fecha_cita as date), 111)  ) fecha_cita  from  VIEW_mconsultas_02 where Cedula='$id' and codconsulta<>'07' group by codclien";
 		$result = mssqlConn::Listados($query);
		$o = json_decode($result, true);
		$lenO = sizeof($o);
		if ($lenO>0) {
			$fecha_cita=$o[0]['fecha_cita'];

			$query="Select observacion from VIEW_mconsultas_02 where Cedula='$id'  and fecha_cita='$fecha_cita' and codconsulta<>'07'";
		}

	}else{

		$query="Select max(convert(varchar(10), cast(fecha_cita as date), 111)  ) fecha_cita  from  VIEW_mconsultas_02 where Cedula='$id' and codconsulta='07' group by codclien";
 		$result = mssqlConn::Listados($query);
		$o = json_decode($result, true);
		$lenO = sizeof($o);
		if ($lenO>0) {
			$fecha_cita=$o[0]['fecha_cita'];

			$query="Select observacion from VIEW_mconsultas_02 where Cedula='$id'  and fecha_cita='$fecha_cita' and codconsulta='07'";
		}
	}

}else{
	
	if ($codconsulta!='07') {
	 
		$query="Select max(convert(varchar(10), cast(fecha_cita as date), 111)  ) fecha_cita  from  VIEW_mconsultas_02 where codclien='$id' and codconsulta<>'07' group by codclien";
 		$result = mssqlConn::Listados($query);
		$o = json_decode($result, true);
		$lenO = sizeof($o);
		if ($lenO>0) {
			$fecha_cita=$o[0]['fecha_cita'];

			$query="Select observacion from VIEW_mconsultas_02 where codclien='$id'  and fecha_cita='$fecha_cita' and codconsulta<>'07'";
		}

	}else{

		$query="Select max(convert(varchar(10), cast(fecha_cita as date), 111)  ) fecha_cita  from  VIEW_mconsultas_02 where codclien='$id' and codconsulta='07' group by codclien";
 		$result = mssqlConn::Listados($query);
		$o = json_decode($result, true);
		$lenO = sizeof($o);
		if ($lenO>0) {
			$fecha_cita=$o[0]['fecha_cita'];

			$query="Select observacion from VIEW_mconsultas_02 where codclien='$id'  and fecha_cita='$fecha_cita' and codconsulta='07'";
		}
	}
}

$result = mssqlConn::Listados($query);
echo $result;