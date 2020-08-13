<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';
require '../escpos/autoload.php';

//require __DIR__ . '/escpos/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");    
    return;
}else{
    $usuario=$_SESSION['username'];
    $codperfil=$_SESSION['controlcita'];
    $prninvoice=$_SESSION['prninvoice'];
	$autoposprn=$_SESSION['autoposprn'];
	$pathprn=$_SESSION['pathprn'];
}
$dbmsql = mssqlConn::getConnection();

$numfactu = $_POST['numfactu'];
$usuario = $_POST['user'];
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

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);

 if(sizeof($resP)>0){} 
 	for ($t=0; $t <$times ; $t++) { 


		try {
			// Enter the share name for your USB printer here
			//$connector = new WindowsPrintConnector("smb://Laserpc2/EPSON TM-T20II Laserpc2");
    		$connector = new WindowsPrintConnector("smb://".$pathprn);	
			$printer = new Printer($connector);


			$nfact = $resP[0]['numfactu'];
			$printer -> setEmphasis(true);
			$printer -> text("               Factura # ".$resP[0]['numfactu']."\n");
			$printer -> text("            MEDICINA SISTEMICA, LLC \n");
			$printer -> text("                    servicios \n");	
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
			$printer -> text(substr($resP[0]['telfhabit'],0,12). "        Record # ".$resP[0]['Historia'] );
			$printer -> feed();
			$printer -> text("Cajero: ".$resP[0]['usuario']);		
			//$printer -> text();
			//$printer -> feed();
			//$printer -> text($resP[0]['direccionH']);
			$printer -> feed();
			$printer -> text("Descripcion               Cant Precio  Subtotal\n");
			$printer -> text("-----------------------------------------------\n");
		                       
		    $lenA =sizeof($resP);
		    
			for ($i=0; $i <$lenA ; $i++) { 
				$printer -> feed();
				
				
				$na=$resP[$i]['nombre_alterno'];
				if($resP[$i]['nombre_alterno']!==""){
		                    $descripcion = substr($resP[$i]['nombre_alterno'],0,26);
					
				}else{
		                    $descripcion = substr($resP[$i]['desitems'],0,26);
					
				}
		        if( strlen($descripcion)<26){
		            $can =26-strlen($descripcion);   
		            $descripcion =   str_pad($descripcion,$can," ");
		        }
		              
		        $test=strlen($descripcion);
		                
		                
				$printer -> text($descripcion);		
				
				$printer -> text("    ".$resP[$i]['cantidad']);
				
				$printer -> text("   ".number_format((float)$resP[$i]['precunit'], 2, '.', ''));		
				
				$printer -> text("  ".number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''));		
			   	$test=($resP[$i]['cantidad']*$resP[$i]['precunit']);
			}
		    $printer -> feed();
			$printer -> text("================================================");
			$printer -> feed();
			$printer -> text("                               Subtotal $ ".number_format((float)$resP[0]['subtotal'], 2, '.', ''));
			$printer -> feed();
			
			$discount =(int)$resP[0]['descuento'];
			if($discount>0){
				$printer -> text("                     Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''));
			}
			$printer -> feed();
			$printer -> text("                                 Total  $ ".number_format((float)$resP[0]['total'], 2, '.', ''));
			$printer -> feed();	
			$printer -> text(" Forma de Pago : ".$resP[0]['modopago']);	


			$printer -> feed();	
			

			$printer -> feed();	
			$printer -> text("              Gracias por su compra\n");	
			$printer -> text("   No se aceptan devoluciones después de 72 Hrs\n");	
			//$printer -> text("            después de 72 Hrs\n");	
			$printer -> text("         Pida su Cita al (787) 780-7575\n");	
			$printer -> text("         www.centromedicoadaptogeno.com\n");	  

			/* Bar-code at the end */
			$printer -> setJustification(Printer::JUSTIFY_CENTER);
			$printer -> barcode($nfact);
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





/* ASCII constants */








//$query="UPDATE MClientes Set  nombres='$nombres' where codclien='$codclien' ";
//$result = mssqlConn::insert($query);

//echo $result;
//printinvservice.php