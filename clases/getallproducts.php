<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$prod_serv = $_POST['prod_serv'];
$orderby = $_POST['orderby'];

 if (isset($_POST['prod_serv']) && $_POST['prod_serv']!="") {                 	
        $query="SELECT * from  MInventario where prod_serv='$prod_serv' and activo = 1 order by '$orderby'";
 }else{
	$query="SELECT * from  MInventario where  activo = 1 order by '$orderby'";
}



$res = mssqlConn::Listados($query);
echo $res;