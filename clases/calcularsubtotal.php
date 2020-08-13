<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

//$string = 

 $coditems  = ltrim(rtrim($_POST['coditems'])); //str_replace(' ', '', $_POST['coditems']);  
 $codtipre  = str_replace(' ', '', $_POST['codtipre']) ;
 $descuento = str_replace(' ', '', $_POST['descuento']) ;
 $cantidad  = str_replace(' ', '', $_POST['cantidad']) ;

 $precio    = str_replace(' ', '', $_POST['precio']) ;

 if ($descuento!=="") {
  
 }
$query="SELECT * from  MPrecios where coditems='$coditems' and  codtipre='$codtipre'";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);

if($len>0){

    $pu = $result[0]['precunit'];
	$isTax=esGravable( $coditems, $pu  );

	if ($isTax[0]['aplicaIva']=="1" ) {
		$percentage = calcularImpuesto();
		
		$pe = $percentage[0]['percentage'];
		
        $pre=(int)$precio;
        if ($pre>0) {
        	$pu=$precio;
        }
        $pu= number_format($pu, 2, '.', '');
      //  $pu=round($pu);
    	$response['precunit'] = $pu ;//$result[0]['precunit'];
    	$response['impuesto'] = (($pu*$cantidad)*$percentage[0]['percentage'])/100;		
     	$response['subtotal'] = $pu*$cantidad;        
    

 		$response['tax'] = $pe;
 		$response['taxapply'] = '1';
        $response['aplicadcto']=$isTax[0]['aplicadcto'];
        $response['aplicaComMed']=$isTax[0]['aplicaComMed'];
        $response['aplicaComTec']=$isTax[0]['aplicaComTec'];
        $response['costo']=$isTax[0]['costo'];      
		
	} else {
		$pre=(int)$precio;
	   if ($pre>0) {
        	$pu=$precio;
        } 
        $pu= number_format($pu, 2, '.', '');
		$response['precunit'] = $pu;//$result[0]['precunit'];
 		$response['subtotal'] = $pu*$cantidad; //$pu
 		$response['taxapply'] = '0';
	}
}else{

}

echo  json_encode($response);


function calcularImpuesto(){
	$query="SELECT sum(Porcentaje) percentage from  Impuestos where Activo =1 ";
	$res = mssqlConn::Listados($query);
	$result = json_decode($res, true);
	$len = sizeof($result);
	if($len>0){	
      
	}
  return $result;
}


function esGravable( $coditems,$precunit ){

$respon=false;
$query="SELECT * from  MInventario where coditems='$coditems' ";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);
if($len>0){	
	if ($result[0]['aplicaIva']=="1"){
	    $response['subtotal'] =  $precunit*$cantidad;// $result[0]['precunit']*$cantidad;
	    $respon=true;
	}
}
return $result;
}