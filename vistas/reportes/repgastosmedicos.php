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
    $record  =$_GET['record'];
  	$usuario =$_GET['usuario'];
    $codclien=$_GET['codclien'];
    $paciente=$_GET['paciente'];
    $titulo  =$_GET['titulo'];
    $autorizada=$_GET['autorizada'];

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
      $record  =$_GET['record'];
      $usuario =$_GET['usuario'];   
      $codclien=$_GET['codclien'];
      $paciente=$_GET['paciente'];
      $titulo  =$_GET['titulo'];
      if ($titulo=='true') {
         $titulo='la Sra. ';
      } else {
         $titulo='el Sr. ';
      }
      
      

        $date_s = date_create($fecha); 
        $date_e = date_create($fecha2); 
        //$pdf->Cell(80 , 5, date_format($date_s , ' jS F Y')  ,0,0,'R');
    }

    // Logo
    $this->Image('../../img/cmr.jpg',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'CENTRO DE MEDICINA REGENERATIVA'),0,1,'C');
    $this->Cell(127,8,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'R');
    $this->Ln(2);
    $this->Cell(188,7, iconv("UTF-8", "CP1250//TRANSLIT",'CERTIFICACIÓN DE GASTOS'),0,1,'C');    
    //$this->Cell(180 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    //$this->Cell(180 , 8, $paciente ,0,1,'C');
    
    // Line break
    $this->Ln(20);

    $this->SetFont('Arial','',12);
    //$this->Cell(1 , 5, '#'  ,0,0,'R');
    $this->Cell(54 , 5, 'A quien pueda interesar:' ,0,0,'R');
    $this->Ln(20);
    // $this->Cell(70 , 5, 'Telefono' ,0,0,'R');
    // $this->Cell(20 , 5, 'Medico'   ,0,0,'R');
    // $this->Cell(25 , 5, 'Record'   ,0,0,'R');
    // $this->Cell(28 , 5, 'Prox Cita',0,0,'R');
    
    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

    $fecha_1= explode( '/',$fecha);    
    $fecha_2= explode( '/',$fecha2);

    $mes=(int)$fecha_1[0];
    $mes=$mes-1;
    $periodoI=$meses[$mes].' '.$fecha_1[1].' '.$fecha_1[2];

    $mes=(int)$fecha_2[0];
    $mes=$mes-1; 
    $periodoF=$meses[$mes].' '.$fecha_2[1].' '.$fecha_2[2];
 
// echo $dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;

    $this->MultiCell(195 , 5,iconv("UTF-8", "CP1250//TRANSLIT", 'Por la presente certifico que '.$titulo.$paciente.' es paciente de Centro de Medicina Regenerativa de Bayamón.')  );
    $this->Ln(4);
    $this->MultiCell(195 , 5, iconv("UTF-8", "CP1250//TRANSLIT", 'Dicho paciente ha pagado la siguiente cantidad en los diferentes servicios de la clínica en el período que comprende '.$periodoI.' a '.$periodoF )  );
    //$this->MultiCell(25,6,'Por la presen certifico que '.$paciente. ' es paciente de Cento Medico Adaptogeno de Bayamon' , 'LRT', 'L', 0);
   


   // $this->Cell(20 , 5, 'Asistido',0,1,'R');

  //  $this->Line($this->GetX(), $this->GetY(), 210-$this->GetX(), $this->GetY());  //Dibuja una linea
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


