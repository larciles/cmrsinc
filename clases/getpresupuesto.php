<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

if(isset($_POST['numfactu']) && $_POST['numfactu']!==""){
	$numfactu=$_POST['numfactu'];
}

$query="SELECT CONVERT(VARCHAR(10),fechafac,101) fechafac2,* from presupuesto_m where numfactu ='$numfactu' ";

$res = mssqlConn::Listados($query);
echo $res;