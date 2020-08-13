<?php
include "../clases/fpdf/fpdf.php";
include '../db/mssqlconn.php';	

if(isset($_GET['p']))
{
	$pon=$_GET['p'];
}

class RepPurchaceOrder extends FPDF
{
	
// 	// function __construct(argument)
// 	// {
// 	// 	# code...
// 	// }

	public function Header(){
		//$this->Image("../img/phplogo.jpg",10,10,20,20);
		$this->SetFont("Arial","B",20);
		$this->Cell(200,10,"Orden de compra ",0,1,"C");
		
	}
}

$titulos = array('Producto','Cantidad','Precio Unit','Total');
$report = new RepPurchaceOrder();



$report->addPage();
$fecha=date("Y-m-d");
$report->Cell(200,8,'# '.$pon,0,0,"C");
$report->Ln(20);
$report->SetFont("Arial","B",12);
$report->Cell(350,10,$fecha,0,0,"C");


$report->Ln(10);
$report->SetFillColor(255,255,255);	
$report->SetTextColor('0','0','0');
for ($i=0; $i < count($titulos) ; $i++) { 
	$report->SetFont('Courier','B',10);
	$report->SetTextColor('0','0','0');
	//$report->Cell(43 , 5, $titulos[$i] ,1,0,"C", true);	         
}

$report->Cell(50 , 5, $titulos[0] ,0,0,"C", true);
$report->Cell(60 , 5, $titulos[1] ,0,0,"R", true);	
$report->Cell(35 , 5, $titulos[2] ,0,0,"R", true);	
$report->Cell(20 , 5, $titulos[3] ,0,0,"R", true);	
$report->Line($report->GetX()+100, $report->GetY()+5, 10-$report->GetX(), $report->GetY()+5);

$report->Ln(10);
$dbmsql = mssqlConn::getConnection();


//TOTALES
$query="Select sum(p.compra) cantidadtotal , sum( p.compra*i.costo) total From purchaseorder p inner join MInventario i On p.coditems=i.coditems where p.purchaseOrder='$pon' and p.compra>0";
$result=$dbmsql->query($query);
foreach($result as $fila){
    $canTotal=$fila['cantidadtotal'];
    $sumTotalPO=$fila['total'];
}
//DETALLES
$query="Select p.coditems,p.compra,p.purchaseOrder, i.desitems,i.costo, p.compra*i.costo costototal From purchaseorder p inner join MInventario i On p.coditems=i.coditems where p.purchaseOrder='$pon' and p.compra>0 order by i.desitems";
$result=$dbmsql->query($query);
foreach($result as $fila){
	$desitems=$fila['desitems'];
	$cantidad=$fila['compra'];
	$costo=round($fila['costo'],2);
	$costototal=round($fila['costototal'],2);

	$report->SetFillColor(255,255,255);	

	$report->Cell(80 , 5, strtoupper($desitems) ,0,0,"L", true);
	$report->Cell(30 , 5, number_format($cantidad) ,0,0,"R", true);
	$report->Cell(30 , 5, '$'.number_format($costo,2,",",".") ,0,0,"R", true);
	$report->Cell(30 , 5, '$'.number_format($costototal,2,".",",") ,0,1,"R", true);
	$report->Ln(1);
}

    $report->Ln(10);
    $report->Cell(80 , 5, "Total unidades" ,0,0,"C", true);
	$report->Cell(50 , 5, number_format($canTotal) ,0,0,"C", true);
	$report->Cell(40 , 5, "P.O. Total $".number_format($sumTotalPO ,2,".",",") ,0,0,"R", true);
    //$report->Cell(28 , 5, '$'.number_format($sumTotalPO ,2,",",".") ,0,0,"R", true);
//	$report->Ln(10);

$report->Output();
?>