<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$prod_serv = $_POST['prod_serv'];
$coditem = $_POST['idprouct'];

$query="SELECT *, REPLACE(CONVERT(CHAR(15), fecing, 101), '', '-') AS ingreso from  MInventario where prod_serv='$prod_serv' and coditems='$coditem'";


$result = mssqlConn::Listados($query);
echo $result;

