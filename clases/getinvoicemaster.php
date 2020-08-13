<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

if(isset($_POST['numfactu']) && $_POST['numfactu']!==""){
	$numfactu=$_POST['numfactu'];
}
if (isset($_POST['empresa']) && $_POST['empresa']!=="") {

	$empresa=$_POST['empresa'];
}
if ($empresa=='3') {
	$query="SELECT CONVERT(VARCHAR(10),fechafac,101) fechafac2,* from  MSSMFact where numfactu='$numfactu' ";
}else{
	$query="SELECT CONVERT(VARCHAR(10),fechafac,101) fechafac2,* from  cma_MFactura where numfactu='$numfactu' ";	
}

$res = mssqlConn::Listados($query);
echo $res;

