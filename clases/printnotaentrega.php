<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';
require '../escpos/autoload.php';
date_default_timezone_set('America/Puerto_Rico');
//require __DIR__ . '/escpos/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$dbmsql = mssqlConn::getConnection();

$numfactu = $_POST['numfactu'];

if (isset($_POST['times'])) {
	$times = $_POST['times'];
}else{
	$times=1;
}

$query="SELECT   a.numfactu,  CONVERT(VARCHAR(10),a.fechafac,101) fechafac,a.descuento,a.Alicuota, a.subtotal, a.total, a.statfact, a.usuario, a.codseguro, c.cantidad, c.precunit, b.direccionH, b.telfhabit, b.Historia, b.nombres, e.seguro, d.desitems,d.nombre_alterno, f.modopago "
         . "FROM  MSSMFact a 	 "
         . "INNER JOIN  MClientes b ON  a.codclien = b.codclien    "
         . "INNER JOIN  MSSDFact  c ON  a.numfactu = c.numfactu    "
         . "LEFT OUTER JOIN  mseguros e ON  a.codseguro = e.codseguro  "
         . "INNER JOIN  MInventario   d ON  c.coditems = d.coditems    "
         . "LEFT OUTER JOIN  viewTipoPagoLaser f ON c.numfactu = f.numfactu "
         . "Where a.numfactu='$numfactu' ";

