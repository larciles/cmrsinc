<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

//laser_sales_view

$query=" SELECT  sum((a.subtotal- a.descuento )) neto,  a.usuario FROM  VentasDiariasMSS a	 INNER JOIN MDocumentos b ON   a.doc = b.codtipodoc 	where a.fechafac  between '$fechai'  and '$fechaf'  and a.statfact<>'2'	group by a.usuario";


$query=" SELECT  sum((a.subtotal- a.descuento )) neto,  a.usuario FROM  laser_sales_view a	 INNER JOIN MDocumentos b ON   a.doc = b.codtipodoc 	where a.fechafac  between '$fechai'  and '$fechaf'  and a.statfact<>'2'	group by a.usuario";


$query=" SELECT  sum((a.subtotal- a.descuento )) neto,  a.usuario FROM  VentasDiariasMSS a	 INNER JOIN MDocumentos b ON   a.doc = b.codtipodoc 	where a.fechafac  between '$fechai'  and '$fechaf'  and a.statfact<>'2'	group by a.usuario";


$result = mssqlConn::Listados($query);

echo $result;