<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_i = $_POST['fi'];
$fecha_f = $_POST['ff'];

$a_hora=["7","8","9","10","11","12","1","2","3","4","5","6"];

$a_empresas=["HORA","CONSULTAS","SUERO TERAPIA","EXOSOMAS","PRODUCTOS","LASER"];

#CONSULTA	
$query=" SELECT SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) t,max(Id), sum(total) total, count(*) xhora
   from  cma_MFactura
   where fechafac='$fecha_i' and ts is not null and statfact=3 and numfactu in(select numfactu from cma_DFactura where fechafac ='$fecha_i' and cod_subgrupo='CONSULTA' group  by numfactu )
   group by SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) 
   order by max(Id) ";

$consultas= json_decode( respuesta($query) );

#SUERO TERAPIA
$query=" SELECT SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) t,max(Id), sum(total) total, count(*) xhora
  from  cma_MFactura
  where fechafac='$fecha_i' and ts is not null and statfact=3 and numfactu in(select numfactu from cma_DFactura where fechafac ='$fecha_i' and cod_subgrupo='SUEROTERAPIA' group  by numfactu )
  group by SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) 
  order by max(Id) ";

$suero= json_decode( respuesta($query) );

#CELULAS MADRE
$query=" SELECT  SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) t,max(Id), sum(total) total, count(*) xhora
  from  cma_MFactura
  where fechafac='$fecha_i' and ts is not null and statfact=3 and numfactu in(select numfactu from cma_DFactura where fechafac ='$fecha_i' and cod_subgrupo='CEL MADRE' group  by numfactu )
  group by SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) 
  order by max(Id)";

$celulas=json_decode( respuesta($query) );

#PRODUCTOS
$query=" SELECT SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) t,max(Id), sum(total) total
   from  MFactura
   where fechafac='$fecha_i' and ts is not null and statfact=3
   group by SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) 
   order by max(Id)";
$productos=json_decode( respuesta($query) );

#LASER
$query=" SELECT SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) t,max(Id), sum(total) total
   from  MSSMFact
   where fechafac='$fecha_i' and ts is not null and statfact=3
   group by SUBSTRING( RIGHT(CONVERT(VARCHAR, ts, 100),7),1, CHARINDEX(':', RIGHT(CONVERT(VARCHAR, ts, 100),7) )-1 ) 
   order by max(Id)";

$laser=json_decode( respuesta($query) );



$a_main = array();


#PRIMER ELEMENTO DEL ARRAY DONDE VAN LOS TTULOS (HORAS Y EMPRESAS)
array_push($a_main, $a_empresas); 

#DATO QUE COMPLETAN EL ARRAY PAR LA GRAFICA
$l=sizeof($a_hora);
$l_m=false;
$ampm="am";
for ($i=0; $i <$l ; $i++) { 
    $a_temp = array(); 
    $t_consultas = getVenta($consultas,$a_hora[$i]);
    $t_suero     = getVenta($suero,$a_hora[$i]);
    $t_celulas   = getVenta($celulas,$a_hora[$i]);
    $t_productos = getVenta($productos,$a_hora[$i]);
    $t_laser     = getVenta($laser,$a_hora[$i]);

    if ($l_m==true) {
       $ampm="pm";
    }
        
    if ($a_hora[$i]==12) {     
        $ampm="m";
        $l_m=true;       
    }
    

    $a_temp = array($a_hora[$i].$ampm, $t_consultas[0],$t_suero[0],$t_celulas[0],$t_productos[0],$t_laser[0] );
	  array_push($a_main,$a_temp);
}
   
 echo json_encode( $a_main ) ;




 function respuesta($query){
   $res = mssqlConn::Listados($query);
   return $res;
 }

 function getVenta($arr,$hora){
 	$len=sizeof($arr);
 	$res=0;
  $pos-1;
 	for ($i=0; $i < $len ; $i++) { 
            $x=$arr[$i]->t;
 	if ($hora==$arr[$i]->t) {
      $res= floatval( $arr[$i]->total ) ;
      $pos=$i;
 			break;
 		}
 	}
  $a_tmp = array( $res,$pos );
  return $a_tmp;
 }