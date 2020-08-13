<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['q'];
$codclien = $_POST['codclien'];

$query="Select * from mconsultas where codclien='$codclien' and fecha_cita='$fecha_cita'  ";
$result = mssqlConn::Listados($query);
echo $result;
