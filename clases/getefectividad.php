<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
$sd     = $_POST['sd'];
$ed     = $_POST['ed'];
$efctype= $_POST['efctype'];
$order  = $_POST['order'];

if ($efctype=='1') {


$query="    SELECT COUNT( distinct P.codclien ) vistos,P.codmedico,
    (
    select COUNT( distinct s.codclien ) compras  from view_percentstats s where s.fechafac between '$sd' and '$ed' and s.codmedico=P.codmedico
     AND s.codclien in (
        select distinct codclien  from Mconsultas where fecha_cita between '$sd' and '$ed'and asistido=3   and codmedico= s.codmedico 
    )
    
    )  compras ,
    max( concat(m.nombre+' ',m.apellido)) medico
    from Mconsultas P
    inner join Mmedicos m On m.Codmedico=P.codmedico
    where P.fecha_cita between '$sd' and '$ed' and asistido=3 
    GROUP BY P.codmedico order by compras desc";



}else{


    $query="SELECT COUNT( distinct P.codclien ) vistos,P.codmedico,
    (
    select COUNT( distinct s.codclien ) compras  from view_percentstats s where s.fechafac between '$sd' and '$ed' and s.codmedico=P.codmedico
     AND s.codclien in (
        select distinct codclien  from Mconsultas where fecha_cita between '$sd' and '$ed'and asistido=3   and codmedico= s.codmedico 
    )
    
    )  compras ,
    max( concat(m.nombre+' ',m.apellido)) medico,
    CAST ((
    select COUNT( distinct s.codclien ) compras  from view_percentstats s where s.fechafac between '$sd' and '$ed' and s.codmedico=P.codmedico
     AND s.codclien in (
        select distinct codclien  from Mconsultas where fecha_cita between '$sd' and '$ed'and asistido=3   and codmedico= s.codmedico 
    )
    
    ) AS decimal )/COUNT( distinct P.codclien )  efectividad
    from Mconsultas P
    inner join Mmedicos m On m.Codmedico=P.codmedico
    where P.fecha_cita between '$sd' and '$ed' and asistido=3 
    GROUP BY P.codmedico order by $order desc";
}


$resultm = mssqlConn::Listados($query);
$objmed = json_decode($resultm, true);
$lenefe = sizeof($objmed);

$header="Medicos,Vistos,Compraron,Efectividad";
//$array_efec="";
   
$array_efec[]  = explode(",",$header);  // PRIMERA LINEA DEL ARRAY

if ($efctype=='1') {
    for ($j=0; $j <$lenefe ; $j++) { 
        $vistos=$objmed[$j]['vistos'];
        $compras=$objmed[$j]['compras'];
        $medico=$objmed[$j]['medico'];

        $efectividad=((int)$compras/(int)$vistos)*100;
        $efecstr=(string) number_format((float)$efectividad, 2,'.',',');

        $record =$medico.','.$vistos.','.$compras.','.$efecstr;
        $array_efec[]  = explode(",",$record);  // RESTO DE LAS LINEAS DEL ARRAY
 
	}        	# code...
}else{
	for ($j=0; $j <$lenefe ; $j++) { 
        $vistos=$objmed[$j]['vistos'];
        $compras=$objmed[$j]['compras'];
        $medico=$objmed[$j]['medico'];        
        $efectividad=$objmed[$j]['efectividad']*100;        
        $efecstr=(string) number_format((float)$efectividad, 2,'.',',');

        $record =$medico.','.$vistos.','.$compras.','.$efecstr;
        $array_efec[]  = explode(",",$record);  // RESTO DE LAS LINEAS DEL ARRAY
 
}
}            


echo stripslashes(json_encode($array_efec)); 