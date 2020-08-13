<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");

require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codclien  = $_POST['clientcode'];
$zipcode   = $_POST['zipcode'];
$municipio = $_POST['municipio'];
$dob       = $_POST['dob'];
$sexo      = $_POST['sex'];
 
$fecha =date("Y-m-d");

$result = mssqlConn::Listados($query);

$query="UPDATE  MClientes  SET NACIMIENTO ='$dob'
                              ,codpostal  ='$zipcode'
                              ,hCiudad    ='$municipio'
                              ,sexo       ='$sexo'  where codclien='$codclien'  ";

$result = mssqlConn::insert($query);                  

echo $result;