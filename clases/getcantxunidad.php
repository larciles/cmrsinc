<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$coditem = $_POST['idprouct'];

$query="SELECT *  from  NTPRODUCTOS where Cod_prod='$coditem'";

$result = mssqlConn::Listados($query);
echo $result;

