 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set("America/Puerto_Rico");
set_time_limit(0);
require_once '../db/mssqlconn.php';
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
}

$dbmsql = mssqlConn::getConnection();

 $hoy =date("m-d-Y");

 $number = count($_POST["serv"]);  
 if($number > 0)  
 {  


  $usuario = $user;
  $hora    = date("H:i:s");        
  $idpo    = $_POST["idpo"];
  $codprov = $_POST["idprove"];
  $observacion = $_POST["idnota"];  
  $fechacomp   = $_POST["fecha"];
  $facclose    = $_POST["transfer"];
  $factcomp    = $_POST["compran"];
  
  if ($factcomp=="") {    
     $factcomp  =generateRandomString(6);
  }

 
 
 if (trim($_POST["serv"][0] != '') && $_POST["cantidad"][0] != '0') {
    #SAVE MASTER DATABASE MCOMPRAS
    saveMaster($factcomp,$fechacomp,$observacion,$codprov,$facclose,$user,$fechacomp,$hora,$fechacomp);
 }

  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["serv"][$i] != '') && $_POST["cantidad"][$i] != '0')  
       {           
            $coditems    = $_POST["serv"][$i];                     
            $cantidad    = $_POST["cantidad"][$i];          
          
            if ( $coditems  !=="") {
              #SAVE DETAILS DCOMPRAS
              saveDetails($factcomp,$fechacomp,$coditems,$cantidad,$hora,$user );   
              if ($facclose=='1') {
                  # ACTUALIZA INVENTARIO
                  inventoryUpdate($coditems,$cantidad);
                  if ($coditems=='50GST' ) {
                      ajustadbcierre($coditems,$fechacomp);
                  }else if ($coditems=='LI001' || $coditems=='LI002' ) {
                      ajustadbcierre($coditems,$fechacomp);
                  }
              }
            }
            
       }  
  }  

  
   
   echo json_encode($factcomp); // print_r($_POST);  
 }  
 else  
 {  
      echo "";  
 }  

//-----------------------------------------------------------------------------------
 function ajustadbcierre($coditems,$fecha){
  if ($coditems=='50GST') {
     $query="Delete Cmacierreinv where fechacierre >= '$fecha' ";   
     $res = mssqlConn::insert($query);
     mssqlConn::stexec('cmaci');
  }else if ($coditems=='LI001' || $coditems=='LI002') {
     $query="Delete mscierre where fechacierre >= '$fecha' ";   
     $res = mssqlConn::insert($query);
     mssqlConn::stexec('cierremslaser');
  }
 }
//-----------------------------------------------------------------------------------
function saveMaster($factcomp,$fechacomp,$observacion,$codprov,$facclose,$user,$fecreg,$hora,$fechapost ){
 
  $query="SELECT *  from MCompra  WHERE factcomp = '$factcomp' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    
  }else{
     $query="INSERT INTO  MCompra (factcomp,fechacomp,observacion,codprov,facclose,usuario,fecreg,hora,fechapost)"
                            . " Values ('$factcomp','$fechacomp','$observacion','$codprov','$facclose','$user','$fecreg','$hora','$fechapost' )";
       mssqlConn::insert($query);
      }
}
//-----------------------------------------------------------------------------------
function saveDetails($factcomp,$fechacomp,$coditems,$cantidad,$hora,$user ){
  $query="INSERT INTO DCompra (factcomp ,fecha ,coditems ,cantidad ,fecreg,hora,usuario) "
          . "                VALUES  ('$factcomp','$fechacomp','$coditems','$cantidad','$fechacomp','$hora','$user' )";
          mssqlConn::insert($query);
 }
//-----------------------------------------------------------------------------------
function inventoryUpdate($coditems,$qty){
  $query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  mssqlConn::insert($query);
 }
//-----------------------------------------------------------------------------------
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------
?> 