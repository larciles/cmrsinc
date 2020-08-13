<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
$pon =$_POST["pon"];
$query="Update purchaseOM Set Status = 2 WHERE pon = '$pon' and status='1' ";
$query="delete purchaseOM WHERE pon = '$pon'";
 mssqlConn::insert($query);
//$result=$dbmsql->query($query);
    
$query="delete purchaseorder   where purchaseOrder= '$pon' ";
 mssqlConn::insert($query);
    //$result=$dbmsql->query($query);
echo '0';