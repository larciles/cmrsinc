<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
 
$tipo_doc  = $_POST['tipo_doc'];
$id_centro = $_POST['id_centro'];
$numfactu  = $_POST['numfactu'];

$query="Select nota  FROM cma_MFactura where  numfactu = '$numfactu'  ";

$result = mssqlConn::Listados($query);
echo $result;