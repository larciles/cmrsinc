<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codtipre = $_POST['codtipre'];
$coditem = $_POST['idprouct'];

$query="SELECT * from  MPrecios where coditems='$coditem' and codtipre ='$codtipre'";

$result = mssqlConn::Listados($query);
echo $result;

