<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 setlocale(LC_MONETARY,"en_US");
require_once '../../db/mssqlconn.php';
require_once "../../clases/fpdf/fpdf.php";
$dbmsql = mssqlConn::getConnection();
$start=50;
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
$pdf->AddPage();

#establace la fuente
$pdf->SetFont('Arial');

$pdf->Cell(200 , 5, 'Medicina Sistemica'  ,0,1,'C');
$pdf->Cell(200 , 5, 'Sueroterapia - Bayamon PR'  ,0,1,'C');
$pdf->Cell(200 , 5, 'Fecha de Operacion comercial '.$fecha  ,0,1,'C');
$pdf->Cell(200 , 5, '        Usuario : '.$usuario  ,0,1,'C');
$pdf->Cell(195 , 5, date('Y-m-d H:i:s') ,0,1,'C');

$items = getCash($fecha, $usuario,'v',$idempresa,$tiporeporte);
$totalcash=0;


$pdf->SetY(35);
$pdf->Cell(55 , 5, 'Conteo de efectivo en caja'  ,0,1,'R');
$pdf->Cell(14 , 5, 'Cant'  ,0,0,'R');
$pdf->Cell(17 , 5, 'Valor'  ,0,0,'R');
$pdf->Cell(28 , 5, 'Monto'  ,0,1,'R');

for ($i=0; $i <sizeof($items) ; $i++) { 
	
	$pdf->Cell(10 , 5, $items[$i]['monto']  ,0,0,'R');
	$pdf->Cell(20 , 5,  '$'.number_format($items[$i]['valor'], 2, '.', '')  ,0,0,'R');
	$pdf->Cell(30 , 5,  '$'.number_format($items[$i]['valor']*$items[$i]['monto'] , 2, '.', ',')  ,0,1,'R');
	$totalcash+=$items[$i]['valor']*$items[$i]['monto'];
}
$pdf->Cell(60 , 5, 'Total Cash $'.number_format($totalcash, 2, '.', ',')   ,0,0,'R');
#-------------------------------------------------
$items =  getGrupo($fecha,$idempresa,$tiporeporte,$usuario);
$inicio=$start;
$pdf->SetXY(100,35);
$pdf->Cell(10 , 5, 'Inicio',0,0,'R');
$pdf->Cell(60 , 5, '$'.number_format($inicio, 2, ".", ",") ,0,1,'R');
#-------------------------------------------------
$cashdraw=0;
for ($i=0; $i <sizeof($items) ; $i++) { 
	if ($items[$i]['modopago']=='CASH') {

        $pdf->SetXY(98,40);
        $pdf->Cell(10,5,'CASH' ,0,0,'L','');
        $pdf->Cell(62,5,'$'.number_format($items[$i]['monto'], 2, ".", ","),0,1,'R');
        $cashdraw=$items[$i]['monto'];
	}
}
#-------------------------------------------------
$pdf->SetXY(100,46); 
$totalcash2=$inicio+$cashdraw;
$pdf->Cell(20 , 5, 'Total Cash' ,0,0,'R');
$pdf->Cell(50 , 5, '$'.number_format($totalcash2, 2, ".", ",") ,0,1,'R');
#-------------------------------------------------
$pdf->SetXY(100,52);  
$final = $totalcash - $totalcash2;
if ($final==0) {
   $pdf->Cell(10 , 5, 'Perfect',0,0,'R');
} elseif ($final>0) {
   $pdf->Cell(9 , 5, 'Over' ,0,0,'R');
}elseif ($final<0) {
   $pdf->Cell(10 , 5, 'Short' ,0,0,'R');
}
 $pdf->Cell(61 , 5, '$'.number_format($final, 2, ".", ",") ,0,1,'R');

