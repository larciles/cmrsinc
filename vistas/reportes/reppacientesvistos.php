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
	  $fecha   =$_GET['fecha'];
    $fecha2  =$_GET['fecha2'];
    $id      =$_GET['id'];
  	$usuario =$_GET['usuario'];
    $codclien=$_GET['codclien'];
    $paciente=$_GET['paciente'];
    $titulo  =$_GET['titulo'];
    $autorizada=$_GET['autorizada'];
    $codmed=$autorizada=$_GET['codmed'];
    $medico=$autorizada=$_GET['medico'];

}

class PDF extends FPDF
{
// Page header
  public  $fecha;
function Header()
{

    if(isset($_GET['fecha'])){
      $fecha   =$_GET['fecha'];
      $fecha2  =$_GET['fecha2'];
      $id      =$_GET['id'];
      $usuario =$_GET['usuario'];   
      $codclien=$_GET['codclien'];
      $paciente=$_GET['paciente'];
      $titulo  =$_GET['titulo'];
      $codmed=$autorizada=$_GET['codmed'];
      $medico=$autorizada=$_GET['medico'];
        

        $date_s = date_create($fecha); 
        $date_e = date_create($fecha2); 
        //$pdf->Cell(80 , 5, date_format($date_s , ' jS F Y')  ,0,0,'R');
    }

    // Logo
   // $this->Image('logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'PACIENTES VISTOS'),0,1,'C');
    //$this->Ln(15);
    // $this->Cell(195,7, iconv("UTF-8", "CP1250//TRANSLIT",'CERTIFICACIÓN DE GASTOS'),0,1,'C');    
    $this->Cell(188 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    $this->Cell(188 , 5,  $titulo   ,0,1,'C');
    $this->Cell(188 , 5,  $medico   ,0,1,'C');
    
    $this->Ln(8);
    //$this->Cell(180 , 8, $paciente ,0,1,'C');
    
    // Line break
    // $this->Ln(20);

    $this->SetFont('Arial','B',9);
  
    $this->Cell(5 , 5, '#'   ,0,0,'R');
    $this->Cell(25 , 5, 'Fecha'  ,0,0,'R');   
    $this->Cell(15 , 5, 'Rec#'  ,0,0,'R');    
    $this->Cell(23 , 5, 'Paciente'  ,0,0,'R');
    
    $this->Cell(60 , 5, 'Veces',0,0,'R');
  
    $fecha_1= explode( '/',$fecha);    
    $fecha_2= explode( '/',$fecha2);

    $mes=(int)$fecha_1[0];
    $mes=$mes-1;
    $periodoI=$meses[$mes].' '.$fecha_1[1].' '.$fecha_1[2];

    $mes=(int)$fecha_2[0];
    $mes=$mes-1; 
    $periodoF=$meses[$mes].' '.$fecha_2[1].' '.$fecha_2[2];
 
  
    $this->Line($this->GetX()-190, $this->GetY()+5, $this->GetX(), $this->GetY()+5);  //Dibuja una linea
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



 function getReportData($fecha,$fecha2,$codmed){

  $date=date("m/d/Y");  
  $query="SELECT VD.*, MS.destatus,CONVERT(VARCHAR(10), VD.fechafac,110)fecha from VentasDiariasCMACELMADRESnoCons VD INNER JOIN Mstatus MS ON VD.statfact=MS.status WHERE(fechafac BETWEEN '$fecha' AND '$fecha2' AND statfact<>'2' and codmedico='$codmed')  ";
   
  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;        
 }

 function getTipoPago($fecha, $fecha2,$table){
  //KEY VALUE CLAVE VALOR
  $query="SELECT  a.numfactu,  CASE WHEN COUNT(*) =1 THEN  case when  Max(A.modopago)='MASTERCARD' then 'MC' else  Max(A.modopago) end ELSE 'SLPIT' END  modopago, MAX(A.fechapago) fechapago 
          FROM ".$table." A where fechapago between '$fecha' AND '$fecha2' group by a.numfactu ORDER BY fechapago ASC ";
  $result = mssqlConn::Listados($query);
  $obj= json_decode($result, true);
  
  $pagos = array();        
  foreach($obj as $fila){
    $pagos[$fila['numfactu']]=$fila['modopago'];
  }

    return $pagos;           
 }


 function getPacientesVistos($fecha, $fecha2,$codmed){
  $query="Select  max(fecha) Fecha, a.record,nombres,medico,  count(*)  veces from (
   Select CONVERT(VARCHAR(10),a.fecha_cita,110) fecha ,a.record, b.nombres, CONCAT(c.nombre,' ',c.apellido) medico from Mconsultas a  
     inner join MClientes b On a.codclien=b.codclien
     inner join Mmedicos c on a.codmedico=c.Codmedico
     where a.fecha_cita between '$fecha' AND '$fecha2' and a.codmedico ='$codmed' and a.asistido=3
     ) a group by a.record,nombres,medico order by record desc";

  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;     
 }


function getPacientesVistosVeces($fecha, $fecha2,$codmed){
  $query="Select veces, Sum(veces) pacientes  from (
          Select  max(fecha) Fecha, a.record,nombres,medico, count(*)  veces from (
          Select CONVERT(VARCHAR(10),a.fecha_cita,110) fecha ,a.record, b.nombres, CONCAT(c.nombre,' ',c.apellido) medico from Mconsultas a  
        inner join MClientes b On a.codclien=b.codclien
        inner join Mmedicos c on a.codmedico=c.Codmedico
        where a.fecha_cita between '$fecha' AND '$fecha2' and a.codmedico ='$codmed' and a.asistido=3   ) a group by a.record,nombres,medico  ) b group by veces order by veces desc ";

  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;     
 }



