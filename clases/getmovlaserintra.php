<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

$query="SELECT
  mscierre.coditems 
  ,CONVERT(VARCHAR(10),mscierre.fechacierre,101) fechacierre
  ,mscierre.existencia
  ,mscierre.compras
  ,mscierre.DevCompras
  ,mscierre.ventas
  ,mscierre.ventas*MInventario.costo costoDVentas
  ,mscierre.anulaciones
  ,mscierre.ajustes
  ,mscierre.NotasCreditos
  ,mscierre.NotasEntregas
  ,mscierre.InvPosible
  ,mscierre.InvActual
  ,MInventario.coditems 
  ,MInventario.desitems
  ,mscierre.InvPosible*MInventario.costo costoTtlVentas
  ,MInventario.nombre_alterno
FROM
  mscierre
  INNER JOIN MInventario
    ON mscierre.coditems = MInventario.coditems
    where  mscierre.fechacierre between '$fechai' and '$fechaf'";
$result = mssqlConn::Listados($query);

echo $result;