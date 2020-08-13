<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$id_centro=$_POST['id_centro'];
$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];

$query="DELETE FROM Cuadre WHERE fechA='$fecha' and id_centro='$id_centro' and usuario='$usuario'  ";

$res = mssqlConn::insert($query);
echo $res;