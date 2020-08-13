<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

$query="SELECT  sum(a.cantidad) cantidad ,sum((a.subtotal- a.descuento )) neto,CONCAT(c.nombre,' ',c.apellido) medico FROM VentasDiariasCMACELMADRESnoCons a INNER JOIN MDocumentos b ON a.doc=b.codtipodoc INNER JOIN Mmedicos c ON a.codmedico=c.Codmedico where a.fechafac between '$fechai' and '$fechaf' and a.statfact<>'2' group by c.nombre, c.apellido  order by neto desc";

$result = mssqlConn::Listados($query);

echo $result;