<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();
$filtro ="";
if (isset($_POST['filtro'])) {
   $filtro =  $_POST['filtro'];
}
$query="SELECT codmedico,Concat(nombre,' ',apellido) medico  FROM Mmedicos where activo =1 order by nombre";
if ($filtro!=="") {
    $query="SELECT codmedico,Concat(nombre,' ',apellido) medico  FROM Mmedicos where activo =1 and codmedico <>'000' order by nombre";
}

$medicos = mssqlConn::Listados($query);
echo $medicos;