<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];
$usuario = $_POST['usuario'];


$query="SELECT "
      ." SUM( a.total) total , COUNT(*) facturas "
	." ,ISNULL ( d.desTipoTargeta , 'EXCENTO') DesTipoTargeta "
      ." FROM " 
      ." VentasDiarias a " 
	." INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc " 
	." LEFT JOIN VIEWPagosPR_W7  d ON a.numfactu = d.numfactu " 
      ." WHERE"
      ." a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3 /* and a.usuario='$usuario' */ "
      ." GROUP BY " 
      ." desTipoTargeta " 
      ." Order by "
      ." desTipoTargeta ";

$query="SELECT sum(monto) total , COUNT(*) facturas , modopago DesTipoTargeta,codforpa  FROM VIEWPagosPR_W7  where fechapago='$fecha' and statfact=3   /*and usuario='$usuario'*/  group by modopago,codforpa  order by modopago";
$res = mssqlConn::Listados($query);
echo $res;

//getproductosgrupototales.php