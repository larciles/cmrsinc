<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);

session_start();
if(!isset($_SESSION['username'])){
    header("Location:vistas/login/login.php");
}else{
    $user  = $_SESSION['username'];
    $ip    = $_SERVER['HTTP_CLIENT_IP'];
    $perfil= $_SESSION['codperfil'];
}

require_once '../db/mssqlconn.php';

$tipoConsulta = $_POST['tipoConsulta']; 
$usuario= $_POST['usuario'];
$codclien= $_POST['codclien'];
$fecha_cita= $_POST['fecha'];
$coditems= $_POST['coditems'];    
$checkvalue= $_POST['resp'];   

$confirmado=0;
if($checkvalue=='true') 
{
	$confirmado=2;
}


$dbmsql = mssqlConn::getConnection();
if($tipoConsulta=='7'){
  	if ($perfil=='06' || $perfil=='01') {
		$query="Update  mconsultasS Set confirmado='$confirmado' where codclien='$codclien'  and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems'  ";  	
  	}else{
   		$query="Update  mconsultasS Set confirmado='$confirmado',  usuario='$usuario'  where codclien='$codclien'  and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems'  ";
  	}
}else{
  	if ($perfil=='06' || $perfil=='01') {
   	    $query="Update  mconsultas  Set confirmado='$confirmado' where codclien='$codclien'  and fecha_cita='$fecha_cita' ";
	}else{
		$query="Update  mconsultas  Set confirmado='$confirmado',  usuario='$usuario'  where codclien='$codclien'  and fecha_cita='$fecha_cita' ";
	}

}

$result = mssqlConn::insert($query);
echo json_encode("Yes!");