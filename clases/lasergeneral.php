<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fechai'];

$query="SELECT e.codclien,sum(e.terapias) terapias  "
."from VIEW_mconsultas_02 e "
."where e.fecha_cita >='$fecha_cita' and e.codconsulta='07' and e.ASISTIDOS='Asistido' and e.coditems like 'TD%' and e.terapias is not null  group by e.codclien  ";
$res = mssqlConn::Listados($query);

$obj= json_decode($res, true);
$lenObj = sizeof($obj);

if ($lenObj>0) {
	for ($i=0; $i < $lenObj; $i++) { 
        $key =$obj[$i]['codclien'];             
        $value=$obj[$i]['terapias'];
		$arr[] = array( $key =>$value );
	}
	
}

echo $res;

