<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];


$query="SELECT "
      ." SUM( a.total) total , COUNT(*) facturas "
	  ." ,ISNULL ( d.desTipoTargeta , 'EXCENTO') DesTipoTargeta "
      ." FROM " 
      ." VentasDiariasCMACST a " 
	  ." INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc " 
	  ." LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu " 
      ." WHERE"
      ." a.fechafac between '$fecha' AND '$fecha' AND   a.cod_subgrupo = 'CONSULTA' and  a.statfact=3 "
      ." GROUP BY " 
      ." desTipoTargeta " 
      ." Order by "
      ." desTipoTargeta ";
$res = mssqlConn::Listados($query);
echo $res;

