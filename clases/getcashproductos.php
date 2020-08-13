<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$tiporeporte='false';
$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
if (isset($_POST['tiporeporte'])) {
   $tiporeporte= $_POST['tiporeporte'];
}

$query="SELECT valor,sum(monto) monto from cuadre WHERE fecha='$fecha' and id_centro = '1' group by valor order by valor desc";
if ($tiporeporte=='true') {
    $query="SELECT * from cuadre WHERE fecha='$fecha' and usuario='$usuario' and id_centro = '1' order by valor desc";
}

$res = mssqlConn::Listados($query);
echo $res;