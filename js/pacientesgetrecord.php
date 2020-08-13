<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$field = $_POST['q'];

$t = $_POST['t'];

$query="SELECT numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac,total,codmedico FROM mfactura where codclien='$field' and statfact<>'2' order by id desc ";    

$query="SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac,total,codmedico,1 id_centro,id FROM mfactura where codclien='$field' and statfact<>'2' "
	." union " 
	."SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac,total,codmedico,2 id_centro,id FROM cma_mfactura where codclien='$field' and statfact<>'2' "
	." union " 
	."SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac,total,codmedico,3 id_centro,id FROM MSSMFact where codclien='$field' and statfact<>'2' "
	."order by id_centro , id desc, fechafac desc ";
if ($t=="r") {

	 $query="SELECT codclien  FROM mclientes where historia='$field' ";
	 $result = mssqlConn::Listados($query);
	 $res = json_decode($result, true);	     
	 $field =  $res[0]['codclien'];
}

$query="SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac, fechafac as fecha,total,codmedico,1 id_centro,id FROM mfactura where codclien='$field' and statfact<>'2' 
	union 
	SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac, fechafac as fecha,total,codmedico,2 id_centro,id FROM cma_mfactura where codclien='$field' and statfact<>'2'
	union 
	SELECT codclien,numfactu,CONVERT(VARCHAR(10),fechafac,101) fechafac, fechafac as fecha,total,codmedico,3 id_centro,id FROM MSSMFact where codclien='$field' and statfact<>'2'
	order by  fecha desc";	
$query="SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,1 id_centro,a.id,b.nombres
    FROM mfactura a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2' 
    SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,2 id_centro,a.id ,b.nombres
    FROM cma_mfactura a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2'
    SELECT a.codclien,a.numfactu,CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.fechafac as fecha,total,a.codmedico,3 id_centro,a.id ,b.nombres
    FROM MSSMFact a
    inner join MClientes b On a.codclien=b.codclien
    where a.codclien='$field' and statfact<>'2'";



$result = mssqlConn::Listados($query);
echo $result;