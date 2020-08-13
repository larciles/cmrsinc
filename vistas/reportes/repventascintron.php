<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
require_once '../../db/mssqlconn.php';
require_once "../../clases/fpdf/fpdf.php";
require('../../controllers/ComisionesController.php');
require('../../controllers/CMA_DFacturaController.php');

$dbmsql = mssqlConn::getConnection();
$start=50;
if(isset($_GET['fecha']))
{
    $fecha    =$_GET['fecha'];
    $fecha2  =$_GET['fecha2'];
    $usuario  =$_GET['usuario'];
    $idempresa=$_GET['idempresa'];
    $total_parcial=$_GET['tot_par'];
    if (isset($_GET['start'])) {
       $start= (float) $_GET['start'];
    }
    if (isset($_GET['tiporeporte'])) {
       $tiporeporte= $_GET['tiporeporte'];
    }
     $with_serv="0";
    if ($total_parcial=="false") {
       $with_serv='1';  
    }

    
}


#constructor
$pdf = new FPDF();
#crea una nueva pagina


#establace la fuente
$pdf->SetFont('Arial');


#muestra la pagina
#SEGUNDA PARTE VENTAS DE SERVICIO
$comisionesController = new ComisionesController();
$cmadFacturaController = new CMA_DFacturaController();

$subtotal=0;
$descuento=0;
$totalc=0;
$comision_total=0;

// $with_serv='1';

$vservicos=getMaster($fecha, $fecha2,"333",1,$usuario,$tiporeporte,$with_serv);
$totalservicos=getMasterTotal($fecha, $fecha2,"333",1,$usuario,$tiporeporte,$with_serv);
$total =Total($fecha, $fecha2,"333",1,$usuario,$tiporeporte,$with_serv);


