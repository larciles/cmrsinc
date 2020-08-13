<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();



if (isset($_POST['action']) && !empty($_POST['action'])){
    $action = $_POST['action'];
    //$value = (isset($_POST['value']) && !empty($_POST['value']))? $_POST['value'] : null;
    
    $upInvoice = new UpdateInvoice();
    $numfactu     = $_POST['numfactu'];
    $coditems     = $_POST['coditems'];
    $cantidad     = $_POST['cantidad'];
    if ($action=='delete') {
    	
        $qty = $upInvoice::searchDetails($numfactu,$coditems);   // BUSCO EN EL DETALLE DE  LA FACTURA PARA DEVOLVER AL  INVENTARIO LA CANTIDAD EXISTENTE

        if ( $qty >0) {
            $xx= $upInvoice->findKit($coditems,$qty,$numfactu);
        }
        // $qty =  $qty*-1;                                         // LA MULTIPLICO POR -1 PARA SUMAR AL INVENTARIO
        // $upInvoice::inventoryUpdate($coditems,$qty);             // ACTUALIZO EL INVENTARIO 
        // $upInvoice::deleteRecord($coditems,$numfactu);   

    }
}



/**
* 
*/
class UpdateInvoice
{
	public static function updateDetails($numfactu,$coditems,$cantidad,$codtipre,$aplicaiva,$precunit,$aplicadcto,$aplicacommed,$aplicacomtec,$costo,$dosis,$cant_sugerida,$descuento,$monto_imp){
      
    	$query="UPDATE  DFactura SET coditems = '$coditems',cantidad = '$cantidad',codtipre = '$codtipre',aplicaiva = '$aplicaiva',precunit = '$precunit',aplicadcto = '$aplicadcto',aplicacommed = '$aplicacommed',aplicacomtec = '$aplicacomtec',costo = '$costo',dosis = '$dosis',cant_sugerida = '$cant_sugerida',descuento = '$descuento',monto_imp = '$monto_imp' WHERE numfactu = '$numfactu' and coditems = '$coditems' "; 
	}

	public static function searchDetails($numfactu,$coditems){
		$cantidad=0;
		$query="SELECT * From  DFactura  Where coditems ='$coditems' and numfactu = '$numfactu'  ";
 		$result = mssqlConn::Listados($query);
 		$obj = json_decode($result, true);
 		$lenObj = sizeof($obj); 	
 		if ($lenObj >0) {
 			$cantidad =$obj[0]['cantidad'];			
 		}
 		return $cantidad;
	}

	public static function inventoryUpdate($coditems,$qty){
  		$query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  		mssqlConn::insert($query);
 	}

 	public static function deleteRecord($coditems,$numfactu){
 		$query="DELETE FROM DFactura WHERE coditems ='$coditems' AND numfactu='$numfactu'  ";
 		mssqlConn::insert($query);
 	}

    public static function insertUpdate(){
        $query="INSERT INTO DFactura   (numfactu,fechafac,fecreg,horareg,coditems,codseguro,codtipre,aplicaiva,aplicadcto,aplicacommed,aplicacomtec,costo,dosis,cant_sugerida,cantidad,precunit,descuento,monto_imp,workstation,ipaddress,usuario,Codmedico ) "
        . "                VALUES  ('$invoiceNumber','$fechafac','$fechafac','$hora','$coditems','$codseguro','$codtipre','$aplicaiva','$aplicadcto','$aplicacommed','$aplicacomtec','$costo','$dosis','$cant_sugerida','$cantidad','$precunit','$descuento','$monto_imp','$workstation','$ipaddress','$usuario','$codmedico' )";
        mssqlConn::insert($query);        

    }


    public static function findKit($coditems,$qty,$numfactu){
        $query="SELECT * from kit where coditems='$coditems' ";
        $res = mssqlConn::Listados($query);
        $result = json_decode($res, true);
        $len = sizeof($result);
        if ($len>0) {
            for ($i=0; $i <$len ; $i++) { 
                $codikit= $result[$i]['codikit'];
                self::inventoryUpdate($codikit,$qty);
            }
        }else{
            self::inventoryUpdate($coditems,$qty);
        }


        self::deleteRecord($coditems,$numfactu); 
    }
}


?>