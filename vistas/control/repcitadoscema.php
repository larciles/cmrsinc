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
    $this->Cell(80 , 5, 'Telefono',0,0,'R');
    $this->Cell(35 , 5, 'Medico'  ,0,0,'R');
    $this->Cell(30 , 5, 'Asistido',0,0,'R');
    $this->Cell(25 , 5, 'Record'  ,0,1,'R');
    

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
             ."a.nombres, a.telfhabit, a.Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast(a.UltimaAsistida as date), 101)   UltimaAsistida, a.observacion, a.fallecido "
             ."FROM "
             ."VIEW_repconsultas4 a "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados=1 and a.usuario='$usuario' and a.fallecido<>'1' and a.inactivo<>'1'  order by Medicos";
             
if ($codperfil=='1') {
    $query="SELECT "
             ."a.nombres, a.telfhabit,concat( b.nombre,' ', b.apellido) Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora,  convert(varchar(10), cast( (SELECT TOP (1) PERCENT  MAX(c.fecha_cita) AS UltimaAsistida FROM dbo.Mconsultass c WHERE (c.asistido = 3 and c.coditems='CMKCINTRON')  and c.codclien=a.codclien GROUP BY c.codclien) as date), 101)   UltimaAsistida, a.observacion  "
             ."FROM "
             ."VIEW_mconsultas_02 a   inner join Mmedicos b On a.codmedico=b.Codmedico"
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados='Citado' and a.usuario='$usuario' and a.descons='CEL MADRE'  and a.inactivo<>'1'    order by Medicos";

} else {
    $query="SELECT "
             ."a.nombres, a.telfhabit,concat( b.nombre,' ', b.apellido) Medicos, a.Historia, a.citados, a.asistido,a.activa,a.usuario, a.fecha_cita, a.hora, convert(varchar(10), cast( (SELECT TOP (1) PERCENT  MAX(c.fecha_cita) AS UltimaAsistida FROM dbo.Mconsultass c WHERE (c.asistido = 3 and c.coditems='CMKCINTRON')  and c.codclien=a.codclien GROUP BY c.codclien) as date), 101)   UltimaAsistida, a.observacion "
             ."FROM "
             ."VIEW_mconsultas_02 a  inner join Mmedicos b On a.codmedico=b.Codmedico "
             ."Where "
             ."(a.fecha_cita between '$fecha' and '$fecha2') and a.activa<>'0' and a.citados='Citado'  and a.descons='CEL MADRE'  and a.inactivo<>'1' order by Medicos";
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
    $pdf->Cell(70 , 5, $items[$i]['telfhabit']   ,0,0,'R');
    $pdf->Cell(50 , 5, $items[$i]['Medicos']      ,0,0,'L');
    $pdf->Cell(-100 , 5, $items[$i]['Historia']   ,0,0,'L');
    $pdf->Cell(95 , 5, $items[$i]['UltimaAsistida'],0,1,'R');
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(1 , 5, $items[$i]['observacion']   ,0,1,'L');
}
    $pdf->Output();
?>