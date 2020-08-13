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

if (isset($_POST['idempresa'])) {
  $idcentro=$_POST['idempresa'];
}

if ($idcentro=='2') {
    $dbmaster='CMA_Mnotacredito';
    $dbdetails='CMA_Dnotacredito';
    $dbpagos='VIEWPagosDEVCMA';
}else if($idcentro=='3'){    
    $dbmaster='MSSMDev';
    $dbdetails='MSSDDev';
    $dbpagos='VIEWPagosDEVMSS';
}


$query="SELECT "
    ." a.numnotcre "
    .", a.fechanot "
    .", a.subtotal "
    .", a.descuento "
    .", a.totalnot  total"
    .", a.statnc "
    .", a.usuario "
    .", a.concepto "
    .", a.codseguro "
    .", a.TotImpuesto "
    .", a.monto_flete "
    .", a.alicuota "
    .", e.seguro "
    .", e.dirFiscal  "
    .", b.Cedula "
    .", b.direccionH "
    .", b.nombres "
    .", b.telfhabit "
    .", b.Historia    "
    .", c.cantidad "
    .", c.precunit "
    .", d.nombre "
    .", d.apellido "
    .", f.desitems "
    .", f.nombre_alterno "
    ." FROM  ".$dbmaster." a "
    ." INNER JOIN  MClientes        b ON  a.codclien  = b.codclien "
    ." INNER JOIN  ".$dbdetails." c ON  a.numnotcre = c.numnotcre "
    ." INNER JOIN  Mmedicos         d ON  a.codmedico = d.Codmedico "
    ." LEFT OUTER JOIN  mseguros    e ON  a.codseguro = e.codseguro "
    ." INNER JOIN   MInventario     f ON  c.coditems  = f.coditems "
    ." WHERE  a.numnotcre='$numfactu' ";

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);


$query="SELECT * from ".$dbpagos." where id_centro='$idcentro' and numnotcre='$numfactu' ";
$result = mssqlConn::Listados($query);
$respagos = json_decode($result, true);



 for ($i=0; $i <$times ; $i++) { 
 	# code...
 
	 

	try {
		// Enter the share name for your USB printer here
		//$connector = new WindowsPrintConnector("smb://Laserpc2/EPSON TM-T20II Laserpc2");
		//$connector = new WindowsPrintConnector("smb://Laserpc1/laser terapi");  //EPSON TM-T20II Laserpc2
		$connector = new WindowsPrintConnector("smb://".$pathprn);

		$printer = new Printer($connector);


		$nfact = $resP[0]['numnotcre'];
		$printer -> setEmphasis(true);
		$printer -> text("               Devolucion # ".$resP[0]['numnotcre']."\n");
        if ($idcentro=='2') {
        	$printer -> text("                    C M R   \n");
			$printer -> text("         CENTRO DE MEDICINA REGENERATIVA \n");	
        }else if($idcentro=='3'){ 
			$printer -> text("            MEDICINA SISTEMICA, LLC \n");
			$printer -> text("                  servicios \n");	
			$printer -> text("                MN: 0647913-0012\n");
		 }
		$printer -> text(" Av Dr Veve #51 Esq Calle Marti Bayamon PR 00961\n");
	    $printer -> text("Cliente:");
		$printer -> setEmphasis(false);
		$printer -> text("                             ".$resP[0]['fechanot']);	
		$printer -> setEmphasis(true);
		$printer -> feed();
		$printer -> text($resP[0]['nombres']);
		$printer -> setEmphasis(false);
		$printer -> feed();
		$printer -> text($resP[0]['telfhabit']);
		$printer -> feed();
		$printer -> text("Cajero: ".$resP[0]['usuario'] . "            Record # ".$resP[0]['Historia']);		
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
	                        
			$printer -> text("      ".$resP[$i]['cantidad']);
			
			$printer -> text("     ".number_format((float)$resP[$i]['precunit'], 2, '.', ''));		
			
			$printer -> text("    ".number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''));		
		   	$test=($resP[$i]['cantidad']*$resP[$i]['precunit']);
		}
	    $printer -> feed();
		$printer -> text("================================================");
		$printer -> feed();
	        $st=$resP[0]['subtotal'];
	        $st=(float)$st;
		$printer -> text("                               Subtotal $ ".number_format((float)$resP[0]['subtotal'], 2, '.', ''));
		$printer -> feed();
		
		$discount =(int)$resP[0]['descuento'];
		//if($discount!==0){
			$printer -> text("                     Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''));
		//}
		$printer -> feed();
		$printer -> text("                                Total  $ ".number_format((float)$resP[0]['total'], 2, '.', ''));
		$printer -> feed();	
		$printer -> text(" Forma de Pago : ".$respagos[0]['modopago']);	


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