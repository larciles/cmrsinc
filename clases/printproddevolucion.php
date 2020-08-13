<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';
require '../escpos/autoload.php';
date_default_timezone_set('America/Puerto_Rico');
//require __DIR__ . '/escpos/autoload.php';
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

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$dbmsql = mssqlConn::getConnection();

$numfactu = $_POST['numfactu'];

if (isset($_POST['times'])) {
	$times = $_POST['times'];
}else{
	$times=1;
}

$usuario  = $_POST['user'];


$query="SELECT   a.numfactu,  CONVERT(VARCHAR(10),a.fechafac,101) fechafac,a.descuento,a.Alicuota, a.subtotal, a.total, a.statfact, a.usuario, a.codseguro, c.cantidad, c.precunit, b.direccionH, b.telfhabit, b.Historia, b.nombres, e.seguro, d.desitems,d.nombre_alterno, f.modopago "
         . "FROM  MSSMFact a 	 "
         . "INNER JOIN  MClientes b ON  a.codclien = b.codclien    "
         . "INNER JOIN  MSSDFact  c ON  a.numfactu = c.numfactu    "
         . "LEFT OUTER JOIN  mseguros e ON  a.codseguro = e.codseguro  "
         . "INNER JOIN  MInventario   d ON  c.coditems = d.coditems    "
         . "LEFT OUTER JOIN  viewTipoPagoLaser f ON c.numfactu = f.numfactu "
         . "Where a.numfactu='$numfactu' ";

