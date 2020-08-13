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
$numfactu = $_GET['numfactu'];
$usuario  = $_GET['user'];
if (isset($_GET['times'])) {
    $times = $_GET['times'];
}

if (isset($_GET['idempresa'])) {
  $idcentro=$_GET['idempresa'];
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
    .", CONVERT(VARCHAR(10),a.fechanot,101) fechanot "
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
    .", a.tasadesc "
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
$pdf->SetFont('Arial', '', 12);



    if ($idcentro=='2') { 
        $sucursal = $pdf->getBranch('2');           
        $pdf->Text(3, 10, $sucursal[0]['Nombre']  ,1,0,'C',0);
    }else if($idcentro=='3'){ 
       $sucursal = $pdf->getBranch('3'); 
       $pdf->Text(3, 10, $sucursal[0]['Nombre']  ,1,0,'C',0);
       $pdf->SetFont('Arial', '', 10);
       $pdf->SetY(($pdf->GetY()+5));
       $pdf->Text(18, $pdf->GetY(), ' MN: '. $sucursal[0]['mn']  ,1,0,'C',0);
       $pdf->SetY(($pdf->GetY()+5)); 
       $pdf->Text(25, $pdf->GetY(), 'servicios' ,1,0,'C',0);
     }
$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->SetFont('Arial', '', 9);
$pdf->Text(3, $pdf->GetY(), $sucursal[0]['Direccion']  ,0,1,'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(14,$pdf->GetY(), $sucursal[0]['zip'].' Phone'.$sucursal[0]['tel'] ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(20,$pdf->GetY(), "Devolucion # ".$resP[0]['numnotcre'] ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Patien or Resposible Part            ".$resP[0]['fechanot'] ,0,1,'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), $resP[0]['nombres'] ,0,1,'C');
$pdf->SetFont('Arial', '', 10);

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Record ".$resP[0]['Historia'] ,0,1,'C');
$pdf->SetFont('Arial', '', 8);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(),rtrim( ltrim( $resP[0]['direccionH'])) ,0,1,'C');
$pdf->SetFont('Arial', '', 10);

$pdf->SetY(($pdf->GetY()+5));
//$pdf->Text(3, $pdf->GetY(), "Description                 Rate       Qty        Total",0,1,'C');
$pdf->Text(3, $pdf->GetY(), "Descripcion                Precio     Cant    Subtotal",0,1,'C');
               

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "--------------------------------------------------------------",0,1,'C');

$lenA =sizeof($resP);
$yCoor=0;
for ($i=0; $i <$lenA ; $i++) { 
    
    $pdf->SetY(($pdf->GetY()+5));

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
          
    $pdf->SetFont('Arial', '', 9); 
    $pdf->Text(3, $pdf->GetY(), $descripcion ,0,0,'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Text(35,$pdf->GetY(), number_format((float)$resP[$i]['precunit'], 2, '.', '') ,0,0,'C');
    $pdf->Text(52,$pdf->GetY(), $resP[$i]['cantidad'] ,0,0,'C');
    $pdf->Text(63,$pdf->GetY(), number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', '') ,0,1,'L');
}
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "====================================",0,0,'C');

$pdf->SetY(($pdf->GetY()+5));

$pdf->Text(45, $pdf->GetY(), "Subtotal $ ".number_format((float)$resP[0]['subtotal'],   2, '.', ''),0,0,'C');

$discount =(int)$resP[0]['descuento'];
if($discount!==0){
    $pdf->SetY(($pdf->GetY()+5)); 
    $pdf->Text(30,  $pdf->GetY(), "Descuento ".number_format((float)($resP[0]['tasadesc']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''),0,0,'C');
            
}

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(43,  $pdf->GetY(),"       Total $ ".number_format((float)$resP[0]['total'],      2, '.', ''),0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY()," Forma de Pago  : ".$respagos[0]['modopago'],0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY(),strtoupper($resP[0]['usuario']),0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "         Gracias por su visita",0,0,'C');


//$pdf->SetY(($pdf->GetY()+5)); 
//$pdf->Text(2,  $pdf->GetY(), "No se aceptan devoluciones despues de 72 Hrs",0,0,'C'); 

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "    Pida su Cita al (787) 780-7575",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "www.centromedicoadaptogeno.com",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 

        


$pdf->AutoPrint();
$pdf->Output();

?>