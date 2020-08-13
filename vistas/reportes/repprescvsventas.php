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
    $all     =$_GET['all'];
    $autorizada=$_GET['autorizada'];
    $codmed=$autorizada=$_GET['codmed'];
    $medico=$autorizada=$_GET['medico'];

}

class PDF extends FPDF
{
// Page header
  public  $fecha;
  public  $lineF;
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
    $this->SetTextColor(0);
    $this->SetFillColor(224,235,255);
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,7,iconv("UTF-8", "CP1250//TRANSLIT",'MEDICINA SISTÉMICA LLC'),0,1,'C');
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'PACIENTES VISTOS'),0,1,'C');
    $this->SetFont('Arial','B',14);
    $this->Cell(190,7,iconv("UTF-8", "CP1250//TRANSLIT",'Prescripción / Ventas'),0,1,'C');
     
    $this->Cell(188 , 5, $fecha.' al '.$fecha2  ,0,1,'C');
    $this->Cell(188 , 5,  $titulo   ,0,1,'C');
    $this->Cell(188 , 5,  $medico   ,0,1,'C');
    
    $this->Ln(8);

    $this->SetFont('Arial','B',9);
  
    $this->Cell(5 , 5, '#'   ,0,0,'R');
    $this->Cell(25 , 5, 'Fecha'  ,0,0,'R');   
    $this->Cell(15 , 5, 'Rec#'  ,0,0,'R');    
    $this->Cell(23 , 5, 'Paciente'  ,0,0,'R');
    
    $this->Cell(60 , 5, 'Visitas al CMR',0,0,'R');
  
    $fecha_1= explode( '/',$fecha);    
    $fecha_2= explode( '/',$fecha2);

    $mes=(int)$fecha_1[0];
    $mes=$mes-1;
    $periodoI=$meses[$mes].' '.$fecha_1[1].' '.$fecha_1[2];

    $mes=(int)$fecha_2[0];
    $mes=$mes-1; 
    $periodoF=$meses[$mes].' '.$fecha_2[1].' '.$fecha_2[2];
 
  
    $this->Line(10, $this->GetY()+5, $this->GetX(), $this->GetY()+5);  //Dibuja una linea
    $this->Ln();
}

// Page footer
function Footer()
{
	$this->SetTextColor(0);
    $this->SetFillColor(224,235,255);
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
  $query="Select  max(fecha) Fecha, a.record,nombres,medico,  count(*)  veces,a.codclien,a.codmedico from (
   Select CONVERT(VARCHAR(10),a.fecha_cita,110) fecha ,a.record, b.nombres, CONCAT(c.nombre,' ',c.apellido) medico,a.codclien,a.codmedico from Mconsultas a  
     inner join MClientes b On a.codclien=b.codclien
     inner join Mmedicos c on a.codmedico=c.Codmedico
     where a.fecha_cita between '$fecha' AND '$fecha2' and a.codmedico ='$codmed' and a.asistido=3
     ) a group by a.record,nombres,medico,a.codclien,a.codmedico order by record desc";


  $query="SELECT  max(fecha) Fecha, a.record,nombres,medico,  count(*)  veces,a.codclien,a.codmedico from (
   Select CONVERT(VARCHAR(10),a.fecha_cita,110) fecha ,a.Historia record, b.nombres, CONCAT(c.nombre,' ',c.apellido) medico,a.codclien,a.codmedico from VIEW_Mconsultas_02 a  
     inner join MClientes b On a.codclien=b.codclien
     inner join Mmedicos c on a.codmedico=c.Codmedico
     where a.fecha_cita between '$fecha' AND '$fecha2' and a.codmedico ='$codmed'and A.asistido=3
     ) a group by a.record,nombres,medico,a.codclien,a.codmedico order by record desc
";
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


 function getPrescripcion($fecha, $fecha2,$codclien){

    $query="SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo 
   from prescrito a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' order by codclien ,coditems   ";
    $res = mssqlConn::Listados($query);
    $obj= json_decode($res, true);
    return $obj;   
 }

  function getPrescripcionProd($fecha, $fecha2,$codclien){
    $query="SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo, 'Prod' grupo
   from prescritop a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2'  order by codclien ,coditems  ";
     $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  return $obj;   

  }
  function getPrescripcionLaser($fecha, $fecha2,$codclien){
     $query="SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo, 'Laser' grupo
   from prescritol a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2'  order by codclien ,coditems  ";
     $res = mssqlConn::Listados($query);
     $obj= json_decode($res, true);
  return $obj;   
  }

  function getPrescripcionGeneral($fecha, $fecha2,$codclien){
    $query="SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo, 'Suero-CM' grupo
   from prescrito a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between  '$fecha' AND '$fecha2'  
union
SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo, 'Laser' grupo
   from prescritol a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between  '$fecha' AND '$fecha2'  
union 
SELECT i.desitems, a.numfactu,a.fechafac,a.coditems,a.cantidad,a.precunit, a.usuario,a.codclien, a.Codmedico, a.cod_grupo,a.cod_subgrupo, 'Prod' grupo
   from prescritop a    
   inner join MInventario i on a.coditems=i.coditems
   where a.fechafac between  '$fecha' AND '$fecha2'  order by codclien ,coditems,grupo ";
    $res = mssqlConn::Listados($query);
     $obj= json_decode($res, true);
  return $obj;   
  }

