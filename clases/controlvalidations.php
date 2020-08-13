<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$valtype = $_POST['valtype'];
$field = $_POST['q'];

if($valtype=='citas-abiertas'){
	$query="Select * from mconsultas where codclien='$field' and asistido=0 ";
}else if ($valtype=='numero-citas') {
	$query="Select count(*) cant from mconsultas where codclien='$field' ";    
}else if ($valtype=='get-user') {	
	$query="Select *  from loginpass where login ='$field' ";   
}

$result = mssqlConn::Listados($query);
echo $result;
