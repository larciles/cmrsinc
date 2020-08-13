<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$field = $_POST['q'];

$t = $_POST['t'];


if ($t=="r") {

	 $query="SELECT codclien  FROM mclientes where historia='$field' ";
	 $result = mssqlConn::Listados($query);
	 $res = json_decode($result, true);	     
	 $field =  $res[0]['codclien'];
}


$query="SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,1 id_centro,a.id,b.nombres
    FROM mfactura a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2'   order by a.fechafac desc
    SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,2 id_centro,a.id ,b.nombres
    FROM cma_mfactura a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2'  order by a.fechafac desc
    SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,3 id_centro,a.id ,b.nombres
    FROM MSSMFact a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2'  order by a.fechafac desc";

$result = mssqlConn::Listados($query);

echo $result;