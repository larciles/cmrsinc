<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$sd = $_POST['sd'];
$ed = $_POST['ed'];

$query="Select sum(total) total from VIEWPRODLASERSUEROINTRA_2 vp  where fechafac between '$sd' and '$ed' and medico<>''";
  //version original 
$query="Select sum(subtotal) total from emt_view vp  where fechafac between '$sd' and '$ed' ";
   //version nueva 2019-08-12
$query="Select sum(total) total from emt_view vp  where fechafac between '$sd' and '$ed' ";


// $query="Select   sum(general) total
//             FROM newconsol3_2_w_cm con 
//             inner join Divisiones E ON con.tipo =E.id
//             inner join MDocumentos D ON con.doc= D.codtipodoc 
//             where con.fechafac between '$sd' and '$ed'  and statfact <>'2' and tipo <> '2' ";


$result = mssqlConn::Listados($query);
echo $result;