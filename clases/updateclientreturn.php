<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien  = $_POST['codclien'];
$numnotcre = $_POST['numfactu'];

if (isset($_POST['idcentro'])) {
	if ($_POST['idcentro']=='3') {
		$query="UPDATE MSSMDev SET codclien='$codclien'  where numnotcre ='$numnotcre' ";
	} elseif ($_POST['idcentro']=='1') {
		$query="UPDATE Mnotacredito SET codclien='$codclien'  where numnotcre ='$numnotcre' ";
	}	
}else{
	$query="UPDATE CMA_Mnotacredito SET codclien='$codclien'  where numnotcre ='$numnotcre' ";
}

$res = mssqlConn::insert($query);
echo $res;

