<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$prod_serv = $_POST['prod_serv'];
$orderby = $_POST['orderby'];

// if (isset($_POST['notin'])) {
// 	$longNotIn=count($_POST['notin']) ;
// 	for ($i=0; $i <$longNotIn ; $i++) { 
                
// 		if ($i==$longNotIn-1) {                    
// 			$notin=$notin."'".$_POST['notin'][$i]."'";
// 		} else {
// 			$notin=$notin."'".$_POST['notin'][$i]."'".',';
// 		}		
// 	}
// 	$query="SELECT * from  MInventario where prod_serv='$prod_serv' and activo = 1 and coditems not in ($notin) order by '$orderby'";
// }else{
	$query="SELECT * from  MInventario where prod_serv='$prod_serv' and activo = 1 order by '$orderby'";
//}



$estados = mssqlConn::Listados($query);
echo $estados;

