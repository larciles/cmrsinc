<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
}

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

$fecha = $_POST['fecha'];
$invoicen = $_POST['invoicen'];
$idusr = $_POST['idusr'];
$id_centro = $_POST['id_centro'];
$tipo_doc = $_POST['tipo_doc'];



if ($cash!=="") {
	// efectivo = 1 / monto / 00
	$payArray[]= array('codforpa' => '1', 'codtipotargeta' => '00','monto'=> $cash);
}

if ($ath!=="") {
	//  ath      = 3 / monto / 06
	$payArray[]= array('codforpa' => '3', 'codtipotargeta' => '06','monto'=> $ath);
}

if ($check!=="") {
	// cheque   = 2 / monto / 09
	$payArray[]= array('codforpa' => '2', 'codtipotargeta' => '09','monto'=> $check);
}

if ($cambio!=="") {
	// vuelto   = 1 /  monto* -1 / 00
	$payArray[]= array('codforpa' => '1', 'codtipotargeta' => '00','monto'=> $cambio* -1);
}


if ($tdc1 !="") {
	$xres = explode("-",$radio1);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc1);
} 

if ($tdc2 !="") {
	$xres = explode("-",$radio2);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc2);
} 

if ($tdc3 !="") {
	$xres = explode("-",$radio3);
	$res2= substr($xres[0],0,2);
	$payArray[]= array('codforpa' => $payTypeArray[$res2]['codforpa'], 'codtipotargeta' => $payTypeArray[$res2]['codtipotargeta'],'monto'=> $tdc3);
} 

$fecha = date("Y-m-d");


$query="DELETE  from mpagos where numfactu='$invoicen' and id_centro = '$id_centro' and tipo_doc = '$tipo_doc'  ";
mssqlConn::insert($query);  
      
	foreach ($payArray as $key ) {
	  $hora =  date("H:i:s", time());
	  $codforpa =	$key['codforpa'];
	  $codtipotargeta =	$key['codtipotargeta'];
	  $monto =	$key['monto'];

      if($id_centro=='2' && $tipo_doc =='04'){	  
      	$monto =	$key['monto']*-1;
      }
       if($monto!==0){
          $query="INSERT Into MPagos (numfactu,fechapago,codforpa,monto,usuario,fecreg,horareg,id_centro,codtipotargeta,tipo_doc,idempresa,workstation,ipaddress)  Values ('$invoicen','$fecha','$codforpa','$monto','$idusr','$fecha','$hora','$id_centro','$codtipotargeta','$tipo_doc','$id_centro','$workstation','$ipaddress')";
          mssqlConn::insert($query);  		   
	   }
	} 

if($id_centro=='2' && $tipo_doc ='04'){
	$query="UPDATE CMA_Mnotacredito set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statnc='3' where numnotcre='$invoicen'";
         mssqlConn::insert($query); 
}



// "update " & tblMSSMDev & " set monto_abonado=(CONVERT(MONEY,'" & TtalPag & "')),cancelado='1', statnc ='3' where numnotcre ='" & Cfact & "'", NewConnection, 2
 // $query="UPDATE cma_mfactura set monto_abonado=monto_abonado+'$pagototal',cancelado='1', statfact='3' where numfactu='$invoicen'";
 // mssqlConn::insert($query);  

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

<!-- numfactu         ,    fechapago                           ,codforpa              ,monto                 ,workstation          ,ipaddress                       ,usuario                 ,fecreg   ,horareg,id_centro,codtipotargeta -->
<!-- OPENTABLA rsupdate, "delete from mpagos where numfactu='" & Cfact & "' and id_centro = '2' and tipo_doc = '" & vtipo_doc & "' ", NewConnection, 2


For i = 0 To UBound(aTotal)
  If aTotal(i)(1) <> 0 Then
     OPENTABLA rsupdate, "insert into " & file_MPagos & "      (numfactu         ,    fechapago                           ,codforpa              ,monto                 ,workstation          ,ipaddress                       ,usuario                 ,fecreg                     ,horareg,              id_centro,     codtipotargeta) values
                                                            ('" & Cfact      & "','" & Date_Format(FrmSerInvP.vfecha) & "','" & aTotal(i)(0) & "','" & aTotal(i)(1) & "','" & xNameEquipo & "','" & Winsock1.LocalIP & "','" & rsloginpass!login & "','" & Date_Format(Date) & "','" & Time & "','" & Xid_centro & "', '" & aTotal(i)(2) & "'   )", NewConnection, 2
  End If
Next

OPENTABLA rsfact, "select * from cma_mfactura where numfactu='" & FrmSerInvP.vfactura.Caption & "' ", NewConnection, 2
If Round((rsfact!monto_abonado + TtalPag), 2) >= Round(rsfact!total, 2) Then
   OPENTABLA rsupdate, "update cma_mfactura set monto_abonado=(CONVERT(MONEY,'" & TtalPag & "')),cancelado='1', statfact='3' where numfactu='" & Cfact & "'", NewConnection, 2
 -->