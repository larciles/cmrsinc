<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];
$usuario = $_POST['usuario'];

$query=" SELECT  convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0 Order  and /*a.usuario='$usuario'*/  by  a.numfactu desc  ";
$result = mssqlConn::Listados($query);

echo $result;
//getproductosdevolucion.php