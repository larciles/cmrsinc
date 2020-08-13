<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$s_user = $_POST['s_user'];
$s_pass = $_POST['s_pass'];


$query="Select    login		,Nombre	,apellido	,cedula	,codperfil	,CODVENDE	,codestat	,controlcita	,permisobusqueda,	Id,	initials,	access from  loginpass where login='$s_user' and passwork='$s_pass' and codperfil='01' ";
$result = mssqlConn::Listados($query);
echo $result;