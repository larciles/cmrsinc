<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$arg='';

if (isset($_POST['arg'])) {
	$arg=$_POST['arg'];
}

if ($arg=='LA') {	
	$query="Select * from VIEW_SoloServicios where coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12')";
}elseif ($arg=="IN") {
    $query="Select * from VIEW_SoloServicios where coditems in ('LI001','LI002')";
}elseif ($arg=="BL") {
	$query="Select * from VIEW_SoloServicios where coditems in ('BLEXONR01','BLEXONR02')";
}elseif ($arg=="CM") {
	$query="Select * from VIEW_SoloServicios where coditems like ('CM%' ) or coditems like ('PC%' )";
}


$result = mssqlConn::Listados($query);
echo $result;
