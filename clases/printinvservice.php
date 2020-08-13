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
//$usuario = $_POST['user'];

if (isset($_POST['times'])) {
	$times = $_POST['times'];
}else{
	$times=1;
}

$service = $_POST['service'];

$query="SELECT   a.numfactu,  CONVERT(VARCHAR(10),a.fechafac,101) fechafac,  a.descuento, a.subtotal, a.total, a.statfact, a.usuario, a.codseguro, c.cantidad, c.precunit, b.direccionH, b.Historia, b.nombres, e.seguro, d.desitems, f.TipoDePago "
         . "FROM  CMA_MFactura a 	 "
         . "INNER JOIN   MClientes   b ON  a.codclien = b.codclien     "
         . "INNER JOIN  CMA_DFactura  c ON  a.numfactu = c.numfactu    "
         . "LEFT OUTER JOIN  mseguros e ON  a.codseguro = e.codseguro  "
         . "INNER JOIN  MInventario   d ON  c.coditems = d.coditems    "
         . "LEFT OUTER JOIN  viewTipoPagoServicios f ON   c.numfactu = f.numfactu "
         . "Where a.numfactu='$numfactu' ";

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);

if(sizeof($resP)>0){} 
for ($t=0; $t <$times ; $t++) { 
	try {
		// Enter the share name for your USB printer here
		$connector = new WindowsPrintConnector("smb://".$pathprn);
		$printer = new Printer($connector);


		$nfact = $resP[0]['numfactu'];
		$printer -> setEmphasis(true);
		$printer -> text("           CENTRO DE MEDICINA REGENERATIVA\n");
		$printer -> text("      Av Dr Veve #51 Esq Calle Marti Bayamon PR\n");
		$printer -> text("            00961 Phone 787-780-7575\n");
		$printer -> text("Invoice # ".$resP[0]['numfactu']."\n");
		$printer -> setEmphasis(false);
		$printer -> text("Patien or Resposible Part    ".$resP[0]['fechafac']);
		$printer -> feed();
		$printer -> setEmphasis(true);
		$printer -> text($resP[0]['nombres']);
		$printer -> setEmphasis(false);
		$printer -> feed();
		$printer -> text("Record ".$resP[0]['Historia']." ");
		$printer -> feed();
		$printer -> text(rtrim( ltrim( $resP[0]['direccionH'])));
		$printer -> feed();
		$printer -> text("Description                Rate   Qty Total\n");
		$printer -> text("--------------------------------------------\n");
	                       
	    $lenA =sizeof($resP);
	    
		for ($i=0; $i <$lenA ; $i++) { 
			$printer -> feed();
			$str = substr(ltrim(rtrim($resP[$i]['desitems'])),0,25);
			$longitud = strlen(substr(ltrim(rtrim($resP[$i]['desitems'])),0,25));
			if ($longitud<26) {
				$str =  str_pad($str,26-$longitud," ",STR_PAD_RIGHT);  
			}
			
			$printer -> text( $str  /*substr(ltrim(rtrim($resP[$i]['desitems'])),0,25)*/   );		
			$printer -> text("   ".number_format((float)$resP[$i]['precunit'], 2, '.', ''));		
			$printer -> text("    ".$resP[$i]['cantidad']);
			$printer -> text("  ".number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''));
		   	$test=($resP[$i]['cantidad']*$resP[$i]['precunit']);
		}
	    $printer -> feed();
		$printer -> text("============================================");
		$printer -> feed();
		$printer -> text("                             Total $ ".number_format((float)$resP[0]['subtotal'],   2, '.', ''));
		$printer -> feed();
		if ($resP[0]['descuento']!='0') {
		   $printer -> text("                  Payments/Credits $ ".number_format((float)$resP[0]['descuento'], 2, '.', ''));
		}
		$printer -> feed();
		$printer -> text("                      Balance Due  $ ".number_format(0,      2, '.', ''));
		$printer -> feed();	
		$printer -> text(" Payment Methods : ".$resP[0]['TipoDePago']);	

		$printer -> feed();	
		$printer -> text("         ".strtoupper($resP[0]['usuario']));	

		$printer -> feed();	
		$printer -> text("      www.centromedicoadaptogeno.com");	
		
		$printer -> feed();
		$printer -> text("Thank you for the opportunity to serve");
		$printer -> feed();		
		$printer -> text(" you. Please call us if you have any");	
		$printer -> feed();
		$printer -> text("       questions. (787) 780-7575");	
		  

		/* Bar-code at the end */
		$printer -> setJustification(Printer::JUSTIFY_CENTER);
		$printer -> barcode($nfact);
		$printer -> feed();
		$printer -> feed();
		$printer -> feed();
		$printer -> feed();	
		$printer -> feed();	
		if ($service == "SUEROTERAPIA") {
			$printer ->cut();
		}else{
			//
			try {
				$printer ->cut();	
			} catch (Exception $e) {
				try {
					$printer ->cutS();			
				} catch (Exception $e) {
					
				}
			  

			}
			
		}
	        
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