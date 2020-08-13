<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha = $_POST['fecha'];
$tipo  = $_POST['tipo'];
$usuario = $_POST['usuario'];

if ($tipo=='rv') {
    $query="SELECT  sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total  FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3   And  a.total>0  /* and usuario='$usuario' */ ";  
} else if ($tipo=='rd') {
	$query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0  /*and usuario='$usuario' */ "; 
} else if ($tipo=='rt') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3 /* and usuario='$usuario'*/  ";
} else if ($tipo=='dv') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total>0  /*and usuario='$usuario' */ Order by a.numfactu desc ";
} else if ($tipo=='dd') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between   '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0  /*and usuario='$usuario' */ Order by a.numfactu desc "; 
} else if ($tipo=='rb') {
	$query="SELECT sum(total+monto_flete) monto, count(*) facturas   from MFactura where fechafac='$fecha'  and statfact=3  and /*usuario='$usuario'*/  ";
}

$result = mssqlConn::Listados($query);

echo $result;

#rv resumen de venta
#rd resumen de devoluciones
#rt resumen de totales general ventas menos devolucion
#dv detalle de venta
#dd detalle de devolucion
#rb resumen facturacion bruta