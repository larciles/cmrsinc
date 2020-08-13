<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$numfactu = $_POST['numfactu'];

$query=" SELECT "
."    a.numfactu, a.nombres, a.fechafac, a.subtotal, a.descuento, a.total, a.statfact, a.usuario, a.TotImpuesto, a.monto_flete, a.doc, a.cod_subgrupo, " 
."    c.desestac, "
."    b.nombre "
."  FROM "
."      VentasDiariasCMACSTRep a  "
."  INNER JOIN   MDocumentos b ON  a.doc = b.codtipodoc "
."  INNER JOIN  vestaciones  c ON  a.workstation = c.Workstation "
."  where numfactu='$numfactu' "
."  ORDER BY "
."  c.desestac ASC, "
."  a.doc ASC, "
."  a.numfactu ASC ";
$result = mssqlConn::Listados($query);

echo $result;



//isconsulta.php