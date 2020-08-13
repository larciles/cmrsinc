<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
$tiporeporte = $_POST['tiporeporte'];

$query=" SELECT  sum(monto) monto, modopago,codforpa from VIEWpagosPRCMACST where fechapago='$fecha' and statfact=3 and cod_subgrupo = 'CONSULTA'  and usuario='$usuario'  group by modopago,codforpa order by codforpa ";
//NUEVO
	$query="SELECT 
       SUM( a.total) monto ,ISNULL ( d.desTipoTargeta , 'EXCENTO') modopago, max(d.codforpa) codforpa  
       FROM  
       VentasDiariasCMACST a  
	   INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc  
	   LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu  
       WHERE
       a.fechafac ='$fecha' and a.usuario='$usuario' AND  a.cod_subgrupo = 'CONSULTA' and  a.statfact=3  and d.desTipoTargeta is  not null
       GROUP BY  
       desTipoTargeta  
       Order by 
       desTipoTargeta ";

if ($tiporeporte=='false') {	
	$query=" SELECT  sum(monto) monto, modopago,codforpa from VIEWpagosPRCMACST where fechapago='$fecha' and statfact=3 and cod_subgrupo = 'CONSULTA' group by modopago,codforpa order by codforpa ";


	//NUEVO
	 $query="SELECT 
       SUM( a.total) monto ,ISNULL ( d.desTipoTargeta , 'EXCENTO') modopago  , max(d.codforpa) codforpa
       FROM  
       VentasDiariasCMACST a  
	   INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc  
	   LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu  
       WHERE
       a.fechafac ='$fecha' AND   a.cod_subgrupo = 'CONSULTA' and  a.statfact=3  and d.desTipoTargeta is  not null
       GROUP BY  
       desTipoTargeta  
       Order by 
       desTipoTargeta ";
}

$res = mssqlConn::Listados($query);
echo $res;