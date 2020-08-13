<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$prod_serv = $_POST['prod_serv'];
$orderby = $_POST['orderby'];

$query="select 'ST' coditems, 'SUERO TERAPIA Vit C' desitems 
 Union select 'LS' coditems, 'LASER' desitems 
 Union select coditems,desitems from  Minventario where prod_serv='P' and activo = 1 order by desitems";

$estados = mssqlConn::Listados($query);
echo $estados;

