<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$arg='';

if (isset($_POST['arg'])) {
	$arg=$_POST['arg'];
}

if ($arg=='') {	
	$query="Select * from VIEW_SoloServicios where coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12','LI001','LI002')  ";
} else {
	$query="Select * from VIEW_SoloServicios where coditems like '%$arg%' ";
}

$result = mssqlConn::Listados($query);
echo $result;
