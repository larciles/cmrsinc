<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
$fecha = $_POST['fecha'];

$query="SELECT nombres, COUNT(*) as facturas FROM VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha'  AND '$fecha'  AND  a.statfact<>2 And a.total>=0 and cod_subgrupo='CONSULTA' group by nombres having COUNT(*) >1 ";
$res = mssqlConn::Listados($query);
echo $res;

