<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
setlocale(LC_MONETARY,"en_US");
date_default_timezone_set("America/Puerto_Rico");
 //header('Content-Type: text/html; charset=utf-8');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="myPDF.pdf');

// Send Headers: Prevent Caching of File
header('Cache-Control: private');
header('Pragma: private');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
require('./pdf_js.php');
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
$service  = $_GET['service'];
$numfactu = $_GET['numfactu'];
$usuario  = $_GET['user'];

if (isset($_GET['times'])) {
    $times = $_GET['times'];
}


$query="SELECT   a.numfactu,  CONVERT(VARCHAR(10),a.fechafac,101) fechafac,  a.descuento, a.subtotal, a.total, a.statfact, a.usuario, a.codseguro, c.cantidad, c.precunit, b.direccionH, b.Historia, b.nombres, e.seguro, d.desitems, f.TipoDePago ,c.coditems, d.nombre_alterno, d.kit,d.print_kit 
 "
         . "FROM  CMA_MFactura a     "
         . "INNER JOIN   MClientes   b ON  a.codclien = b.codclien     "
         . "INNER JOIN  CMA_DFactura  c ON  a.numfactu = c.numfactu    "
         . "LEFT OUTER JOIN  mseguros e ON  a.codseguro = e.codseguro  "
         . "INNER JOIN  MInventario   d ON  c.coditems = d.coditems    "
         . "LEFT OUTER JOIN  viewTipoPagoServicios f ON   c.numfactu = f.numfactu "
         . "Where a.numfactu='$numfactu' ";

$result = mssqlConn::Listados($query);
$resP = json_decode($result, true);




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
$sucursal = $pdf->getBranch('2');

