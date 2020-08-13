<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$id = $_POST['id'];
$activo = $_POST['activo'];


$query="UPDATE loginpass Set  activo='$activo' where Id='$id' ";
$result = mssqlConn::insert($query);

echo $result;