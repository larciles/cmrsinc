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
$query="SELECT sum( a.total) monto FROM VentasDiarias a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and  statfact=3";
if ($tiporeporte=='true') {
  $query="SELECT sum( a.total) monto FROM VentasDiarias a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and usuario='$usuario' and  statfact=3";
}

$res = mssqlConn::Listados($query);
echo $res;