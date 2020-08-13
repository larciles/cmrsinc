<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$factura   = $_POST['q'];
$idempresa = $_POST['idempresa'];

$dbmsql = mssqlConn::getConnection();

if ($idempresa=='1') {
   $query="SELECT desitems,cantidad FROM view_dfactura_1 where numfactu='$factura' order by desitems ";	
} else if ($idempresa=='2') {
  $query="SELECT desitems,cantidad FROM VIEW_cma_dfactura_1 where numfactu='$factura' order by desitems ";
} else if ($idempresa=='3') {
  $query="SELECT desitems,cantidad FROM view_MSSDFact_1 where numfactu='$factura' order by desitems ";  
}

$result = mssqlConn::Listados($query);
echo $result;