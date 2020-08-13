<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechacita = $_POST['fechacita'];
$recordnumber = $_POST['recordnumber'];

$query="  Select * from VIEW_mconsultas_02 where fecha_cita='$fechacita' and Historia='$recordnumber'  and llegada<>'' and ( Historia<>'' or historia <> 'No asign') ";    

$result = mssqlConn::Listados($query);
echo $result;