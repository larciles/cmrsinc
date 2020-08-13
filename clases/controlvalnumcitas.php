<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$field = $_POST['q'];

$query="Select count(*) cant from mconsultas where codclien='$field' ";    

$result = mssqlConn::Listados($query);
echo $result;