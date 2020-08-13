<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
setlocale(LC_MONETARY,"en_US");
date_default_timezone_set("America/Puerto_Rico");
header('Content-Type: text/html; charset=utf-8');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="myPDF.pdf');

// Send Headers: Prevent Caching of File
header('Cache-Control: private');
header('Pragma: private');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
require('pdf_js.php');
require_once '../db/mssqlconn.php';
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");    
    return;
}else{
    $usuario=$_SESSION['username'];
    $codperfil=$_SESSION['controlcita'];
}

$dbmsql = mssqlConn::getConnection();

$times=1;
//$service  = $_GET['service'];
$numfactu = $_GET['numfactu'];
$usuario  = $_GET['user'];

if (isset($_GET['times'])) {
    $times = $_GET['times'];
}


$query="SELECT   a.numfactu,  CONVERT(VARCHAR(10),a.fechafac,101) fechafac,a.descuento,a.Alicuota, a.subtotal, a.total, a.statfact, a.usuario, a.codseguro, c.cantidad, c.precunit, b.direccionH, b.telfhabit, b.Historia, b.nombres, e.seguro, d.desitems,d.nombre_alterno, f.modopago,d.nota,d.coditems "
         . "FROM  MSSMFact a     "
         . "INNER JOIN  MClientes b ON  a.codclien = b.codclien    "
         . "INNER JOIN  MSSDFact  c ON  a.numfactu = c.numfactu    "
         . "LEFT OUTER JOIN  mseguros e ON  a.codseguro = e.codseguro  "
         . "INNER JOIN  MInventario   d ON  c.coditems = d.coditems    "
         . "LEFT OUTER JOIN  viewTipoPagoLaser f ON c.numfactu = f.numfactu "
         . "Where a.numfactu='$numfactu' ";

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);


 #modo pago
$modo=0;

 $query="SELECT  * from idcard a
        inner join MTipoTargeta t on a.tipotarjeta=t.codtipotargeta
        where a.factura='$numfactu' and a.idcompany='3'";
 $result = mssqlConn::Listados($query);
 $modopago = json_decode($result, true);

 if ( is_array($modopago) ) {
     if (sizeof($modopago)<=0) {
           $modo=1;
           $query="SELECT b.DesTipoTargeta, * from Mpagos a  inner join MTipoTargeta b On a.codtipotargeta=b.codtipotargeta    Where a.numfactu='$numfactu'  and a.id_centro='3' order by monto desc";
           $result = mssqlConn::Listados($query);
           $modopago = json_decode($result, true);
     }
 }else{
    $modo=1;
    $query="SELECT b.DesTipoTargeta, * from Mpagos a  inner join MTipoTargeta b On a.codtipotargeta=b.codtipotargeta    Where a.numfactu='$numfactu'  and a.id_centro='3' order by monto desc";
    $result = mssqlConn::Listados($query);
    $modopago = json_decode($result, true);

 }



class PDF_AutoPrint extends PDF_JavaScript
{
    function AutoPrint($printer='')
    {
        // Open the print dialog
        if($printer)
        {
            $printer = str_replace('\\', '\\\\', $printer);
            $script = "var pp = getPrintParams();";
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script .= "pp.printerName = '$printer'";
            $script .= "print(pp);";
        }
        else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }

    function getBranch($idempresa='1'){
       $query="SELECT *   FROM  Empresa Where id='$idempresa' ";
       $res = mssqlConn::Listados($query);
       $obj= json_decode($res, true);
       $lenObj = sizeof($obj);
       return $obj;
  }    
}

$pdf = new PDF_AutoPrint();
$pdf->AddPage();

$sucursal = $pdf->getBranch('1');     

$pdf->SetFont('Arial', '', 12);
$pdf->Text(6, 10, $sucursal[0]['Nombre']  ,1,0,'C',0);
$pdf->SetFont('Arial', '', 10);

