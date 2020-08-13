<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

// if (isset($_POST['servicio'])) {
// 	$servicio=$_POST['servicio'];
// 	if (isset($_POST['orderby']) && isset($_POST['grupo'] )) {
// 		$orden =$_POST['orderby'];
// 		$grupo =$_POST['grupo'];		
// 		$query="SELECT * from  MInventario where prod_serv='$servicio' and activo = 1 and cod_grupo='$grupo' order by '$orden' ";		

	// }elseif (isset($_POST['orderby'])  ) {
	// 	$orden =$_POST['orderby'];		
		$query="select * from MInventario where Prod_serv in('s','c','f')  and cod_grupo='004' and cod_subgrupo IN ('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and activo=1 order by orden, desitems ";			
// 	}else{
// 		$query="SELECT * from  MInventario where prod_serv='$servicio' and activo = 1";
// 		$query="SELECT * from  MInventario where prod_serv IN ('S','M') and activo = 1 and cod_grupo='004' OR  coditems ='TMAG01' order by 'desitems' desc";
// 		$query="SELECT * from  MInventario where prod_serv IN ('M') and activo = 1 and cod_grupo='004' OR  coditems ='TMAG01' order by 'desitems' desc";
// 	}	
// }else{
//    $query="SELECT * from  MInventario where prod_serv='S' and activo = 1 and cod_grupo in('003','002') ";	
// }

$estados = mssqlConn::Listados($query);
echo $estados;

