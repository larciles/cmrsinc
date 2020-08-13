<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fecha_cita'];
$codclien   = $_POST['codclien'];
$codmedico  = $_POST['codmedico'];
$check2  = $_POST['check2'];
$option2 = $_POST['option2']; 
$option3 = $_POST['option3'];
$codconsulta = $_POST['codconsulta'];
$coditems = $_POST['coditems'];
$observ =  $_POST['observ']; 
$valtype  = $_POST['valtype'];
$usuario  = $_POST['usuario']; 
$hoy = $_POST['hoy'];  
$mls = $_POST['mls'];
$hilt= $_POST['hilt'];
$hora = $_POST['hora'];

//TIPO SERVICIO
if (is_numeric($mls))  
{  

}  
else  
{  
  $mls=0;
}


if (!is_numeric($hilt))  
{  
  $hilt=0;
}  
else  
{  
  
}
//"save-data"

$hostname = gethostname();

//VALIDACION DE CITAS PENDIENTES O ABIERTAS
//$result = citasPendientes($codclien,$codconsulta,$coditems);
$objCitasP = json_decode($result, true);
$lencp = sizeof($objCitasP);
if ($lencp>0) {
  $noasistido=$objCitasP[0]['noasistido'];
  $citaActual=$objCitasP[0]['fecha_cita'];
}

//VALIDACION DE EL NUMERO DE CITAS
$citanro =0;
$result = numeroDeCitas($codclien,$codconsulta,$coditems);
$cantcitas= json_decode($result, true);
$lencc = sizeof($cantcitas);
$n_citas=$cantcitas[0][cant];
if($n_citas>0){
  //lreturn = items[0].cant
  $citanro =  $cantcitas[0]['cant'];
     
    $re = medicoTripleCero($codclien,$codconsulta,$coditems);
    $obre = json_decode($re, true);
  $lenre = sizeof($obre);
  if ($lenre>0) {
    updateClienteNuevo($codmedico,$codclien,$codconsulta,$coditems);  
  }

}else{
  updateClienteNuevo($codmedico,$codclien,$codconsulta,$coditems);
}

 $primera_control = "0";
 $vCitados = 1;
 $vNoCitados = 0;

//VALIDA SI EL USUARIO ES DE CONTROL DE CITAS O ATENCION AL PACIENTE
$result = getUser($usuario);
$objUser = json_decode($result, true);
$lenusr = sizeof($objUser);

//VALIDACION DE CITAS PENDIENTES O ABIERTAS   para evaluar y eliminar por que esta doble
$result = citasPendientes($codclien,$codconsulta,$coditems);
$objCitasP = json_decode($result, true);
if($objCitasP==null){
    $lencp =0;
}else{
    $lencp = sizeof($objCitasP);
}

if ($lencp>0) {
  $xUser='';
  $fecha_anterior = $objCitasP[0]['fecha_cita'];
  $xUser = $objCitasP[0]['usuario'];

    $vCitados = 1;
    $vNoCitados = 0;

  $regusuario = $usuario;       
    $xFEC = $hoy;
  
  if ($xUser=='' || $xUser== null) {
    $result= getLastUser($codclien);
      $obj = json_decode($result, true);
      $lenlu = sizeof($obj);
    if($lenlu>0){
       $xUser =  $obj[0]['usuario'];
    }
  }

  updateCita($fecha_cita,$codmedico,$observ,$xUser,$codclien,$regusuario,$codconsulta,$hora,$coditems);

}else{

  $result= getLastUser($codclien);
        $obj = json_decode($result, true);
    $lenlu = sizeof($obj);
  if($lenlu>0){
     $zuser =  $obj[0]['usuario'];
  }else{
    $zuser=selectOperadora();
  }

  if( $check2 == 1) { //servicios
    $xAsistido = "0";
  }else{
    $xAsistido = "0";
  }

  if( $citanro >= 1 ){
     $primera_control = "0";
  }else{
     $primera_control = "1";
  }

  if($citanro == 1 ){
     $vcitacontrol = 1;
    }else{
     $vcitacontrol = 0;
  }

  $result = checkCita($codclien,$fecha_cita,$codconsulta,$coditems);
  $objChkCita = json_decode($result, true);
  $lenchkct = sizeof($objChkCita);
  if($lenchkct==0){
    $Nconfirmado = 0;
    if( $check2 == 0){
      //CONSULTAS
       insertNuevaCita($codclien,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta);
    }else{
      //SERVICIO DE TERAPIA O SUERO
       $xtipocon = "07";
       $cc=$codconsulta ;
             $Xcoditems = $coditems ;
   
             if($option2==1){
                #LASER
                $filtro="option2";  
             }elseif ($option3==1) {
              #SUERO
              $filtro="option3";
             }
             if( substr($coditems,0,3)=="LI0" || substr($coditems,0,2)=="CM" ||  substr($coditems,0,2)=="PC"){
                 $filtro="option2";  
             }
             //insertServicios($codclien,$hoy,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta,$coditems,$filtro);
       insertServicios($codclien,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta,$coditems,$filtro,$mls,$hilt,$hora);
    }
  }else{

  }
}


