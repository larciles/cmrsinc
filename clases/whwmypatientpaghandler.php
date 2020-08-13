<?php

require_once 'paginacion.php';
$paginas = new Pagina();
$limit = 25;

if (isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] )) {

    $search=$_POST["search"];
	
    $query="SELECT COUNT(*) total FROM view_whathappend WHERE Historia='$search' "; 
    
	$res=$paginas->getTotalPages($query,$limit );
	  echo $res;
}

if (isset( $_POST['page'] ) || isset( $_GET['page'] )) {
    
    if (isset($_GET["page"])) { 
    	$page  = $_GET["page"]; 
    } else { 
    	$page=1; 
    } 


    $search=$_GET["search"];

   
        $query=" SELECT Historia
        ,nombres
        ,CONVERT(CHAR(15), fecha_cita, 101) fc 
        ,fecha_cita
        ,usuario
        ,observacion
        ,asistido
        ,codclien
        ,codconsulta
        ,codmedico
        ,descons
        ,medico 
        from view_whathappend where Historia='$search' order by fecha_cita desc";
    
	  $res=$paginas->getNewPage($query,$limit,$page);

	echo $res;	
}