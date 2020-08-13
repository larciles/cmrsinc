<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];

$query=" SELECT d.numfactu, d.modopago From VIEWpagosPRCMA d WHERE d.fechapago between '$fecha' AND '$fecha' Order By d.modopago ";


$query=" SELECT "
  ."  a.numfactu, sum( a.monto) monto , a.id_centro, a.tipo_doc,  CASE WHEN  COUNT(*) = 1 THEN max(a.modopago)  ELSE 'SPILT'END modopago "
  ." FROM "
  ." VIEWpagosPRMSS a "
  ." INNER JOIN  MDocumentos b ON "
  ."  a.tipo_doc = b.codtipodoc "
  ." WHERE "
  ."   a.fechapago='$fecha' "
  ." group by   a.numfactu, a.id_centro, a.tipo_doc "
  ." union all "
  ." SELECT  b.numnotcre,sum( b.monto) monto,  b.id_centro, b.tipo_doc,   CASE WHEN  COUNT(*) = 1 THEN max(b.modopago)  ELSE 'SPILT'END modopago "
  ." FROM   dbo.VIEWPagosDEVMSS b where b.fechapago='$fecha' "
  ." group by   b.numnotcre, b.id_centro, b.tipo_doc ";

$result = mssqlConn::Listados($query);

echo $result;