function medicoTripleCero($codclien,$codconsulta,$coditems){
  
  date_default_timezone_set("America/Puerto_Rico");
  $fecha_cita =date("Y-m-d");


  $query="Select * from mconsultas WHERE CODCLIEN='$codclien' AND fecha_cita='$fecha_cita' and codmedico='000' ";
  if ($codconsulta=='07') {
     $query="Select * from mconsultass WHERE CODCLIEN='$codclien' and  fecha_cita='$fecha_cita'  and  codconsulta='$codconsulta' and coditems='$coditems'  and codmedico='000' ";
  }
 
  $result = mssqlConn::Listados($query);
  return $result;
}

function updateClienteNuevo($codmedico,$codclien,$codconsulta,$coditems){

  date_default_timezone_set("America/Puerto_Rico");
  $fecha_cita =date("Y-m-d");

  if ($codconsulta!="07") {
    $query="UPDATE MCONSULTAS SET codmedico = '$codmedico' WHERE CODCLIEN='$codclien' AND fecha_cita='$fecha_cita' ";   
  }else{  
    $query="UPDATE MCONSULTASS SET codmedico = '$codmedico' WHERE CODCLIEN='$codclien' AND fecha_cita='$fecha_cita'  and  codconsulta='$codconsulta' and coditems='$coditems' ";    
 }


   $result = mssqlConn::insert($query);
  }


function feriado($fecha)
{
  $query="select * from Feriado where FECHA='$fecha' and activo = 1 ";
  $result = mssqlConn::Listados($query);
  return $result;
}

function citasPendientes($codclien,$codconsulta,$coditems){

  $query="Select * from mconsultas where codclien='$codclien' and asistido=0 ";
  if ($codconsulta=='07') {
     if (strpos($coditems,'ST')>-1) {
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like '%ST'";
    }elseif (strpos($coditems,'TD')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like 'TD%' ";
    }elseif (strpos($coditems,'LI0')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like 'LI%' ";
    }elseif (strpos($coditems,'BL')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like 'BL%' ";
    }elseif (strpos($coditems,'CM')>-1 || strpos($coditems,'PC')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like ('CM%' ) or coditems like ('PC%' ) ";
    }
  }
 
  $result = mssqlConn::Listados($query);
  return $result;
}





function numeroDeCitas($codclien,$codconsulta,$coditems){

 $query="Select count(*) cant from mconsultas where codclien='$codclien' ";
 if ($codconsulta=='07') {
    if (strpos($coditems,'ST')>-1) {
        $query="Select count(*) cant from mconsultass where codclien='$codclien' and coditems like '%ST' ";
     }elseif (strpos($coditems,'TD')>-1){          
        $query="Select count(*) cant from mconsultass where codclien='$codclien' and coditems like 'TD%' ";
     }elseif (strpos($coditems,'LI0')>-1){
        $query="Select count(*) cant from mconsultass where codclien='$codclien' and coditems like 'LI%' ";
     }elseif (strpos($coditems,'BL')>-1){
        $query="Select count(*) cant from mconsultass where codclien='$codclien' and coditems like 'BL%' ";
     }elseif (strpos($coditems,'CM')>-1 || strpos($coditems,'PC')>-1){
        $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like ('CM%' ) or coditems like ('PC%' ) ";
    }
 }
 $result = mssqlConn::Listados($query);
 return $result;   
}

