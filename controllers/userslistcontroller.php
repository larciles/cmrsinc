<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

$filter="";  
$dbmsql = mssqlConn::getConnection();
$dbTable="loginpass";
$field=" * ";
$Where=" Where CONCAT( login,Nombre,apellido,codperfil ) ";

  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fecha']) && $_POST['fecha']!="")   ) {
         
    
     
  }else if (isset($_POST['search']) && $_POST['search']!="") {

     $filter=$_POST['search'];     
     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." ".$Where. " like '%$filter%' " ;
     $query    = "SELECT ".$field." FROM ".$dbTable." ".$Where. " like '%$filter%' "; 
      
 }else if(isset($_POST['fecha']) && $_POST['fecha']!=""){

    

 }else{

     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable."  " ;
     $query    = "SELECT ".$field." FROM ".$dbTable."  ";
 }
  

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
  	
  } 
   
   if ($filter!=="") {
   
   }
   

    $query  .=" ORDER BY Nombre ";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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