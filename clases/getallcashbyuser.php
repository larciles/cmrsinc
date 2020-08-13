<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$tiporeporte='false';
$fecha   = $_POST['fecha'];
$usuario = $_POST['usuario'];
if (isset($_POST['tiporeporte'])) {
   $tiporeporte= $_POST['tiporeporte'];
}
$id_centro = $_POST['id_centro'];


$query="SELECT * FROM Cuadre WHERE fechA='$fecha' and id_centro='$id_centro' ";
$query="SELECT usuario,sum(monto*valor) monto from Cuadre where fechA='$fecha' and Id_centro='$id_centro' group by usuario";
if ($tiporeporte=='true') {
    $query="SELECT usuario,sum(monto*valor) monto FROM Cuadre WHERE fechA='$fecha' and id_centro='$id_centro' and usuario='$usuario' group by usuario ";	
}



$res = mssqlConn::Listados($query);
echo $res;

// function CuadreGeneral($fecha,$id_centro){
// 	if ($id_centro=='C') {
// 		$query="SELECT C.usuario,sum(C.monto*C.valor) monto,max(D.venta)  venta
// 		FROM Cuadre C 
// 		left join (Select A.usuario, Sum( A.total ) venta from VIEW_CMA_Mfactura_2 A 
//  		inner join Mpagos B On A.numfactu=B.numfactu And A.usuario=B.usuario  and codforpa=1
//  		where A.fechafac='$fecha' and A.cod_subgrupo='CONSULTA' and A.statfact<>'2' group by A.usuario
// 		) D On D.usuario=C.usuario
// 		WHERE C.fechA='$fecha' and C.id_centro='$id_centro'  group by C.usuario , D.usuario";
// 	} else {
// 		# code...
// 	}
	
	
// }


// function CuadreUsuario($fecha,$id_centro,$usuario){
// 	if ($id_centro=='C') {
// 		$query="SELECT C.usuario,sum(C.monto*C.valor) monto,max(D.venta)  venta
// 		FROM Cuadre C 
// 		left join (Select A.usuario, Sum( A.total ) venta from VIEW_CMA_Mfactura_2 A 
//  		inner join Mpagos B On A.numfactu=B.numfactu And A.usuario=B.usuario  and codforpa=1
//  		where A.fechafac='$fecha' and A.cod_subgrupo='CONSULTA' and A.statfact<>'2' group by A.usuario
// 		) D On D.usuario=C.usuario
// 		WHERE C.fechA='$fecha' and C.id_centro='$id_centro' and C.usuario='$usuario'  group by C.usuario , D.usuario";
// 	} else {
// 		# code...
// 	}
	
	
//}