$query = " SELECT   a.numnotcre numfactu, CONVERT(VARCHAR(10),a.fechanot,101) fechafac, a.subtotal, a.descuento, a.totalnot total, a.statnc statfact, a.concepto desanul, a.codseguro, a.TotImpuesto, a.Alicuota, a.monto_flete,a.usuario, 
	     c.cantidad, c.precunit, c.descuento descuentod, 
	     b.Cedula, b.direccionH, b.nombres, b.telfhabit,b.Historia,
	     e.nombre, e.apellido,    
	     d.pago, 
	     f.desitems, f.nombre_alterno 
		 FROM  
	     Mnotacredito a 
	     INNER JOIN MClientes b ON 
	     a.codclien = b.codclien 
	     INNER JOIN Dnotacredito c ON 
	     a.numnotcre = c.numnotcre 
	     LEFT OUTER JOIN  VIEWPagoTotal d ON 
	     a.numnotcre = d.numfactu
	     LEFT OUTER JOIN  Mmedicos e ON 
	     a.codmedico = e.Codmedico 
	     INNER JOIN  MInventario f ON 
	     c.coditems = f.coditems 
		 WHERE 
	     a.numnotcre = '$numfactu' 
		 ORDER BY  
	     f.nombre_alterno ASC ";         

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);

 if(sizeof($resP)>0){
 	#impuestos

 	$query  ="SELECT *,  CONCAT(Impuesto,' ',Porcentaje,' %') descripcion  from  Impuestos where activo=1";
 	$result = mssqlConn::Listados($query);
 	$taxes  = json_decode($result, true);
    $lentax =sizeof($taxes);

    $query="SELECT *  from  VIEW_ImpuestosxNC where numnotcre='$numfactu'";
 	$result = mssqlConn::Listados($query);
 	$impuestos = json_decode($result, true);
 	$lenimp =sizeof($impuestos);

 	if ($lenimp>0) {
 		for ($t=0; $t <$lentax ; $t++) {   
    	//    $aTax[$taxes[$t]['Impuesto']] = ($taxes[$t]['Porcentaje']*($resP[0]['subtotal']-$resP[0]['descuento'])/100);
    	    $aTax[]  = array(  'impuesto'=> ($taxes[$t]['Porcentaje']*($resP[0]['subtotal']-$resP[0]['descuento'])/100), 'descripcion'=> $taxes[$t]['descripcion'], 'porcentaje'=> $taxes[$t]['Porcentaje'] ) ;
    	}  		
 	}
  
 	#modo pago
 	$query="SELECT *  from  VIEWPagosDEV where numnotcre='$numfactu' and id_centro='1' and tipo_doc='04' and monto<0";
 	$result = mssqlConn::Listados($query);
 	$modopago = json_decode($result, true);
 } 
 	for ($t=0; $t <$times ; $t++) { 


		try {
			// Enter the share name for your USB printer here
			$connector = new WindowsPrintConnector("smb://".$pathprn); //  farmacia01/EPSON TM-T20II farmacia01
			$printer = new Printer($connector);


			$nfact = $resP[0]['numfactu'];
			$printer -> setEmphasis(true);
			$printer -> text("               DEVOLUCION # ".$resP[0]['numfactu']."\n");
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
			$printer -> text("Descripcion        Cant Precio    Desc Subtotal\n");
			$printer -> text("-----------------------------------------------\n");
		                       
		    $lenA =sizeof($resP);
		    
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
				
				$printer -> text(" ".str_pad($resP[$i]['cantidad'], 2, " ", STR_PAD_LEFT));
				$printer -> text(" ".str_pad(number_format((float)$resP[$i]['precunit'], 2, '.', ''), 8, " ", STR_PAD_LEFT));				
				$printer -> text(" ".str_pad(number_format((float)$resP[$i]['descuentod'], 2, '.', ''), 8, " ", STR_PAD_LEFT));		
				
				if ($resP[$i]['descuento']==0){
					$printer -> text(" ".str_pad(number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''), 8, " ", STR_PAD_LEFT));					
				} else{
					$printer -> text(" ".str_pad(number_format((float)( $resP[$i]['cantidad']*$resP[$i]['precunit'])-$resP[$i]['descuentod'] , 2, '.', ''), 8, " ", STR_PAD_LEFT));					
				}						
			   	$test=($resP[$i]['cantidad']*$resP[$i]['precunit']);
			   	
			}
		    $printer -> feed();
			$printer -> text("================================================");
			
			$printer -> feed();
			$printer -> text(str_pad('Subtotal $ ', 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['subtotal'], 2, '.', ''), 8, " ", STR_PAD_LEFT));
            
			
			
			$discount =(int)$resP[0]['descuento'];
			if($discount!==0){
				$printer -> feed();
				$printer -> text("                     Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''));
			}
			
			
			$lenimp =sizeof($impuestos);
		    
			// for ($i=0; $i <$lenimp ; $i++) { 
			// 	$printer -> feed();

			// 	$printer -> text(str_pad($impuestos[$i]['Descripcion'], 39, " ", STR_PAD_LEFT)." $".str_pad(number_format((float)$impuestos[$i]['montoimp'], 2, '.', ''), 7, " ", STR_PAD_LEFT));
			// }

			foreach($aTax as  $key => $item) {
  					$valor=(string)$item['porcentaje'];
  					$clave=$item['impuesto'];
  					$descr=$item['descripcion'];
  					$xxxxx=ltrim(rtrim( $item['descripcion'] ) ) ; //.' '. ltrim(rtrim( $valor ) )+' %'
  					$first=str_pad($item['descripcion'], 39, " ", STR_PAD_LEFT);
  				    $second = str_pad(number_format((float)$item['impuesto'], 2, '.', ''), 7, " ", STR_PAD_LEFT);

  					$printer -> text(str_pad($item['descripcion'], 39, " ", STR_PAD_LEFT)." $".str_pad(number_format((float)$item['impuesto'], 2, '.', ''), 7, " ", STR_PAD_LEFT));
                }

			$flete = (int)$resP[0]['monto_flete'];

			if ($flete !==0) {
					$printer -> feed();
					$printer -> text(str_pad("Envio $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['monto_flete'], 2, '.', ''), 8, " ", STR_PAD_LEFT));			
			}	
			
			
			$printer -> feed();

			$printer -> text(str_pad("Total  $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['total'], 2, '.', ''),8, " ", STR_PAD_LEFT));
			$printer -> feed();	
			$printer -> text(" Forma de Pago : ");

			$lenmodpag =sizeof($modopago);
			for ($i=0; $i <$lenmodpag ; $i++) { 
					$printer -> feed();
					$printer -> text($modopago[$i]['modopago']." ".number_format((float)$modopago[$i]['monto'], 2, '.', ''));
			}
				

			$printer -> feed();	
			
            	$printer -> text(date("F j, Y, g:i a")."\n");	
 

			$printer -> feed();	
			$printer -> text("              Gracias por su compra\n");	
			$printer -> text("   No se aceptan devoluciones después de 24 Hrs\n");	
			//$printer -> text("            después de 72 Hrs\n");	
			$printer -> text("         Pida su Cita al (787) 780-7575\n");	
			$printer -> text("         www.centromedicoadaptogeno.com\n");	
			$printer -> text("    ORDEN SU REFILL AL 787 780-7676 Y LE SERA\n");	  
			$printer -> text("                ENVIADO POR CORREO\n");	  

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


 if(sizeof($resP)>0){

 	$query = "SELECT * from  Mnotacredito where numnotcre='$numfactu'";
 	$result = mssqlConn::Listados($query);
 	$stat = json_decode($result, true);
 	if(sizeof($stat)>0){
 		if ($stat[0]['statnc']=='1' ){
 			$query = "UPDATE Mnotacredito Set statnc='3' where numnotcre='$numfactu'";
 			$result = mssqlConn::insert($query);
 		}
 	}

}



/* ASCII constants */








//$query="UPDATE MClientes Set  nombres='$nombres' where codclien='$codclien' ";
//$result = mssqlConn::insert($query);

//echo $result;
//printinvservice.php