<?php
require_once 'paginacion.php';
$paginas = new Pagina();

$limit =25;

$dbTable="VIEW_CMA_Mfactura_2";

$field="CONVERT(VARCHAR(10),fechafac,101) fechafac,numfactu, nombres, Medico,statfact,subtotal,descuento,total,id,Status,numnotcre,usuario,historia,codmedico ";



if ( isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] ) ) {
   $grupo =$_POST['grupo']; 

   if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fecha']) && $_POST['fecha']!="") ) {

      $fecha=$_POST['fecha'];
      $query  = "SELECT COUNT(*) total FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo = '$grupo'" ;

   }else if (isset($_POST['search']) && $_POST['search']!="") {

      $filter=$_POST['search'];
      $len = strlen($filter);
      $isnum = is_numeric($filter);
     if (is_numeric($filter) && $len>=6 ) {
        $query  = "SELECT COUNT(*) total FROM ".$dbTable." WHERE numfactu = '$filter' and cod_subgrupo = '$grupo'" ;        
     }else{
        $query  = "SELECT COUNT(*) total FROM ".$dbTable." Where CONCAT(numfactu,nombres,Medico,historia,Status,usuario) like '%$filter%' and cod_subgrupo = '$grupo' " ;        
     }
     
   }else if (  isset($_POST['fecha']) && $_POST['fecha']!=""   ) {

      $filter=$_POST['search'];
      $fecha=$_POST['fecha'];
      $query  = "SELECT COUNT(*) total FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo = '$grupo' and CONCAT( numfactu,nombres,Medico,historia,Status,usuario) like '%$filter%' " ;

   } else {

      $query  = "SELECT COUNT(*) total FROM ".$dbTable ;      

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

    $grupo =$_GET['grupo'];

	   if (isset($_GET['fecha']) && $_GET['fecha']!="") {

      $fecha=$_GET['fecha'];
      $query    = "SELECT ".$field." FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo = '$grupo' Order by numfactu Desc";

   }else if (isset($_GET['search']) && $_GET['search']!="") {

      $filter=$_GET['search'];
      $len = strlen($filter);
      $isnum = is_numeric($filter);
     if (is_numeric($filter) && $len>=6 ) {
        $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  numfactu='$filter' and cod_subgrupo = '$grupo' Order by numfactu Desc"; 
     }else{
        $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  CONCAT(numfactu,nombres,Medico,historia,Status,usuario) like '%$filter%' and cod_subgrupo = '$grupo' Order by numfactu Desc "; 
     }
     
   }else if (  (isset($_GET['search']) && $_GET['search']!="")  && (isset($_GET['fecha']) && $_GET['fecha']!="")   ) {

      $filter=$_GET['search'];
      $fecha=$_GET['fecha'];
      $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  fechafac ='$fecha' and cod_subgrupo = '$grupo'  and   CONCAT( numfactu,nombres,Medico,historia,Status,usuario) like '%$filter%' Order by numfactu Desc ";

   } else {

      $query    = "SELECT ".$field." FROM ".$dbTable." Where cod_subgrupo = '$grupo' Order by numfactu Desc";

   }

	$res=$paginas->getNewPage($query,$limit,$page);
	echo $res;	
}