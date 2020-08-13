<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codmedico = $_POST['codmedico'];
$codclien = $_POST['codclien'];

$query="UPDATE Mclientes SET codmedico='$codmedico'  where codclien ='$codclien' ";

$res = mssqlConn::insert($query);
echo $res;