$pdf->SetFont('Arial','B',12);
try{
  if(sizeof($vservicos)>0) {
    $size=sizeof($vservicos);
    $pdf->AddPage();

    $pdf->SetY($pdf->GetY());
    $pdf->SetY($pdf->GetY()+7);
 
    $pdf->Cell(200 , 5, 'AMNISOMA - Bayamon PR'  ,0,1,'C');
    $pdf->Cell(200 , 5, 'Detalle de Servicios',0,1,'C');
    $pdf->Cell(200 , 5, ' Dr. Cintron ',0,1,'C');
    $pdf->Cell(200 , 5, $fecha. ' - '.$fecha2   ,0,1,'C');

    $pdf->SetY($pdf->GetY()+3);
    $pdf->SetFont('Arial');
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(11 , 5, 'Factura'  ,0,0,'R');
    $pdf->Cell(14 , 5, 'Fecha'  ,0,0,'R');
    $pdf->Cell(20 , 5, 'Cliente'  ,0,0,'R');

    if ($total_parcial=="true") {
        $pdf->Cell($pdf->GetX()-7 , 5, 'Subtotal'  ,0,0,'R');
        $pdf->Cell($pdf->GetX()-77, 5, 'Descto'  ,0,0,'R');   
        $pdf->Cell($pdf->GetX()-117 , 5, 'Total'  ,0,0,'R');    
    }else{
      $pdf->Cell($pdf->GetX()-7 , 5, ' '  ,0,0,'R');
        $pdf->Cell($pdf->GetX()-77, 5, ' '  ,0,0,'R');   
        $pdf->Cell($pdf->GetX()-117 , 5, ' '  ,0,0,'R');    
    }


    $pdf->SetFont('Arial','',9);


   
    for ($i=0; $i <$size ; $i++) { 

        $pdf->SetXY(5,$pdf->GetY()+6);  
        $pdf->SetFont('Arial','B',9); 
        $pdf->Cell($pdf->GetX()+12,4,$vservicos[$i]['numfactu'],0,0,'R');
        $pdf->SetFont('Arial','',9);
        $pdf->Cell($pdf->GetX(),4,$vservicos[$i]['fecha'],0,0,'L'); 

         if ($total_parcial=="true") {       
        

        $pdf->Cell($pdf->GetX(),4,  substr($vservicos[$i]['nombres'],0,20 )  ,0,0,'L');

        $pdf->Cell($pdf->GetX(),4,number_format($vservicos[$i]['subtotal'], 2, ".", ","),0,0,'L');
        $pdf->Cell(-50,4,number_format($vservicos[$i]['descuento'], 2, ".", ","),0,0,'R');   
        $pdf->SetFont('Arial','B',9);      
        $pdf->Cell(18,4,number_format($vservicos[$i]['total'], 2, ".", ","),0,1,'R');

      }else{
        $pdf->Cell($pdf->GetX(),4,  substr($vservicos[$i]['nombres'],0,20 )  ,0,1,'L');
      }


        $pdf->SetFont('Arial','',9);
        $y2_axis=$y2_axis+6;


        $subtotal=$subtotal+(float)$vservicos[$i]['subtotal'];
        $descuento=$descuento+(float)$vservicos[$i]['descuento'];
        $totalc=$totalc+(float)$vservicos[$i]['total'];




      $dvs=getDetails($vservicos[$i]['numfactu'],+(float)$vservicos[$i]['subtotal']);
     

      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(10,4,"Producto",0,0,'L');
      $pdf->Cell(80,4,"Cantidad",0,0,'R');
      if ($total_parcial=="true") {       
      $pdf->Cell(20,4,"Precio",0,0,'R');
      $pdf->Cell(26,4,"Descuento",0,0,'R');
      $pdf->Cell(20,4,"Total",0,0,'R');
    }


      $pdf->Cell(30,4,"Honorarios",0,1,'R');

      $pdf->SetFont('Arial','',9);
      try{
          if(sizeof($dvs)>0) {         
            
            for ($j=0; $j <sizeof($dvs) ; $j++) { 


                $coditems= $dvs[$j]['coditems'];
                $cantidad= $dvs[$j]['cantidad'];

                $arrcomision=array('codmedico'=>'333','coditems'=>$coditems,'activo'=>'1');
                $r_comision=$comisionesController->readAllW($arrcomision);

                $apli_comision=$r_comision[0]['comision'];
                $tipo_comision=$r_comision[0]['tipo'];


                $line_subtotal =((int)$dvs[$j]['cantidad']*(float)$dvs[$j]['precunit'])-(float)$dvs[$j]['descuento'];
                $line_comision=0;


                if ((float)$vservicos[$i]['total']!=0) {
                  
                
                      if ($tipo_comision=="m") {
                        $line_comision=1;
                      } else if ($tipo_comision=="p"){
                          $line_comision=  ($line_subtotal* $apli_comision)/100;
                      } else if ($tipo_comision=="mu"){
                         $line_comision= $apli_comision* $cantidad;
                      }else if ($tipo_comision=="md"){
                        $dividido=0;
                        if ($cantidad>1) {
                          $dividido=$apli_comision/2;
                        }
                        $line_comision= $apli_comision+$dividido;
                      }

                }

                $fac_or_dev=1;

                if ((int)$vservicos[$i]['subtotal']<0) {
                		$fac_or_dev=-1; 
                }

                $comision_total=$comision_total+($line_comision*$fac_or_dev);


              
                $form_precunit =number_format( $dvs[$j]['precunit']*$fac_or_dev, 2, ".", ",");
                $form_descuento=number_format( $dvs[$j]['descuento']*$fac_or_dev, 2, ".", ",");
                $form_subtotal =number_format( $line_subtotal*$fac_or_dev, 2, ".", ",");
                $form_comision =number_format( $line_comision*$fac_or_dev, 2, ".", ",");

                                
                $pdf->Cell(10,4,$dvs[$j]['desitems'],0,0,'L');
                $pdf->Cell(75,4,$dvs[$j]['cantidad'],0,0,'R');
                   if ($total_parcial=="true") { 
                $pdf->Cell(25,4,$form_precunit,0,0,'R');
                $pdf->Cell(25,4,$form_descuento,0,0,'R');
                $pdf->Cell(25,4,$form_subtotal,0,0,'R');
                $pdf->Cell(25,4,$form_comision,0,1,'R');
              }else{
                $pdf->Cell(35,4,$form_comision,0,1,'R');
              }

    
            }
            $pdf->SetXY(5,$pdf->GetY()+6);   

          }

      }catch(Exception $e){

      }


    }    

 $pdf->SetFont('Arial','B',9);
 $y2_axis=$pdf->GetY()+2;
 //$pdf->SetXY(5,$pdf->GetY()+4);
 

 $pdf->SetXY(5,$pdf->GetY()+2);   
 $pdf->Cell(20,4,str_repeat("=",105),0,0,'L');

 $pdf->SetXY(5,$pdf->GetY()+4);   

 $pdf->Cell(20,4,'Total Servicios ',0,0,'L');

  if ($total_parcial=="true") {
 $pdf->Cell(75,4,number_format( $subtotal, 2, ".", ","),0,0,'R');
 $pdf->Cell(25,4,number_format($descuento, 2, ".", ","),0,0,'R'); 
 $pdf->Cell(25,4,number_format($totalc, 2, ".", ","),0,0,'R');
 $pdf->Cell(45,4,number_format($comision_total, 2, ".", ","),0,1,'R');
}else{
  $pdf->Cell(105,4,number_format($comision_total, 2, ".", ","),0,1,'R');
}
 



#TOTAL CONSULTA
 $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE' ";
 $consultas = $cmadFacturaController->readUDF($query);

 $total_consultas= $consultas[0]['total'];


#PACIENTES VISTOS
 $query="SELECT  COUNT(*) vistos FROM VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2' AND  a.statfact=3  And  a.total>=0 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'";  

 $pacientes = $cmadFacturaController->readUDF($query);
 $vistos=$pacientes[0]['vistos'];


 #CONSULTAS DEVUELTAS
  $query="SELECT  distinct z.numfactu, b.fechafac,convert(varchar(10), cast(b.fechafac as date), 101) fecha,
 (
 select  sum(a.cantidad*a.precunit) 
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) subtotal,
 (
 select  sum(descuento)
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento))
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) total
 from cma_DFactura z
 inner join cma_MFactura b On z.numfactu=b.numfactu  
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechafac between '$fecha' AND '$fecha2'   and b.statfact=3    AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000') and c.clase_a='ia'
 order by z.numfactu desc";


 $devueltas = $cmadFacturaController->readUDF($query);

 $c_devueltas=0;
 try {
    $c_devueltas=sizeof($devueltas);
 } catch (Exception $e) {
   
 }




