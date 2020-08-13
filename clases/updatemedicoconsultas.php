<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codmedico = $_POST['codmedico'];
$numfactu = $_POST['numfactu'];

if (isset($_POST['idcentro'])) {
	$query="UPDATE MSSMFact SET codmedico='$codmedico'  where numfactu ='$numfactu' ";	
}else{
  	$query="UPDATE cma_MFactura SET codmedico='$codmedico'  where numfactu ='$numfactu' ";	
}

$res = mssqlConn::insert($query);
echo $res;

