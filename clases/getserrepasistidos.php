<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha  = $_POST['fecha_cita'];
$fecha2  = $_POST['fecha_cita2'];
$usuario= $_POST['usuario'];

    $query="SELECT "
    ."a.nombres, a.Historia, a.desitems, a.medico as Medicos, a.telfhabit, a.Cedula, a.fecha_cita " 
    ."FROM "
    ."VIEW_Consulta_Serv a " 
    ."WHERE "
    ."(a.fecha_cita between'$fecha' and '$fecha2') and a.asistido=3 and  a.activa<>'0' /* and a.usuario='$usuario' */ " 
    ."ORDER BY "
    ."a.desitems ASC, "
    ."a.nombres ASC ";



$result = mssqlConn::Listados($query);
echo $result;