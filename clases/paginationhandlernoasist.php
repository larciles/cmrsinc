<?php

require_once 'paginacion.php';
$paginas = new Pagina();
$limit = 25;

$fecha    =$_POST['fechai'];
$fecha2   =$_POST['fechaf'];
$usuario  =$_POST['usuario'];
$codperfil=$_SESSION['codperfil'];  //NO ES codperfil ES controlcita
$codperfil=$_SESSION['controlcita'];//$_GET['codperfil'];  


if (isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] )) {
  	$fechai=$_POST["fechai"];
  	$fechaf=$_POST["fechaf"];
	
    if ($codperfil=='1') {
        $query="SELECT COUNT(*) total "
        ."FROM "
        ."VIEW_repconsultas4 a "
        ."Where "
        ."(a.fecha_cita  between '$fechai' and '$fechaf') and a.activa<>'0' and a.asistido<>3  and a.usuario='$usuario'  and a.fallecido<>'1' and a.inactivo<>'1'  ";
    } else {
        $query="SELECT  COUNT(*) total "
        ."FROM "
        ."VIEW_repconsultas4 a "
        ."Where "
        ."(a.fecha_cita  between '$fechai' and '$fechaf') and a.activa<>'0' and a.asistido<>3  and a.fallecido<>'1' and a.inactivo<>'1'  ";
    }
     
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
    $usuario=$_GET['usuario'];

    if ($codperfil=='1') {
        $query="SELECT 
        a.nombres
       ,a.telfhabit
       ,a.Medicos
       ,a.Historia
       ,a.citados
       ,a.asistido
       ,a.activa
       ,a.usuario
       ,a.fecha_cita
       ,a.hora
       ,convert(varchar(10)
       ,cast(a.UltimaAsistida as date), 101)  UltimaAsistida
       ,a.observacion
       ,a.fallecido 
       FROM 
       VIEW_repconsultas4 a 
       Where 
        a.fecha_cita  between '$fechai' and '$fechaf' and a.activa<>'0' and a.asistido<>3  and a.usuario='$usuario'  and a.fallecido<>'1' and a.inactivo<>'1'   order by a.fecha_cita desc, a.nombres";

    } else {
       $query="SELECT a.nombres,a.telfhabit,a.Medicos,a.Historia,a.citados,a.asistido,a.activa,a.usuario,a.fecha_cita,a.hora,convert(varchar(10),cast(a.UltimaAsistida as date),101) UltimaAsistida,a.observacion,a.fallecido FROM VIEW_repconsultas4 a Where a.fecha_cita  between '$fechai' and '$fechaf' and a.activa<>'0' and a.asistido<>3  and a.fallecido<>'1' and a.inactivo<>'1'  order by a.fecha_cita desc, a.nombres";

    }

	$res=$paginas->getNewPage($query,$limit,$page);

	echo $res;	
}