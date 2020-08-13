<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fecha_cita'];
$codclien   = $_POST['codclien'];
$id         = $_POST['key'];
$time_start = $_POST['time_start'];
$time_end   = $_POST['time_end'];


$query  = "update mconsultass set hora='$time_start', endtime='$time_end' where fecha_cita ='$fecha_cita' and codclien ='$codclien' and id='$id' ";
  


$result = mssqlConn::insert($query);
echo $result;