#-------------------------------------------------
$pdf->SetFont('Arial','B');
$pdf->SetXY(100,58);   
$pdf->Cell(25 , 5, 'Deposit Cash',0,0,'R');
$pdf->Cell(45 , 5, '$'.number_format($cashdraw, 2, ".", ",") ,0,1,'R');
$pdf->SetFont('Arial');
#-------------------------------------------------
$pdf->SetXY(100,64);   
$pdf->Cell(42 , 5, 'Cobranzas en efectivo',0,0,'R');
$pdf->Cell(28 , 5, '$'.number_format($cashdraw, 2, ".", ",") ,0,1,'R');
#------------------------------------------------- 
#  COBROS EN CHEQUE
$pdf->SetXY(100,72);
$y2_axis =  72 ;
$cheques =  getCheque($fecha,$idempresa,$tiporeporte,$usuario);
$pdf->SetXY(98,$y2_axis);   
$pdf->Cell(52,4,$cheques[0]['modopago'],0,0,'L','');
$pdf->Cell(38,4,'$'.number_format($cheques[0]['monto'], 2, ".", ","),0,1,'L','');

$pdf->SetXY(100,78);
$y2_axis =  78;
#--------------------------------------------------
$visamaster=0;
$totaltarjeta=0;
$totalgeneral=0;
//$y2_axis =  71 ;

// $pdf->Cell(52,4,$cheques[0]['modopago'],0,0,'L','');
// $pdf->Cell(38,4,'$'.number_format($cheques[0]['monto'], 2, ".", ","),0,1,'L','');

for ($i=0; $i <sizeof($items) ; $i++) { 
	if ($items[$i]['modopago']!=='CASH') {

        $pdf->SetXY(98,$y2_axis);   
        $pdf->Cell(52,4,$items[$i]['modopago'],0,0,'L',''); 
        $pdf->Cell(38,4,'$'.number_format($items[$i]['monto'], 2, ".", ","),0,1,'L','');
        $y2_axis+=6;
        $totaltarjeta+=$items[$i]['monto']; #COBRANZAS EN TARJETAS

        if ($items[$i]['modopago']=='VISA' || $items[$i]['modopago']=='MASTERCARD') {
        	$visamaster+=$items[$i]['monto'];  # VISA + MASTER      	
        }      
	}

	$totalgeneral+=$items[$i]['monto']; #TOTAL GENERAL ADAPTOHEALTH
}
#-------------------------------------------------
$y2_axis = $pdf->GetY();
$pdf->SetFont('Arial','B');
$pdf->SetXY(100,$y2_axis);  
$pdf->Cell(44 , 5, 'VISA + MASTERCARD' ,0,0,'R');
$pdf->Cell(27 , 5, '$'.number_format($visamaster, 2, ".", ",") ,0,1,'R');
#------------------------------------------------- 
$y2_axis = $pdf->GetY()  ;
$pdf->SetXY(100,$y2_axis); 
$pdf->Cell(41 , 5, 'Cobranzas en tarjetas',0,0,'R');
$pdf->Cell(30 , 5, '$'.number_format($totaltarjeta, 2, ".", ",") ,0,1,'R');
#------------------------------------------------- 

$totalgeneral=$totalgeneral+$cheques[0]['monto'];
$y2_axis = $pdf->GetY()  ;
$pdf->SetXY(100,$y2_axis+2); 
$pdf->Cell(41 , 5, 'Total ',0,0,'R');
$pdf->Cell(30 , 5, '$'.number_format($totalgeneral, 2, ".", ",") ,0,1,'R');
$pdf->SetFont('Arial');
#------------------------------------------------- 

$bruta = getFactBruta($fecha,$idempresa,$tiporeporte,$usuario);

if ($bruta!=='') {
	$y2_axis = $pdf->GetY()  ;
    $pdf->SetXY(100,$y2_axis+4);   
	$pdf->Cell(41 , 5, 'Facturacion Bruta '.$bruta[0]['facturas'] ,0,0,'R');	
	$pdf->Cell(30 , 5, '$'. number_format($bruta[0]['monto'], 2, ".", ",")   ,0,1,'R');	
}
#------------------------------------------------- 
$devoluciones = getDevoluciones($fecha,$idempresa,$tiporeporte,$usuario);

