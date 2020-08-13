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
    $codpro=$_GET['codpro'];
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
      $codpro=$_GET['codpro'];
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

    $this->SetFont('Arial','B',8);
    //$this->Cell(1 , 5, '#'  ,0,0,'R');
    // $this->Cell(54 , 5, 'A quien pueda interesar:' ,0,0,'R');
    // $this->Ln(20);
    $this->Cell(18,5, 'Producto'   ,0,0,'R');
    $this->Cell(70,5,'Precio'  ,0,0,'R');
    $this->Cell(17,5,'Costo'  ,0,0,'R');
    $this->Cell(20,5,'U. vendidas',0,0,'R');
    $this->Cell(18,5,'Venta Bruta',0,0,'R');
    $this->Cell(18,5,'Costo Bruto'     ,0,0,'R');
    $this->Cell(28,5,'Venta por servicio'   ,0,0,'R');
    
    
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



 function getReportData($fecha, $fecha2,$id,$codpro){
 

  $query_b3="Alter View view_cocientes as Select  t1.coditems, t1.precunit,t1.costo ,(t2.ventas-t2.anulaciones)-(t2.NotasCreditos)ventas, t2.fecha, t2.anulaciones anuladas,t1.desitems producto
  from (select pr.coditems, pr.precunit,mi.costo,mi.desitems from mprecios pr inner join minventario mi on pr.coditems=mi.coditems and mi.Prod_serv='P' where codtipre='00' and  mi.activo=1  ) as t1 join ( select ci.fechacierre fecha, ci.coditems, ci.ventas,ci.anulaciones ,ci.NotasCreditos  from dcierreinventario ci) as t2 on t1.coditems=t2.coditems";
  mssqlConn::insert($query_b3);

  $query="Select producto,max(precunit) precunit, max(costo) costo , sum(ventas) ventas, sum(precunit*ventas) vbrutas, sum(ventas*costo)cbruto from view_cocientes vt  where fecha between '$fecha' and '$fecha2'  group by producto ";
  if ($id=="false") {
    $query="Select producto,max(precunit) precunit, max(costo) costo , sum(ventas) ventas, sum(precunit*ventas) vbrutas, sum(ventas*costo)cbruto from view_cocientes vt  where fecha between '$fecha' and '$fecha2' and coditems='$codpro'  group by producto ";
  }

  $res = mssqlConn::Listados($query); 
  $obj= json_decode($res, true);
  return $obj;        
 }

 function getVentasServicios($fecha, $fecha2){
  //KEY VALUE CLAVE VALOR

   $query_a4="Select  sum( total ) as totalv from cma_mfactura where statfact=3 and fechafac between '$fecha' and '$fecha2' ";

 
 
 $result = mssqlConn::Listados($query_a4);
 $obj= json_decode($result, true);
  
  $total=0;
  foreach($obj as $fila){
   $total=$fila['totalv'];
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


$items    = $pdf->getReportData($fecha,$fecha2,$id,$codpro); // Busca los datos de los citados en la base de datos

$total =  $pdf->getVentasServicios($fecha,$fecha2);

$lenObj   = sizeof($items); //Longitud del array
$unidades =0;
$ventas=0;
$costoven=0;

for($i=0;$i<=$lenObj;$i++){
    //$pdf->Cell(0,10,'Printing line number '.$i,0,1);
    $pdf->SetFont('Arial','',8);
    $numerador = $i+1;
    if ($i<$lenObj) {
       //$pdf->Cell(1 , 5, $numerador  ,0,0,'R');
    }
 
      

       if ( (float)$items[$i]['ventas']!=0 ) {  

     
          $link=$pdf->AddLink();

          $v=$items[$i]['v'];
    
          $pdf->Cell(18 , 5, $items[$i]['producto'] ,0,0,'L');
          
          $pdf->Cell(70,5,number_format((float)$items[$i]['precunit'], 2, '.', ',')   ,0,0,'R');


          // $avg=0;
          // try {
          //   $cot=$items[$i]['personas'];
          //   $xt=$total;
          //    if ((float)$items[$i]['personas']!=0 ) {
          //       $avg=(float)$items[$i]['personas']/(float)$total;  
          //       $avg=$avg*100;
          //     } 
             
          //  } catch (Exception $e) {
             
          //  } 
           /*
$pdf->SetY(($pdf->GetY()+5)); 
$pdf->Text(10,  $pdf->GetY(), "
           */
          
          
          $pdf->Cell(15,5,number_format((float)$items[$i]['costo'], 2, '.', ',')   ,0,0,'R');
          $pdf->Cell(15,5,$items[$i]['ventas']   ,0,0,'R');          
          $pdf->Cell(20,5,number_format((float)$items[$i]['vbrutas'], 2, '.', ',')   ,0,0,'R');
          $pdf->Cell(20,5,number_format((float)$items[$i]['cbruto'], 2, '.', ',')   ,0,0,'R');
          
          //$pdf->Image("../../img/boton.png",8,11.3,73,0,"","repreatedproddetails.php");
          ///$url='http://'.$curserver.'/cma/vistas/reportes/';
          //$pdf->Image("../../img/boton.png",$pdf->GetX()+20,$pdf->GetY()+1,10,4,"",$url."repreatedproddetails.php?v=$v&fecha=$fecha&fecha2=$fecha2&id=$id&titulo=$titulo");
          
          $pdf->Cell(50,5,""   ,0,1,'R');
          //$pdf->Write(55,5,"http://www.google.com",$link);

          $unidades +=(float)$items[$i]['ventas'] ;
          $ventas+=(float)$items[$i]['vbrutas'] ;
          $costoven+=(float)$items[$i]['cbruto'] ;


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
 $pdf->Line($pdf->GetX()+90, $pdf->GetY(), $pdf->GetX()+190, $pdf->GetY());  //Dibuja una linea
 $pdf->Cell(30 , 5, 'Total '    ,0,0,'L');
 $pdf->Cell(87 , 5, number_format((float)$unidades, 0, '.',',')  ,0,0,'R');
 $pdf->Cell(22 , 5, '$ '.number_format((float)$ventas, 2,'.',',')  ,0,0,'R');
 $pdf->Cell(19 , 5, '$ '.number_format((float)$costoven, 2,'.',',')  ,0,0,'R');
 $pdf->Cell(19 , 5, '$ '.number_format((float)$total, 2,'.',',')  ,0,1,'R');
 $pdf->SetFont('Arial','B',10);
 $pdf->Cell(82 , 5, 'M1: '.number_format((float)$ventas/(float)$costoven, 2,'.',',')  ,0,1,'R');
 $pdf->Cell(82 , 5, 'M2: '.number_format(((float)$ventas+(float)$total)/(float)$costoven, 2,'.',',')  ,0,1,'R');
 
 

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