<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST['fechai'];
$fechaf = $_POST['fechaf'];

//LASER VENDIDOS
$query="SELECT sum(a.cantidad) vendidos  FROM view_Laser_00 a  inner join MClientes d on a.codclien=d.codclien where a.fechafac  between '$fechai' and '$fechaf'    ";
$res = mssqlConn::Listados($query);

$obj= json_decode($res, true);
$lenObj = sizeof($obj);

$laser_vendidos=0;
$laser_aplicados=0;
$laser_restantes=0;

if ($lenObj>0) {
	for ($i=0; $i < $lenObj; $i++) { 
        $key ='vendidos';             
        $value=$obj[$i]['vendidos'];
		$arr[] = array( $key =>$value );
		$laser_vendidos=$value;
	}	
}else{
	    $key ='vendidos';             
        $value='0';		
}


//LASER APLICADOS

$query="SELECT sum(e.terapias) aplicados from VIEW_mconsultas_02 e where e.fecha_cita >='$fechai' and e.codconsulta='07' and e.ASISTIDOS='Asistido' and e.coditems like 'TD%' and e.terapias is not null ";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);

if ($lenObj>0) {
	for ($i=0; $i < $lenObj; $i++) { 
        $key ='aplicados';             
        $value=$obj[$i]['aplicados'];
		$arr[] = array( $key =>$value );
		$laser_aplicados =$value;
	}	
}else{
	    $key ='aplicados';             
        $value='0';
		$arr[] = array( $key =>$value );		
}

//LASER RESTANTES

		$laser_restantes=$laser_vendidos-$laser_aplicados;

		$key ='restantes';             
        $value=$laser_restantes;
		$arr[] = array( $key =>$value );
		

  $res = json_encode($arr);

echo $res;