$query = " SELECT 
         a.numnotent numfactu, CONVERT(VARCHAR(10),a.fechanot,101) fechafac,  0 subtotal, 0 descuento, 0 total, a.statunot statfact, a.desanul, '' codseguro, 0 TotImpuesto, 0 Alicuota, 0 monto_flete,a.usuario, 
	     c.cantidad, 0 precunit, 0 descuentod, 
	     b.Cedula, b.direccionH, b.nombres, b.telfhabit,b.Historia,
	     '' nombre, '' apellido,    
	     d.pago, 
	     f.desitems, f.nombre_alterno 
		 FROM  
	     NotaEntrega a 
	     INNER JOIN MClientes b ON 
	     a.codclien = b.codclien 
	     INNER JOIN NotEntDetalle c ON 
	     a.numnotent = c.numnotent 
	     LEFT OUTER JOIN  VIEWPagoTotal d ON 
	     a.numnotent = d.numfactu 	     
	     INNER JOIN  MInventario f ON 
	     c.coditems = f.coditems 
		 WHERE 
	     a.numnotent = '$numfactu' 
		 ORDER BY  
	     f.nombre_alterno ASC ";         

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);

 if(sizeof($resP)>0){
 	#impuestos
 	// $query="SELECT *  from  VIEW_ImpxFact where numfactu='$numfactu'";
 	// $result = mssqlConn::Listados($query);
 	// $impuestos = json_decode($result, true);
 	#modo pago
 	// $query="SELECT *  from  VIEWPAGOSFAC_W7 where numfactu='$numfactu' and id_centro='1' and tipo_doc='01' and monto>0";
 	// $result = mssqlConn::Listados($query);
 	// $modopago = json_decode($result, true);
 } 
 	for ($t=0; $t <$times ; $t++) { 


		try {
			// Enter the share name for your USB printer here
			//$connector = new WindowsPrintConnector("smb://DESKTOP-5MS92AS/EPSONTM-T20II");
			$connector = new WindowsPrintConnector("smb://FARMACIA01/EPSON TM-T20II farmacia01");
			$printer = new Printer($connector);


			$nfact = $resP[0]['numfactu'];
			$printer -> setEmphasis(true);
			$printer -> text("             Nota de Entrega # ".$resP[0]['numfactu']."\n");
			$printer -> text("            MEDICINA SISTEMICA, LLC \n");
			$printer -> text("                MN: 0647913-0012\n");
			$printer -> text(" Av Dr Veve #51 Esq Calle Marti Bayamon PR 00961\n");
		    $printer -> text("Cliente:");
			$printer -> setEmphasis(false);
			$printer -> text("                             ".$resP[0]['fechafac']);	
			$printer -> setEmphasis(true);
			$printer -> feed();
			$printer -> text($resP[0]['nombres']);
			$printer -> setEmphasis(false);
			$printer -> feed();
			$printer -> text(substr($resP[0]['telfhabit'],0,12). "        R-# ".$resP[0]['Historia'] );
			$printer -> feed();
			$printer -> text("Cajero: ".$resP[0]['usuario']);		
			//$printer -> text();
			//$printer -> feed();
			//$printer -> text($resP[0]['direccionH']);
			$printer -> feed();
			$printer -> text("Descripcion                           Cantidad \n");
			$printer -> text("-----------------------------------------------\n");
		                       
		    $lenA =sizeof($resP);
		    $totalProd =0; 
			for ($i=0; $i <$lenA ; $i++) { 
				if ($i>0) {
					$printer -> feed();
				}
				$na=$resP[$i]['nombre_alterno'];
				if($resP[$i]['nombre_alterno']!==""){
		                    $descripcion = str_pad(substr($resP[$i]['nombre_alterno'],0,18), 18);					
				}else{
		                    $descripcion = str_pad(substr($resP[$i]['desitems'],0,18), 18);					
				}
		        if( strlen($descripcion)<26){
		            $can =18-strlen($descripcion);   
		            $descripcion =   str_pad($descripcion,$can," ");
		        }
		              
		        $test=strlen($descripcion);
		               
		                
				$printer -> text($descripcion);		
				
				$printer -> text("                       ".str_pad($resP[$i]['cantidad'], 2, " ", STR_PAD_LEFT));
				// $printer -> text(" ".str_pad(number_format((float)$resP[$i]['precunit'], 2, '.', ''), 8, " ", STR_PAD_LEFT));				
				// $printer -> text(" ".str_pad(number_format((float)$resP[$i]['descuentod'], 2, '.', ''), 8, " ", STR_PAD_LEFT));		
				
				// if ($resP[$i]['descuento']==0){
				// 	$printer -> text(" ".str_pad(number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''), 8, " ", STR_PAD_LEFT));					
				// } else{
				// 	$printer -> text(" ".str_pad(number_format((float)( $resP[$i]['cantidad']*$resP[$i]['precunit'])-$resP[$i]['descuentod'] , 2, '.', ''), 8, " ", STR_PAD_LEFT));					
				// }						
			 //   	$test=($resP[$i]['cantidad']*$resP[$i]['precunit']);
				$totalProd=$totalProd+(int)$resP[$i]['cantidad'];
			   	
			}
		    $printer -> feed();
			$printer -> text("================================================");
			
			$printer -> feed();
			// $printer -> text(str_pad('Subtotal $ ', 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['subtotal'], 2, '.', ''), 8, " ", STR_PAD_LEFT));
            $printer -> text(str_pad('Cant Prod ', 40, " ", STR_PAD_LEFT).str_pad($totalProd, 2, " ", STR_PAD_LEFT));
			
			
			// $discount =(int)$resP[0]['descuento'];
			// if($discount!==0){
			// 	$printer -> feed();
			// 	$printer -> text("                     Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''));
			// }
			
			
			// $lenimp =sizeof($impuestos);
		    
			// for ($i=0; $i <$lenimp ; $i++) { 
			// 	$printer -> feed();

			// 	$printer -> text(str_pad($impuestos[$i]['Descripcion'], 39, " ", STR_PAD_LEFT)." $".str_pad(number_format((float)$impuestos[$i]['montoimp'], 2, '.', ''), 7, " ", STR_PAD_LEFT));
			// }

			// $flete = (int)$resP[0]['monto_flete'];

			// if ($flete !==0) {
			// 		$printer -> feed();
			// 		$printer -> text(str_pad("Envio $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['monto_flete'], 2, '.', ''), 8, " ", STR_PAD_LEFT));			
			// }	
			
			
			$printer -> feed();

			// $printer -> text(str_pad("Total  $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['total'], 2, '.', ''),8, " ", STR_PAD_LEFT));
			// $printer -> feed();	
			// $printer -> text(" Forma de Pago : ");

			// $lenmodpag =sizeof($modopago);
			// for ($i=0; $i <$lenmodpag ; $i++) { 
			// 		$printer -> feed();
			// 		$printer -> text($modopago[$i]['modopago']." ".number_format((float)$modopago[$i]['monto'], 2, '.', ''));
			// }
				

			$printer -> feed();	
			
            	$printer -> text(date("F j, Y, g:i a")."\n");	
 

			$printer -> feed();	
			// $printer -> text("              Gracias por su compra\n");	
			// $printer -> text("   No se aceptan devoluciones después de 24 Hrs\n");	
			//$printer -> text("            después de 72 Hrs\n");	
			// $printer -> text("         Pida su Cita al (787) 780-7575\n");	
			$printer -> text("         www.centromedicoadaptogeno.com\n");	
			// $printer -> text("    ORDEN SU REFILL AL 787 780-7676 Y LE SERA\n");	  
			// $printer -> text("                ENVIADO POR CORREO\n");	  

			/* Bar-code at the end */
			$printer -> setJustification(Printer::JUSTIFY_CENTER);
			// $printer -> barcode($nfact);
			$printer -> feed();
			$printer -> feed();
			$printer -> feed();
		        $printer ->cut();
			/* Close printer */
			$printer -> close();
		} catch(Exception $e) {
			echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
		}
	}


 if(sizeof($resP)>0){

 	$query = "SELECT * from  NotaEntrega where numnotent='$numfactu'";
 	$result = mssqlConn::Listados($query);
 	$stat = json_decode($result, true);
 	if(sizeof($stat)>0){
 		if ($stat[0]['statfact']=='1' ){
 			$query = "UPDATE NotaEntrega Set statunot='3' where numnotent='$numfactu'";
 			$result = mssqlConn::insert($query);
 		}
 	}

}



/* ASCII constants */








//$query="UPDATE MClientes Set  nombres='$nombres' where codclien='$codclien' ";
//$result = mssqlConn::insert($query);

//echo $result;
//printinvservice.php