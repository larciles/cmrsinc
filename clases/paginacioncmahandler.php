<?php
require_once 'paginacion.php';
$paginas = new Pagina();

$limit =25;

if (isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] )) {
	$fechai=$_POST["fechai"];
	$fechaf=$_POST["fechaf"];
	//$query=" Cmacierreinv  INNER JOIN MInventario
    //ON Cmacierreinv.coditems = MInventario.coditems";

   $query="SELECT COUNT(*) total FROM Cmacierreinv  INNER JOIN MInventario ON Cmacierreinv.coditems = MInventario.coditems Where Cmacierreinv.fechacierre between '$fechai' and '$fechaf'";

//	$res=$paginas->getTotalPages($query," Cmacierreinv.fechacierre between '$fechai' and '$fechaf'",25 );
    $res=$paginas->getTotalPages($query,$limit );
	echo $res;
}

if (isset( $_POST['page'] ) || isset( $_GET['page'] )) {
    
    if (isset($_GET["page"])) { 
    	$page  = $_GET["page"]; 
    } else { 
    	$page=1; 
    };  

	$fechai=$_GET["fechai"];
	$fechaf=$_GET["fechaf"];

$query="SELECT 
  Cmacierreinv.coditems 
  ,CONVERT(VARCHAR(10),Cmacierreinv.fechacierre,101) fechacierre
  ,Cmacierreinv.existencia
  ,Cmacierreinv.compras
  ,Cmacierreinv.DevCompras
  ,Cmacierreinv.ventas
  ,Cmacierreinv.ventas*MInventario.costo costoDVentas
  ,Cmacierreinv.anulaciones
  ,Cmacierreinv.ajustes
  ,Cmacierreinv.NotasCreditos
  ,Cmacierreinv.NotasEntregas
  ,Cmacierreinv.InvPosible
  ,Cmacierreinv.InvActual
  ,MInventario.coditems 
  ,MInventario.desitems
  ,Cmacierreinv.InvPosible*MInventario.costo costoTtlVentas FROM  Cmacierreinv  INNER JOIN MInventario
    ON Cmacierreinv.coditems = MInventario.coditems
    where  Cmacierreinv.fechacierre between '$fechai' and '$fechaf' ORDER BY fechacierre DESC ";

	$res=$paginas->getNewPage($query,$limit,$page);
	echo $res;	
}



