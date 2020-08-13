<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$codclien    = ltrim(rtrim($_POST['codclien']));
$codconsulta = ltrim(rtrim($_POST['codconsulta']));
$coditems    = ltrim(rtrim($_POST['coditems']));
$fecha_cita  = ltrim(rtrim($_POST['fecha_cita']));
$terapias    = ltrim(rtrim($_POST['terapias']));



$query="UPDATE mconsultass Set  terapias='$terapias' where codclien='$codclien' and fecha_cita='$fecha_cita' and coditems='$coditems' and codconsulta='$codconsulta' ";
$result = mssqlConn::insert($query);

echo $result;