<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query="Select UPPER(cod_subgrupo) cod_subgrupo, min(Prod_serv) Prod_serv from MInventario  where Prod_serv<>'p' and cod_subgrupo  is not null   group by cod_subgrupo ";
$query="Select CASE WHEN  cod_subgrupo IS NULL  THEN 'NO DEFINIDO'  ELSE UPPER(cod_subgrupo) END   cod_subgrupo, min(Prod_serv) Prod_serv from MInventario  where Prod_serv<>'p'  group by cod_subgrupo ";

$result = mssqlConn::Listados($query);
echo $result;

