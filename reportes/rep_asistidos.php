<?php
include "../clases/fpdf/fpdf.php";
include '../db/mssqlconn.php';	

if(isset($_GET['p']))
{
	$fecha_cita=$_GET['p'];
}

class Repasistidosclass extends FPDF
{
	

	public function Header(){
		//$this->Image("../img/phplogo.jpg",10,10,20,20);
		$this->SetFont("Arial","B",20);
		$this->Cell(100,10,"Orden de compra ",0,0,"C");
		
	}
}

$titulos = array('Paciente','Teléfono','Méedico','Record', 'Próx cita', '# Cita');
$report = new Repasistidosclass();



$report->addPage();
date_default_timezone_set("America/Puerto_Rico");
$fecha=date("Y-m-d");
$report->Cell(10,8,'# '.$fecha_cita,0,0,"C");
$report->Ln(20);
$report->SetFont("Arial","B",12);
$report->Cell(250,10,$fecha,0,0,"C");


$report->Ln(10);
for ($i=0; $i < count($titulos) ; $i++) { 
	$report->SetFont('Courier','B',10);
	$report->SetTextColor('255','255','255');
	$report->Cell(40 , 5, $titulos[$i] ,1,0,"C", true);	         
}

$report->Ln(10);
$dbmsql = mssqlConn::getConnection();


//TOTALES
// $query="Select sum(p.compra) cantidadtotal , sum( p.compra*i.costo) total From purchaseorder p inner join MInventario i On p.coditems=i.coditems where p.purchaseOrder='$pon' and p.compra>0";
// $result=$dbmsql->query($query);
// foreach($result as $fila){
//     $canTotal=$fila['cantidadtotal'];
//     $sumTotalPO=$fila['total'];
// }
//DETALLES

$query= "Select a.nombres, a.telfhabit, a.Medicos, a.Historia, a.fecha_cita, a.activa, a.asistido, a.usuario,a.ProximaCita,a.observacion, b.NumeroCitas FROM VIEW_repconsultas3 a inner join  VIEW_repconsultasNCitas b ON a.codclien=b.codclien where a.asistido=3 and fecha_cita='$fecha_cita' ";

$result=$dbmsql->query($query);
foreach($result as $fila){
	$nombres=$fila['nombres'];
	// $cantidad=$fila['compra'];
	// $costo=round($fila['costo'],2);
	// $costototal=round($fila['costototal'],2);

	$report->SetFillColor(100,100,200);	

	$report->Cell(40 , 5, $nombres ,1,0,"C", true);
	// $report->Cell(40 , 5, $cantidad ,1,0,"C", true);
	// $report->Cell(40 , 5, $costo ,1,0,"C", true);
	// $report->Cell(40 , 5, $costototal ,1,0,"C", true);
	$report->Ln(10);
}
 
    $report->Ln(10);
 //    $report->Cell(40 , 5, "Total unidades" ,1,0,"C", true);
	// $report->Cell(40 , 5, $canTotal ,1,0,"C", true);
	// $report->Cell(40 , 5, "P.O. Total" ,1,0,"C", true);
 //    $report->Cell(40 , 5, $sumTotalPO ,1,0,"C", true);
//	$report->Ln(10);

$report->Output();
?>