$pdf->SetFont('Arial', '', 12);
$pdf->Text(3, 10, $sucursal[0]['Nombre']  ,1,0,'C',0);
$pdf->SetFont('Arial', '', 10);
$pdf->Text(3, 15, $sucursal[0]['Direccion']  ,0,1,'R');
$pdf->Text(18,20, $sucursal[0]['zip'].' Phone'.$sucursal[0]['tel']  ,0,1,'C');
$pdf->Text(27,25, "Invoice # ".$resP[0]['numfactu'] ,0,1,'C');
$pdf->Text(3, 30, "Patien or Resposible Part            ".$resP[0]['fechafac'] ,0,1,'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Text(3, 35, utf8_decode($resP[0]['nombres']) ,0,1,'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Text(3, 40, "Record ".$resP[0]['Historia'] ,0,1,'C');
$pdf->Text(3, 45,rtrim( ltrim( $resP[0]['direccionH'])) ,0,1,'C');
$pdf->Text(3, 50, "Description                 Rate       Qty        Total",0,1,'C');
$pdf->Text(3, 55, "--------------------------------------------------------------",0,1,'C');
$y2_axis=$pdf->GetY();
$lenA =sizeof($resP);
$yCoor=0;
$coorY=60;
$pdf->SetY(60); 

//Desde 3/8/2019 
$cmex=["PCEXOCEMA","PCDCINTRON","PCEXOIV","PCCELMADRE"];
$cmexl=false;
$cod="";
//Desde 3/8/2019 



for ($i=0; $i <$lenA ; $i++) { 
    $yCoor+=5;

    $str = substr(ltrim(rtrim($resP[$i]['desitems'])),0,20);
    $longitud = strlen(substr(ltrim(rtrim($resP[$i]['desitems'])),0,20));
    if ($longitud>18) {
        $str =  str_pad($str,26-$longitud," ",STR_PAD_RIGHT);  
        $str = substr(ltrim(rtrim($resP[$i]['nombre_alterno'])),0,20);       
    }
    $pdf->SetY(($pdf->GetY()));
    $pdf->SetFont('Arial', '', 8);
    $pdf->Text(3, $pdf->GetY(), $str ,0,0,'C');
    $pdf->SetFont('Arial', '', 10);

    $lenPrecio=strlen($resP[$i]['precunit']);
    $posicionPrecio=35; 
    if ($lenPrecio==8) {
       $posicionPrecio=37; 
     }
    $pdf->Text($posicionPrecio,$pdf->GetY(), number_format((float)$resP[$i]['precunit'], 2, '.', '') ,0,0,'R');
    $pdf->Text(54,$pdf->GetY(), $resP[$i]['cantidad'] ,0,0,'C');
    $pdf->Text(65,$pdf->GetY(), number_format((float)($resP[$i]['cantidad']*$resP[$i]['precunit']), 2, '.', '') ,0,1,'R');
    $coorY+1;
    $pdf->SetY(($pdf->GetY()+5));

   

       //PARA IMPRIMIR DETALLES DE LOS KITS
    if ($resP[$i]['kit']=="1") {
       if ($resP[$i]['print_kit']=='1') {
   
          $query="Select a.coditems,a.codikit,a.disminuir,b.desitems,b.nombre_alterno from Kit a 
           inner join MInventario b On a.codikit=b.coditems where a.coditems='pck2aa'  ";

          $result = mssqlConn::Listados($query);
          $resK = json_decode($result, true);
          for ($j=0; $j < sizeof($resK); $j++) { 
                $yCoor+=5;
                $str = substr(ltrim(rtrim($resK[$j]['desitems'])),0,25);
                $longitud = strlen(substr(ltrim(rtrim($resK[$j]['desitems'])),0,25));
                if ($longitud>18) {
                    $str =  str_pad($str,26-$longitud," ",STR_PAD_RIGHT);
                    $str = substr(ltrim(rtrim($resK[$j]['nombre_alterno'])),10,25);
                }
                $pdf->SetY(($pdf->GetY()));
                $pdf->SetFont('Arial', '', 8);
                $pdf->Text(3, $pdf->GetY(), "-".$str ,0,0,'C');
                // $pdf->Ln();                
                $pdf->SetFont('Arial', '', 10);


                $pdf->Text(41,$pdf->GetY(), number_format((float)"0", 2, '.', ''));
                $pdf->Text(54,$pdf->GetY(), $resK[$j]['disminuir'] );
                $pdf->Text(70,$pdf->GetY(), number_format((float)"0", 2, '.', ''));
             
    

                $coorY+1;
                $pdf->SetY(($pdf->GetY()+5)); 
          }
    }
        
   } 
    //
    // if(!$cmexl){
 
    //     $cod=array_search($resP[$i]['coditems'],$cmex);
    //     if(gettype($cod)!="boolean"){
    //       $cmexl=true;
    //     };
 
    // }




}

// if($cmexl){

//     $yCoor+=5;

//     if ($cod==0) {
//         $str="LASER INTRAVENOSO";
//         $qty=12;
//     }elseif ($cod==1){
//         $str="Terapia LASER MLS o Hilt";
//         $qty=10;
//     }elseif ($cod==2){
//          $str="LASER INTRAVENOSO";
//          $qty=1;
//     }elseif ($cod==3){
//         $qty=1;
//         $str="LASER INTRAVENOSO";

//     }

//      $pdf->SetY(($pdf->GetY()));
//      $pdf->SetFont('Arial', '', 8);
//      $pdf->Text(3, $pdf->GetY(), $str ,0,0,'C');
//      $pdf->SetFont('Arial', '', 10);
//      $pdf->Text(40,$pdf->GetY(), number_format(0 ),0,0,'C');
//      $pdf->Text(52,$pdf->GetY(), $qty ,0,0,'C');
//      $pdf->Text(70,$pdf->GetY(), number_format(0, 2, '.', '') ,0,1,'L');

// }

$yCoor=$yCoor+60;
$pdf->SetY($yCoor); 
$y2_axis=$pdf->GetY();
$pdf->Text(3, $y2_axis, "====================================",0,0,'C');
$pdf->SetY(($y2_axis+5)); 
if ($resP[0]['descuento']!='0') {
   $pdf->Text(48, $pdf->GetY(), "Sub total $ ".number_format((float)$resP[0]['subtotal'],   2, '.', ''),0,0,'C');
}else{
    $pdf->Text(54, $pdf->GetY(), "Total $ ".number_format((float)$resP[0]['subtotal'],   2, '.', ''),0,0,'C');
}

if ($resP[0]['descuento']!='0') {
    $pdf->SetY(($pdf->GetY()+5)); 
    $pdf->Text(36,  $pdf->GetY(), "Payments/Credits $ ".number_format((float)$resP[0]['descuento'], 2, '.', ''),0,0,'C');
}

if ($resP[0]['descuento']!='0') {
   $pdf->SetY(($pdf->GetY()+5)); 
   $pdf->Text(54, $pdf->GetY(), "Total $ ".number_format((float)$resP[0]['total'],   2, '.', ''),0,0,'C');
}
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(44,  $pdf->GetY(),"Balance Due  $ ".number_format(0,      2, '.', ''),0,0,'C');
$pdf->SetY(($pdf->GetY()+5)); 


$pdf->Text(3,  $pdf->GetY()," Payment Methods : ".$resP[0]['TipoDePago'],0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(3,  $pdf->GetY(),strtoupper($resP[0]['usuario']),0,0,'C');

$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "No returns accepted after 24 Hrs",0,0,'C');
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "www.centromedicoadaptogeno.com",0,0,'C');
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(9,  $pdf->GetY(),"Thank you for the opportunity to serve" ,0,0,'C');
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(12,  $pdf->GetY(),"you. Please call us if you have any" ,0,0,'C');
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(20,  $pdf->GetY(),"questions. (787) 780-7575" ,0,0,'C');
        

// $pdf->Text(3, 55, "--------------------------------------------------------------",0,0,'C');
// $pdf->Text(3, 55, "--------------------------------------------------------------",0,0,'C');
// $pdf->Text(3, 55, "--------------------------------------------------------------",0,0,'C');

$pdf->AutoPrint();
$pdf->Output();

?>