function getVenta($fecha, $fecha2,$codclien){
   $query="SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo
   from cma_mfactura a   
   inner join cma_DFactura d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3 and i.cod_subgrupo not in ('CONSULTA')  order by codclien,coditems";
    $res = mssqlConn::Listados($query);
    $obj= json_decode($res, true);
    return $obj; 
 }

 function getVentaProd($fecha, $fecha2,$codclien){   
   $query="SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo
   from mfactura a   
   inner join DFactura d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3  order by codclien,coditems";
    $res = mssqlConn::Listados($query);
    $obj= json_decode($res, true);
    return $obj; 
 }

 function getVentaLaser($fecha, $fecha2,$codclien){
   $query+"SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo
   from MSSMFact a   
   inner join MSSDFact d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3 order by codclien,coditems";
   $res = mssqlConn::Listados($query);
    $obj= json_decode($res, true);
    return $obj;
 }

  function getVentaGeneral($fecha, $fecha2,$codclien){

   $query="SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo, 'Prod' grupo
   from mfactura a   
   inner join DFactura d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3 and i.cod_subgrupo not in ('CONSULTA') 
   Union all 
   SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo, 'Laser' grupo
   from MSSMFact a   
   inner join MSSDFact d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3 and i.cod_subgrupo not in ('CONSULTA')
   union all 
   SELECT  i.desitems, a.numfactu,a.fechafac,d.coditems,d.cantidad,d.precunit, a.usuario, a.codclien, a.Codmedico, i.cod_grupo,i.cod_subgrupo, 'Suero-CM' grupo
   from cma_mfactura a   
   inner join cma_DFactura d On d.numfactu=a.numfactu
   inner join MInventario  i On d.coditems=i.coditems
   where a.fechafac between '$fecha' AND '$fecha2' and a.statfact=3 and i.cod_subgrupo not in ('CONSULTA')  order by codclien,coditems   ";
    $res = mssqlConn::Listados($query);
    $obj= json_decode($res, true);
    return $obj; 
  }

 function setFecha($fecha){
   $this->fecha=$fecha;
 }
 function getFecha(){

 }

 function setLineFeed($lineF){
   $this->lineF=$lineF;
 }
 function getLineFeed(){
   return $this->lineF;
 }


 // Colored table
function FancyTable($header, $data,$x,$y)
{

    $ap=0;
    if (!is_null($data[0])) {
       $ap= count($data[0]);  
    }

    $av=0;
    if (!is_null($data[1])) {
       $av= count($data[1]);
    }
    
    
   
    $long=0;
    if ($ap>=$av) {
      $long=$ap;
    }else if($av>=$ap){
      $long=$av;
    }

    if ($long>0) {


    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    // Header
    $wt = array(95, 95);
    $headert = array('Prescripcion','Venta' ); 

    $w = array(75, 20,75, 20);

    $this->SetFillColor(232,232,232);
    $this->SetTextColor(0);
    $this->SetFont('');
    // Data
    
    $fill = false;

       for ($i=0; $i <$long ; $i++) { 

           $alignap="L";
            try{
                $ap1=utf8_decode($data[0][$i]['desitems']);
                $ap2=$data[0][$i]['cantidad'];

              if ( is_null($ap2) || empty($ap2)) {
                $ap2="0";
              }
              if (is_null($ap1) || empty($ap1)) {
                $ap1="- - -";
                $alignap="C";
              }

            }catch(Exception $e){

            }
        
           $alignav="L";   
           try{
              $av1= utf8_decode($data[1][$i]['desitems']);
              $av2= $data[1][$i]['cantidad']  ;
              if ( is_null($av2) || empty($av2)) {
                $av2="0";
              }
              if (is_null($av1) || empty($av1)) {
                $av1="- - -";
                $alignav="C";   
              }
           }catch(Exception $e){

           }


          $this->Cell($w[0],6,$ap1,'',0,$alignap,1);
          $this->Cell($w[1],6,$ap2,'',0,'C',1);       
          $this->Cell($w[2],6,$av1,'',0,$alignav,1);
          $this->Cell($w[3],6,$av2,'',0,'C',1);       
          $this->Ln();
          $fill = !$fill;

      }
       
    } 
    

    
}



}



