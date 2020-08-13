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
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'VENTAS SIN IVU'),0,1,'C');
    //$this->Ln(15);
    // $this->Cell(195,7, iconv("UTF-8", "CP1250//TRANSLIT",'CERTIFICACIÓN DE GASTOS'),0,1,'C');    
    $this->Cell(188 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    $this->Cell(188 , 5,  $titulo   ,0,1,'C');
    $this->Ln(8);
    //$this->Cell(180 , 8, $paciente ,0,1,'C');
    
    // Line break
    // $this->Ln(20);

    $this->SetFont('Arial','B',9);
    //$this->Cell(1 , 5, '#'  ,0,0,'R');
    // $this->Cell(54 , 5, 'A quien pueda interesar:' ,0,0,'R');
    // $this->Ln(20);
    $this->Cell(12 , 5, 'Factura'   ,0,0,'R');
    $this->Cell(15 , 5, 'Fecha'  ,0,0,'R');    
    $this->Cell(20 , 5, 'Paciente'  ,0,0,'R');
    $this->Cell(66 , 5, 'Subtotal',0,0,'R');
    $this->Cell(20 , 5, 'Descuento',0,0,'R');
    $this->Cell(14 , 5, iconv("UTF-8", "CP1250//TRANSLIT",'Envío'),0,0,'R');
    $this->Cell(14 , 5, 'Total',0,0,'R');
    $this->Cell(15 , 5, 'Estatus'     ,0,0,'R');
    $this->Cell(14 , 5, 'M. Pago'   ,0,0,'R');    
    //$this->Cell(17 , 5, 'Concepto',0,0,'R');
    
    // $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

    $fecha_1= explode( '/',$fecha);    
    $fecha_2= explode( '/',$fecha2);

    $mes=(int)$fecha_1[0];
    $mes=$mes-1;
    $periodoI=$meses[$mes].' '.$fecha_1[1].' '.$fecha_1[2];

    $mes=(int)$fecha_2[0];
    $mes=$mes-1; 
    $periodoF=$meses[$mes].' '.$fecha_2[1].' '.$fecha_2[2];
 
    // echo $dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;

    // $this->MultiCell(195 , 5,iconv("UTF-8", "CP1250//TRANSLIT", 'Por la presente certifico que '.$titulo.$paciente.' es paciente de Centro Médico Adaptógeno de Bayamón.')  );
    // $this->Ln(4);
    // $this->MultiCell(195 , 5, iconv("UTF-8", "CP1250//TRANSLIT", 'Dicho paciente ha pagado la siguiente cantidad en los diferentes servicios de la clínica en el período que comprende '.$periodoI.' a '.$periodoF )  );
    //$this->MultiCell(25,6,'Por la presen certifico que '.$paciente. ' es paciente de Cento Medico Adaptogeno de Bayamon' , 'LRT', 'L', 0);
   


    // $this->Cell(20 , 5, 'Asistido',0,1,'R');

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



 function getReportData($fecha,$fecha2){

  $date=date("m/d/Y");  
  $query="SELECT VD.*, MS.destatus, CONVERT(VARCHAR(10), VD.fechafac,110)fecha  from VentasDiarias VD INNER JOIN Mstatus MS ON VD.statfact=MS.status WHERE(fechafac BETWEEN '$fecha' AND '$fecha2' AND statfact<>'2' AND VD.TotImpuesto=0)  order by fechafac desc";
   
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

$metodopago =  $pdf->getTipoPago($fecha, $fecha2, 'VIEWpagosPR');


$items=$pdf->getReportData($fecha,$fecha2); // Busca los datos de los citados en la base de datos

$lenObj   = sizeof($items); //Longitud del array
$subtotal =0;
$descuento=0;
$grantotal=0;

for($i=0;$i<$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    
    


    //if ( !is_null($items[$i]['TOTALNOT']) || !empty($items[$i]['TOTALNOT']) ) {
   //     $pdf->Cell(50 , 5, $items[$i]['producto']    ,0,0,'L');   
   //     $pdf->Cell(10 , 5, '$ '.number_format((float)$items[$i]['TOTALNOT'], 2, '.', '')  ,0,1,'R');
          $numfactu=$items[$i]['numfactu'];
          $fecha=$items[$i]['fecha'];
          $nombres=$items[$i]['nombres'];
          $subtotal='$ '.number_format((float)$items[$i]['subtotal'], 2, '.', '');
          $descuento='$ '.number_format((float)$items[$i]['descuento'], 2, '.', '');
          $monto_flete='$ '.number_format((float)$items[$i]['monto_flete'], 2, '.', '');;
          
          $total='$ '.number_format((float)$items[$i]['total']+(float)$items[$i]['monto_flete'], 2, '.', '');;
          $destatus=$items[$i]['destatus'];
          $paymentmthd=$metodopago[$numfactu];

          $total_subtotal+=(float)$items[$i]['subtotal'];
          $total_descuento+=(float)$items[$i]['descuento'];
          $total_monto_flete+=$items[$i]['monto_flete'];;
          $total_total+=(float)$items[$i]['total'];          
          $total_total+=(float)$items[$i]['monto_flete'];
          
          

    //      $total=(int)$existencia-(int)$ventas+(int)$compras+(int)$devVentas-(int)$NE+(int)$NC+(int)$Ajustes_neg+(int)$Ajustes_mas;                 
          
          $pdf->Cell(18, 5, $numfactu,0,0,'L');
          $pdf->Cell(14, 5, $fecha,0,0,'R');
          $pdf->Cell(14, 5, $nombres,0,0,'L');
          $pdf->Cell(64, 5, $subtotal,0,0,'R');
          $pdf->Cell(18, 5, $descuento,0,0,'R');
          $pdf->Cell(18, 5, $monto_flete,0,0,'R');          
          $pdf->Cell(18, 5, $total,0,0,'R');
          $pdf->Cell(7, 5, $destatus,0,0,'L');
          $pdf->Cell(20 , 5, $paymentmthd,0,1,'R');
          // $pdf->Cell(14 , 5, $NC,0,0,'R');
          // $pdf->Cell(14 , 5,  $total,0,1,'R'); 
  
}
 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 $pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
 $pdf->Cell(80 , 5, '$ '.number_format((float)$total_subtotal, 2, '.',',')  ,0,0,'R');
 $pdf->Cell(18 , 5, '$ '.number_format((float)$total_descuento, 2,'.',',')  ,0,0,'R');
 $pdf->Cell(18 , 5, '$ '.number_format((float)$total_monto_flete, 2,'.',',')  ,0,0,'R'); 
 $pdf->Cell(18 , 5, '$ '.number_format((float)$total_total, 2,'.',',')  ,0,1,'R');

//  $pdf->Ln(4);
//  $pdf->MultiCell(195 , 5,  iconv("UTF-8", "CP1250//TRANSLIT",'Cualquier duda sobre el partícular puede comunicarse con nosotros al (787) 780-7575 ')   );
//  $pdf->Ln(38);

//  $pdf->Line($pdf->GetX(), $pdf->GetY(), 70-$pdf->GetX(), $pdf->GetY());  //Dibuja una linea
//  $pdf->Ln(4);
//  $pdf->Cell(150 , 5, $autorizada ,0,1,'L');
//  $pdf->Ln(4);
//  $pdf->Cell(150 , 5, iconv("UTF-8", "CP1250//TRANSLIT", 'C.M.A Bayamón')  ,0,0,'L');

// $date_s = date_create($fecha); 
// $pdf->Cell(80 , 5, date_format($date_s , ' jS F Y')  ,0,0,'R');


 



$pdf->Output();



?>