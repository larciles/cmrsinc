<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

#PRIMERA VERSION
$query="SELECT  sum((a.subtotal- a.descuento )) neto,  a.usuario FROM  VentasDiariasCMACST_4_CM a	INNER JOIN MDocumentos b ON   a.doc = b.codtipodoc 	where a.fechafac  between '$fechai'  and '$fechaf'  and a.statfact<>'2'	group by a.usuario";

#SEGUNDA VERSION ORIGINAL
$query="SELECT  sum( cantidad*precunit )-sum(descuento) as  neto, usuario FROM  VentasDiariasCMACST_4_EXO  a	INNER JOIN MDocumentos b ON   a.doc = b.codtipodoc 	where a.fechafac  between '$fechai'  and '$fechaf'   and a.statfact<>'2'	group by a.usuario";

#TERCERA VERSION
#NUEVO PARA AGREGAR LOS BLOQUEO CON EXOSOMAS SE CREO UNA NUEVA VISTA PARA UNIR LAS VENTAS
$query="SELECT sum(neto) as neto, usuario, sum(cantidad) AS cantidad from exos_plus_bloque_view where fechafac between '$fechai' and '$fechaf' group by usuario";

$result = mssqlConn::Listados($query);

echo $result;