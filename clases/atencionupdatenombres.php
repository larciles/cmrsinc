<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$codclien = $_POST['codclien'];
$nombres = $_POST['nombres'];
$old_str = $record;
$record  = str_replace(' ', '', $old_str);

$query="UPDATE MClientes Set  nombres='$nombres' where codclien='$codclien' ";
$result = mssqlConn::insert($query);

echo $result;