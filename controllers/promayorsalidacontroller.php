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
$medico = $_POST['medico'];
//$pr     = $_POST['pro'];

$sd = str_replace('/','-',$sd);
$ed = str_replace('/','-',$ed);

if ($type =="1") {
   getProdSales($sd,$ed,$medico);
}

if ($type =="2") {
   getFormulasDesc($sd,$ed,$medico);
}

if ($type =="S") {
   getS($sd,$ed);
}

if ($type =="L") {
   getL($sd,$ed);
}
 function getProdSales($sd,$ed,$medico){

  //TOTAL POR PRODUCTO 
  


  $query="Select sum(cantidad) qty, desitems  FROM VIEW_Ventas_Medicos_W_Ret  where codmedico='$medico' and fechafac between '$sd' and '$ed' and  Prod_serv='p'   group by desitems   order by qty  desc";
  $P = mssqlConn::Listados($query);
  $P = json_decode($P, true);
  $lenP = sizeof($P);
  
  //TOTAL GENERAL
  $query="Select sum(cantidad) qty FROM VIEW_Ventas_Medicos_W_Ret  where codmedico='$medico' and fechafac between '$sd' and '$ed' and  Prod_serv='p'";
  $G = mssqlConn::Listados($query);
  $G = json_decode($G,true);
  $lenG = sizeof($G);

  for ($i=0; $i <$lenP ; $i++) { 
  	
  	 $qty 	   = $P[$i]['qty'];
     $desitems = $P[$i]['desitems'];

     $arrtotal =  $desitems.','.
                  $qty ;    
    
     $result[]  = explode(",",$arrtotal); 
  }
  
  $qty 	   = $G[0]['qty'];
  $total   = $G[0]['total'];

  $arrtotal =  $total.','.
               $qty;                  
  $result[]  = explode(",",$arrtotal); 
 echo stripslashes(json_encode( $result )); 
 }


 function getFormulasDesc($sd,$ed,$medico){
  //MONTO DE SUEROS
 // $result="";
  $query="  Select f.desitems, isnull(( select sum(cantidad) qty FROM VIEW_Ventas_Medicos_W_Ret vnts where vnts.codmedico='$medico' and vnts.fechafac between '$sd' and '$ed' AND vnts.coditems= f.coditems group by vnts.desitems ),0) qty from  view_ventas_formulas_Selected f order by qty desc";
  $s = mssqlConn::Listados($query);
  $s = json_decode($s, true);
  $lenS = sizeof($s);

  $query=" Select sum(vnts.cantidad) qty , Max('Total') texto from  view_ventas_formulas_Selected f inner join VIEW_Ventas_Medicos_W_Ret vnts on f.coditems = vnts.coditems where vnts.codmedico='$medico' and vnts.fechafac between '$sd' and '$ed' ";
  $t = mssqlConn::Listados($query);
  $t = json_decode($t, true);
  $lenT = sizeof($t);

  if ($lenS >0) {
      for ($i=0; $i <$lenS ; $i++) { 
           $qty      = $s[$i]['qty'];
           $desitems = $s[$i]['desitems'];

           $arrtotal =  $desitems.','.
                        $qty ;    
          
           $result[]  = explode(",",$arrtotal); 
     }
  }

 if ($lenT >0) {
    $qty     = $t[0]['qty'];
    $texto   = $t[0]['texto'];

    $arrtotal =  $texto.','.
                 $qty;                  
    $result[] = explode(",",$arrtotal); 
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
 $query="SELECT max('Laser Aplicados ')  tit , sum(d.cantidad) laser from MSSMFact m inner join MSSDFact d ON m.numfactu=d.numfactu where m.statfact<>'2' and m.fechafac between '$sd' and '$ed' ";
  $l = mssqlConn::Listados($query);
  $l = json_decode($l, true);
  $lenL = sizeof($l);

   if ($lenL >0) {
     $result[] = $l[0] ;
  }
   
 echo stripslashes(json_encode($result));
 }