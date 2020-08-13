<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query="Select login,concat(Nombre,' ',apellido) usuario from loginpass  where activo ='1' order by usuario";
$paises = mssqlConn::Listados($query);
echo $paises;