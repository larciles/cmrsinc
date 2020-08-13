<?php
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
set_time_limit(0);

session_start();

if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
    $access=$_SESSION['access'];
    $codperfil=$_SESSION['codperfil'];

    $prninvoice=$_SESSION['prninvoice'];
    $autoposprn=$_SESSION['autoposprn'];
    $pathprn=$_SESSION['pathprn'];
}
$d=__DIR__;

require('../controllers/MSSMDevController.php');
require('../controllers/MSSMFactController.php');

require('../controllers/MnotacreditoController.php');
require('../controllers/MFacturaController.php');

require('../controllers/CMA_MnotacreditoController.php');
require('../controllers/CMA_MFacturaController.php');

require('../controllers/MpagosController.php');
require('../controllers/IdcardController.php');

$msMDEVController = new MSSMDevController();
$msmfactController = new MSSMFactController();

$mnotacreditoController = new MnotacreditoController();
$mfacturaController = new MFacturaController();

$cmaMnotacreditoController = new CMA_MnotacreditoController();
$cma_MfacturaController = new CMA_MFacturaController();
$mpagosController = new MpagosController();
$idcardController = new IdcardController();


$v=$_POST['json_data'];
$datatosave=json_decode($v);

$cash      = $datatosave->cash;
$tdc1      = $datatosave->tdc1;
$tdc2      = $datatosave->tdc2;
$tdc3      = $datatosave->tdc3;
$ath       = $datatosave->ath;
$check     = $datatosave->check;
$pagototal = $datatosave->pagototal; 
$saldo     = $datatosave->saldo;
$dueamount = $datatosave->dueamount;

$cambio    = $datatosave->cambio;
$radio1    = $datatosave->radio1;
$radio2    = $datatosave->radio2;
$radio3    = $datatosave->radio3;

$fecha     = $datatosave->fecha;
$invoicen  = $datatosave->invoicen;
$idusr     = $datatosave->idusr;
$id_centro = $datatosave->id_centro;
$tipo_doc  = $datatosave->tipo_doc;

$por       = $datatosave->por;

//nuevo
$idcard1="";
$idcard2="";
$idcard3="";
 
$idcard1=$datatosave->idcard1 ; 
$idcard2=$datatosave->idcard2 ; 
$idcard3=$datatosave->idcard3 ;
 
//f nuevo

$payTypeArray['vs']= array( 'codforpa'=> '4', 'codtipotargeta' => '02');
$payTypeArray['mc']= array( 'codforpa'=> '4', 'codtipotargeta' => '01');
$payTypeArray['am']= array( 'codforpa'=> '4', 'codtipotargeta' => '03');
$payTypeArray['dc']= array( 'codforpa'=> '4', 'codtipotargeta' => '04');
$payTypeArray['ck']= array( 'codforpa'=> '4', 'codtipotargeta' => '05');

$payArray = array();



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


$mpagosController->del($invoicen,$id_centro,$tipo_doc);
$idcardController->del($invoicen,$id_centro,$tipo_doc);


    foreach ($payArray as $key ) {
      $hora =  date("H:i:s", time());
      $codforpa =   $key['codforpa'];
      $codtipotargeta = $key['codtipotargeta'];
      $monto =  $key['monto'];
      $cnumber= $key['idcard'];
      $valMonto =  (int) $monto;
     // if ($valMonto>0) {
         if ($tipo_doc=='04') {
            $valMonto=floatval($monto)*-1;
         }else{
            $valMonto =  floatval($monto) ; 
         }
         
      
        if( $monto!==0){

            $set_data = array(
                 'numfactu'=>$invoicen
                ,'fechapago'=>$fecha
                ,'codforpa' =>$codforpa
                ,'monto'=>$valMonto
                ,'usuario'=>$idusr
                ,'fecreg'=>$fecha
                ,'horareg'=>$hora
                ,'id_centro'=>$id_centro
                ,'codtipotargeta'=>$codtipotargeta
                ,'tipo_doc'=>$tipo_doc
                ,'idempresa'=>$id_centro
                ,'cnumber'=>$cnumber
                ,'por'=>$por
                ,'cnumber'=>$cnumber

            );
            $mpagosController->create($set_data);
           
           
             
          try {
            if ($cnumber!='' or $cnumber!=null) {

                $set_data = array(
                    'cnumber'=>$cnumber
                    ,'factura'=>$invoicen
                    ,'fecha'=>$fecha
                    ,'codforpa'=>$codforpa
                    ,'monto'=>$valMonto
                    ,'usuario'=>$idusr
                    ,'hora'=>$hora
                    ,'idcompany'=>$id_centro
                    ,'tipotarjeta'=>$codtipotargeta
                    ,'tipo_doc'=>$tipo_doc

                );

                $idcardController->create($set_data);

            }
            
          } catch (Exception $e) {
            
          }
           
        }
     // }
       
    } 

    if ($id_centro =='2' and $tipo_doc=="01") {

        $res=$cma_MfacturaController->read($invoicen);

        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statfact' =>  '3' 
             
        );

        $where_data = array(
            'numfactu' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_MfacturaController->update($array_edit);
        
    }elseif ($id_centro =='2' and $tipo_doc=="04") {
        $res=$cmaMnotacreditoController->read($invoicen);

        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statnc' =>  '3' 
             
        );

        $where_data = array(
            'numnotcre' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cmaMnotacreditoController->update($array_edit);
    } elseif($id_centro =='1' and $tipo_doc=="01") {

        $res=$mfacturaController->read($invoicen);






        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statfact' =>  '3' 
             
        );

        $where_data = array(
            'numfactu' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $mfacturaController->update($array_edit);
    } elseif  ($id_centro =='1' and $tipo_doc=="04") {
        $res=$mnotacreditoController->read($invoicen);

        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statnc' =>  '3' 
             
        );

        $where_data = array(
            'numnotcre' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $mnotacreditoController->update($array_edit);
    }  elseif($id_centro =='3' and $tipo_doc=="01") {

        $res=$msmfactController->read($invoicen);






        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statfact' =>  '3' 
             
        );

        $where_data = array(
            'numfactu' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $msmfactController->update($array_edit);
    } elseif  ($id_centro =='3' and $tipo_doc=="04") {
        $res=$mnotacreditoController->read($invoicen);

        $monto_abonado=$res[0]['monto_abonado'];
        $monto_abonado+=$pagototal;

                $set_data = array(
            'monto_abonado' =>$monto_abonado
            ,'cancelado' => '1'
            ,'statnc' =>  '3' 
             
        );

        $where_data = array(
            'numnotcre' =>   $invoicen
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $msMDEVController->update($array_edit);
    } 