$pdf->SetY(($pdf->GetY()+5));
$pdf->SetFont('Arial', '', 9);
$pdf->Text(3, $pdf->GetY(), $sucursal[0]['Direccion']  ,0,1,'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(14,$pdf->GetY(), $sucursal[0]['zip'].' Phone'.$sucursal[0]['tel'] ,0,1,'C');


$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(18, $pdf->GetY(), ' MN: '. $sucursal[0]['mn']  ,1,0,'C',0);

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(25, $pdf->GetY(), 'servicios' ,1,0,'C',0);




$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(20,$pdf->GetY(), "Factura # ".$resP[0]['numfactu'] ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Patien or Resposible Part            ".$resP[0]['fechafac'] ,0,1,'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), utf8_decode( $resP[0]['nombres'] ),0,1,'C');
$pdf->SetFont('Arial', '', 10);

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Record ".$resP[0]['Historia'] ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), rtrim( ltrim( $resP[0]['direccionH'])) ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
//$pdf->Text(3, $pdf->GetY(), "Description                 Rate       Qty        Total",0,1,'C');
$pdf->Text(3, $pdf->GetY(), "Descripcion                Precio     Cant    Subtotal",0,1,'C');
               

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "--------------------------------------------------------------",0,1,'C');

$lenA =sizeof($resP);
$yCoor=0;


$a_notas = array();
for ($i=0; $i <$lenA ; $i++) { 
    
    $pdf->SetY(($pdf->GetY()+5));

    $na=$resP[$i]['nombre_alterno'];
    if($resP[$i]['nombre_alterno']!==""){
        $descripcion = utf8_decode(substr($resP[$i]['nombre_alterno'],0,26));        
    }else{
        $descripcion = utf8_decode(substr($resP[$i]['desitems'],0,26)) ;        
    }
    if( strlen($descripcion)<26){
        $can =26-strlen($descripcion);   
        $descripcion =   str_pad($descripcion,$can," ");
    }
          
    $pdf->SetFont('Arial', '', 9); 
    $pdf->Text(3, $pdf->GetY(), $descripcion ,0,0,'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Text(35,$pdf->GetY(), number_format((float)$resP[$i]['precunit'], 2, '.', '') ,0,0,'C');
    $pdf->Text(52,$pdf->GetY(), $resP[$i]['cantidad'] ,0,0,'C');
    $pdf->Text(63,$pdf->GetY(), number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', '') ,0,1,'L');

    if ($resP[$i]['nota']==1) {
        $a_notas[]=$resP[$i]['coditems'];
    }
}
//---------------------------------------------------------
if (count($a_notas)>0) {
   $pdf->SetY(($pdf->GetY()+5));

   $pdf->SetFont('Arial', '', 8); 
   
   for ($i=0; $i <count($a_notas) ; $i++) { 
       $pdf->SetY(($pdf->GetY()+5));
       $item_note=$a_notas[$i];
       $query="SELECT  * from nota  Where coditems='$item_note' ";
       $result = mssqlConn::Listados($query);
       $text_nota = json_decode($result, true);
       $pdf->Text(3, $pdf->GetY(), utf8_decode($text_nota[0]['nota']) ,0,1,'C');
       // $pdf->SetY(($pdf->GetY()+5));
   }
}
//--------------------------------------------------------- 
$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "====================================",0,0,'C');

$pdf->SetY(($pdf->GetY()+5));

$pdf->Text(45, $pdf->GetY(), "Subtotal $ ".number_format((float)$resP[0]['subtotal'],   2, '.', ''),0,0,'C');

$discount =(int)$resP[0]['descuento'];
if($discount!==0){
    $pdf->SetY(($pdf->GetY()+5)); 
    $pdf->Text(30,  $pdf->GetY(), "Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''),0,0,'C');
            
}

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(43,  $pdf->GetY(),"       Total $ ".number_format((float)$resP[0]['total'],      2, '.', ''),0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
// $pdf->Text(3,  $pdf->GetY()," Forma de Pago  : ".$resP[0]['modopago'],0,0,'C');

$lenmodpag =sizeof($modopago);
if ($lenmodpag >0) {

if ($modo==0) {

   for ($i=0; $i <$lenmodpag ; $i++) { 
        $pdf->SetY(($pdf->GetY()+5)); 
        //$printer -> text();
        $pdf->Text(3,  $pdf->GetY(),$modopago[$i]['DesTipoTargeta']." ****".$modopago[$i]['cnumber']." ".number_format((float)$modopago[$i]['monto'], 2, '.', ''),0,0,'C');
    }
}else{
     $pdf->Text(3,  $pdf->GetY(),"Forma de Pago  : ",0,0,'C'); 
    for ($i=0; $i <$lenmodpag ; $i++) { 
        $pdf->SetY(($pdf->GetY()+5));

        if ($modopago[$i]['monto']>0) {
            $pdf->Text(3,  $pdf->GetY(),$modopago[$i]['DesTipoTargeta'],0,0,'C');
            $pdf->Text(20,  $pdf->GetY(),"$".$modopago[$i]['monto'],0,1,'R');
         }else{
            $pdf->Text(3,  $pdf->GetY(),"Cambio",0,0,'C');
            $pdf->Text(20,  $pdf->GetY(),"$".$modopago[$i]['monto'],0,1,'R');
         } 
        

    }


}
   


}else{
    $pdf->Text(3,  $pdf->GetY()," Forma de Pago  : ".$resP[0]['modopago'],0,0,'C');    
}

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY(),strtoupper($resP[0]['usuario']),0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "         Gracias por su compra",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(2,  $pdf->GetY(), "No se aceptan devoluciones despues de 24 Hrs",0,0,'C'); 

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "    Pida su Cita al (787) 780-7575",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "www.centromedicoadaptogeno.com",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 

        


$pdf->AutoPrint();
$pdf->Output();

?>