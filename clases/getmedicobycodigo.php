<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codmedico = $_POST['q'];

$query="SELECT codmedico,concat(nombre,' ',apellido) medico  FROM Mmedicos where  codmedico ='$codmedico'";
$medicos = mssqlConn::Listados($query);
echo $medicos;