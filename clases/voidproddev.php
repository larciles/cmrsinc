<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
require_once '../db/mssqlconn.php';

require('../controllers/MInventarioController.php');


$dbmsql = mssqlConn::getConnection();

$numfactu    = ltrim(rtrim($_POST['numfactu']));
$desanul     = ltrim(rtrim($_POST['razonanulacion']));
$idempresa   = ltrim(rtrim($_POST['idempresa']));
$fechanul    = date("Y-m-d");

if ($idempresa=='2') {
	$masterdb='CMA_Mnotacredito';
} else if ($idempresa=='1') {
	$masterdb='Mnotacredito';
	$detaildb='Dnotacredito';
}else if ($idempresa=='3') {
 	$masterdb='MSSMDev';
	$detaildb='MSSDDev';
}

$query="SELECT * From  ".$masterdb." Where numnotcre = '$numfactu'  ";
$result = mssqlConn::Listados($query);
$obj = json_decode($result, true);
$lenObj = sizeof($obj); 
if ($lenObj >0) {
	$statfact =$obj[0]['statnc'];
	if ($statfact!='2') {

		$query="UPDATE ".$masterdb." SET statnc='2', fechanul='$fechanul',concepto='$desanul',numfactu=''  WHERE numnotcre='$numfactu' and statnc in('1','3') ";
		$result = mssqlConn::insert($query);

 		if ($idempresa=='1' || $idempresa=='3') {
			searchDetails($numfactu,$detaildb,$idempresa);
		}

	}
}


function searchDetails($numfactu,$detaildb,$idempresa){
	$cantidad=0;
	$query="SELECT * From  ".$detaildb."  Where numnotcre = '$numfactu'  ";
	$result = mssqlConn::Listados($query);
	$obj = json_decode($result, true);
	$lenObj = sizeof($obj); 	
	if ($lenObj >0) {
		for ($i=0; $i <$lenObj ; $i++) { 
			$cantidad =$obj[$i]['cantidad'];	
			$coditems =$obj[$i]['coditems'];	

			$prodInfo    = getProductsData($coditems);

			$kit         = $prodInfo['kit'];

            if ($kit=='1') {
                findKit($coditems,$cantidad);
            }else{
                inventoryUpdate($coditems,$cantidad );
            }
			
		}					
	}

}



function findKit($coditems,$qty){
  $query="SELECT * from kit where coditems='$coditems' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  for ($i=0; $i <$len ; $i++) { 
  	  $dis= $result[$i]['disminuir'];
      $cantidad=$qty;
      if (!is_null( $dis ) ) {
         $cantidad=$cantidad*$dis;
      }
      $codikit= $result[$i]['codikit'];
      inventoryUpdate($codikit,$cantidad);
  }
}

function inventoryUpdate($coditems,$qty){
  		$query="UPDATE MInventario SET  existencia = existencia - $qty Where coditems ='$coditems' ";
  		mssqlConn::insert($query);
 }


 function getProductsData($coditems){
  $minventariocontroller = new MInventarioController();
  $array;

  $query="SELECT * from  MInventario where coditems='$coditems' ";
  $invetario=$minventariocontroller->readUDF($query);

  $len = sizeof($invetario);
  if($len>0){ 
    $array['aplicaiva']    = $invetario[0]['aplicaiva'];
    $array['aplicadcto']   = $invetario[0]['aplicadcto'];
    $array['aplicacommed'] = $invetario[0]['aplicacommed'];
    $array['aplicacomtec'] = $invetario[0]['aplicacomtec'];
    $array['costo']        = $invetario[0]['costo'];
    $array['kit']          = $invetario[0]['kit'];
    $array['cod_subgrupo'] = $invetario[0]['cod_subgrupo'];
    $array['cod_grupo']    = $invetario[0]['cod_grupo'];
  }

  return  $array;
}
echo $result;