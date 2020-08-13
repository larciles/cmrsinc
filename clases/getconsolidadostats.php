<?php
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require('../controllers/MFacturaController.php');



if (isset($_POST['avg'])) {

	$hoy= date('m/d/Y');
	if ($_POST['fi']==$hoy) {
		$result= getAVG();	
	}
  
}

echo( json_encode($result) );



function getAVG(){ 
 
		 $fecha_i = $_POST['fi'];
		 $fecha_f = $_POST['ff'];
		 $fecha_p = $_POST['avg'];	 
		 $mfacturaController = new MFacturaController();
        //promedio
		$query="SELECT AVG(inner_query.total) avg
		from 
		(
		SELECT  sum(general) As total
		            FROM consolidated_view con 
		            inner join MDocumentos D ON con.doc= D.codtipodoc 
		            where con.fechafac between '$fecha_p' and '$fecha_f' and statfact ='3' group by con.fechafac 
		) as inner_query ";
		  $avg=$mfacturaController->readUDF($query);

          //- venta del dia de la semana anterior
		  $day_last_week=date("Y-m-d",strtotime($fecha_i."- 1 week")); 
		  $query="SELECT  sum(general) As anterior
		            FROM consolidated_view con 
		            inner join MDocumentos D ON con.doc= D.codtipodoc 
		            where con.fechafac between '$day_last_week' and '$day_last_week' and statfact ='3' group by con.fechafac  ";

          $dlw=$mfacturaController->readUDF($query);

          if (is_null($dlw[0]['anterior']) || empty($dlw[0]['anterior'])) {
          	 $dlw[0]['anterior']=0;
          }


          //venta de este dia

		  $query="SELECT  sum(general) As hoy
		            FROM consolidated_view con 
		            inner join MDocumentos D ON con.doc= D.codtipodoc 
		            where con.fechafac between '$fecha_i' and '$fecha_f' and statfact ='3' group by con.fechafac  ";

          $hoy=$mfacturaController->readUDF($query);
 
   
         $porcentaje=   (($hoy[0]['hoy']/$dlw[0]['anterior'])-1)*100  ;
		 $icon='<span class="mas"><i class="fas fa-angle-double-up" style="color: green;"></i></span> ';
         if ($porcentaje<0) {
         	$icon='<span class="menos"><i class="fas fa-angle-double-down" style="color: red;"></i></span>';
         }
    
       //	'porcentaje'=>$icon.$porcentaje."%", 
         $porcentaje= number_format( $porcentaje, 2 );

          $tmp = array(
          	'hoy' => $hoy[0],
          	'avg'=>$avg[0],
          	'anterior'=>$dlw[0],
          	'porcentaje'=>$icon.$porcentaje."%", 

          );  
		  $res[]=$tmp;


		return $res;  
}


 

