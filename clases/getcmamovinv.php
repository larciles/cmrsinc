<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

$query="SELECT
  Cmacierreinv.coditems 
  ,CONVERT(VARCHAR(10),Cmacierreinv.fechacierre,101) fechacierre
  ,Cmacierreinv.existencia
  ,Cmacierreinv.compras
  ,Cmacierreinv.DevCompras
  ,Cmacierreinv.ventas
  ,Cmacierreinv.ventas*MInventario.costo costoDVentas
  ,Cmacierreinv.anulaciones
  ,Cmacierreinv.ajustes
  ,Cmacierreinv.NotasCreditos
  ,Cmacierreinv.NotasEntregas
  ,Cmacierreinv.InvPosible
  ,Cmacierreinv.InvActual
  ,MInventario.coditems 
  ,MInventario.desitems
  ,Cmacierreinv.InvPosible*MInventario.costo costoTtlVentas
FROM
  Cmacierreinv
  INNER JOIN MInventario
    ON Cmacierreinv.coditems = MInventario.coditems
    where  Cmacierreinv.fechacierre between '$fechai' and '$fechaf'";
$result = mssqlConn::Listados($query);

echo $result;