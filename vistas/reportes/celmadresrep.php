<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
 date_default_timezone_set("America/Puerto_Rico");
 header('Content-Type: text/html; charset=utf-8');
 require_once '../../db/mssqlconn.php'; 
 require_once "../../clases/fpdf/fpdf.php";
 
 $dbmsql = mssqlConn::getConnection();

 $curserver=SERVER_HOST; 

if(isset($_GET['fecha'])){
	  $fecha   =$_GET['fecha'];
    $fecha2  =$_GET['fecha2'];
    $titulo  =$_GET['titulo'];
    $usuario =$_GET['usuario'];
   // $id      =$_GET['id'];
  	
    // $codpro=$_GET['codpro'];
    // $paciente=$_GET['paciente'];
    
    //$autorizada=$_GET['autorizada'];

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
        $titulo  =$_GET['titulo'];
        $usuario =$_GET['usuario'];
        

        $date_s = date_create($fecha); 
        $date_e = date_create($fecha2); 
      
    }

    // Logo
   // $this->Image('logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'CENTRO MÉDICO DOCENTE ADAPTÓGENO'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
    //$this->Ln(15);
    // $this->Cell(195,7, iconv("UTF-8", "CP1250//TRANSLIT",'CERTIFICACIÓN DE GASTOS'),0,1,'C');    
    $this->Cell(188 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    $this->Cell(188 , 5,  $titulo   ,0,1,'C');
    $this->Ln(8);

    $this->SetFont('Arial','B',9);

    $this->Cell(8,5, '# Factura'   ,0,0,'R');
    $this->Cell(20,5,'Fecha'  ,0,0,'R');
    $this->Cell(27,5,'Paciente'  ,0,0,'R');
    $this->Cell(50,5,'Subtotal',0,0,'R');
    $this->Cell(30,5,'Descuento',0,0,'R');
    $this->Cell(20,5,'Total',0,0,'R');
   

    $fecha_1= explode( '/',$fecha);    
    $fecha_2= explode( '/',$fecha2);

    $mes=(int)$fecha_1[0];
    $mes=$mes-1;
    $periodoI=$meses[$mes].' '.$fecha_1[1].' '.$fecha_1[2];

    $mes=(int)$fecha_2[0];
    $mes=$mes-1; 
    $periodoF=$meses[$mes].' '.$fecha_2[1].' '.$fecha_2[2];


    $this->Line($this->GetX()-170, $this->GetY()+5, $this->GetX(), $this->GetY()+5);  //Dibuja una linea
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



 function getReportData($fecha, $fecha2 ){
 
   $query="select a.numfactu, REPLACE(CONVERT(CHAR(15), a.fechafac, 101), '', '-') AS fechafac, c.nombres,sum(b.cantidad*b.precunit) subtotal , sum(b.descuento) descuento , sum(b.cantidad*b.precunit)-sum(b.descuento)  total  from MSSMFact a
inner join MSSDFact b on b.coditems in ('BLIA1118','BLNL1118') and b.numfactu=a.numfactu 
inner join MClientes c on c.codclien=a.codclien
where a.statfact=3 and a.fechafac between '$fecha' and '$fecha2'
group by a.numfactu,a.fechafac, c.nombres order by a.fechafac desc";


  
  
  

  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;        
 }

 function getTipoPago($id,$codpro){
  //KEY VALUE CLAVE VALOR
 if ($id=='true') {
   $query_a4="SELECT count(RH.codclien) v FROM MClientes MC INNER JOIN viewRepeatWHOLE RH ON MC.codclien=RH.codclien ";
 } else {
   $query_a4="SELECT count(*) v FROM viewRepeatV4 ";
 }
 
 
 $result = mssqlConn::Listados($query_a4);
 $obj= json_decode($result, true);
  
  $total=0;
  foreach($obj as $fila){
   $total=$fila['v'];
  }

    return $total;           
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

$items    = $pdf->getReportData($fecha,$fecha2); // Busca los datos de los citados en la base de datos



$lenObj   = sizeof($items); //Longitud del array
$subtotal =0;
$descuento=0;
$grantotal=0;

for($i=0;$i<=$lenObj;$i++){
 
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    if ($i<$lenObj) {
          
          $subtotal=(float)$items[$i]['subtotal'];  
          $descuento=(float)$items[$i]['descuento'];  
          $total=(float)$items[$i]['total'];  
          
          $g_subtotal+=$subtotal;
          $g_descuento+=$descuento;
          $g_total+=$total;
   
          $pdf->Cell(18 , 5, $items[$i]['numfactu'] ,0,0,'L');
          $pdf->Cell(17 , 5, $items[$i]['fechafac']   ,0,0,'L');
          $pdf->Cell(12 , 5, $items[$i]['nombres']   ,0,0,'L');
 
          $pdf->Cell(60,5,number_format((float)$subtotal, 2, '.', ',')   ,0,0,'R');
          $pdf->Cell(20,5,number_format((float)$descuento, 2, '.', ',')   ,0,0,'R');
          $pdf->Cell(30,5,number_format((float)$total, 2, '.', ',')   ,0,1,'R');

    }


}

 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 $pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
 $pdf->Cell(80 , 5, '$ '.number_format((float)$g_subtotal, 2, '.',',')  ,0,0,'R');
 $pdf->Cell(17 , 5, '$ '.number_format((float)$g_descuento, 2,'.',',')  ,0,0,'R');
 $pdf->Cell(30 , 5, '$ '.number_format((float)$g_total, 2,'.',',')  ,0,1,'R');



$pdf->Output();



?>