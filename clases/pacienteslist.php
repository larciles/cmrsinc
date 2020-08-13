<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$codclien = $_POST['c'];

$dbmsql = mssqlConn::getConnection();

$query="SELECT * FROM [farmacias].[dbo].[MClientes] where codclien='$codclien' ";
$result = mssqlConn::Listados($query);
echo $result;