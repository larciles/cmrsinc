<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$valtype = $_POST['valtype'];
$codconsulta = $_POST['codconsulta'];
$codclien = $_POST['codclien'];
$fecha = $_POST['fecha'];


$query="UPDATE Mconsultas Set  codconsulta='$codconsulta' where codclien='$codclien' and fecha_cita='$fecha'";

$result = mssqlConn::insert($query);
