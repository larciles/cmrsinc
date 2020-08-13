<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$fecha_i = $_POST['fi'];
$fecha_f = $_POST['ff'];

//
$query="SELECT  sum(general) total
            FROM newconsol3_2_w_cm con 
            inner join Divisiones E ON con.tipo =E.id
            inner join MDocumentos D ON con.doc= D.codtipodoc 
            where con.fechafac between '$fecha_i' and '$fecha_f' and statfact <>'2' group by con.fechafac  ORDER BY con.fechafac ";

$query="SELECT  sum(general) total
            FROM consolidated_view con 
           /* inner join divisions E ON con.tipo =E.cod*/
            inner join MDocumentos D ON con.doc= D.codtipodoc 
            where con.fechafac between '$fecha_i' and '$fecha_f' and statfact <>'2' group by con.fechafac  ORDER BY con.fechafac ";

$res = mssqlConn::Listados($query);
echo $res;