 function setFecha($fecha){
   $this->fecha=$fecha;
 }
 function getFecha(){

 }

 // Colored table
function FancyTable($header, $data)
{
    // Colors, line width and bold font
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    // Header
    $w = array(40, 35);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
    $this->Ln();
    // Color and font restoration
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    // Data
    $fill = false;
    foreach($data as $row)
    {
        $a=$row['veces'];
        $b=$row['pacientes'];
        $this->Cell($w[0],6,$a,'LR',0,'C',$fill);
        $this->Cell($w[1],6,$b,'LR',0,'C',$fill);       
        $this->Ln();
        $fill = !$fill;
    }
    // Closing line
    $this->Cell(array_sum($w),0,'','T');
}



}

// Instanciation of inherited class





$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// $metodopago =  $pdf->getTipoPago($fecha, $fecha2, 'VIEWpagosPRCMACST');


// $items=$pdf->getReportData($fecha,$fecha2,$codmed); // Busca los datos de los citados en la base de datos

$items=$pdf->getPacientesVistos($fecha,$fecha2,$codmed);
$itemsVeces=$pdf->getPacientesVistosVeces($fecha,$fecha2,$codmed);

$lenObj   = sizeof($items); //Longitud del array
$sumveces =0;


for($i=0;$i<$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;

    $fecha=$items[$i]['Fecha'];
    $record=$items[$i]['record'];
    $nombres=$items[$i]['nombres'];
    $veces=$items[$i]['veces'];

    $sumveces +=$veces;

          $pdf->Cell(18, 5, $numerador,0,0,'L');
          $pdf->Cell(14, 5, $fecha,0,0,'R');
          $pdf->Cell(14, 5, $record,0,0,'R');
          $pdf->Cell(14, 5, $nombres,0,0,'L'); 
          $pdf->Cell(64, 5, $veces,0,1,'R');
    
 
  
}
 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 $pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total  ('.$lenObj.')'    ,0,0,'L');

 
 $pdf->Cell(95 , 5, $sumveces,0,1,'R'); 

 $lenObj   = sizeof($itemsVeces); 
 $header = array('Veces', '# de Pacientes');
 $pdf->FancyTable($header,$itemsVeces);
 // for($i=0;$i<$lenObj;$i++){

 //     $veces=$itemsVeces[$i]['veces'];
 //     $pacientes=$itemsVeces[$i]['pacientes'];

 //     $pdf->Cell(14, 5, $veces,0,0,'L'); 
 //     $pdf->Cell(64, 5, $pacientes,0,1,'R');
 //   }


$pdf->Output();



?>