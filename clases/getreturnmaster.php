<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

if(isset($_POST['numfactu']) && $_POST['numfactu']!==""){
	$numfactu=$_POST['numfactu'];
}

$query="SELECT * from  CMA_Mnotacredito where numnotcre='$numfactu' ";

$res = mssqlConn::Listados($query);
echo $res;

