<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$query="SELECT * from MInventario where prod_serv in( 'P','I','h') and activo in('1','3') or ( Prod_serv='M' and Inventariable=1 ) order by cod_grupo DESC, desitems ";	
	

$estados = mssqlConn::Listados($query);
echo $estados;