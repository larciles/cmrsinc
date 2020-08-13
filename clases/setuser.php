<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
session_start();
date_default_timezone_set("America/Puerto_Rico");
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}

require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$insert  = $_POST['newUpdate'];
$perfil  = $_POST['perfil'];
$access  = $_POST['access'];
$nombre  = $_POST['nombre'];
$apellido= $_POST['apellido'];
$usuar   = $_POST['usuar'];   
$contrase= $_POST['contrase'];
$iduser  = $_POST['iduser'];
$controlcita = $_POST['ctrlcita'];

$prnfact = $_POST['prnfact'];
$autoprnfact = $_POST['autoprnfact'];
$pathprn = $_POST['pathprn'];


if ($controlcita=='true') {
	$controlcita=1;
}else{
	$controlcita=0;
}

if ($prnfact=='true') {
	$prnfact=1;
}else{
	$prnfact=0;
}

if ($autoprnfact=='true') {
	$autoprnfact=1;
}else{
	$autoprnfact=0;
}




if ($insert=='false') {
  Updates($perfil,$access,$nombre,$apellido,$contrase,$iduser,$controlcita,$prnfact,$autoprnfact,$pathprn);
}else{
  Inserts($perfil,$access,$nombre,$apellido,$contrase,$usuar,$controlcita,$prnfact,$autoprnfact,$pathprn);
}


echo $result;


function Updates($perfil,$access,$nombre,$apellido,$contrase,$iduser,$controlcita,$prnfact,$autoprnfact,$pathprn){

	if ($contrase=="") {
		$query="UPDATE loginpass Set Nombre = '$nombre', apellido = '$apellido', codperfil = '$perfil',controlcita  = '$controlcita', access  = '$access'  , prninvoice='$prnfact', autoposprn='$autoprnfact', pathprn='$pathprn'  Where Id = '$iduser' ";
	} else {
		$query="UPDATE loginpass Set Nombre = '$nombre', apellido = '$apellido', codperfil = '$perfil',controlcita  = '$controlcita', access  = '$access', passwork = '$contrase', prninvoice='$prnfact', autoposprn='$autoprnfact', pathprn='$pathprn' Where Id = '$iduser' ";
	}	
	
	$result = mssqlConn::insert($query);
	return 0;	
}

function Inserts($perfil,$access,$nombre,$apellido,$contrase,$usuar,$controlcita,$prnfact,$autoprnfact,$pathprn){
    $fecha=date("Y-m-d");
    $inicials=strtoupper(substr($nombre,0,1)).strtoupper(substr($apellido,0,1));
	$query="INSERT INTO loginpass
	       (codperfil,access,  Nombre,apellido,passwork,login,controlcita,initials,prninvoice,autoposprn,pathprn )
	VALUES('$perfil','$access','$nombre','$apellido','$contrase','$usuar','$controlcita','$inicials','$prnfact','$autoprnfact','$pathprn') ";
	$result = mssqlConn::insert($query);

	 return 0 ;
}