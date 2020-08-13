<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$arg='';

if (isset($_POST['arg'])) {
	$arg=$_POST['arg'];
}

$query="Select * from VIEW_SoloServicios where cod_subgrupo = 'BLOQUEO'  ";

$result = mssqlConn::Listados($query);
echo $result;
