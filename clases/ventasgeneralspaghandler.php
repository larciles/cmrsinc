<?php

require_once 'paginacion.php';
$paginas = new Pagina();
$limit = 25;

if (isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] )) {
  	$fechai=$_POST["fechai"];
  	$fechaf=$_POST["fechaf"];
    $med=$_POST["med"];
    if ($med=="") {
        $query="SELECT  COUNT(*) total FROM view_audit a inner join MClientes C ON C.codclien= a.codclien inner join Mmedicos M ON M.Codmedico=a.codmedico WHERE a.fechafac between  '$fechai' and '$fechaf'"; 
    }else{
        $query="SELECT  COUNT(*) total FROM view_audit a inner join MClientes C ON C.codclien= a.codclien inner join Mmedicos M ON M.Codmedico=a.codmedico WHERE a.fechafac between  '$fechai' and '$fechaf' AND a.codmedico='$med' "; 
    }
	
    
	$res=$paginas->getTotalPages($query,$limit );
	  echo $res;
}

if (isset( $_POST['page'] ) || isset( $_GET['page'] )) {
    
    if (isset($_GET["page"])) { 
    	$page  = $_GET["page"]; 
    } else { 
    	$page=1; 
    } 

  	$fechai=$_GET["fechai"];
	$fechaf=$_GET["fechaf"];
    $med=$_GET["med"];

    if ($med=="") {
        $query=" SELECT  Historia record
        ,CONCAT(m.nombre+' ', m.apellido) medico
        ,a.numfactu factura
        ,CONVERT(VARCHAR(10),a.fechafac,101)   fecha
        ,c.nombres cliente
        ,a.codclien
        ,a.company empresa 
        ,a.total
        ,a.usuario
        ,a.fechafac
        ,a.codcompany
        ,a.codmedico 
        ,S.asistido
        ,a.id
        ,(Select  count(*) from Mconsultas where codclien=a.codclien and fecha_cita < S.fecha_cita ) ncitas
        FROM view_audit a 
        inner join MClientes C ON C.codclien= a.codclien 
        inner join Mmedicos M ON M.Codmedico=a.codmedico 
        left join Mconsultas S ON S.codclien=a.codclien and S.fecha_cita=a.fechafac
        WHERE a.fechafac between '$fechai' and '$fechaf' ORDER BY medico DESC,record";
    }else{
        $query=" SELECT Historia record
        ,CONCAT(m.nombre+' ', m.apellido) medico
        ,a.numfactu factura
        ,CONVERT(VARCHAR(10),a.fechafac,101)   fecha
        ,c.nombres cliente
        ,a.codclien
        ,a.company empresa 
        ,a.total
        ,a.usuario
        ,a.fechafac
        ,a.codcompany
        ,a.codmedico 
        ,S.asistido
        ,a.id
        ,(Select  count(*) from Mconsultas where codclien=a.codclien and fecha_cita < S.fecha_cita ) ncitas
        FROM view_audit a 
        inner join MClientes C ON C.codclien= a.codclien 
        inner join Mmedicos M ON M.Codmedico=a.codmedico 
        left join Mconsultas S ON S.codclien=a.codclien and S.fecha_cita=a.fechafac
        WHERE a.fechafac between '$fechai' and '$fechaf' and a.codmedico='$med' ORDER BY medico DESC, record";
    }
	  $res=$paginas->getNewPage($query,$limit,$page);

	echo $res;	
}