function getPaciente($record){

   $query="select A.nombres,A.Historia,A.codclien from MClientes A where A.Historia ='$record' ";
   $res = mssqlConn::Listados($query);
   $obj= json_decode($res, true);
 return $obj;
}

 function getGastosmedicos($fecha, $fecha2,$codclien){
 
 // version original
   $query="select 'Consultas Medicas ' producto, sum(total) total  from VIEW_CMA_Mfactura_3 where  codclien='$codclien' AND cod_subgrupo='CONSULTA' and statfact='3' and fechafac between '$fecha' and '$fecha2'
      union all
      select 'Suero Vitamica C ' producto, sum(total) total  from VIEW_CMA_Mfactura_4 where  codclien='$codclien' AND cod_subgrupo='SUEROTERAPIA' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all
          select 'Adaptogenos '  producto, sum (total) total from MFactura where codclien='$codclien' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all
          select 'Laser'      producto, sum (total) total from MSSMFact where codclien='$codclien' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all 
          select 'Exosomas ' producto, sum(total) total  from VIEW_CMA_Mfactura_3 where  codclien='$codclien' AND cod_subgrupo='CEL MADRE' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all 
          select 'Bloqueo + Exo ' producto, sum(total) total  from VIEW_CMA_Mfactura_3 where  codclien='$codclien' AND cod_subgrupo='BLOQUEO' and statfact='3' and fechafac between '$fecha' and '$fecha2'";

// nueva 
      $query="select 'Consultas Medicas ' producto, sum(total) total  from VIEW_CMA_Mfactura_3 where  codclien='$codclien' AND cod_subgrupo='CONSULTA' and statfact='3' and fechafac between '$fecha' and '$fecha2'
      union all
      select 'Suero Vitamica C ' producto, sum(total) total  from VIEW_CMA_Mfactura_4_web where  codclien='$codclien' AND cod_subgrupo='SUEROTERAPIA' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all
          select 'Adaptogenos '  producto, sum (total) total from MFactura where codclien='$codclien' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all
          select 'Laser'      producto, sum (total) total from MSSMFact where codclien='$codclien' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all 
          select 'Exosomas ' producto, sum(total) total  from VIEW_CMA_Mfactura_4_web where  codclien='$codclien' AND cod_subgrupo='CEL MADRE' and statfact='3' and fechafac between '$fecha' and '$fecha2'
          union all 
          select 'Bloqueo + Exo ' producto, sum(total) total  from VIEW_CMA_Mfactura_4_web where  codclien='$codclien' AND cod_subgrupo='BLOQUEO' and statfact='3' and fechafac between '$fecha' and '$fecha2'";       
   $res = mssqlConn::Listados($query);
   $obj= json_decode($res, true);
  return $obj;
 }

 function setFecha($fecha){
   $this->fecha=$fecha;
 }
 function getFecha(){

 }



}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->setFecha($fecha);
$paciente =  $pdf->getPaciente($record);
$codclien=$paciente[0]['codclien'];
$items = $pdf->getGastosmedicos($fecha,$fecha2,$codclien); // Busca los datos de los citados en la base de datos
$lenObj = sizeof($items); //Longitud del array
$grantotal=0;
for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',12);
    $numerador = $i+1;
    if ($i<$lenObj) {
       //$pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
    
   if ( !is_null($items[$i]['total']) || !empty($items[$i]['total']) ) {
       $pdf->Cell(50 , 5, $items[$i]['producto']    ,0,0,'L');
       //$pdf->Cell(43 , 5, $items[$i]['total']  ,0,0,'R');    
       $pdf->Cell(10 , 5, '$ '.number_format((float)$items[$i]['total'], 2, '.', ',')  ,0,1,'R');
       $grantotal+=(float)$items[$i]['total'];
   }
 
  
}

$pdf->Ln(2);
$pdf->Line($pdf->GetX(), $pdf->GetY(), 81-$pdf->GetX(), $pdf->GetY());  //Dibuja una linea
$pdf->Cell(50 , 5, 'Total '    ,0,0,'L');
$pdf->Cell(10 , 5, '$ '.number_format((float)$grantotal, 2, '.', ',')  ,0,1,'R');

 $pdf->Ln(4);
 $pdf->MultiCell(195 , 5,  iconv("UTF-8", "CP1250//TRANSLIT",'Cualquier duda sobre el partícular puede comunicarse con nosotros al (787) 780-7575 ')   );
 $pdf->Ln(38);

 $pdf->Line($pdf->GetX(), $pdf->GetY(), 70-$pdf->GetX(), $pdf->GetY());  //Dibuja una linea
 $pdf->Ln(4);
 $pdf->Cell(150 , 5, $autorizada ,0,1,'L');
 $pdf->Ln(4);
 $pdf->Cell(150 , 5, iconv("UTF-8", "CP1250//TRANSLIT", 'C.M.R Bayamón')  ,0,0,'L');

// $date_s = date_create($fecha); 
// $pdf->Cell(80 , 5, date_format($date_s , ' jS F Y')  ,0,0,'R');


 



$pdf->Output();



?>