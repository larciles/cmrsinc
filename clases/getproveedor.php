<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
if (isset($_POST['codProv'])) {
	$codProv = $_POST['codProv'];
	$query="SELECT * from MProveedores where codProv='$codProv'  ";	
}else{
   $query="SELECT * from MProveedores  order by Desprov ";		
}


$estados = mssqlConn::Listados($query);
echo $estados;