$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();


$items=$pdf->getPacientesVistos($fecha,$fecha2,$codmed);
$itemsVeces=$pdf->getPacientesVistosVeces($fecha,$fecha2,$codmed);


if ( strtolower($all) =="false") {
   $precrip=$pdf->getPrescripcionGeneral($fecha, $fecha2,'');
   $venta=$pdf->getVentaGeneral($fecha, $fecha2,'');

}else {
  $precrip=$pdf->getPrescripcion($fecha, $fecha2,'');
  $venta=$pdf->getVenta($fecha, $fecha2,'');
}




#PRESCRIPTION
if (count($precrip)>0) {
   $arrPres  = array();
   for ($p=0; $p <count($precrip) ; $p++) { 
        $cliente=$precrip[$p]['codclien'];

        $arrDetalle  = array(
             'desitems' => $precrip[$p]['desitems'],
             'cantidad' => $precrip[$p]['cantidad'],
             'tipo'=>'Prescripcion'
        );

        $arrPres[$cliente][] =$arrDetalle ;
   }
}

#VENTA
if (count($venta)>0) {
   $arrVenta  = array();
   for ($v=0; $v <count($venta) ; $v++) { 
        $cliente=$venta[$v]['codclien'];

        $arrDetalle  = array(
             'desitems' => $venta[$v]['desitems'],
             'cantidad' => $venta[$v]['cantidad'],
             'tipo'=>'Venta'
        );

         $arrVenta[$cliente][] =$arrDetalle ; 
   }
}

#GENERAL
$aGeneral = array();
for ($g=0; $g <sizeof($items); $g++) { 

   $codclien=$items[$g]['codclien'];
   $aGeneral[$codclien][]=$arrPres[$codclien];
   $aGeneral[$codclien][]=$arrVenta[$codclien];
}



$lenObj   = sizeof($items); //Longitud del array
$sumveces =0;

 $pdf->SetFillColor(230,230,230);

for($i=0;$i<$lenObj;$i++){
    
          $pdf->SetFont('Arial','',8);
          $numerador = $i+1;

          $fecha=$items[$i]['Fecha'];
          $record=$items[$i]['record'];
          $nombres=$items[$i]['nombres'];
          $veces=$items[$i]['veces'];

	        $codclien=$items[$i]['codclien'];
          $details=$aGeneral[$codclien]; 
        
	        $pdf->SetFillColor(232,232,232);

          $fill=0;
	        if ( !is_null($details[0]) || !is_null($details[1]) ) {

	      	  $ap=0;
              if (!is_null($data[0])) {
                 $ap= count($data[0]);  
              }

              $av=0;
              if (!is_null($data[1])) {
                 $av= count($data[1]);
              }

              if ( $av>0 || $ap>0) {
              	$fill=1;
              } 
              $fill=1;

              $pdf->SetTextColor(41, 128, 185);
              $pdf->SetFont('Arial','B',8);
              if ($numerador>1) {
                 $pdf->Ln();
              }
              
              
              $pdf->setLineFeed(TRUE);
	        }else{
              $toF = $pdf->getLineFeed();
              if ($toF) {
                 $pdf->Ln();
               } 
             $pdf->setLineFeed(FALSE);
        }

          $sumveces +=$veces;
      
          $pdf->Cell(18, 5, $numerador,0,0,'L',$fill);
          $pdf->Cell(14, 5, $fecha,0,0,'R',$fill);
          $pdf->Cell(14, 5, $record,0,0,'R',$fill);
          $pdf->Cell(54, 5, utf8_decode($nombres) ,0,0,'L',$fill); 
          $pdf->Cell(14, 5, $veces,0,1,'R',$fill);

  		    $pdf->SetTextColor(0);
          if ( !is_null($details[0]) || !is_null($details[1]) ) {

             $x_coor= $pdf->GetX();
             $y_coor= $pdf->GetY();

             $header = array('Producto', 'Cantidad','Producto','Cantidad');
             $pdf->FancyTable($header,$details,0,0);

         
             $pdf->SetX($x_coor);
          }
          

 
  
}
 $pdf->SetFont('Arial','B',8);
 $pdf->Ln(2);
 $pdf->Output();
?>