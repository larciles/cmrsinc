<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$table = $_POST['table'];
$key   = $_POST[''];
$cancelado = $_POST['cancelado'];
$status    = $_POST['status'];
$docnumber = $_POST['docnumber'];
$monto     = $_POST['monto'];

//$query="UPDATE ".$table." SET monto_abonado='$monto' , ".'$cancelado'."='1',".'$status'."='3' where ".$key".='$docnumber' ";
mssqlConn::insert($query);

echo json_encode("true");
// "update " & tblMSSMDev & " set monto_abonado=(CONVERT(MONEY,'" & TtalPag & "')),cancelado='1', statnc ='3' where numnotcre ='" & Cfact & "'", NewConnection, 2