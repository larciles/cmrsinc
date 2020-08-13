<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$testdir =  __DIR__ ;
if ($testdir  == "http://localhost/cma/controllers/") {
  require_once '../db/mssqlconn.php';
}else{
   require_once '../db/mssqlconn.php';  
}

$dbmsql = mssqlConn::getConnection();
$type   = $_POST['type'];
$sd     = $_POST['sd'];
$ed     = $_POST['ed'];
//$rpt    = $_POST['rpt'];
//$pr     = $_POST['pro'];

$sd = str_replace('/','-',$sd);
$ed = str_replace('/','-',$ed);

if ($type =="G") {
   getProdSales($sd,$ed);
}

if ($type =="C") {
   getC($sd,$ed);
}

if ($type =="S") {
   getS($sd,$ed);
}

if ($type =="L") {
   getL($sd,$ed);
}

if ($type =="I") {
   getLI($sd,$ed);
}

 function getProdSales($sd,$ed){

  //TOTAL GENERAL 
  $query="Select  max('Total Ventas Generales') nombre,sum(con.subtotal) subtotal,sum(con.descuento) descuento, sum(con.subtotal) - sum(con.descuento) neto, sum(con.TotImpuesto) TotImpuesto,sum(con.monto_flete) envio , sum(general) total 
          FROM newconsol3 con 
          inner join  Divisiones E ON con.tipo =E.id
          inner join MDocumentos D ON con.doc= D.codtipodoc 
          where con.fechafac between '$sd' and '$ed' and statfact <>'2'";
  $G = mssqlConn::Listados($query);
  $G = json_decode($G, true);
  $lenG = sizeof($G);

  //TODAS LAS EMPRESAS
  $query = "Select  concat( max(E.Nombre),' ',MAX(D.abreviatura)) nombre,sum(con.subtotal) subtotal,sum(con.descuento) descuento, sum(con.subtotal) - sum(con.descuento) neto, sum(con.TotImpuesto) TotImpuesto,sum(con.monto_flete) envio , sum(general) total,con.tipo  
            FROM newconsol3 con 
            inner join Divisiones E ON con.tipo =E.id
            inner join MDocumentos D ON con.doc= D.codtipodoc 
            where con.fechafac between '$sd' and '$ed' and statfact <>'2' group by con.tipo,con.doc order by con.tipo";
  $resultE = mssqlConn::Listados($query);
  $objE = json_decode($resultE, true);
  $lenE = sizeof($objE);
 //TOTAL POR EMPRESAS
  $query="Select  max(concat('Total US$ ',' ',E.Nombre)) nombre,sum(con.subtotal) subtotal,sum(con.descuento) descuento, sum(con.subtotal) - sum(con.descuento) neto, sum(con.TotImpuesto) TotImpuesto,sum(con.monto_flete) envio , sum(general) total,con.tipo  
          FROM newconsol3 con 
          inner join  Divisiones E ON con.tipo =E.id
          inner join MDocumentos D ON con.doc= D.codtipodoc 
          where con.fechafac between '$sd' and '$ed' and statfact <>'2' group by con.tipo order by con.tipo";
  $resultT = mssqlConn::Listados($query);
  $objT = json_decode($resultT, true);
  $lenT = sizeof($objT);  

  $result="";
  if($lenT>0){
       for ($i=0; $i <$lenT ; $i++) { 
            $tipo=$objT[$i]['tipo'];
            $company     = $objT[$i]['nombre'] ;
            $subtotal    = $objT[$i]['subtotal'];
            $descuento   = $objT[$i]['descuento'];
            $neto        = $objT[$i]['neto'];
            $TotImpuesto = $objT[$i]['TotImpuesto'];
            $envio       = $objT[$i]['envio'] ;
            $total       = $objT[$i]['total'];

            $arrtotal =  $company     .','.
                         $subtotal    .','.
                         $descuento   .','.
                         $neto        .','.
                         $TotImpuesto .','.
                         $envio       .','.
                         $total ;
            if (substr($arrtotal,0,1)==",") {
               $arrtotal=substr($arrtotal,1);
            }

            for ($j=0; $j < $lenE ; $j++) { 
              if ( $objE[$j]['tipo']==$tipo) {
                   $arrtemp = $objE[$j]['nombre'].','.$objE[$j]['subtotal'].','. $objE[$j]['descuento'].',' .$objE[$j]['neto'].','. $objE[$j]['TotImpuesto'].',' .$objE[$j]['envio'].','.$objE[$j]['total'];

                   $result[]  = explode(",",$arrtemp); 
              }
             
            }

             $result[]  = explode(",",$arrtotal); 
         
       }

      $arrtotal = $G[0]['nombre'].','.$G[0]['subtotal'].','.$G[0]['descuento'].','.$G[0]['neto'].','.$G[0]['TotImpuesto'].',' .$G[0]['envio'].','.$G[0]['total'];
      $result[]  = explode(",",$arrtotal); 

  }
  
 echo stripslashes(json_encode($result)); 
 }


 function getS($sd,$ed){
  //MONTO DE SUEROS
  $result="";
  $query="SELECT  cod_subgrupo, monto  from View_GrupoConsultas where fechafac between  '$sd' and '$ed' and cod_subgrupo='SUEROTERAPIA' ";
  $s = mssqlConn::Listados($query);
  $s = json_decode($s, true);
  $lenS = sizeof($s);
  if ($lenS >0) {
     $result[] = $s[0] ;
  }

   
 echo stripslashes(json_encode($result));
 }




 function getC($sd,$ed){

   
  //MONTO DE CONSULTAS 
  $query="SELECT  cod_subgrupo, monto  from View_GrupoConsultas where fechafac between  '$sd' and '$ed' and cod_subgrupo='CONSULTA' ";
  $c = mssqlConn::Listados($query);
  $c = json_decode($c, true);
  $lenC = sizeof($c);
    if ($lenC >0) {
     $result[] = $c[0] ;
  }
  
 echo stripslashes(json_encode($result));
 }




 function getL($sd,$ed){
 
 //NUMERO DE LASER APLICADOS
 $query="SELECT max('Laser Aplicados ')  tit , sum(d.cantidad) laser from MSSMFact m inner join MSSDFact d ON m.numfactu=d.numfactu where m.statfact<>'2' and m.fechafac between '$sd' and '$ed' and d.coditems like 'TD%' ";
  $l = mssqlConn::Listados($query);
  $l = json_decode($l, true);
  $lenL = sizeof($l);

   if ($lenL >0) {
     $result[] = $l[0] ;
  }
   
 echo stripslashes(json_encode($result));
 }

  function getLI($sd,$ed){
 
 //NUMERO DE LASER INTRAVENOSOS VENDIDOS
 $query="SELECT max('Laser Intra. Vendidos ')  tit , sum(d.cantidad) laser from MSSMFact m inner join MSSDFact d ON m.numfactu=d.numfactu where m.statfact<>'2' and (m.fechafac between '$sd' and '$ed') and d.coditems like 'LI%' ";
  $l = mssqlConn::Listados($query);
  $l = json_decode($l, true);
  $lenL = sizeof($l);

   if ($lenL >0) {
     $result[] = $l[0] ;
  }
   
 echo stripslashes(json_encode($result));
 }