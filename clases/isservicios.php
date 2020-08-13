<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$numfactu = $_POST['numfactu'];

$query=" SELECT "
."  a.numfactu, a.nombres, a.fechafac, a.subtotal, a.descuento, a.total, a.statfact, a.usuario, a.TotImpuesto, a.monto_flete, a.doc, a.cod_subgrupo " 
."  FROM "
."  VentasDiariasCMACSTRep a  "
."  where a. numfactu='$numfactu' ";

$result = mssqlConn::Listados($query);

echo $result;



//isconsulta.php