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
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'CENTRO MÉDICO DOCENTE ADAPTÓGENO'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
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
    $this->Cell(18 , 5, 'Producto'   ,0,0,'R');
    $this->Cell(64 , 5, 'Inicio'  ,0,0,'R');
    $this->Cell(14 , 5, 'Compra'  ,0,0,'R');
    $this->Cell(14 , 5, 'Venta'  ,0,0,'R');
    $this->Cell(14 , 5, 'Devol',0,0,'R');
    $this->Cell(16 , 5, 'Ajus + ',0,0,'R');
    $this->Cell(14 , 5, 'Ajus - ',0,0,'R');
    $this->Cell(10 , 5, 'NE'     ,0,0,'R');
    $this->Cell(14 , 5, 'NC'   ,0,0,'R');
    $this->Cell(15 , 5, 'Total' ,0,0,'R');
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
    $this->Cell(-20,10, date("F j, Y, g:i a"),0,0,'C');
    
}



 function getReportData($fecha,$fecha2){

  $date=date("m/d/Y");
  if ($date==$fecha2) {
     $query="SELECT desitems,coditems,(SELECT isnull(SUM(D.cantidad),0)+SUM(t.ventas) FROM dfactura D RIGHT JOIN mfactura ON D.numfactu=mfactura.numfactu WHERE coditems=t.coditems AND D.fechafac='$fecha2') AS ventas,(SELECT isnull(SUM(C.cantidad),0)+SUM(t.compra) FROM dcompra C LEFT JOIN mcompra E ON C.factcomp=E.factcomp WHERE coditems=t.coditems AND E.fechapost='$fecha') AS compras,(SELECT isnull(SUM(V.cantidad),0)+SUM(t.devcompra) FROM devcompra V WHERE coditems=t.coditems AND fecreg='$fecha2') AS devcompra,(SELECT isnull(SUM(D.cantidad),0)+SUM(t.anulaciones) FROM dfactura D RIGHT JOIN mfactura M ON D.numfactu=M.numfactu WHERE coditems=t.coditems AND M.fechanul='$fecha2' AND M.statfact='2') AS devVentas,(SELECT isnull(SUM(N.cantidad),0)+SUM(t.ne) FROM notentdetalle N RIGHT JOIN notaentrega M ON N.numnotent=M.numnotent WHERE coditems=t.coditems AND M.fechanot='$fecha2' AND M.statunot<>'2') AS NE,(SELECT isnull(SUM(D.cantidad),0)+SUM(t.nc) FROM dnotacredito D RIGHT JOIN mnotacredito M ON D.numnotcre=M.numnotcre WHERE coditems=t.coditems AND M.fechanot='$fecha2' AND M.statnc<>'2') AS NC,(SELECT isnull(SUM(D.cantidad),0)+SUM(ajustespos) FROM dajustes D WHERE coditems=t.coditems AND fechajust='$fecha2' AND D.cantidad>0) AS Ajustes_mas,(SELECT isnull(SUM(P.cantidad),0)+SUM(ajustesneg) FROM dajustes P WHERE coditems=t.coditems AND fechajust='$fecha2' AND P.cantidad<0) AS Ajustes_neg,(SELECT isnull(SUM(I.existencia),0) From DCierreInventario I WHERE coditems=t.coditems AND fechacierre='$fecha') AS existencia FROM TEST011516 t WHERE(fechacierre BETWEEN '$fecha' AND '$fecha2') GROUP BY desitems,coditems ORDER BY desitems";
  } else {
     $query="SELECT desitems,coditems,SUM(t.ventas) ventas,SUM(compra) compras,SUM(devcompra) AS devcompra,SUM(anulaciones) AS devVentas,SUM(ne) AS NE,SUM(nc) AS NC,SUM(ajustespos) AS Ajustes_mas,SUM(ajustesneg) AS Ajustes_neg,(SELECT isnull(SUM(DC.existencia),0) From DCierreInventario DC WHERE coditems=t.coditems AND fechacierre='$fecha') AS existencia FROM TEST011516 t WHERE (fechacierre BETWEEN '$fecha' AND '$fecha2') GROUP BY desitems,coditems ORDER BY desitems";
  }
   $res = mssqlConn::Listados($query);
   $obj= json_decode($res, true);
   return $obj;        
 }

 function getTipoPago($fecha, $fecha2,$table){
  //KEY VALUE CLAVE VALOR
  $query="SELECT  a.numnotcre,  CASE WHEN COUNT(*) =1 THEN  case when  Max(A.modopago)='MASTERCARD' then 'MC' else  Max(A.modopago) end ELSE 'SLPIT' END  modopago, MAX(A.fechapago) fechapago 
          FROM ".$table." A where fechapago between '$fecha' AND '$fecha2' group by a.numnotcre ORDER BY fechapago ASC ";
  $result = mssqlConn::Listados($query);
  $obj= json_decode($result, true);
  
  $pagos = array();        
  foreach($obj as $fila){
    $pagos[$fila['numnotcre']]=$fila['modopago'];
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


$items=$pdf->getReportData($fecha,$fecha2); // Busca los datos de los citados en la base de datos

$lenObj   = sizeof($items); //Longitud del array
$subtotal =0;
$descuento=0;
$grantotal=0;

for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    if ($i<$lenObj) {
       //$pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
    


    //if ( !is_null($items[$i]['TOTALNOT']) || !empty($items[$i]['TOTALNOT']) ) {
   //     $pdf->Cell(50 , 5, $items[$i]['producto']    ,0,0,'L');   
   //     $pdf->Cell(10 , 5, '$ '.number_format((float)$items[$i]['TOTALNOT'], 2, '.', '')  ,0,1,'R');
          $desitems=$items[$i]['desitems'];
          $existencia=$items[$i]['existencia'];
          $compras=$items[$i]['compras'];
          $ventas=$items[$i]['ventas'];
          $devVentas=$items[$i]['devVentas'];
          $Ajustes_mas=$items[$i]['Ajustes_mas'];
          $Ajustes_neg=$items[$i]['Ajustes_neg'];
          $NE=$items[$i]['NE'];
          $NC=$items[$i]['NC']; 

          $total=(int)$existencia-(int)$ventas+(int)$compras+(int)$devVentas-(int)$NE+(int)$NC+(int)$Ajustes_neg+(int)$Ajustes_mas;                 
          
          $pdf->Cell(18 , 5, $desitems,0,0,'L');
          $pdf->Cell(60 , 5, $existencia,0,0,'R');
          $pdf->Cell(14 , 5, $compras,0,0,'R');
          $pdf->Cell(14 , 5, $ventas,0,0,'R');
          $pdf->Cell(14 , 5, $devVentas,0,0,'R');
          $pdf->Cell(14 , 5, $Ajustes_mas,0,0,'R');
          $pdf->Cell(14 , 5, $Ajustes_neg,0,0,'R');
          $pdf->Cell(14 , 5, $NE,0,0,'R');
          $pdf->Cell(14 , 5, $NC,0,0,'R');
          $pdf->Cell(14 , 5,  $total,0,1,'R'); 
  
}
 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 // $pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+170, $pdf->GetY());  //Dibuja una linea
 // $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
 // $pdf->Cell(80 , 5, '$ '.number_format((float)$subtotal, 2, '.',',')  ,0,0,'R');
 // $pdf->Cell(16 , 5, '$ '.number_format((float)$descuento, 2,'.',',')  ,0,0,'R');
 // $pdf->Cell(17 , 5, '$ '.number_format((float)$grantotal, 2,'.',',')  ,0,1,'R');

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