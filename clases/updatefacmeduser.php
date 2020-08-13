<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';


$factura  = $_POST['factura']; 
$idempresa= $_POST['idempresa'];
$fieLd    = $_POST['fieLd'];
$typefield= $_POST['typefield'];
$id= $_POST['id'];    

if ($idempresa=='1') {
	$table ="MFactura";
}elseif ($idempresa=='2' || $idempresa=='4'){
	$table=" cma_MFactura";
}elseif ($idempresa=='3') {
    $table="MSSMFact ";
}

if ($typefield=="m") {
	$campo="codmedico";
}elseif ($typefield=="u") {
	$campo="usuario";
}

$dbmsql = mssqlConn::getConnection();

$query="Update ".$table." Set ".$campo."="."'".$fieLd."'"." where numfactu='$factura' and id ='$id'";



$result = mssqlConn::insert($query);
echo json_encode($result);