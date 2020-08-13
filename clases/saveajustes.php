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
 // $idajuste    = $_POST["idajuste"];
  $docnumbr = $_POST["docnumbr"];
  $observacion = $_POST["idnota"];  
  $fecha   = $_POST["fecha"];
  $facclose    = $_POST["transfer"];
  //$factcomp    = $_POST["compran"];
  
  if ($factcomp=="") {    
     $factcomp  =generateRandomString(6);
  }
   
 
 
 if (trim($_POST["serv"][0] != '') && $_POST["cantidad"][0] != '0') {
    #SAVE MASTER DATABASE MCOMPRAS
          
          
    $ajustes=saveMaster($docnumbr,$fecha,$observacion,$usuario,$hora );

    $idajuste =  $ajustes['lastInsertId'] ;
    

 }

  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["serv"][$i] != '') && $_POST["cantidad"][$i] != '0')  
       {           
            $coditems    = $_POST["serv"][$i];                     
            $cantidad    = $_POST["cantidad"][$i];          
          
            if ( $coditems  !=="") {
              #SAVE DETAILS DCOMPRAS
              saveDetails($coditems,$cantidad,$fecha,$user,$hora,$idajuste);   
              if ($facclose=='1') {
                  # ACTUALIZA INVENTARIO
                  inventoryUpdate($coditems,$cantidad);
                  if ($coditems=='50GST' ) {
                      ajustadbcierre($coditems,$fecha);
                  }
              }
            }
            
       }  
  }  

  
   
   echo json_encode($idajuste); // print_r($_POST);  
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
  }
 }
//-----------------------------------------------------------------------------------
function saveMaster($docnumbr,$fecha,$observacion,$usuario,$hora ){
 
  // $query="SELECT *  from majustes  WHERE codajus = '$idajuste' ";
  // $res = mssqlConn::Listados($query);
  // $result = json_decode($res, true);
  // $len = sizeof($result);
  // if($len>0){
    
  // }else{
     // $query="INSERT INTO  majustes (codajus,numfactu,fechajus,observacion,transferido,usuario,fecreg,horareg)"
     //                        . " Values ('$idajuste','$docnumbr','$fecha','$observacion','0','$usuario','$fecha','$hora' )";


    $query="INSERT into majustes (codajus, observacion,horareg,fecreg,usuario,numfactu,fechajus)
SELECT IDENT_CURRENT('Majustes')
,'$observacion' 
,(SELECT convert(varchar(10), GETDATE(), 108))
,(SELECT getdate())
,'$usuario'
,'$docnumbr'
,'$fecha' ";
                      
     $res=  mssqlConn::insert($query);
     return $res;
      // }
}
//-----------------------------------------------------------------------------------
function saveDetails($coditems ,$cantidad,$fecha ,$user ,  $hora,   $idajuste ){
  $query="INSERT INTO dajustes (coditems , cantidad , fechajust,usuario, fecreg , horareg ,codajus) "
          . "                VALUES  ('$coditems' ,'$cantidad','$fecha' ,'$user' , '$fecha', '$hora',   '$idajuste' )";
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
function getInvoiceNumber(){
  $UltimoAjuste=1;
  $query="SELECT * from empresa where id_centro='1' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $UltimoAjuste = $result[0]['UltimoAjuste']; 
     $UltimoAjuste++;
  }
  $UltimoAjuste =  str_pad($UltimoAjuste, 7, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET UltimoAjuste ='$UltimoAjuste' where id_centro='1' ";
  mssqlConn::insert($query);
 return  $UltimoAjuste;
}
//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------
?> 