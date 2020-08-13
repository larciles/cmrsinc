<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
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
}

require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$iduser     = ltrim(rtrim($_POST['iduser']));
$nuevoperfil= ltrim(rtrim($_POST['nuevoperfil']));



if ($codperfil=='01' || $access=='6') {	
	$query="UPDATE loginpass Set  codperfil='$nuevoperfil' where Id='$iduser' ";
	$result = mssqlConn::insert($query);
}


echo $result;