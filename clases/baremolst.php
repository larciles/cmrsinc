<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


   $query="SELECT * from  tipoprecio  ";	


$estados = mssqlConn::Listados($query);
echo $estados;

