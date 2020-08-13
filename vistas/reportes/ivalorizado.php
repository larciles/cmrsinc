<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
 date_default_timezone_set("America/Puerto_Rico");
 header('Content-Type: text/html; charset=utf-8');
 require_once '../../db/mssqlconn.php'; 
 require_once "../../clases/fpdf/fpdf.php";
 $dbmsql = mssqlConn::getConnection();

if(isset($_GET['tipo'])){
	  $tipo   =$_GET['tipo'];   

}

class PDF extends FPDF
{
// Page header
  public  $fecha;
function Header()
{

    if(isset($_GET['tipo'])){
      $tipo   =$_GET['tipo'];      
    }

    // Logo
   // $this->Image('logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'Inventory'),0,1,'C');    
    $this->Cell(188 , 5,  $titulo   ,0,1,'C');
    $this->Ln(8);
    $this->SetFont('Arial','B',9);
    $this->Cell(12 , 5, 'Product'   ,0,0,'R');
    $this->Cell(80 , 5, 'Quantity'   ,0,0,'R');
    $this->Cell(30 , 5, 'Cost'  ,0,0,'R');      
    $this->Cell(40 , 5, 'total'  ,0,0,'R');      



    $this->Line($this->GetX()-200, $this->GetY()+5, $this->GetX(), $this->GetY()+5);  //Dibuja una linea
    $this->Ln();
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    $this->Cell(-10,10, date("F j, Y, g:i a"),0,0,'C');
    
}



 function getReportData($tipo){

  $date=date("m/d/Y");  
  $query="SELECT a.desitems,a.activo,a.Prod_serv,b.codtipre, b.precunit,c.destipre, a.costo, a.existencia FROM MInventario a INNER JOIN MPrecios b ON a.coditems = b.coditems INNER JOIN TipoPrecio c ON b.codtipre = c.codtipre WHERE a.activo= '1' AND  a.Prod_serv = 'P'  and existencia>0 ORDER BY a.desitems  ASC ";
   
  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;        
 }


}



$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();


$items=$pdf->getReportData( $tipo ); // Busca los datos de los citados en la base de datos

$lenObj   = sizeof($items); //Longitud del array
$subtotal =0;
$descuento=0;
$grantotal=0;
$tcantidad=0;

for($i=0;$i<$lenObj;$i++){
  
          $pdf->SetFont('Arial','',8);
          $numerador = $i+1;
  

          $cantidad=(int)$items[$i]['existencia'];
          $costo='$ '.number_format((float)$items[$i]['costo'], 2, '.', '');
          $total='$ '.number_format((float)($items[$i]['existencia']*$items[$i]['costo']), 2, '.', '');

          $desitems=$items[$i]['desitems'];

          $grantotal+=($items[$i]['existencia']*$items[$i]['costo']);

          $tcantidad+=$cantidad;

          $pdf->Cell(40, 5, $desitems,0,0,'L');
          $pdf->Cell(50, 5, $cantidad,0,0,'R');
          $pdf->Cell(30, 5, $costo,0,0,'R');
          $pdf->Cell(40, 5, $total,0,1,'R');
          
  
}

 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 //$pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
 $pdf->Cell(130 , 5, '$ '.number_format((float)$grantotal, 2, '.',',')  ,0,0,'R');
 

$pdf->Output();



?>