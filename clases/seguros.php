<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query="SELECT * from  mseguros where status=1 order by codseguro";
$res = mssqlConn::Listados($query);
echo $res;