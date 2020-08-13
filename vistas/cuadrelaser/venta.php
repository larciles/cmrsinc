<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
require_once '../../db/mssqlconn.php';
require_once "../../clases/fpdf/fpdf.php";
$dbmsql = mssqlConn::getConnection();
$start=50;
$tiporeporte="true";
if(isset($_GET['fecha']))
{
    $fecha    =$_GET['fecha'];
    $usuario  =$_GET['usuario'];
    $idempresa=$_GET['idempresa'];
    if (isset($_GET['start'])) {
       $start= (float) $_GET['start'];
    }

    if (isset($_GET['tiporeporte'])) {
       $tiporeporte= $_GET['tiporeporte'];
    }
}


#constructor
$pdf = new FPDF();
#crea una nueva pagina


#establace la fuente
$pdf->SetFont('Arial');

 #DETALLE DE LA VENTA
 $pdf->AddPage();
 $pdf->SetY(10);
 $groupventas =  getGrupoVentasTotal($fecha,$idempresa,$usuario,$tiporeporte);
 $ventas = getVentas($fecha,$idempresa,$usuario,$tiporeporte);
 $mpagos = getMetodoPago($fecha,$idempresa,$usuario,$tiporeporte);
 $pdf->Cell(200 , 5, 'CMR Laser - Bayamon PR'  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Reporte de Facturacion',0,1,'C');
 $pdf->Cell(200 , 5, $fecha  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Usuario : '.$usuario  ,0,1,'C');
 $pdf->SetY(30);
 $pdf->SetFont('Arial');
 $pdf->SetFont('Arial','',9);
 $pdf->Cell(11 , 5, 'Factura'  ,0,0,'R');
 $pdf->Cell(14 , 5, 'Fecha'  ,0,0,'R');
 $pdf->Cell(17 , 5, 'Cliente'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()+8 , 5, 'Subtotal'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()-97 , 5, 'Descto'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()-116 , 5, 'Impto'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()-125 , 5, 'Envio'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()-135 , 5, 'Total'  ,0,0,'R');
 $pdf->Cell($pdf->GetX()-131 , 5, 'Metodo Pago   Record U'  ,0,1,'R');
 $y2_axis=$pdf->GetY()+1;

 for ($j=0; $j <sizeof($groupventas) ; $j++) { 
  
     $pdf->Cell($pdf->GetX()+12,4,"Usuario : ".$groupventas[$j]['usuario'],0,0,'R');

     for ($i=0; $i <sizeof($ventas) ; $i++) { 

        if ($ventas[$i]['usuario']==$groupventas[$j]['usuario']) {

            $pdf->SetXY(5,$pdf->GetY()+6);   
            $pdf->Cell($pdf->GetX()+12,4,$ventas[$i]['numfactu'],0,0,'R');
            $pdf->Cell($pdf->GetX()-5,4,$ventas[$i]['fechapago'],0,0,'R');        
            $pdf->Cell($pdf->GetX()+3,4,$ventas[$i]['nombres'],0,0,'L');
            $pdf->Cell($pdf->GetX()-50,4,number_format($ventas[$i]['subtotal'], 2, ".", ","),0,0,'R');
            $pdf->Cell($pdf->GetX()-100,4,number_format($ventas[$i]['descuento'], 2, ".", ","),0,0,'R');
            
            $pdf->Cell($pdf->GetX()-110,4,number_format($ventas[$i]['TotImpuesto'], 2, ".", ","),0,0,'R');
            $pdf->Cell($pdf->GetX()-125,4,number_format($ventas[$i]['monto_flete'], 2, ".", ","),0,0,'R');
            $pdf->Cell($pdf->GetX()-135,4,number_format($ventas[$i]['monto'], 2, ".", ","),0,0,'R');
            $pdf->Cell(0,4,$mpagos[$ventas[$i]['numfactu']],0,0,'L');
            $pdf->Cell(7,4,"    ".' '.$ventas[$i]['Historia'].' '.$ventas[$i]['initials'],0,0,'R');
        
          
            $y2_axis=$y2_axis+6;

           
       } 

     }
     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);
     $pdf->Cell($pdf->GetX()+22,4,"Total USD $ ".$groupventas[$j]['usuario'],0,0,'R');
     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);
     $pdf->SetFont('Arial','B',9);
     $pdf->Cell($pdf->GetX()+92,4,number_format($groupventas[$j]['subtotal'], 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-100,4,number_format($groupventas[$j]['descuento'], 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-110,4,number_format($groupventas[$j]['TotImpuesto'], 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-125,4,number_format($groupventas[$j]['monto_flete'], 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-135,4,number_format($groupventas[$j]['monto'], 2, ".", ","),0,0,'R');
     $pdf->SetFont('Arial','',9);
     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);

     //$pdf->Cell($pdf->GetX()+50,4,' ',0,0,'L');    

 }   

 $pdf->SetFont('Arial','B',9);
 $y2_axis=$pdf->GetY()+2;
 $pdf->SetXY(5,$pdf->GetY()+4);
 $totalesv = getTotal($fecha,$idempresa,$usuario,$tiporeporte);

 $pdf->SetXY(5,$pdf->GetY()+4);   
 $pdf->Cell($pdf->GetX()+25,4,'Total Ventas ',0,0,'R');

 $pdf->Cell($pdf->GetX()+42,4,number_format($totalesv[0]['subtotal'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-54,4,number_format($totalesv[0]['descuento'], 2, ".", ","),0,0,'L');

 $pdf->Cell($pdf->GetX()-200,4,number_format($totalesv[0]['impuesto'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-129,4,number_format($totalesv[0]['envio'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-135,4,number_format($totalesv[0]['total'], 2, ".", ","),0,0,'L');


#DETALLE DE LA DEVOLUCION
 $pdf->SetY($pdf->GetY()+7);
 $return = getFacDevueltas($fecha,$idempresa,$usuario,$tiporeporte);
 if ($return!==null) {
         $pdf->Cell(200 , 5, 'CMR Laser - Bayamon PR'  ,0,1,'C');
         $pdf->Cell(200 , 5, 'Reporte de Facturacion',0,1,'C');
         $pdf->Cell(200 , 5, 'Devoluciones' ,0,1,'C');
         $pdf->Cell(200 , 5, 'Usuario : '.$usuario  ,0,1,'C');
         $pdf->SetY($pdf->GetY()+3);
         $pdf->SetFont('Arial');
         $pdf->SetFont('Arial','',9);
         $pdf->Cell(11 , 5, 'Devolucion'  ,0,0,'R');
         $pdf->Cell(14 , 5, 'Fecha'  ,0,0,'R');
         $pdf->Cell(17 , 5, 'Cliente'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()+8 , 5, 'Subtotal'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()-97 , 5, 'Descto'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()-116 , 5, 'Impto'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()-125 , 5, 'Envio'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()-135 , 5, 'Total'  ,0,0,'R');
         $pdf->Cell($pdf->GetX()-145 , 5, 'Metodo Pago'  ,0,1,'R');
         $pdf->SetFont('Arial','',9);

         for ($i=0; $i <sizeof($return) ; $i++) { 

                 $pdf->SetXY(5,$pdf->GetY()+6);   
                 $pdf->Cell($pdf->GetX()+12,4,$return[$i]['numfactu'],0,0,'R');
                 $pdf->Cell($pdf->GetX()-5,4,$return[$i]['fechapago'],0,0,'R');        
                 $pdf->Cell($pdf->GetX()+3,4,$return[$i]['nombres'],0,0,'L');
                 $pdf->Cell($pdf->GetX()-50,4,number_format($return[$i]['subtotal'], 2, ".", ","),0,0,'R');
                 $pdf->Cell($pdf->GetX()-100,4,number_format($return[$i]['descuento'], 2, ".", ","),0,0,'R');
                
                 $pdf->Cell($pdf->GetX()-110,4,number_format($return[$i]['TotImpuesto'], 2, ".", ","),0,0,'R');
                 $pdf->Cell($pdf->GetX()-125,4,number_format($return[$i]['monto_flete'], 2, ".", ","),0,0,'R');
                 $pdf->Cell($pdf->GetX()-135,4,number_format($return[$i]['monto'], 2, ".", ","),0,0,'R');
                 $pdf->Cell($pdf->GetX()+50,4,$mpagos[$return[$i]['numfactu']],0,0,'L');
         }
         $pdf->SetFont('Arial','B',9);
         $y2_axis=$pdf->GetY()+2;
         $pdf->SetXY(5,$pdf->GetY()+4);
         $totalesdev = getTotalFacDevueltas($fecha,$idempresa,$usuario,$tiporeporte);

         $pdf->SetXY(5,$pdf->GetY()+4);   
         $pdf->Cell($pdf->GetX()+25,4,'Total Devoluciones ',0,0,'R');

         $pdf->Cell($pdf->GetX()+42,4,number_format($totalesdev[0]['subtotal'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-55,4,number_format($totalesdev[0]['descuento'], 2, ".", ","),0,0,'L');
         $pdf->Cell($pdf->GetX()-198,4,number_format($totalesdev[0]['impuesto'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-129,4,number_format($totalesdev[0]['envio'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-135,4,number_format($totalesdev[0]['total'], 2, ".", ","),0,0,'R');
         
         #GRAN TOTAL
         $grantotal = getGranTotal($fecha,$idempresa,$usuario,$tiporeporte);
         $pdf->SetY($pdf->GetY()+7);
         $pdf->SetXY(5,$pdf->GetY()+4);
         $pdf->Cell($pdf->GetX()+25,4,'Total Ventas  ',0,0,'R');

         $pdf->Cell($pdf->GetX()+42,4,number_format($grantotal[0]['subtotal'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-55,4,number_format($grantotal[0]['descuento'], 2, ".", ","),0,0,'L');
         $pdf->Cell($pdf->GetX()-198,4,number_format($grantotal[0]['impuesto'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-129,4,number_format($grantotal[0]['envio'], 2, ".", ","),0,0,'R');
         $pdf->Cell($pdf->GetX()-135,4,number_format($grantotal[0]['total'], 2, ".", ","),0,0,'R');
         
}
#muestra la pagina
$pdf->Output();

#--------------------------------------------------
function getCash($fecha, $usuario,$typesystem,$idempresa='1',$tiporeporte){
    
if ($typesystem=='v') {
    if ($idempresa=='1') {
        $query="SELECT * from cuadre WHERE fecha='$fecha' and estacion='ADAPTOHEALTH1' and id_centro = '1' order by valor desc";
    } else if ($idempresa=='C' ) {
       $query="SELECT * from cuadre WHERE fecha='$fecha' and estacion='CMA' and id_centro = 'C' order by valor desc";
    }else if ($idempresa=='2' && $tipo=='S') {
        # code...
    }else if ($idempresa=='3') {

        $query="SELECT * from cuadre WHERE fecha='$fecha' and usuario='$usuario' and id_centro = '$idempresa' order by valor desc";
        if ($tiporeporte=="false") {
            $query="SELECT valor,sum(monto) monto from cuadre WHERE fecha='$fecha' and id_centro = '$idempresa' group by valor  order by valor desc";
        }
        
    }     
	
} else {
	$query="SELECT * from cuadre WHERE fecha='$fecha' and usuario='$usuario' and id_centro = '1' order by valor desc";
}

$res = mssqlConn::Listados($query);

$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getGrupo($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
        $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWPagosPR_W7  where fechapago='$fecha' and statfact=3  group by modopago,codforpa  order by modopago";
    } else if ($idempresa=='C' ) {
        $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST  where fechapago='$fecha' and statfact=3   and id_centro = '2' and cod_subgrupo='CONSULTA'  group by modopago,codforpa  order by modopago";
}else if ($idempresa=='3') {

    $query=" SELECT "
    ." sum(a.monto) monto, a.modopago,a.codforpa "
    ." FROM "
    ."  VIEWpagosPRMSS_W7 a  "
    ."  /*INNER JOIN vestaciones b ON  a.workstation = b.Workstation AND  a .id_centro = b.id_centro */"
    ." WHERE "
    ."  a.statfact <> '2' AND "
    ."  a.id_centro = '3' AND "
    ."  a.fechapago = '$fecha' AND "
    ."  a.userfac = '$usuario' "
    ." group by  a.modopago,a.codforpa " 
    ."  order by a.modopago ";

    if ($tiporeporte=='false') {
         $query=" SELECT "
        ." sum(a.monto) monto, a.modopago,a.codforpa "
        ." FROM "
        ."  VIEWpagosPRMSS_W7 a  "
        ."  /*INNER JOIN vestaciones b ON  a.workstation = b.Workstation AND  a .id_centro = b.id_centro */"
        ." WHERE "
        ."  a.statfact <> '2' AND "
        ."  a.id_centro = '3' AND "
        ."  a.fechapago = '$fecha' "
        ." group by  a.modopago,a.codforpa " 
        ."  order by a.modopago ";
    }



}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFactBruta($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT sum(total+monto_flete) monto, count(*) facturas   from MFactura where fechafac='$fecha'  and statfact=3  ";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(total+monto_flete) monto, count(*) facturas from VentasDiariasCMACST where fechafac='$fecha'  and statfact=3 and cod_subgrupo='CONSULTA' ";    
}else if ($idempresa=='3' ) {
    $query="SELECT sum(total+monto_flete) monto, count(*) facturas from VentasDiariasMSS where fechafac='$fecha'  and statfact=3  and usuario='$usuario'    and doc='01' ";    
    if ($tiporeporte=='false') {
        $query="SELECT sum(total+monto_flete) monto, count(*) facturas from VentasDiariasMSS where fechafac='$fecha'  and statfact=3  and doc='01'  ";    
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getDevoluciones($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiarias a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3";
}else if ($idempresa=='C' ) {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasCMACST a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3 and  cod_subgrupo ='CONSULTA' ";    
}else if ($idempresa=='3' ) {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasMSS a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac =  '$fecha'  and a.doc='04' and statfact=3 and usuario='$usuario' ";    
    if ($tiporeporte=='false') {
        $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasMSS a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac =  '$fecha'  and a.doc='04' and statfact=3  ";    
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}

#--------------------------------------------------
function getVentas($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total>=0 Order by a.numfactu desc ";
}else if ($idempresa=='C' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo='CONSULTA' Order by a.numfactu desc ";    
}else if ($idempresa=='3' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario,a.Historia,a.initials   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'   AND  a.statfact=3  And  a.total>=0  and usuario='$usuario'  Order by  a.usuario desc, a.numfactu desc";    
    if ($tiporeporte=='false') {
        $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.Historia,a.initials   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'   AND  a.statfact=3  And  a.total>=0   Order by a.numfactu desc ";    
        $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario,a.Historia,a.initials   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'   AND  a.statfact=3  And  a.total>=0  Order by  a.usuario desc, a.numfactu desc";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getMetodoPago($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPR d WHERE d.fechapago between '$fecha' AND '$fecha' Order By d.modopago ";
}else if ($idempresa=='C' ) {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPRCMACST d WHERE d.fechapago between '$fecha' AND '$fecha' and cod_subgrupo='CONSULTA' Order By d.modopago";
}else if ($idempresa=='3' ) {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPRMSS_W7 d WHERE d.fechapago between '$fecha' AND '$fecha' /*and  d.userfac='$usuario' */ Order By d.modopago";
    if ($tiporeporte=='false') {
        $query="SELECT d.numfactu, d.modopago From VIEWpagosPRMSS_W7 d WHERE d.fechapago between '$fecha' AND '$fecha'  Order By d.modopago";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
$pagos = array();

if ($lenObj>0) {
    foreach($obj as $fila){
        $pagos[$fila['numfactu']]=$fila['modopago'];
    }
}
return $pagos;
}

#--------------------------------------------------
function getTotal($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END total FROM VentasDiarias a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 And  a.total>0";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END total FROM VentasDiariasCMACST a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 and cod_subgrupo='CONSULTA' And   a.total>0  ";
}else if ($idempresa=='3' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END total  FROM VentasDiariasMSS a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha' AND '$fecha' AND a.statfact=3  and  usuario='$usuario'  And  a.doc='01' ";
    if ($tiporeporte=='false') {
        $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END total  FROM VentasDiariasMSS a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha' AND '$fecha' AND a.statfact=3  And    a.doc='01'  ";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFacDevueltas($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between   '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0 Order by a.numfactu desc "; 
}else if ($idempresa=='C' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha' AND  a.statfact=3 and cod_subgrupo='CONSULTA'   And  a.total<0 Order by a.numfactu desc  "; 
}else if ($idempresa=='3' ) {
     $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, CASE  WHEN a.monto_flete is null  THEN a.total  ELSE a.total+a.monto_flete END monto, a.statfact, a.TotImpuesto, CASE  WHEN  a.monto_flete is null  THEN 0  ELSE  a.monto_flete END monto_flete  FROM VentasDiariasMSS a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha' AND '$fecha' AND a.statfact=3    and a.usuario='$usuario'  And   a.total<0 /* Order by a.numfactu desc */ ";
    if ($tiporeporte=='false') {
         $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, CASE  WHEN a.monto_flete is null  THEN a.total  ELSE a.total+a.monto_flete END monto, a.statfact, a.TotImpuesto, CASE  WHEN  a.monto_flete is null  THEN 0  ELSE  a.monto_flete END monto_flete  FROM VentasDiariasMSS a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha' AND '$fecha' AND a.statfact=3   /* and a.usuario='$usuario' */ And   a.total<0 /* Order by a.numfactu desc */ ";
         $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, CASE  WHEN a.monto_flete is null  THEN a.total  ELSE a.total+a.monto_flete END monto, a.statfact, a.TotImpuesto, CASE  WHEN  a.monto_flete is null  THEN 0  ELSE  a.monto_flete END monto_flete  FROM VentasDiariasMSS a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between  '$fecha' AND '$fecha' AND a.statfact=3   /* and a.usuario='$usuario' */ And   a.total<0 /* Order by a.numfactu desc */ ";
    }
}    

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getTotalFacDevueltas($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0 "; 
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo='CONSULTA'   And  a.total<0 "; 
}else if ($idempresa=='3' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  and a.usuario='$usuario'  And  a.total<0 "; 
    if ($tiporeporte=='false') {
        $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total<0 "; 
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getGranTotal($fecha,$idempresa='1',$usuario,$tiporeporte){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3 ";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total+a.monto_flete)) END  total   FROM  VentasDiariasCMACST a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo='CONSULTA' ";
}else if ($idempresa=='3' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total)) END  total   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  and a.usuario='$usuario'   ";
    if ($tiporeporte=='false') {
         $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , CASE  WHEN sum( a.monto_flete) is null  THEN sum(a.total)  ELSE sum( (a.total)) END  total   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3   ";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
 

function getGrupoVentasTotal($fecha,$idempresa='1',$usuario,$tiporeporte){ 
//    if ($tiporeporte=='true') {
        if ($idempresa=='3' ) {
           $query="SELECT a.usuario, sum( a.subtotal) subtotal, sum(a.descuento) descuento,  (sum(a.total)+sum(a.monto_flete))  monto, sum( a.TotImpuesto) TotImpuesto, sum(a.monto_flete) monto_flete   FROM  VentasDiariasMSS a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0   group by  usuario  Order by monto desc"; 
        }
        $res = mssqlConn::Listados($query);
        $obj= json_decode($res, true);
        $lenObj = sizeof($obj);
        return $obj;
    // }
}

?>

