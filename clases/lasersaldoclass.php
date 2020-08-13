<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  
  $dbmsql = mssqlConn::getConnection();
  $dbTable="VIEW_CMA_Mfactura_1";
  $field=" a.codclien,sum(a.cantidad) cantidad,d.nombres , max(d.historia) record ";

 



  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fechai']) && $_POST['fechai']!="")   && (isset($_POST['fechaf']) && $_POST['fechaf']!="")   ) {
         
      $filter=$_POST['search'];
      $fechai=$_POST['fechai'];
      $fechaf=$_POST['fechaf'];
      $qryRows  = "SELECT COUNT(*) num_rows FROM view_Laser_00 a inner join MClientes d on a.codclien=d.codclien  where a.fechafac  between '$fechai' and '$fechaf' and nombres like '%$filter%' group by a.codclien  " ;
      $query    = "SELECT ".$field." FROM view_Laser_00 a  inner join MClientes d on a.codclien=d.codclien where a.fechafac  between '$fechai' and '$fechaf' and nombres like '%$filter%'  group by a.codclien,d.nombres  ";     
  }else if (isset($_POST['search']) && $_POST['search']!="") {

     // $filter=$_POST['search'];
     // $len = strlen($filter);
     // $isnum = is_numeric($filter);
     // if (is_numeric($filter) && $len>=6 ) {
     //    $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." WHERE numfactu = '$filter' " ;
     //    $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  numfactu='$filter'  "; 
     // }else{
     //    $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." Where CONCAT( numfactu,nombres,Medico,Status,total) like '%$filter%' " ;
     //    $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  CONCAT( numfactu,nombres,Medico,Status,total) like '%$filter%'  "; 
     // }
      
 }else if(isset($_POST['fechai']) && $_POST['fechai']!=""){

     $fechai=$_POST['fechai'];
     $fechaf=$_POST['fechaf'];   
     $qryRows  = "SELECT COUNT(*) num_rows FROM view_Laser_00 a  where a.fechafac  between '$fechai' and '$fechaf' group by a.codclien  " ;
     $query    = "SELECT ".$field." FROM view_Laser_00 a  inner join MClientes d on a.codclien=d.codclien where a.fechafac  between '$fechai' and '$fechaf' group by a.codclien,d.nombres  ";

 }else{

//     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable."  " ;
//     $query    = "SELECT ".$field." FROM ".$dbTable."  ";
 }
  

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
  	
  } 
   
   if ($filter!=="") {
   
   }
   

    $query  .=" ORDER BY d.nombres ";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


    if($page==0)
    {
        $page=1;
    }
  //
    $Paginator  = new Paginator( $dbmsql, $query,$qryRows );
    $tp =$Paginator->getTotal();
    if ($tp >0) {
    	# code...
   
	    $lastPage=ceil( $tp/$limit );
	     if($page>$lastPage){
	         $page=$lastPage;
	     }
	    $results    = $Paginator->getData( $page, $limit,"mssql" );
	    $lastPage=ceil( $results->total/$limit );
	    if($page>ceil( $results->total/$limit )){
	        if($lastPage!=0){
	      		 $page=ceil( $results->total/$limit );
	       		 $results    = $Paginator->getData( $page, $limit );
	        }
	    }

	    $totalRegistros=$results->datalen;
	    $respuesta=  $results ;
 	}else
 	{
 		$totalRegistros=0;
 		$respuesta="";
 	} 

	echo json_encode($respuesta);