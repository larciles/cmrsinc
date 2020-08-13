<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';


    $filter= $_GET['term'];
	$invert = explode(' ', $filter);
	$len=sizeof($invert);
    $len--;
	for ($i=$len; $i >=0 ; $i--) { 
		if ( $i >=0) {
			$invertFilter.=$invert[$i].'%';
		}else{
			$invertFilter.=$invert[$i];
		}		
	}


$dbmsql = mssqlConn::getConnection();

$query="Select codclien,nombres  FROM [farmacias].[dbo].[MClientes] where CONCAT (codclien,[Cedula],[telfhabit],[Historia],[nombres]) like '%$filter%'  ";
//$query="SELECT * FROM [farmacias].[dbo].[MClientes] where codclien='$codclien' ";
$result = mssqlConn::Listados($query);
echo $result;