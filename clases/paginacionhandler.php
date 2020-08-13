<?php

require_once 'paginacion.php';
$paginas = new Pagina();
$limit = 25;

if (isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] )) {
  	$fechai=$_POST["fechai"];
  	$fechaf=$_POST["fechaf"];
	
    $query="SELECT COUNT(*) total FROM mscierre INNER JOIN MInventario  ON mscierre.coditems = MInventario.coditems WHERE mscierre.fechacierre between  '$fechai' and '$fechaf'"; 
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

    $query="SELECT mscierre.coditems 
    ,CONVERT(VARCHAR(10),mscierre.fechacierre,101) fechacierre
    ,mscierre.existencia
    ,mscierre.compras
    ,mscierre.DevCompras
    ,mscierre.ventas
    ,mscierre.ventas*MInventario.costo costoDVentas
    ,mscierre.anulaciones
    ,mscierre.ajustes
    ,mscierre.NotasCreditos
    ,mscierre.NotasEntregas
    ,mscierre.InvPosible
    ,mscierre.InvActual
    ,MInventario.coditems 
    ,MInventario.desitems
    ,mscierre.InvPosible*MInventario.costo costoTtlVentas
    ,MInventario.nombre_alterno FROM mscierre INNER JOIN MInventario
    ON mscierre.coditems = MInventario.coditems
    where  mscierre.fechacierre between '$fechai' and '$fechaf' ORDER BY fechacierre DESC";

	  $res=$paginas->getNewPage($query,$limit,$page);

	echo $res;	
}