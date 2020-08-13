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

//"save-data"

$hostname = gethostname();

//VALIDACION DE CITAS PENDIENTES O ABIERTAS
$result = citasPendientes($codclien,$codconsulta);
$objCitasP = json_decode($result, true);
$lencp = sizeof($objCitasP);
if ($lencp>0) {
	$noasistido=$objCitasP[0]['noasistido'];
	$citaActual=$objCitasP[0]['fecha_cita'];
}

//VALIDACION DE EL NUMERO DE CITAS
$citanro =0;
$result = numeroDeCitas($codclien,$codconsulta);
$cantcitas= json_decode($result, true);
$lencc = sizeof($cantcitas);
if($lencc>0){
	//lreturn = items[0].cant
	$citanro =  $cantcitas[0]['cant'];
}
 $primera_control = "0";
 $vCitados = 1;
 $vNoCitados = 0;

//VALIDA SI EL USUARIO ES DE CONTROL DE CITAS O ATENCION AL PACIENTE
$result = getUser($usuario);
$objUser = json_decode($result, true);
$lenusr = sizeof($objUser);

//VALIDACION DE CITAS PENDIENTES O ABIERTAS   para evaluar y eliminar por que esta doble
$result = citasPendientes($codclien,$codconsulta);
$objCitasP = json_decode($result, true);
$lencp = sizeof($objCitasP);
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

	updateCita($fecha_cita,$codmedico,$observ,$xUser,$codclien,$regusuario,$codconsulta);

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

	$result = checkCita($codclien,$fecha_cita);
	$objChkCita = json_decode($result, true);
	$lenchkct = sizeof($objChkCita);
	if($lenchkct==0){
		$Nconfirmado = 0;
		if( $check2 == 0){
			//CONSULTAS
			 insertNuevaCita($codclien,$hoy,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta);
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
             insertServicios($codclien,$hoy,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta,$coditems,$filtro);
		}
	}else{

	}
}


function feriado($fecha)
{
  $query="select * from Feriado where FECHA='$fecha' and activo = 1 ";
  $result = mssqlConn::Listados($query);
  return $result;
}

function citasPendientes($codclien,$codconsulta){

  $query="Select * from mconsultas where codclien='$codclien' and asistido=0 ";
  if ($codconsulta=='07') {
  	 $query="Select * from mconsultass where codclien='$codclien' and asistido=0 ";
  }
 
  $result = mssqlConn::Listados($query);
  return $result;
}

function numeroDeCitas($codclien,$codconsulta){

 $query="Select count(*) cant from mconsultas where codclien='$codclien' ";
 if ($codconsulta=='07') {
 	$query="Select count(*) cant from mconsultass where codclien='$codclien' ";
 }
 $result = mssqlConn::Listados($query);
 return $result;   
}

function getUser($usuario){
 $query="Select *  from loginpass where login ='$usuario' ";
 $result = mssqlConn::Listados($query);
 return $result;   
}  

function checkCita($codclien,$fecha_cita){
 $query="Select * from mconsultas where codclien='codclien' and fecha_cita='$fecha_cita'  ";
 $result = mssqlConn::Listados($query);
 return $result;   
}

function insertNuevaCita($codclien,$hoy,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta){
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

function updateCita($fecha_cita,$codmedico,$observ,$xUser,$codclien,$regusuario,$codconsulta){

  if ($codconsulta!="07") {
	  
	  $query="SELECT maxid= max(id) FROM MCONSULTAS WHERE CODCLIEN='$codclien' AND ASISTIDO=0";
	  $result = mssqlConn::Listados($query);
	  $objUpd = json_decode($result, true);
	  $lenupd = sizeof($objUpd);
	  if($lenupd>0){
	     $max_id = $objUpd[0]['maxid'];
	  } 
	      
	  $query="UPDATE MCONSULTAS SET FECHA_CITA='$fecha_cita',usuario = '$xUser',confirmado='0',noasistido='0',activa='1',codmedico = '$codmedico', observacion = '$observ', regusuario='$regusuario' WHERE CODCLIEN='$codclien' AND ASISTIDO='0' and ID='$max_id' ";
	  
  }else{
  
	  $query="SELECT maxid= max(id) FROM MCONSULTASS WHERE CODCLIEN='$codclien' AND ASISTIDO=0";
	  $result = mssqlConn::Listados($query);
	  $objUpd = json_decode($result, true);
	  $lenupd = sizeof($objUpd);
	  if($lenupd>0){
	     $max_id = $objUpd[0]['maxid'];
	  } 
	      
	  $query="UPDATE MCONSULTASS SET FECHA_CITA='$fecha_cita',usuario = '$xUser',confirmado='0',noasistido='0',activa='1',codmedico = '$codmedico', observacion = '$observ', regusuario='$regusuario' WHERE CODCLIEN='$codclien' AND ASISTIDO='0' and ID='$max_id' ";
	  
  }


   $result = mssqlConn::insert($query);
  }
         
function insertServicios($codclien,$hoy,$vCitados,$fecha_cita ,$codmedico,$zuser,$hoy,$observ,$primera_control,$vNoCitados,$Nconfirmado,$vcitacontrol,$check2,$xAsistido,$codconsulta,$coditems,$filtro){
  if($filtro=='option2'){
  	 $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like 'TD%' ";
  }elseif($filtro=='option3'){
  	 $query="SELECT MAX(ID) maxid from mconsultass a where a.codclien='$codclien' AND ASISTIDO='0' and coditems like '%ST' ";
  }
 
  $result = mssqlConn::Listados($query);
  $objUpd = json_decode($result, true);
  $lenupd = sizeof($objUpd);
  if($lenupd>0){
       $max_id = $objUpd[0]['maxid'];
      if( $max_id!=null){      
      $query="UPDATE mconsultaSS  set fecha ='$hoy' , fecha_cita ='$fecha_cita' where id ='$max_id'";    
      }
      else{
          $query="INSERT into mconsultaSS (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta,Coditems) Values ('$codclien','$hoy','$vCitados','$fecha_cita' ,'$codmedico','$zuser','$hoy','$observ','$primera_control','$vNoCitados','$Nconfirmado','$vcitacontrol','$check2','$xAsistido','$codconsulta','$coditems')"; 
      }    
  }else{
     $query="INSERT into mconsultaSS (codclien,fecha,citados,fecha_cita,codmedico,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta,Coditems) Values ('$codclien','$hoy','$vCitados','$fecha_cita' ,'$codmedico','$zuser','$hoy','$observ','$primera_control','$vNoCitados','$Nconfirmado','$vcitacontrol','$check2','$xAsistido','$codconsulta','$coditems')";
  }
  $result = mssqlConn::insert($query);
}