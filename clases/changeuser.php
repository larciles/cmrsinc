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


$docnumber= ltrim(rtrim($_POST['docnumber']));
$tipodoc  = ltrim(rtrim($_POST['tipodoc']));
$usernew  = ltrim(rtrim($_POST['usernew']));
$user     = ltrim(rtrim($_POST['user']));
$id_centro= ltrim(rtrim($_POST['id_centro']));
$query='';

if ($codperfil=='01' || ($access=='6' || $access=='1')) {
	if ($id_centro=='3') {
		if ($tipodoc =='01' ) {
			$query="UPDATE MSSMFact Set  usuario='$usernew' where numfactu='$docnumber' ";			
		} elseif($tipodoc =='04') {
			$query="UPDATE MSSMDev Set  usuario='$usernew' where numnotcre='$docnumber' ";
		}
		$_qry_pagos="UPDATE Mpagos Set usuario='$usernew' Where numfactu='$docnumber' and id_centro='3' and tipo_doc='$tipodoc' ";
	
	} elseif($id_centro=='1') {
		if ($tipodoc =='01' ) {
			$query="UPDATE MFactura Set  usuario='$usernew' where numfactu='$docnumber' ";
		} elseif($tipodoc =='04' ) {
			$query="UPDATE Mnotacredito Set  usuario='$usernew' where numnotcre='$docnumber' ";
		}
		$_qry_pagos="UPDATE Mpagos Set usuario='$usernew' Where numfactu='$docnumber' and id_centro='1' and tipo_doc='$tipodoc' ";
	} elseif($id_centro=='2') {
		if ($tipodoc =='01' ) {
			$query="UPDATE cma_MFactura Set  usuario='$usernew' where numfactu='$docnumber' ";
		} elseif($tipodoc =='04' ) {
			$query="UPDATE CMA_Mnotacredito Set  usuario='$usernew' where numnotcre='$docnumber' ";
		}
		$_qry_pagos="UPDATE Mpagos Set usuario='$usernew' Where numfactu='$docnumber' and id_centro='2' and tipo_doc='$tipodoc'";
	}
	if ($query!='') {

		$result = mssqlConn::insert($query);
		$result = mssqlConn::insert($_qry_pagos);
	}
	

}


echo $result;