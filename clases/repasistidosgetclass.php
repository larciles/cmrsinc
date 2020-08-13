
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fechai = $_POST['fechai'];
$fechaf = $_POST['fechaf'];


$query= "Select a.nombres, a.telfhabit, a.Medicos, a.Historia, a.fecha_cita, a.activa, a.asistido, a.usuario,a.ProximaCita,a.observacion, b.NumeroCitas FROM VIEW_repconsultas3 a inner join  VIEW_repconsultasNCitas b ON a.codclien=b.codclien where a.asistido=3 and fecha_cita between '$fechai' and  '$fechaf' ";


$result = mssqlConn::Listados($query);
echo $result;

        