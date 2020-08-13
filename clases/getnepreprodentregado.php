<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$numnotent   = $_POST['numnotent'];


$query="SELECT * from cuadre WHERE fecha='$fecha' and usuario='$usuario' and id_centro = '1' order by valor desc";

$query="SELECT c.numnotent, c.coditems,c.cantidad,d.precunit, (c.cantidad*d.precunit) total ,f.desitems from NotEntDetalle c 
inner join MPrecios d On c.coditems=d.coditems and d.codtipre='00'
inner join MInventario f On c.coditems=f.coditems
where c.numnotent='$numnotent' ";

$res = mssqlConn::Listados($query);
echo $res;