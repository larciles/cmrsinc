<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codmedico = $_POST['codmedico'];
$numnotcre = $_POST['numfactu'];

if (isset($_POST['idcentro'])) {
	$query="UPDATE MSSMDev SET codmedico='$codmedico' where numnotcre ='$numnotcre' ";	
}else{
  	$query="UPDATE CMA_Mnotacredito SET codmedico='$codmedico' where numnotcre ='$numnotcre' ";	
}

$res = mssqlConn::insert($query);
echo $res;