$total_cdevueltas=0;
 try {
   $total_cdevueltas=($c_devueltas*$total_consultas)/ $vistos;
 } catch (Exception $e) {
   
 }


 $total_cdevueltas=$total_cdevueltas*-1;
 $supercomision= ($comision_total+$total_consultas)+$total_cdevueltas;

 
   if ($total_parcial=="true") {
   
    if ($with_serv=="0") {
       $pdf->Cell(20,4,'Total Consultas              '.'('.$vistos.')',0,0,'L');
       $pdf->Cell(165,4,number_format($total_consultas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Consultas  Devueltas   '.'('.$c_devueltas.')',0,0,'L');
       $pdf->Cell(165,4,number_format($total_cdevueltas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Honorarios ',0,0,'L');
       $pdf->Cell(165,4,number_format($supercomision, 2, ".", ","),0,1,'R');
      
    }else{
       $pdf->Cell(20,4,'Total Consultas              '.'('.$vistos.')',0,0,'L');
       $pdf->Cell(165,4,number_format($total_consultas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Consultas  Devueltas   '.'('.$c_devueltas.')',0,0,'L');
       $pdf->Cell(165,4,number_format($total_cdevueltas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Honorarios ',0,0,'L');
       $pdf->Cell(165,4,number_format($supercomision, 2, ".", ","),0,1,'R');
      
    }

    

   }else{

    if ($with_serv=="0") {
       $pdf->Cell(20,4,'Total Consultas              '.'('.$vistos.')',0,0,'L');
       $pdf->Cell(100,4,number_format($total_consultas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Consultas  Devueltas   '.'('.$c_devueltas.')',0,0,'L');
       $pdf->Cell(100,4,number_format($total_cdevueltas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Honorarios ',0,0,'L');
       $pdf->Cell(100,4,number_format($supercomision, 2, ".", ","),0,1,'R');      
    }else{
             $pdf->Cell(20,4,'Total Consultas              '.'('.$vistos.')',0,0,'L');
       $pdf->Cell(100,4,number_format($total_consultas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Consultas  Devueltas   '.'('.$c_devueltas.')',0,0,'L');
       $pdf->Cell(100,4,number_format($total_cdevueltas, 2, ".", ","),0,1,'R');
       $pdf->Cell(20,4,'Total Honorarios ',0,0,'L');
       $pdf->Cell(100,4,number_format($supercomision, 2, ".", ","),0,1,'R');      

    }


    

   }

 




 /*

 $pdf->SetFont('Arial','B',9);
 $y2_axis=$pdf->GetY()+2;
 $pdf->SetXY(5,$pdf->GetY()+4);
 

  for ($i=0; $i <sizeof($total) ; $i++) { 
     $_subtotal+=$total[$i]['subtotal'];
     $_descuento+=$total[$i]['descuento'];
     $_total+=$total[$i]['total'];
  }

 $pdf->SetXY(5,$pdf->GetY()+4);   
 $pdf->Cell($pdf->GetX()+25,4,'Total General ',0,0,'R');






 $pdf->Cell($pdf->GetX()+34,4,number_format($subtotal, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-80,4,number_format($descuento, 2, ".", ","),0,0,'R');
 
 $pdf->Cell($pdf->GetX()-100,4,number_format($totalc, 2, ".", ","),0,1,'L');
 
*/


  }
}catch(Exception $e){

}





$pdf->Output();

#--------------------------------------------------
function getCash($fecha, $usuario,$typesystem,$idempresa='1',$tiporeporte, $fecha2){
    
$query="SELECT valor,sum(monto) monto from cuadre WHERE fechafac BETWEEN '$fecha' AND '$fecha2' and id_centro = 'S' group by valor  order by valor desc";

$res = mssqlConn::Listados($query);

$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getGrupo($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST  where fechapago  BETWEEN '$fecha' AND '$fecha2' and statfact=3   and id_centro = '2'  and cod_subgrupo ='CEL MADRE'    group by modopago,codforpa  order by modopago";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFactBruta($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT sum(total+monto_flete) monto, count(*) facturas from VentasDiariasCMACST where fechafac BETWEEN '$fecha' AND '$fecha2' and statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'   ";    

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getDevoluciones($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

 $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasCMACST a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac BETWEEN '$fecha' AND '$fecha2'  and a.doc='04' and statfact=3 and  cod_subgrupo ='CEL MADRE'  ";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}

#--------------------------------------------------
function getVentas($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario,a.Historia, a.initials FROM VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3  And  a.total>=0 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'  /* and usuario='$usuario' */ Order by  a.numfactu   desc ";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getMetodoPago($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

 $query="SELECT d.numfactu, d.modopago From VIEWpagosPRCMACST d WHERE d.fechapago between '$fecha' AND '$fecha2'  and cod_subgrupo ='CEL MADRE'   Order By d.modopago";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
$pagos = array();

foreach($obj as $fila){
  $mp= setPaymentMethod($fila['numfactu'],$fecha, $fecha2);
  	//$pagos[$fila['numfactu']]=$fila['modopago'];
  $pagos[$fila['numfactu']]=$mp;
}
return $pagos;
}
#--------------------------------------------------
function setPaymentMethod($numfactu,$fecha, $fecha2){
  $query="SELECT d.DesTipoTargeta, d.monto From VIEWpagosPRCMA d WHERE d.fechapago BETWEEN '$fecha' AND '$fecha2'  AND d.numfactu ='$numfactu' ";
  $res = mssqlConn::Listados($query);
  $objr= json_decode($res,true);
  $lenres = sizeof($objr);
  $mp='';
  if ($lenres>1) {
      for ($i=0; $i <$lenres ; $i++) {          
         $spl =   substr($objr[$i]['DesTipoTargeta'],0,4);
         if ($i <$lenres-1) {
             $mp=$mp.$spl.'/';
         }else{
             $mp=$mp.$spl;
         }
         
      }
  }else  if ($lenres==1){
     $mp= $objr[0]['DesTipoTargeta'];
  }
  return  $mp; 
}

#--------------------------------------------------
function getTotal($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete)) total FROM VentasDiariasCMACST a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha2' AND a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'   And   a.doc='01'  ";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFacDevueltas($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha2' AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'     And  a.total<0  /* and usuario='$usuario'  Order by a.numfactu desc */ "; 

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getTotalFacDevueltas($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'     And  a.total<0 ";     

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getGranTotal($fecha,$idempresa='1',$tiporeporte,$usuario, $fecha2){

$query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'   ";

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
 
 
function getGrupoVentasTotal($fecha,$idempresa='1',$usuario,$tiporeporte, $fecha2){ 

        $query="SELECT a.usuario, sum( a.subtotal) subtotal, sum(a.descuento) descuento,  (sum(a.total)+sum(a.monto_flete))  monto, sum( a.TotImpuesto) TotImpuesto, sum(a.monto_flete) monto_flete   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3  And  a.total>=0 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'    group by  usuario Order by monto desc ";

        $res = mssqlConn::Listados($query);
        $obj= json_decode($res, true);
        $lenObj = sizeof($obj);
        return $obj;
    // }
}
#-------------------------------------------------------------------------------
# REPORTE DE VENTAS DE SERVICIOS
function getMaster($fecha, $fecha2,$codmedico,$idempresa,$usuario,$tiporeporte,$with_serv){

if ($with_serv=="0") {
  $query="SELECT *,convert(varchar(10), cast(a.fechafac as date), 101) fecha,b.nombres, b.Historia from cma_MFactura a  inner join MClientes b On a.codclien=b.codclien  where a.fechafac between '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3 and a.numfactu in (select distinct numfactu from cma_DFactura a where  a.fechafac between '$fecha' and '$fecha2' and a.cod_subgrupo in('CEL MADRE','INTRAVENOSO','TERAPIA LASER') and a.coditems in( select b.coditems from exclusivos b where b.Codmedico='$codmedico'  )) order by a.numfactu desc ";

   $query="SELECT *,convert(varchar(10), cast(a.fechafac as date), 101) fecha, (select nombres from MClientes b where a.codclien=b.codclien )nombres, (select Historia  from MClientes b where a.codclien=b.codclien ) Historia
 from cma_MFactura a  
 where a.fechafac between  '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3  order by a.numfactu desc";
           
$query=" SELECT  distinct z.numfactu, b.fechafac,convert(varchar(10), cast(b.fechafac as date), 101) fecha,
 (
 select  sum(a.cantidad*a.precunit) 
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) subtotal,
 (
 select  sum(descuento)
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento))
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) total



 from cma_DFactura z
 inner join cma_MFactura b On z.numfactu=b.numfactu  
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechafac between   '$fecha' and '$fecha2' and b.statfact=3    AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000')
 order by z.numfactu desc
";





$query="
SELECT  distinct z.numfactu, b.fechafac,convert(varchar(10), cast(b.fechafac as date), 101) fecha,nombres,
 (
 select  sum(a.cantidad*a.precunit) 
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) subtotal,
 (
 select  sum(descuento)
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento))
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) total

 from cma_DFactura z
 inner join cma_MFactura b On z.numfactu=b.numfactu  
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechafac between   '$fecha' and '$fecha2'  and b.statfact=3  AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000')

union all

  select  distinct z.numnotcre, b.fechanot,convert(varchar(10), cast(b.fechanot as date), 101) fecha,nombres,
 (
 select  sum(a.cantidad*a.precunit) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre
 ) subtotal,
 (
 select  sum(descuento) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre 
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento)) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre
 ) total

 from CMA_Dnotacredito z
 inner join CMA_Mnotacredito b On z.numnotcre=b.numnotcre
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechanot between  '$fecha' and '$fecha2'  and b.statnc=3  AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000')
 order by numfactu desc
";

}else{
/// SIN LOS SERVICIOS
 $query=" SELECT  distinct z.numfactu, b.fechafac,convert(varchar(10), cast(b.fechafac as date), 101) fecha,nombres,
 (
 select  sum(a.cantidad*a.precunit) 
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) subtotal,
 (
 select  sum(descuento)
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento))
 from cma_DFactura a 
 where z.numfactu=a.numfactu
 ) total

 from cma_DFactura z
 inner join cma_MFactura b On z.numfactu=b.numfactu  
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechafac between   '$fecha' and '$fecha2'  and b.statfact=3  AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000')
    And c.cod_subgrupo='CEL MADRE'

union all

  select  distinct z.numnotcre, b.fechanot,convert(varchar(10), cast(b.fechanot as date), 101) fecha,nombres,
 (
 select  sum(a.cantidad*a.precunit) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre
 ) subtotal,
 (
 select  sum(descuento) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre 
 ) descuento,

 (
 select  (sum(a.cantidad*a.precunit) -sum(descuento)) *-1
 from CMA_Dnotacredito a 
 where z.numnotcre=a.numnotcre
 ) total

 from CMA_Dnotacredito z
 inner join CMA_Mnotacredito b On z.numnotcre=b.numnotcre
 inner join MInventario c On c.coditems=z.coditems
 inner join MClientes d On d.codclien=b.codclien   
 where b.fechanot between  '$fecha' and '$fecha2'  and b.statnc=3  AND b.codmedico='333' and z.coditems  not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ,'0000000000')
    And c.cod_subgrupo='CEL MADRE'
 order by numfactu desc "; 
}

 

  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  $lenObj = sizeof($obj);
  return $obj;
}

function getMasterTotal($fecha, $fecha2,$codmedico,$idempresa,$usuario,$tiporeporte,$with_serv){

  if ($with_serv=="0") {
     $query="SELECT sum(a.subtotal) subtotal, SUM(a.descuento) descuento,0 impuesto, 0 envio, sum(a.total) total from cma_MFactura a  inner join MClientes b On a.codclien=b.codclien  where a.fechafac between '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3 and a.numfactu in (select distinct numfactu from cma_DFactura a where  a.fechafac between '$fecha' and '$fecha2' and a.cod_subgrupo in('CEL MADRE','INTRAVENOSO','TERAPIA LASER') and a.coditems in( select b.coditems from exclusivos b where b.Codmedico='$codmedico'  ))";
  }else{
    // SIN SERVICIOS

    $query="SELECT sum(a.subtotal) subtotal, SUM(a.descuento) descuento,0 impuesto, 0 envio, sum(a.total) total from cma_MFactura a  inner join MClientes b On a.codclien=b.codclien  where a.fechafac between '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3 and a.numfactu in (select distinct numfactu from cma_DFactura a where  a.fechafac between '$fecha' and '$fecha2' and a.cod_subgrupo in('CEL MADRE') and a.coditems in( select b.coditems from exclusivos b where b.Codmedico='$codmedico'  ))";

  }


  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  $lenObj = sizeof($obj);
  return $obj;
}

function Total($fecha, $fecha2,$codmedico,$idempresa,$usuario,$tiporeporte,$with_serv){

  if ($with_serv=="0") {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'  
union all
SELECT sum(a.subtotal) subtotal, SUM(a.descuento) descuento,0 impuesto, 0 envio, sum(a.total) total from cma_MFactura a  inner join MClientes b On a.codclien=b.codclien  where a.fechafac between '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3 and a.numfactu in (select distinct numfactu from cma_DFactura a where  a.fechafac between '$fecha' and '$fecha2' and a.cod_subgrupo in('CEL MADRE','INTRAVENOSO','TERAPIA LASER') and a.coditems in( select b.coditems from exclusivos b where b.Codmedico='$codmedico'  ))";

  }else{
    //sin servicios
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha2'  AND  a.statfact=3 and  coditems  in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) and cod_subgrupo ='CEL MADRE'  
union all
SELECT sum(a.subtotal) subtotal, SUM(a.descuento) descuento,0 impuesto, 0 envio, sum(a.total) total from cma_MFactura a  inner join MClientes b On a.codclien=b.codclien  where a.fechafac between '$fecha' and '$fecha2' and a.codmedico='$codmedico' and a.statfact=3 and a.numfactu in (select distinct numfactu from cma_DFactura a where  a.fechafac between '$fecha' and '$fecha2' and a.cod_subgrupo in('CEL MADRE') and a.coditems in( select b.coditems from exclusivos b where b.Codmedico='$codmedico'  ))";
  }

  
  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  $lenObj = sizeof($obj);
  return $obj;
}


function getDetails($numfactu,$monto){
  $query="SELECT b.desitems,  a.* from cma_DFactura a inner join MInventario b On a.coditems=b.coditems where numfactu='$numfactu'";
  if ($monto<0) {
    $query="SELECT b.desitems,  a.* from CMA_Dnotacredito a inner join MInventario b On a.coditems=b.coditems where numnotcre='$numfactu'";
  }
  
  $res = mssqlConn::Listados($query);
  $obj= json_decode($res, true);
  $lenObj = sizeof($obj);
  return $obj;
}

#-------------------------------------------------------------------------------

?>

