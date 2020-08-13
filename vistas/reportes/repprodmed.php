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
    $id      =$_GET['id'];
  	$usuario =$_GET['usuario'];
    $codmed=$_GET['codmed'];
    $paciente=$_GET['paciente'];
    $titulo  =$_GET['titulo'];
    $autorizada=$_GET['autorizada'];
    $medico=$_GET['medico'];

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
      $codmed=$_GET['codmed'];
      $paciente=$_GET['paciente'];
      $titulo  =$_GET['titulo'];
      $medico=$_GET['medico'];
        

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
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'CENTRO MÉDICO DOCENTE ADAPTÓGENO'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
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
    //$this->Cell(1 , 5, '#'  ,0,0,'R');
    // $this->Cell(54 , 5, 'A quien pueda interesar:' ,0,0,'R');
    // $this->Ln(20);
    //$this->Cell(8,5, 'Veces'   ,0,0,'R');
    $this->Cell(20,5,'Producto'  ,0,0,'R');
    //$this->Cell(27,5,'% del total'  ,0,0,'R');
    $this->Cell(102,5,'Cantidad',0,0,'R');
    //$this->Cell(48,5,'Listado de pacientes',0,0,'R');
    /*$this->Cell(14 , 5, 'Dto'     ,0,0,'R');
    $this->Cell(15 , 5, 'Total'   ,0,0,'R');
    $this->Cell(16 , 5, 'F. Pago' ,0,0,'R');
    $this->Cell(17 , 5, 'Concepto',0,0,'R');
    */
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



 function getReportData($fecha, $fecha2,$codmed){
  
    $query="SELECT nombremedico,desitems, sum(cantidad) cantidad from  VIEW_Ventas_Medicos_W_Ret where  fechafac between '$fecha' AND '$fecha2' and codmedico='$codmed' and Prod_serv='p' group by desitems,nombremedico order by cantidad desc ";

//    $query="SELECT RH.v, COUNT(MC.codclien) personas,  convert(varchar(10), cast( min( RH.mini)  as date), 101) mini,convert(varchar(10), cast( max( RH.maxi)  as date), 101) maxi FROM MClientes MC INNER JOIN viewRepeatWHOLE RH  ON  MC.codclien  = RH.codclien group by  RH.v ORDER BY     RH.v DESC ";

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
//$pdf->setFecha($fecha);

$items    = $pdf->getReportData($fecha,$fecha2,$codmed); // Busca los datos de los citados en la base de datos

//$total =  $pdf->getTipoPago($id,$codpro);

$lenObj   = sizeof($items); //Longitud del array
$subtotal =0;
$descuento=0;
$grantotal=0;
$totcan=0;
for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    if ($i<$lenObj) {
       //$pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
 
      

       if ( (float)$items[$i]['cantidad']!=0 ) {  

     
          $link=$pdf->AddLink();

          $v=$items[$i]['v'];
    
          $pdf->Cell(18 , 5, $items[$i]['desitems'] ,0,0,'L');
          $pdf->Cell(102 , 5, $items[$i]['cantidad'] ,0,1,'R');
          
          try {
            $cot=$items[$i]['personas'];
            $xt=$total;
             if ((float)$items[$i]['cantidad']!=0 ) {
                $totcan+=(float)$items[$i]['cantidad'];                  
              } 
             
           } catch (Exception $e) {
             
           } 
           /*
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "
           */
          
          
       
          // $pdf->Cell(27,5,$items[$i]['mini']   ,0,0,'R');          
          // $pdf->Cell(27,5,$items[$i]['maxi'],0,0,'R');
          // //$pdf->Image("../../img/boton.png",8,11.3,73,0,"","repreatedproddetails.php");
          // $url='http://'.$curserver.'/cma/vistas/reportes/';
          // $pdf->Image("../../img/boton.png",$pdf->GetX(),$pdf->GetY()+1,10,4,"",$url."repreatedproddetails.php?v=$v&fecha=$fecha&fecha2=$fecha2&id=$id&titulo=$titulo");
          
          // $pdf->Cell(50,5,"http://www.google.com"   ,0,1,'R');
          // //$pdf->Write(55,5,"http://www.google.com",$link);

      }
    


        //  $pdf->Cell(20,5 ,'','','','',false, "http://www.intranet.com/mb/rprh06/final.php?folio=".$items[$i]['maxi'],0,1,'R');
          //$pdf->Cell(40 , 5, iconv("UTF-8", "CP1250//TRANSLIT", $items[$i]['nombres'] )   ,0,0,'L');
          //$pdf->Cell(30 , 5, '$ '.number_format((float)$items[$i]['ST'], 2, '.', ',')  ,0,0,'R');                                  
          /*$pdf->Cell(17 , 5, '$ '.number_format((float)$items[$i]['DISCOUNT'], 2, '.', ',')  ,0,0,'R');
          $pdf->Cell(15 , 5, '$ '.number_format((float)$items[$i]['TOTALNOT'], 2, '.', ',')  ,0,0,'R');
          $pdf->SetFont('Arial','',7);
          $pdf->Cell(9 , 5, $metodopago[$items[$i]['numnotcre']]   ,0,0,'L');    
          $pdf->SetFont('Arial','',6);
          $pdf->Cell(23 , 5, $items[$i]['concepto']   ,0,1,'L');
          $pdf->SetFont('Arial','',8);
          $subtotal+=(float)$items[$i]['ST'];
          $descuento+=(float)$items[$i]['DISCOUNT'];
          $grantotal+=(float)$items[$i]['TOTALNOT'];*/

 
  
}
 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 //$pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
    $pdf->Cell(92,5,number_format((float)$totcan, 2, '.', ',')   ,0,1,'R');
/* $pdf->Cell(80 , 5, '$ '.number_format((float)$subtotal, 2, '.',',')  ,0,0,'R');
 $pdf->Cell(16 , 5, '$ '.number_format((float)$descuento, 2,'.',',')  ,0,0,'R');
 $pdf->Cell(17 , 5, '$ '.number_format((float)$grantotal, 2,'.',',')  ,0,1,'R');
*/
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