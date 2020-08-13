<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';


if (isset($_POST['pais'])) {
	$state = $_POST['pais'];

    if ( $_POST['alter']!="") {
		$state = $_POST['alter'];
	}
}




$dbmsql = mssqlConn::getConnection();
if (isset($_POST['pais'])) {
	$query="SELECT [State],[Abre],[PAIS],[COD],[Id]  FROM [farmacias].[dbo].[States] where pais ='$state' order by State";
}else{
	$query="SELECT [State],[Abre],[PAIS],[COD],[Id]  FROM [farmacias].[dbo].[States] order by State";	
}
$estados = mssqlConn::Listados($query);
echo $estados;

