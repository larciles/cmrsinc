<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
 date_default_timezone_set("America/Puerto_Rico");
 header('Content-Type: text/html; charset=utf-8');
require_once '../../db/mssqlconn.php';
require_once "../../clases/fpdf/fpdf.php";
$dbmsql = mssqlConn::getConnection();

if(isset($_GET['fecha'])){
	$fecha    =$_GET['fecha'];
    $fecha2   =$_GET['fecha2'];
	$usuario  =$_GET['usuario'];
    $idempresa=$_GET['idempresa'];
    $titulo_reporte=$_GET['titulo_reporte'];    
}

class PDF extends FPDF
{
// Page header
function Header()
{

    if(isset($_GET['fecha'])){
    $fecha    =$_GET['fecha'];
    $fecha2   =$_GET['fecha2'];
    $usuario  =$_GET['usuario'];
    $idempresa=$_GET['idempresa'];
    $titulo_reporte=$_GET['titulo_reporte'];    
}
    // Logo
   // $this->Image('logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,10,'MEDICINA SISTEMICA LLC - Bayamon PR',0,1,'C');
    $this->Cell(180 , 5, $titulo_reporte  ,0,1,'C');
    $this->Cell(180 , 5, $fecha.' al '.$fecha2 ,0,1,'C');
    // Line break
    $this->Ln(15);

    $this->SetFont('Arial','',10);
    $this->Cell(1 , 5, '#'  ,0,0,'R');
    $this->Cell(20 , 5, 'Paciente' ,0,0,'R');
    $this->Cell(70 , 5, 'Telefono' ,0,0,'R');
    $this->Cell(20 , 5, 'Medico'   ,0,0,'R');
    $this->Cell(25 , 5, 'Record'   ,0,0,'R');
    $this->Cell(28 , 5, 'Asistido',0,1,'R');
    //$this->Cell(18 , 5, '# Cita'   ,0,1,'R');
   // $this->Cell(20 , 5, 'Asistido',0,1,'R');

    $this->Line($this->GetX(), $this->GetY(), 210-$this->GetX(), $this->GetY());  //Dibuja una linea
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
    $this->Cell(-20,10, date("F j, Y, g:i a"),0,0,'C');
    
}

 function getCitados($fecha, $usuario,$fecha2){

$query="SELECT a.codclien, 
               a.nombres, 
               a.telfhabit, 
               a.Medicos, 
               a.Historia, 
               a.asistido, 
               convert(varchar(10), cast(a.fecha_cita as date), 101) fecha_cita, 
               a.observacion, 
               a.coditems FROM  viewSSNoasistidos a Where (a.fecha_cita  between  '$fecha' and '$fecha2' ) and a.asistido=0 and   a.coditems like '%ST' ";



        $res = mssqlConn::Listados($query);

        $obj= json_decode($res, true);

     return $obj;
 }

}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$items = $pdf->getCitados($fecha, $usuario,$fecha2); // Busca los datos de los citados en la base de datos
$lenObj = sizeof($items); //Longitud del array

for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    if ($i<$lenObj) {
       $pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
    
    $pdf->Cell(50 , 5, $items[$i]['nombres']    ,0,0,'L');
    $pdf->Cell(43 , 5, $items[$i]['telfhabit']  ,0,0,'R');
    $pdf->Cell(50 , 5, $items[$i]['Medicos']    ,0,0,'L');
    $pdf->Cell(-10 , 5, $items[$i]['Historia']  ,0,0,'R');
    $pdf->Cell(29 , 5, $items[$i]['fecha_cita'],0,1,'R');
    //$pdf->Cell(17 , 5, $items[$i]['NumeroCitas'],0,1,'R');    
    $pdf->SetFont('Arial','',7);
    $pdf->Cell(1 , 5, $items[$i]['observacion'] ,0,1,'L');
}
    $pdf->Output();
?>