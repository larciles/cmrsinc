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

$query="SELECT * FROM Cuadre WHERE fechA='$fecha' and id_centro='C' ";
$query="SELECT valor,sum(monto) monto from Cuadre where fechA='$fecha'  and Id_centro='C' group by valor";
if ($tiporeporte=='true') {
    $query="SELECT * FROM Cuadre WHERE fechA='$fecha' and id_centro='C' and usuario='$usuario' ";	
}


$res = mssqlConn::Listados($query);
echo $res;