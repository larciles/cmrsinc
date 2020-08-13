<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codmedico = $_POST['codmedico'];
$codclien = $_POST['codclien'];



$query="SELECT count(*) ncitas from Mconsultas where codclien='$codclien'  ";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);

try {
	$ncitas =(int) $result[0]['ncitas'];
	if ($ncitas>1) {
		//uno
		$query="UPDATE Mclientes SET codmedico='$codmedico' where codclien ='$codclien' ";		
		$res = mssqlConn::insert($query);		
		//dos
		$query="UPDATE Mconsultas SET codmedico='$codmedico' where codclien='$codclien' and ( codmedico='000' or  codmedico='')  ";
	    $result = mssqlConn::insert($query);
	    //tres
	    $query="UPDATE Mconsultass SET codmedico='$codmedico' where codclien='$codclien' and ( codmedico='000' or  codmedico='')  ";
     	$result = mssqlConn::insert($query);
	}
	
} catch (Exception $e) {
	
}



//echo $res;