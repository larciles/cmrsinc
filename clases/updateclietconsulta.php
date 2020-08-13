<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien = $_POST['codclien'];
$numfactu = $_POST['numfactu'];

if (isset($_POST['idcentro'])) {
	if ($_POST['idcentro']=='1') {
		$query="UPDATE MFactura SET codclien='$codclien '  where numfactu ='$numfactu' ";
	}else{
		$query="UPDATE MSSMFact SET codclien='$codclien '  where numfactu ='$numfactu' ";
	}

}else{
	$query="UPDATE cma_MFactura SET codclien='$codclien '  where numfactu ='$numfactu' ";
}

$res = mssqlConn::insert($query);
echo $res;