function getUser($usuario){
 $query="Select *  from loginpass where login ='$usuario' ";
 $result = mssqlConn::Listados($query);
 return $result;   
}  

function checkCita($codclien,$fecha_cita,$codconsulta,$coditems){


  $query="Select * from mconsultas where codclien='$codclien' and fecha_cita='$fecha_cita'  ";
  if ($codconsulta=='07') {
     if (strpos($coditems,'ST')>-1) {
     $query="Select * from mconsultass where codclien='$codclien' and fecha_cita='$fecha_cita'   and coditems like '%ST'";
    }elseif (strpos($coditems,'TD')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and fecha_cita='$fecha_cita'  and coditems like 'TD%' ";
    }elseif (strpos($coditems,'LI0')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and fecha_cita='$fecha_cita'  and coditems like 'LI%' ";
    }elseif (strpos($coditems,'BL')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and fecha_cita='$fecha_cita'  and coditems like 'BL%' ";
    }elseif (strpos($coditems,'CM')>-1 || strpos($coditems,'PC')>-1){
      $query="Select * from mconsultass where codclien='$codclien' and asistido=0 and coditems like ('CM%' ) or coditems like ('PC%' ) ";
    }
  }
 
  $result = mssqlConn::Listados($query);
  return $result;


 // $query="Select * from mconsultas where codclien='$codclien' and fecha_cita='$fecha_cita'  ";
 // $result = mssqlConn::Listados($query);
 // return $result;   
}

function insertNuevaCita($codclien,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta){
 $query="insert into mconsultas  (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta) values ('$codclien','$hoy','$vCitados','$fecha_cita' ,'$codmedico','$zuser','$hoy','$observ','$primera_control','$vNoCitados','$Nconfirmado','$vcitacontrol','$check2','$xAsistido','$codconsulta')" ;
 $result = mssqlConn::insert($query);
 return $result;
}


function getLastUser($codclien){
 $query="SELECT usuario From  mconsultas a  Where a.Id=(select max(id) from mconsultas where codclien='$codclien')";
 $result = mssqlConn::Listados($query);
 return $result;
}

function selectOperadora(){
 $query="SELECT id,usuario from operadortel where bandera=1";
 $result = mssqlConn::Listados($query);
 $obj = json_decode($result, true);
 $lenObj = sizeof($obj);  
 if ($lenObj >0) {
  $id=$obj[0]['id'];
  $operador=$obj[0]['usuario'];
 }
 
 $query="SELECT id from operadortel where bandera=0";
 $result = mssqlConn::Listados($query);
 $obj = json_decode($result, true);
 $lenObj = sizeof($obj);  
 if ($lenObj >0) {
  $idCero=$obj[0]['id'];

  $query="UPDATE operadortel set bandera='0' where id='$id' ";
  $result = mssqlConn::insert($query);

  $query="UPDATE operadortel set bandera='1' where id='$idCero' ";
  $result = mssqlConn::insert($query);

 }

 return $operador;

}

