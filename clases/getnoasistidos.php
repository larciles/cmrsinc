<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_cita = $_POST['fecha_cita'];

$query="Select distinct a.numfactu, a.nombres, b.observacion, b.usuario,c.telfhabit,c.Historia,c.codclien, CONCAT(e.nombre,' ',e.apellido) medico from VentasDiariasCMACST a inner join Mconsultas b ON  a.codclien=b.codclien  and b.fecha_cita='$fecha_cita' inner join MClientes c ON  a.codclien=c.codclien inner join Mmedicos e ON b.codmedico=e.Codmedico where a.fechafac='$fecha_cita' and a.cod_subgrupo='CONSULTA' and a.statfact =3 and asistido =0 and confirmado =2";

$result = mssqlConn::Listados($query);
echo $result;