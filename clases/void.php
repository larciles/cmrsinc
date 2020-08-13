<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
require_once '../db/mssqlconn.php';
require('../controllers/MovimientoController.php');
require('../controllers/KitController.php');

$dbmsql = mssqlConn::getConnection();

$numfactu    = ltrim(rtrim($_POST['numfactu']));
$desanul     = ltrim(rtrim($_POST['razonanulacion']));
$idempresa   = ltrim(rtrim($_POST['idempresa']));
$fechanul    = date("Y-m-d");

if ($idempresa=='2') {
	$masterdb='cma_MFactura';
} else if ($idempresa=='1') {
	$masterdb='MFactura';
}else if ($idempresa=='3') {
	$masterdb='MSSMFact';
}

$query="SELECT * From  ".$masterdb." Where numfactu = '$numfactu'  ";
$result = mssqlConn::Listados($query);
$obj = json_decode($result, true);
$lenObj = sizeof($obj); 
if ($lenObj >0) {
	$statfact =$obj[0]['statfact'];
	$fechanul =$obj[0]['fechafac'];
	if ($statfact!='2') {

		$query="UPDATE ".$masterdb." SET statfact='2', fechanul='$fechanul',desanul='$desanul' WHERE numfactu='$numfactu' and statfact in('1','3') ";
		$result = mssqlConn::insert($query);

 		if ($idempresa=='1' || $idempresa=='3'  || $idempresa=='2') {
			searchDetails($numfactu, $idempresa);
		}

	}
}


function searchDetails($numfactu,$idempresa){
	$cantidad=0;
	if ($idempresa=='1') {
		$query="SELECT * From  DFactura  Where numfactu = '$numfactu'  ";
	} elseif ($idempresa=='3') {
		$query="SELECT * From  MSSDFact  Where numfactu = '$numfactu'  ";
	}elseif ($idempresa=='2') {
		$query="SELECT * From  cma_DFactura  Where numfactu = '$numfactu'  ";
	}
	
	
	$result = mssqlConn::Listados($query);
	$obj = json_decode($result, true);
	$lenObj = sizeof($obj); 	
	if ($lenObj >0) {
		for ($i=0; $i <$lenObj ; $i++) { 
			$cantidad =$obj[$i]['cantidad'];	
			$coditems =$obj[$i]['coditems'];
			if ($idempresa=='1') {	
				inventoryUpdate($coditems,$cantidad);
			}elseif ($idempresa=='3' || $idempresa=='2'){
				findKit($coditems,$cantidad);
				if ( $idempresa=='2') {
					fixKit($coditems,$cantidad,'del',$numfactu);
				}
				
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
      $cantidad= $qty;
      if (!is_null( $dis ) ) {
         $cantidad=$cantidad*$dis;
      }
      $codikit= $result[$i]['codikit'];

      inventoryUpdate($codikit,$cantidad);
  }
}

//------------------------------------------------------------------

 function fixKit($coditems,$qty,$option,$invoiceNumber){
    $kitcontroller = new KitController();
    $movimientoController = new MovimientoController();

    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    for ($i=0; $i <$len ; $i++) { 
        $dis= $kit[$i]['disminuir'];
        if (!is_null( $dis ) ) {
            $qty=$qty*$dis;
        }
        $codikit= $kit[$i]['codikit'];
        
        if ($option=='del') {             
            
              $where_data = array(
                  'coditems' => $codikit,
                  'numfactu' => $invoiceNumber,
                  'tipo'=>'01',
                  'empresa'=>'02'
              );

               $array_edit = array(                              
                  'where' => $where_data
              );
              $movimientoController->delete($array_edit); 
           # code...
         }else{
          $movimientoController->create(array('coditems' =>$codikit ,'cantidad' =>$qty ,   'numfactu' =>$invoiceNumber ,'tipo'=>'01' ,'empresa'=>'02'    )) ;

         } 

    }
}

//------------------------------------------------------------------

function inventoryUpdate($coditems,$qty){
  		$query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  		mssqlConn::insert($query);
}

echo $result;