<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
session_start();
date_default_timezone_set("America/Puerto_Rico");
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}

require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();


$insert  = $_POST['insert'];

//MINVENTARIO
$inventariable =0;
$coditems     = $_POST['coditems'];
$desitems     = $_POST['desitems'];
$activo       = $_POST['activo'];
$Exisminima   = $_POST['Exisminima'];
$Exismaxima   = $_POST['Exismaxima'];   
$Prod_serv    = $_POST['Prod_serv'];
$aplicaIva    = $_POST['aplicaIva'];
$aplicadcto   = $_POST['aplicadcto'];
$aplicaComMed = $_POST['aplicaComMed'] ;
$aplicaComTec = $_POST['aplicaComTec'];
$cod_subgrupo = $_POST['cod_subgrupo'];
$codgrupo     = $_POST['codgrupo'];
$costo        = $_POST['costo'];  
$nombre_alterno = $_POST['nombre_alterno']; 

if(isset($_POST['inventariable'])) {
	$inventariable  = $_POST['inventariable']; 
}

//MPRECIOS
$codtipre    = $_POST['codtipre'];
$precunit    = $_POST['precunit'];
$sugerido    = $_POST['sugerido'];
//NTPRODUCTOS
$CapsulasXUni = $_POST['CapsulasXUni'];  
$CapsulasXUni = is_null($_POST['CapsulasXUni']) ? '0': $_POST['CapsulasXUni'];  

if ($insert=='false') {
  Updates($coditems,$desitems,$activo,$Exisminima,$Exismaxima,$Prod_serv,$aplicaIva,$aplicadcto,$aplicaComMed,$aplicaComTec,$cod_subgrupo,$costo,$nombre_alterno,$codtipre,$precunit,$sugerido,$CapsulasXUni,$codgrupo,$inventariable);
}else{
  Inserts($coditems,$desitems,$activo,$Exisminima,$Exismaxima,$Prod_serv,$aplicaIva,$aplicadcto,$aplicaComMed,$aplicaComTec,$cod_subgrupo,$costo,$nombre_alterno,$codtipre,$precunit,$sugerido,$CapsulasXUni,$codgrupo,$inventariable);
}


echo $result;


function Updates($coditems,$desitems,$activo,$Exisminima,$Exismaxima,$Prod_serv,$aplicaIva,$aplicadcto,$aplicaComMed,$aplicaComTec,$cod_subgrupo,$costo,$nombre_alterno,$codtipre,$precunit,$sugerido,$CapsulasXUni,$codgrupo,$inventariable){
	$query="UPDATE MInventario Set 
	desitems     = '$desitems',
	activo       = '$activo',
	Exisminima   = '$Exisminima',
	Exismaxima   = '$Exismaxima',
	Prod_serv    = '$Prod_serv',
	aplicaIva    = '$aplicaIva',
	aplicadcto   = '$aplicadcto',
	aplicaComMed = '$aplicaComMed', 
	aplicaComTec = '$aplicaComTec',
	cod_subgrupo = '$cod_subgrupo',
	cod_grupo    = '$codgrupo',
	costo        = '$costo',
	Inventariable = $inventariable,
	nombre_alterno = '$nombre_alterno' Where coditems = '$coditems' ";
	$result = mssqlConn::insert($query);

	$query="UPDATE NTPRODUCTOS Set
	Nombre = '$desitems',
	CapsulasXUni = '$CapsulasXUni' Where Cod_prod = '$coditems' ";  
	$result = mssqlConn::insert($query);

	$query="SELECT * FROM MPrecios Where coditems='$coditems' AND  codtipre='$codtipre'  ";     
    $res = mssqlConn::Listados($query);
    $result = json_decode($res, true);
    $len = sizeof($result);
    if($len>0){
	   $query="UPDATE MPrecios Set
	    precunit    = '$precunit',
		sugerido    = '$sugerido',
		activo      = '$activo'  Where coditems='$coditems' AND  codtipre='$codtipre'";
		$result = mssqlConn::insert($query);
    }else{
    	$query="INSERT INTO MPrecios 
		(coditems,codtipre,precunit,sugerido,activo)
		VALUES('$coditems','$codtipre','$precunit','$sugerido','$activo')";
		$result = mssqlConn::insert($query);
    }

	return $result;
}

function Inserts($coditems,$desitems,$activo,$Exisminima,$Exismaxima,$Prod_serv,$aplicaIva,$aplicadcto,$aplicaComMed,$aplicaComTec,$cod_subgrupo,$costo,$nombre_alterno,$codtipre,$precunit,$sugerido,$CapsulasXUni,$codgrupo,$inventariable){
    $fecha=date("Y-m-d");
	$query="INSERT INTO MInventario
	(coditems,desitems,activo,Exisminima,Exismaxima,Prod_serv,aplicaIva,aplicadcto,aplicaComMed,aplicaComTec,cod_subgrupo,costo,nombre_alterno,fecing,cod_grupo,Inventariable )
	VALUES('$coditems','$desitems','$activo','$Exisminima','$Exismaxima','$Prod_serv','$aplicaIva','$aplicadcto','$aplicaComMed','$aplicaComTec','$cod_subgrupo','$costo','$nombre_alterno','$fecha','$codgrupo','$inventariable') ";
	$result = mssqlConn::insert($query);

	//MPRECIOS
	$query="INSERT INTO MPrecios 
	(coditems,codtipre,precunit,sugerido,activo)
	VALUES('$coditems','$codtipre','$precunit','$sugerido','$activo')";
	$result = mssqlConn::insert($query);

	//NTPRODUCTOS
	$query="INSERT INTO NTPRODUCTOS 
	(Cod_prod ,Nombre,CapsulasXUni)
	VALUES('$coditems','$desitems','$CapsulasXUni')";
	$result = mssqlConn::insert($query);

	 return $result ;
}