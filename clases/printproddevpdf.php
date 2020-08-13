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
    $aTax = array();
    if ($lenimp>0) {
        for ($t=0; $t <$lentax ; $t++) {           
            
            $total_tax=(float)$impuestos[0]['TotImpuesto'];
            if ($total_tax>0) {
                $aTax[]  = array(  'impuesto'=> ($taxes[$t]['Porcentaje']*($resP[0]['subtotal']-$resP[0]['descuento'])/100), 'descripcion'=> $taxes[$t]['descripcion'], 'porcentaje'=> $taxes[$t]['Porcentaje'] ) ;
            }
            
        }       
    }
  
    #modo pago
    $query="SELECT *  from  VIEWPagosDEV where numnotcre='$numfactu' and id_centro='1' and tipo_doc='04' and monto<0";
    $result = mssqlConn::Listados($query);
    $modopago = json_decode($result, true);
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
$pdf->Text(3, $pdf->GetY(), $sucursal[0]['Direccion'] ,0,1,'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(14,$pdf->GetY(), $sucursal[0]['zip'].' Phone'.$sucursal[0]['tel'] ,0,1,'C');


$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(18, $pdf->GetY(), ' MN: '. $sucursal[0]['mn'] ,1,0,'C',0);

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(25, $pdf->GetY(), 'servicios' ,1,0,'C',0);


$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(20,$pdf->GetY(), "DEVOLUCION # ".$resP[0]['numfactu'] ,0,1,'C');

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Patien or Responsible Part       ".$resP[0]['fechafac'] ,0,1,'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), $resP[0]['nombres'] ,0,1,'C');
$pdf->SetFont('Arial', '', 10);

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Record ".$resP[0]['Historia'] ,0,1,'C');

$pdf->SetFont('Arial', '', 8);
$pdf->SetY(($pdf->GetY()+5));
if ( strlen (rtrim( ltrim( $resP[0]['direccionH'])) )>50) {
    $pdf->SetFont('Arial', '', 7);
}
$pdf->Text(3, $pdf->GetY(),rtrim( ltrim( $resP[0]['direccionH'])) ,0,1,'C');

$pdf->SetFont('Arial', '', 9);

$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "Descripcion             Cant   Precio Descto Subtotal",0,1,'C');

$pdf->SetFont('Arial', '', 10);               
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "--------------------------------------------------------------",0,1,'C');

$lenA =sizeof($resP);
$yCoor=0;

$pdf->SetFont('Arial', '', 8); 
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

    if( strlen($descripcion)>18){
        $pdf->SetFont('Arial', '', 7); 
    }
    
    $pdf->Text(3, $pdf->GetY(), $descripcion ,0,0,'C');
    $pdf->SetFont('Arial', '', 8); 
    
    $pdf->Text(37,$pdf->GetY(), $resP[$i]['cantidad'] ,0,0,'C');

    $pdf->Text(42,$pdf->GetY(), number_format((float)$resP[$i]['precunit'], 2, '.', '') ,0,0,'C');
    $pdf->Text(52,$pdf->GetY(), str_pad(number_format((float)$resP[$i]['descuentod'], 2, '.', ''), 8, " ", STR_PAD_LEFT) ,0,0,'C');

    if ($resP[$i]['descuento']==0){        
        $pdf->Text(63,$pdf->GetY(), str_pad(number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', ''), 8, " ", STR_PAD_LEFT) ,0,1,'L');
    }else{
        $pdf->Text(63,$pdf->GetY(), str_pad(number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit'])-$resP[$i]['descuentod'], 2, '.', ''), 8, " ", STR_PAD_LEFT) ,0,1,'L');
    }                       

    
}
$pdf->SetFont('Arial', '', 10);
$pdf->SetY(($pdf->GetY()+5));
$pdf->Text(3, $pdf->GetY(), "====================================",0,0,'C');

$pdf->SetY(($pdf->GetY()+5));

$pdf->Text(15, $pdf->GetY(), str_pad('Subtotal $ ', 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['subtotal'], 2, '.', ''), 8, " ", STR_PAD_LEFT),0,0,'C');

$discount =(int)$resP[0]['descuento'];
if($discount!==0){
    $pdf->SetY(($pdf->GetY()+5)); 
    $pdf->Text(27,  $pdf->GetY(), "Descuento ".number_format((float)($resP[0]['Alicuota']), 2, '.', '')."%    $ ".number_format((float)($resP[0]['descuento']), 2, '.', ''),0,0,'C');                
}


foreach($aTax as  $key => $item) {
    $pdf->SetY(($pdf->GetY()+5)); 
        $valor=(string)$item['porcentaje'];
        $clave=$item['impuesto'];
        $descr=$item['descripcion'];
        $xxxxx=ltrim(rtrim( $item['descripcion'] ) ) ; //.' '. ltrim(rtrim( $valor ) )+' %'
        $first=str_pad($item['descripcion'], 39, " ", STR_PAD_LEFT);
        $second = str_pad(number_format((float)$item['impuesto'], 2, '.', ''), 7, " ", STR_PAD_LEFT);

        $pdf->Text(12,  $pdf->GetY(),str_pad($item['descripcion'], 39, " ", STR_PAD_LEFT)." $".str_pad(number_format((float)$item['impuesto'], 2, '.', ''), 7, " ", STR_PAD_LEFT),0,0,'C');                      
}








$flete = (int)$resP[0]['monto_flete'];

if ($flete !==0) {
    $pdf->SetY(($pdf->GetY()+5));
    $pdf->Text(17,  $pdf->GetY(),str_pad("Envio $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['monto_flete'], 2, '.', ''), 8, " ", STR_PAD_LEFT),0,0,'C');        
}   

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(17,  $pdf->GetY(),str_pad("Total  $ ", 40, " ", STR_PAD_LEFT).str_pad(number_format((float)$resP[0]['total'], 2, '.', ''),8, " ", STR_PAD_LEFT),0,0,'C');

$lenmodpag =sizeof($modopago);
for ($i=0; $i <$lenmodpag ; $i++) { 
    $pdf->SetY(($pdf->GetY()+5)); 
    //$printer -> text();
    $pdf->Text(3,  $pdf->GetY(),$modopago[$i]['modopago']." ".number_format((float)$modopago[$i]['monto'], 2, '.', ''),0,0,'C');
}


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY(),strtoupper($resP[0]['usuario']),0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY(),date("F j, Y, g:i a"),0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "         Gracias por su compra",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(2,  $pdf->GetY(), "No se aceptan devoluciones despues de 24 Hrs",0,0,'C'); 
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "www.centromedicoadaptogeno.com",0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(14,  $pdf->GetY(), "Pida su Cita al (787) 780-7575",0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(2,  $pdf->GetY(), "ORDEN SU REFILL AL 787 780-7676 Y LE SERA",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(17,  $pdf->GetY(), "ENVIADO POR CORREO",0,0,'C');


$pdf->SetY(($pdf->GetY()+5)); 

        


$pdf->AutoPrint();
$pdf->Output();

?>