function updateCita($fecha_cita,$codmedico,$observ,$xUser,$codclien,$regusuario,$codconsulta,$hora,$coditems){

  if ($codconsulta!="07") {
    
    $query="SELECT maxid= max(id) FROM MCONSULTAS WHERE CODCLIEN='$codclien' AND ASISTIDO=0";
    $result = mssqlConn::Listados($query);
    $objUpd = json_decode($result, true);
    $lenupd = sizeof($objUpd);
    if($lenupd>0){
       $max_id = $objUpd[0]['maxid'];
    } 
        
    $query="UPDATE MCONSULTAS SET FECHA_CITA='$fecha_cita',usuario = '$xUser',confirmado='0',noasistido='0',activa='1',codmedico = '$codmedico', observacion = '$observ', regusuario='$regusuario', llegada='',codconsulta='$codconsulta' WHERE CODCLIEN='$codclien' AND ASISTIDO='0' and ID='$max_id' ";
    
  }else{
          
          $endTime = sum30Min($hora);
          $startFecha = $fecha_cita.' '.date("h:i:s", strtotime($hora));
          $endFecha   = $fecha_cita.' '.date("h:i:s", strtotime($endTime));
          
        if (strpos($coditems,'TD')>-1){ 
           $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0 and coditems like 'TD%'";
        } elseif (strpos($coditems,'ST')>-1) {   
           $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0 and coditems like '%ST'";        
        } elseif (strpos($coditems,'LI0')>-1) {
           $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0 and coditems like 'LI%'"; 
        } elseif (strpos($coditems,'BL')>-1) {
           $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0 and coditems like 'BL%'"; 
        }elseif (strpos($coditems,'CM')>-1 || strpos($coditems,'PC')>-1) {
           $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0 and coditems like ('CM%') or coditems like ('PC%') "; 
        }         
          
    
    $result = mssqlConn::Listados($query);
    $objUpd = json_decode($result, true);
    $lenupd = sizeof($objUpd);
    if($lenupd>0){
       $max_id = $objUpd[0]['maxid'];
    } 
        
    $query="UPDATE MCONSULTASS SET FECHA_CITA='$fecha_cita',enddate='$fecha_cita',endtime='$endTime' ,usuario = '$xUser',confirmado='0',noasistido='0',activa='1',codmedico = '$codmedico', observacion = '$observ', regusuario='$regusuario', llegada='', hora='$hora' WHERE CODCLIEN='$codclien' AND ASISTIDO='0' and ID='$max_id' ";
    
  }


   $result = mssqlConn::insert($query);
  }
  
  
         
function insertServicios($codclien,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta,$coditems,$filtro,$mls,$hilt,$hora){
  if($filtro=='option2'){
     if (strpos($coditems,'TD')>-1) {
        $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like 'TD%'  ";
     } else if (strpos($coditems,'LI0')>-1) {
        $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like 'LI%' ";
     }else if (strpos($coditems,'BL')>-1) {
        $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like 'BL%' ";
     }else if (strpos($coditems,'CM')>-1 || strpos($coditems,'PC')>-1) {
        $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like ('CM%') or coditems like ('PC%') ";
     }

  }elseif($filtro=='option3'){
     $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like '%ST' ";
  }
 
  $result = mssqlConn::Listados($query);
  $objUpd = json_decode($result, true);
  $lenupd = sizeof($objUpd);
  
  
  $endTime = sum30Min($hora);
  $startFecha = $fecha_cita.' '.date("h:i:s", strtotime($hora));
  $endFecha   = $fecha_cita.' '.date("h:i:s", strtotime($endTime));
          
  
  if($lenupd>0){
       $max_id = $objUpd[0]['maxid'];
      if( $max_id!=null){      
      $query="UPDATE mconsultaSS  set fecha ='$hoy' , fecha_cita ='$fecha_cita',enddate='$endFecha', hora ='$hora', endtime='$endTime' where id ='$max_id'";    
      }
      else{
          $query="INSERT into mconsultaSS (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta,Coditems,mls,hilt,hora,enddate,endtime) Values ('$codclien','$hoy','$vCitados','$fecha_cita' ,'$codmedico','$zuser','$hoy','$observ','$primera_control','$vNoCitados','$Nconfirmado','$vcitacontrol','$check2','$xAsistido','$codconsulta','$coditems','$mls','$hilt','$hora','$fecha_cita','$endTime')"; 
      }    
  }else{
          $query="INSERT into mconsultaSS (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta,Coditems,mls,hilt,hora,enddate,endtime) Values ('$codclien','$hoy','$vCitados','$fecha_cita' ,'$codmedico','$zuser','$hoy','$observ','$primera_control','$vNoCitados','$Nconfirmado','$vcitacontrol','$check2','$xAsistido','$codconsulta','$coditems','$mls','$hilt','$hora','$fecha_cita','$endTime')";
  }
  $result = mssqlConn::insert($query);
}


function sum30Min($hour_one){
  
  $hour_two = "00:30";
  $h =  strtotime($hour_one);
  $h2 = strtotime($hour_two);

  $minute = date("i", $h2); 
  $second = date("s", $h2);
  $hour = date("H", $h2);

  $convert = strtotime("+$minute minutes", $h); 
  $convert = strtotime("+$second seconds", $convert);
  $convert = strtotime("+$hour hours", $convert);
  $new_time = date('H:i:s', $convert); 

return $new_time;  
}