if ($devoluciones!=='') {
	$y2_axis = $pdf->GetY()  ;
    $pdf->SetXY(100,$y2_axis+1);  
	$pdf->Cell(41 , 5, 'Devoluciones '.$devoluciones[0]['facturas'] ,0,0,'R');	
	$pdf->Cell(30 , 5, '$'. number_format($devoluciones[0]['monto'], 2, ".", ",")   ,0,1,'R');	
}

#------------------------------------------------- 
$devoluciones = getDevoluciones($fecha,$idempresa,$tiporeporte,$usuario);

if ($devoluciones!=='') {
	$y2_axis = $pdf->GetY()  ;
    $pdf->SetXY(100,$y2_axis+1);  
	$factneta=($bruta[0]['monto'] + $devoluciones[0]['monto']);
	$facturas=($devoluciones[0]['facturas']+$bruta[0]['facturas']);
	$pdf->Cell(41 , 5, 'Facturacion Neta '.$facturas ,0,0,'R');	
	$pdf->Cell(30 , 5, '$'. number_format($factneta, 2, ".", ",")   ,0,1,'R');	
}

#------------------------------------------------- 
$devoluciones = getDevoluciones($fecha,$idempresa,$tiporeporte,$usuario);

if ($devoluciones!=='') {
	$y2_axis = $pdf->GetY()  ;
    $pdf->SetXY(100,$y2_axis+4);  
	$factneta=($bruta[0]['monto'] + $devoluciones[0]['monto']);
	$facturas=($devoluciones[0]['facturas']+$bruta[0]['facturas']);
	
	$control =0;
	$t1 = (float) $totalgeneral;
	$t2 = (float) $factneta;
	$control =$t1-$t2;
        $string = sprintf("%.3f", $control);
	if ($string==-' -0.000') {
		$control=0;
	}
	$pdf->Cell(41 , 5, 'Control debe ser igual a 0 ',0,0,'R');	
	$pdf->Cell(30 , 5, '$'. number_format($control, 2, ".", ",")   ,0,1,'R');	
}

   $pdf->SetXY($pdf->GetX()+ 20 ,$pdf->GetY() + 50);
   $pdf->Cell(20, 5, 'Cajero' ,0,0,'R');	
 
   $pdf->Cell(100, 5, 'Gerente' ,0,1,'R');	
   $pdf->Line($pdf->GetX(), $pdf->GetY()-5, 90-$pdf->GetX(), $pdf->GetY()-5);
   $pdf->Line($pdf->GetX()+90, $pdf->GetY()-5, 190-$pdf->GetX(), $pdf->GetY()-5);
 
 #DETALLE DE LA VENTA
 $pdf->AddPage();
 $pdf->SetY(10);
 $groupventas =  getGrupoVentasTotal($fecha,$idempresa,$usuario,$tiporeporte);
 $ventas = getVentas($fecha,$idempresa,$tiporeporte,$usuario);
 $mpagos = getMetodoPago($fecha,$idempresa,$tiporeporte,$usuario);
 $pdf->Cell(200 , 5, 'Medicina Sistemica'  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Sueroterapia - Bayamon PR'  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Reporte de Facturacion',0,1,'C');
 $pdf->Cell(200 , 5, $fecha." ".  'Usuario : '.$usuario    ,0,1,'C');
 $pdf->Cell(200 , 5, '  '   ,0,1,'C');
 //$pdf->Cell(195 , 5, date('Y-m-d H:i:s') ,0,1,'C');

 $pdf->Cell(200 , 5, ' '   ,0,1,'C');
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


    
     $_gsubtotal=0;
     $_gdescuento=0;
     $_gTotImpuesto=0;
     $_gmonto_flete=0;
     $_gmonto=0;

 
 for ($j=0; $j <sizeof($groupventas) ; $j++) { 
  
     $pdf->Cell($pdf->GetX()+12,4,"Usuario : ".$groupventas[$j]['usuario'],0,0,'R');

    
     $_subtotal=0;
     $_descuento=0;
     $_TotImpuesto=0;
     $_monto_flete=0;
     $_monto=0;


     for ($i=0; $i <sizeof($ventas) ; $i++) { 

        if ($ventas[$i]['usuario']==$groupventas[$j]['usuario']) {

        $pdf->SetXY(5,$pdf->GetY()+6);   

        $pdf->SetFont('Arial');
        if ($ventas[$i]['codmedico']=="333") {
           $pdf->SetFont('Arial','B');
        }

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

        $_subtotal+= (float) $ventas[$i]['subtotal'];
        $_descuento+= (float) $ventas[$i]['descuento'];
        $_TotImpuesto+= (float) $ventas[$i]['TotImpuesto'];
        $_monto_flete+= (float) $ventas[$i]['monto_flete'];
        $_monto+= (float) $ventas[$i]['monto'];


           
       }

       

     }
    $_gsubtotal+=$_subtotal;


$_gdescuento+=$_descuento;
$_gTotImpuesto+=$_TotImpuesto;
$_gmonto_flete+=$_monto_flete;
$_gmonto+=$_monto;

     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);
     $pdf->Cell($pdf->GetX()+22,4,"Total USD $ ".$groupventas[$j]['usuario'],0,0,'R');
     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);
     $pdf->SetFont('Arial','B',9);
     $pdf->Cell($pdf->GetX()+92,4,number_format(  $_subtotal, 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-100,4,number_format( $_descuento, 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-110,4,number_format( $_TotImpuesto, 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-125,4,number_format( $_monto_flete, 2, ".", ","),0,0,'R');
     $pdf->Cell($pdf->GetX()-135,4,number_format( $_monto, 2, ".", ","),0,0,'R');
     $pdf->SetFont('Arial','',9);
     $y2_axis=$pdf->GetY()+3;
     $pdf->SetY($y2_axis+4);

     //$pdf->Cell($pdf->GetX()+50,4,' ',0,0,'L');    

 }   
 $pdf->SetFont('Arial','B',9);
 $y2_axis=$pdf->GetY()+2;
 $pdf->SetXY(5,$pdf->GetY()+4);
 $totalesv = getTotal($fecha,$idempresa,$tiporeporte,$usuario);

 $pdf->SetXY(5,$pdf->GetY()+4);   
 $pdf->Cell($pdf->GetX()+25,4,'Total Ventas ',0,0,'R');

 $pdf->Cell($pdf->GetX()+42,4,number_format($_gsubtotal, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-54,4,number_format($_gdescuento, 2, ".", ","),0,0,'L');

 $pdf->Cell($pdf->GetX()-200,4,number_format($_gTotImpuesto, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-129,4,number_format($_gmonto_flete, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-135,4,number_format($_gmonto, 2, ".", ","),0,0,'L');


#DETALLE DE LA DEVOLUCION
  $pdf->SetY($pdf->GetY()+10);
 $pdf->SetY($pdf->GetY()+7);
 $return = getFacDevueltas($fecha,$idempresa,$tiporeporte,$usuario);
 $pdf->Cell(200 , 5, 'Medicina Sistemica'  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Sueroterapia - Bayamon PR'  ,0,1,'C');
 $pdf->Cell(200 , 5, 'Reporte de Facturacion',0,1,'C');
 $pdf->Cell(200 , 5, 'Devoluciones' ,0,1,'C');
 $pdf->Cell(200 , 5, 'Usuario : '.$usuario  ,0,1,'C');
 $pdf->Cell(200 , 5, ' '   ,0,1,'C');
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
 $totalesdev = getTotalFacDevueltas($fecha,$idempresa,$tiporeporte,$usuario);

 $pdf->SetXY(5,$pdf->GetY()+4);   
 $pdf->Cell($pdf->GetX()+25,4,'Total Devoluciones ',0,0,'R');

 $pdf->Cell($pdf->GetX()+42,4,number_format($totalesdev[0]['subtotal'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-55,4,number_format($totalesdev[0]['descuento'], 2, ".", ","),0,0,'L');
 $pdf->Cell($pdf->GetX()-198,4,number_format($totalesdev[0]['impuesto'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-129,4,number_format($totalesdev[0]['envio'], 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-135,4,number_format($totalesdev[0]['total'], 2, ".", ","),0,0,'R');
 
 #GRAN TOTAL
 $grantotal = getGranTotal($fecha,$idempresa,$tiporeporte,$usuario);
 $pdf->SetY($pdf->GetY()+7);
 $pdf->SetXY(5,$pdf->GetY()+4);
 $pdf->Cell($pdf->GetX()+25,4,'Total Ventas  ',0,0,'R');

//     $_gsubtotal+=$_subtotal;


// $_gdescuento+=$_descuento;
// $_gTotImpuesto+=$_TotImpuesto;
// $_gmonto_flete+=$_monto_flete;
// $_gmonto+=$_monto;

 $_gsubtotal=$_gsubtotal+$totalesdev[0]['subtotal'];
 $_gdescuento=$_gdescuento+$totalesdev[0]['descuento'];
 $_gTotImpuesto=$_gTotImpuesto+$totalesdev[0]['impuesto'];
 $_gmonto_flete=$_gmonto_flete+$totalesdev[0]['envio'];
 $_gmonto=$_gmonto+$totalesdev[0]['total'];

 $pdf->Cell($pdf->GetX()+42,4,number_format($_gsubtotal, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-55,4,number_format($_gdescuento, 2, ".", ","),0,0,'L');
 $pdf->Cell($pdf->GetX()-198,4,number_format($_gTotImpuesto, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-129,4,number_format($_gmonto_flete, 2, ".", ","),0,0,'R');
 $pdf->Cell($pdf->GetX()-135,4,number_format($_gmonto, 2, ".", ","),0,0,'R');
 

#muestra la pagina
$pdf->Output();

#--------------------------------------------------
function getCash($fecha, $usuario,$typesystem,$idempresa='1',$tiporeporte){
    
if ($typesystem=='v') {
    if ($idempresa=='1') {
        $query="SELECT * from cuadre WHERE fecha='$fecha' and estacion='ADAPTOHEALTH1' and id_centro = '1' order by valor desc";
    } else if ($idempresa=='C' ) {
       $query="SELECT * from cuadre WHERE fecha='$fecha' and estacion='CMA' and id_centro = 'C' order by valor desc";
    }else if ($idempresa=='S' ) {
       $query="SELECT * from cuadre WHERE fecha='$fecha'  and  usuario='$usuario' and id_centro = 'S' order by valor desc";
       if ($tiporeporte=='false') {
           $query="SELECT valor,sum(monto) monto from cuadre WHERE fecha='$fecha' and id_centro = 'S' group by valor  order by valor desc";
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
function getGrupo($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWPagosPR_W7  where fechapago='$fecha' and statfact=3  group by modopago,codforpa  order by modopago";
} else if ($idempresa=='C' ) {
    $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST  where fechapago='$fecha' and statfact=3   and id_centro = '2' and cod_subgrupo='CONSULTA'  group by modopago,codforpa  order by modopago";
}else if ($idempresa=='S' ) {
    $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST_1  where fechapago='$fecha' and codforpa not in('2')  and statfact=3   and id_centro = '2' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and usuario='$usuario'  group by modopago,codforpa  order by modopago";
    if ($tiporeporte=='false') {
        $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST_1  where fechapago='$fecha'and codforpa not in('2')   and statfact=3   and id_centro = '2' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  group by modopago,codforpa  order by modopago";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}

#--------------------------------------------------
function getCheque($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='S' ) {
    $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST_1  where fechapago='$fecha' and codforpa  in('2')  and statfact=3   and id_centro = '2' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and usuario='$usuario'  group by modopago,codforpa  order by modopago";
    if ($tiporeporte=='false') {
        $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST_1  where fechapago='$fecha' and codforpa  in('2')  and statfact=3   and id_centro = '2' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  group by modopago,codforpa  order by modopago";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFactBruta($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum(total+monto_flete) monto, count(*) facturas   from MFactura where fechafac='$fecha'  and statfact=3  ";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(total+monto_flete) monto, count(*) facturas from VentasDiariasCMACST1 where fechafac='$fecha'  and statfact=3 and cod_subgrupo='CONSULTA' ";    
}else if ($idempresa=='S' ) {
    $query="SELECT sum(monto) monto, count(*) facturas from VIEWpagosPRCMACST_1 where fechafac='$fecha'  and statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and usuario='$usuario' ";    
    if ($tiporeporte=='false') {
        $query="SELECT sum(monto) monto, count(*) facturas from VIEWpagosPRCMACST_1 where fechafac='$fecha'  and statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') ";    
    }
}
$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getDevoluciones($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiarias a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3";
}else if ($idempresa=='C' ) {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasCMACST1 a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3 and  cod_subgrupo ='CONSULTA' ";    
}else if ($idempresa=='S' ) {
    $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasCMACST1 a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3 and  cod_subgrupo  In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and usuario='$usuario' ";        
    if ($tiporeporte=='false') {
        $query="SELECT sum( a.total) monto, count(*) facturas FROM VentasDiariasCMACST1 a INNER JOIN  MDocumentos b ON  a.doc = b.codtipodoc WHERE  a.fechafac = '$fecha' and a.doc='04' and statfact=3 and  cod_subgrupo  In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') ";        
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}

#--------------------------------------------------
function getVentas($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total>=0 Order by a.numfactu desc ";
}else if ($idempresa=='C' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo='CONSULTA' Order by a.numfactu desc ";    
}else if ($idempresa=='S' ) {
    $query="SELECT distinct a.numfactu, convert(varchar(10), cast(a.fechafac as date), 101)  fechapago , a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario ,a.Historia, a.initials FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  and usuario='$usuario'  Order by a.usuario desc ";
    if ($tiporeporte=='false') {
        $query="SELECT distinct a.numfactu ,convert(varchar(10), cast(a.fechafac as date), 101)  fechapago , a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.Historia FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') Order by a.numfactu desc ";
        $query="SELECT distinct a.numfactu, convert(varchar(10), cast(a.fechafac as date), 101)  fechapago , a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario,a.Historia FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') /* and usuario='$usuario' */ Order by a.usuario desc ";    
        $query="SELECT DISTINCT a.numfactu, convert(varchar(10), cast(a.fechafac as date), 101)  fechapago , a.nombres, a.subtotal, a.descuento,  (a.total+a.monto_flete)  monto, a.statfact, a.TotImpuesto, a.monto_flete,a.usuario,a.Historia, a.initials FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') /* and usuario='$usuario' */ Order by a.usuario desc ";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getMetodoPago($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPR d WHERE d.fechapago between '$fecha' AND '$fecha' Order By d.modopago ";
}else if ($idempresa=='C' ) {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPRCMACST d WHERE d.fechapago between '$fecha' AND '$fecha' and cod_subgrupo='CONSULTA' Order By d.modopago";
}else if ($idempresa=='S' ) {
    $query="SELECT d.numfactu, d.modopago From VIEWpagosPRCMACST d WHERE d.fechapago between '$fecha' AND '$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') /* and usuario='$usuario' */ Order By d.modopago";
    if ($tiporeporte=='false') {
        $query="SELECT d.numfactu, d.modopago From VIEWpagosPRCMACST d WHERE d.fechapago between '$fecha' AND '$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') Order By d.modopago";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
$pagos = array();

foreach($obj as $fila){
  $mp= setPaymentMethod($fila['numfactu'],$fecha);
  	//$pagos[$fila['numfactu']]=$fila['modopago'];
  $pagos[$fila['numfactu']]=$mp;
}
return $pagos;
}
#--------------------------------------------------
function setPaymentMethod($numfactu,$fecha){
  $query="SELECT d.DesTipoTargeta, d.monto From VIEWpagosPRCMA d WHERE d.fechapago = '$fecha' AND d.numfactu ='$numfactu' ";
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
function getTotal($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete)) total FROM VentasDiarias a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 And  a.total>0";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete)) total FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 and cod_subgrupo='CONSULTA' And   a.total>0  ";
}else if ($idempresa=='S' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete)) total FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') And   a.subtotal>0   and usuario='$usuario' ";
    if ($tiporeporte=='false') {
        $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete)) total FROM VentasDiariasCMACST1 a INNER JOIN MDocumentos b ON a.doc = b.codtipodoc WHERE a.fechafac between '$fecha' AND '$fecha' AND a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') And   a.doc='01'  ";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getFacDevueltas($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between   '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0 Order by a.numfactu desc "; 
}else if ($idempresa=='C' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha' AND  a.statfact=3 and cod_subgrupo='CONSULTA'   And  a.total<0 Order by a.numfactu desc  "; 
}else if ($idempresa=='S' ) {
    $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha' AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   And  a.total<0  and usuario='$usuario' /*  Order by a.numfactu desc */ "; 
    if ($tiporeporte=='false') {
        $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha' AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   And  a.total<0 Order by a.numfactu desc  "; 
        $query="SELECT convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, (a.total+a.monto_flete) monto, a.statfact, a.TotImpuesto, a.monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between  '$fecha' AND '$fecha' AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   And  a.total<0  /* and usuario='$usuario'  Order by a.numfactu desc */ "; 
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getTotalFacDevueltas($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3  And  a.total<0 "; 
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo='CONSULTA'   And  a.total<0 "; 
}else if ($idempresa=='S' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   And  a.total<0  and usuario='$usuario'  ";     
    if ($tiporeporte=='false') {
       $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   And  a.total<0 ";     
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
#--------------------------------------------------
function getGranTotal($fecha,$idempresa='1',$tiporeporte,$usuario){

if ($idempresa=='1') {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiarias a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha' AND  a.statfact=3 ";
}else if ($idempresa=='C' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo='CONSULTA' ";
}else if ($idempresa=='S' ) {
    $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and usuario='$usuario' ";
    if ($tiporeporte=='false') {
        $query="SELECT sum(a.subtotal) subtotal , sum(a.descuento) descuento, sum( a.TotImpuesto) impuesto , sum(a.monto_flete) envio , sum( (a.total+a.monto_flete))  total   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') ";
    }
}

$res = mssqlConn::Listados($query);
$obj= json_decode($res, true);
$lenObj = sizeof($obj);
return $obj;
}
 
 
function getGrupoVentasTotal($fecha,$idempresa='1',$usuario,$tiporeporte){ 
//    if ($tiporeporte=='true') {
        if ($idempresa=='S' ) {           
           $query="SELECT a.usuario, sum( a.subtotal) subtotal, sum(a.descuento) descuento,  (sum(a.total)+sum(a.monto_flete))  monto, sum( a.TotImpuesto) TotImpuesto, sum(a.monto_flete) monto_flete   FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc   WHERE a.fechafac between '$fecha' AND '$fecha'  AND  a.statfact=3  And  a.total>=0 and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  group by  usuario Order by monto desc ";
        }
        $res = mssqlConn::Listados($query);
        $obj= json_decode($res, true);
        $lenObj = sizeof($obj);
        return $obj;
    // }
}


?>

