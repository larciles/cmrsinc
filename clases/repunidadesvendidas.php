<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

$query="SELECT D.cantidad,  M.coditems, M.desitems, M.nombre_alterno, f.fechafac FROM DFactura D INNER JOIN MInventario  M ON D.coditems = M.coditems  inner join MFactura f On d.numfactu =  f.numfactu  where f.statfact<>2  and f.fechafac between '$fechai'  and '$fechaf' ORDER BY  M.nombre_alterno ASC ";
$query="SELECT sum( D.cantidad) cantidad,  M.coditems, M.desitems, M.nombre_alterno, max(f.fechafac) FROM DFactura D INNER JOIN MInventario  M ON D.coditems = M.coditems  inner join MFactura f On d.numfactu =  f.numfactu  where f.statfact<>2  and f.fechafac between '$fechai'  and '$fechaf' group by M.coditems, M.desitems, M.nombre_alterno ORDER BY cantidad desc ";

$result = mssqlConn::Listados($query);

echo $result;