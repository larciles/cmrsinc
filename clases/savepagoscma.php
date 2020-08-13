<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$payTypeArray['vs']= array( 'codforpa'=> '4', 'codtipotargeta' => '02');
$payTypeArray['mc']= array( 'codforpa'=> '4', 'codtipotargeta' => '01');
$payTypeArray['am']= array( 'codforpa'=> '4', 'codtipotargeta' => '03');
$payTypeArray['dc']= array( 'codforpa'=> '4', 'codtipotargeta' => '04');
$payTypeArray['ck']= array( 'codforpa'=> '4', 'codtipotargeta' => '05');

$payArray = array();

$cash  = $_POST['cash'];
$tdc1  = $_POST['tdc1'];
$tdc2  = $_POST['tdc2'];
$tdc3  = $_POST['tdc3'];
$ath   = $_POST['ath'];
$check = $_POST['check'];
$pagototal = $_POST['pagototal']; 
$saldo = $_POST['saldo'];
$dueamount = $_POST['dueamount'];

$cambio = $_POST['cambio'];
$radio1 = $_POST['radio1'];
$radio2 = $_POST['radio2'];
$radio3 = $_POST['radio3'];

$nota="n/a";
if (isset($_POST['nota'])) {
	$nota = $_POST['nota'];	
}


$fecha = $_POST['fecha'];
$invoicen = $_POST['invoicen'];
$idusr = $_POST['idusr'];
$id_centro = $_POST['id_centro'];
$tipo_doc = $_POST['tipo_doc'];
$por= $_POST['por'];

//nuevo
$idcard1="";
$idcard2="";
$idcard3="";
if (isset($_POST['idcard1'])) {
 	$idcard1=	$_POST['idcard1'];
}
if (isset($_POST['idcard2'])) {
 	$idcard2=$_POST['idcard2'];
 } 
if (isset($_POST['idcard3'])) {
	$idcard3=$_POST['idcard3'];
}
//f nuevo

$workstation="";
if (isset($_POST['workstation'])) {
	$workstation=$_POST['workstation'];
}



if ($cash!=="") {
	// efectivo = 1 / monto / 00
	$payArray[]= array('codforpa' => '1', 'codtipotargeta' => '00','monto'=> $cash,'idcard'=>'');
}

if ($ath!=="") {
	//  ath      = 3 / monto / 06
	$payArray[]= array('codforpa' => '3', 'codtipotargeta' => '06','monto'=> $ath,'idcard'=>'');
}

if ($check!=="") {
	// cheque   = 2 / monto / 09
	$payArray[]= array('codforpa' => '2', 'codtipotargeta' => '09','monto'=> $check,'idcard'=>'');
}

if ($cambio!=="") {
	// vuelto   = 1 /  monto* -1 / 00
	$payArray[]= array('codforpa' => '1', 'codtipotargeta' => '00','monto'=> $cambio* -1,'idcard'=>'');
}


if ($tdc1 !="") {
	$xres = explode("-",$radio1);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc1,'idcard'=>$idcard1);
} 

if ($tdc2 !="") {
	$xres = explode("-",$radio2);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc2,'idcard'=>$idcard2);
} 

if ($tdc3 !="") {
	$xres = explode("-",$radio3);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc3,'idcard'=>$idcard3);
} 



$query="DELETE  from mpagos where numfactu='$invoicen' and id_centro = '$id_centro' and tipo_doc = '$tipo_doc'  ";
mssqlConn::insert($query);  

$query="DELETE  from idcard where factura='$invoicen' and idcompany = '$id_centro' and tipo_doc = '$tipo_doc'  ";
mssqlConn::insert($query);  

$query="UPDATE cma_mfactura  set  statfact='1' where numfactu='$invoicen'";
mssqlConn::insert($query); 


	foreach ($payArray as $key ) {
	  $hora =  date("H:i:s", time());
	  $codforpa =	$key['codforpa'];
	  $codtipotargeta =	$key['codtipotargeta'];
	  $monto =	$key['monto'];
	  $cnumber=	$key['idcard'];
	  $valMonto =  (int) $monto;
     // if ($valMonto>0) {
      	if ($tipo_doc=='04') {
      		$valMonto=floatval($monto)*-1;
      	}else{
      	    $valMonto =  floatval($monto) ;	
      	}
         
      
        if( (float)$monto!=0){
           $query="INSERT Into MPagos (numfactu,fechapago,codforpa,monto,usuario,fecreg,horareg,id_centro,codtipotargeta,tipo_doc,idempresa,workstation,cnumber,por)  Values ('$invoicen','$fecha','$codforpa','$valMonto','$idusr','$fecha','$hora','$id_centro','$codtipotargeta','$tipo_doc','$id_centro','$workstation','$cnumber','$por' )";
           mssqlConn::insert($query);  	

           $query="UPDATE cma_mfactura  set monto_abonado = Case When monto_abonado >0  then monto_abonado-$valMonto  When monto_abonado=0 then 0  end ,cancelado='0', statfact='1' where numfactu='$invoicen'";
          mssqlConn::insert($query); 

          try {
          	if ($cnumber!=='') {
          		$query="INSERT Into idcard (cnumber,factura,fecha,codforpa,monto,usuario,hora,idcompany,tipotarjeta,tipo_doc) Values ('$cnumber','$invoicen','$fecha','$codforpa','$valMonto','$idusr','$hora','$id_centro','$codtipotargeta','$tipo_doc' )";
           		mssqlConn::insert($query);  		
          	}
          	
          } catch (Exception $e) {
          	
          }
           
        }
     // }
       
	} 

if ($id_centro =='1') {
   $query="UPDATE mfactura set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3' where numfactu='$invoicen'"; 	
}else if ($id_centro =='2') {
  
    if ($saldo<=0) {
      $query="UPDATE cma_mfactura set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3',nota='$nota' where numfactu='$invoicen'";    
    }  
   

}else if ($id_centro =='3' && $tipo_doc=='04') {
   $query="UPDATE MSSMDev set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3' where numfactu='$invoicen'"; 
}else if ($id_centro =='3' && $tipo_doc=='04') {
   $query="UPDATE MSSMFact set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3' where numfactu='$invoicen'"; 
}else if ($id_centro =='3' && $tipo_doc=='01') {
   $query="UPDATE MSSMFact set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3' where numfactu='$invoicen'"; 
}
 
 mssqlConn::insert($query);  

// cheque   = 2 / monto / 09
// ath      = 3 / monto / 06
// master   = 4 / monto / 01
// visa     = 4 / monto / 02
// amex     = 4 / monto / 03
// discover = 4 / monto / 04
// chec x   = 4 / monto / 05
// deposito = 8 /       / 10
// vuelto   = 1 /  monto* -1 / 00
echo true;
?>

