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
    $fecha2    =$_GET['fecha2'];
	$usuario  =$_GET['usuario'];
    $idempresa=$_GET['idempresa'];
    $titulo_reporte=$_GET['titulo_reporte']; 
    $codperfil=$_GET['codperfil'];   
    $codperfil=$_SESSION['controlcita'];//$_GET['codperfil'];  
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
    $codperfil=$_GET['codperfil'];
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
    $this->Cell(180 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    // Line break
    $this->Ln(15);

    $this->SetFont('Arial','',12);
    $this->Cell(1 , 5, '#'  ,0,0,'R');
    $this->Cell(20 , 5, 'Paciente',0,0,'R');
    $this->Cell(70 , 5, 'Telefono',0,0,'R');
    $this->Cell(30 , 5, 'Medico'  ,0,0,'R');
    $this->Cell(48 , 5, 'Record'  ,0,1,'R');
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

 function getCitados($fecha, $usuario,$fecha2,$codperfil){
 
$query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and b.usuario='$usuario'  and  b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 

    if ($codperfil=='1') {
        $query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and b.usuario='$usuario'  and  b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 
    } else {
        $query="SELECT "
    ."concat(a.nombre,' ', a.apellido) Medicos,   b.fecha_cita ,b.observacion, b.usuario, b.servicios, "
    ."c.telfhabit, c.Historia ,c.nombres "
    ."FROM "
    ."Mmedicos a "
    ."INNER JOIN      Mconsultas b ON   a.Codmedico = b.codmedico "
    ."INNER JOIN      MClientes  c ON        b.codclien = c.codclien "
    ."Where "
    ."b.confirmado=2 and b.activa<>'0' and (b.FECHA_cita between '$fecha' and '$fecha2') order by a.nombre"; 
    }
    
    


 $res = mssqlConn::Listados($query);

 $obj= json_decode($res, true);

 return $obj;
 }

}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$items = $pdf->getCitados($fecha, $usuario,$fecha2,$codperfil); // Busca los datos de los citados en la base de datos
$lenObj = sizeof($items); //Longitud del array

for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',9);
    $numerador = $i+1;
    if ($i<$lenObj) {
       $pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
    
    $pdf->Cell(50 , 5, $items[$i]['nombres']     ,0,0,'L');
    $pdf->Cell(55 , 5, $items[$i]['telfhabit']   ,0,0,'R');
    $pdf->Cell(50 , 5, $items[$i]['Medicos']      ,0,0,'L');
    $pdf->Cell(-100 , 5, $items[$i]['Historia']   ,0,1,'L');
    //$pdf->Cell(0 , 5, $items[$i]['UltimaAsistida'],0,1,'R');
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(1 , 5, $items[$i]['observacion']   ,0,1,'L');
}
    $pdf->Output();
?>