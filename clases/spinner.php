<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
// require_once '../db/mssqlconn.php';

// $tipoConsulta = $_POST['tipoConsulta']; 
// $codclien     = $_POST['codclien'];
// $fecha_cita   = $_POST['fecha_cita'];
// $coditems     = $_POST['coditems'];    
// $observacion  = $_POST['observacion'];   


// $dbmsql = mssqlConn::getConnection();
// if($tipoConsulta=='7' || $tipoConsulta=='07'){
//   $query="Update  mconsultasS Set observacion='$observacion' where codclien='$codclien'  and fecha_cita='$fecha_cita'  and  CODITEMS ='$coditems'  ";
// }else{
//   $query="Update  mconsultas  Set observacion='$observacion' where codclien='$codclien'  and fecha_cita='$fecha_cita' ";
// }

// $result = mssqlConn::insert($query);
echo json_encode("Yes!");