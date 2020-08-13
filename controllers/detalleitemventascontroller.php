<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  
$dbmsql = mssqlConn::getConnection();

if(isset($_POST['fechai']) && $_POST['fechai']!=""){
   
     $fechai=$_POST['fechai'];
     $fechaf=$_POST['fechaf'];
     $coditems=$_POST['coditems'];

     $qryRows  = "SELECT COUNT(*) num_rows  FROM MFactura a INNER JOIN MClientes b ON a.codclien = b.codclien  INNER JOIN DFactura c ON a.numfactu = c.numfactu INNER JOIN MInventario d ON c.coditems = d.coditems Where a.fechafac between '$fechai' and  '$fechaf'  and c.coditems='$coditems' " ;   
     

      $query = " SELECT a.numfactu, CONVERT(VARCHAR(10),a.fechafac,110) fechafac ,c.cantidad,b.nombres,d.coditems,d.nombre_alterno FROM MFactura a INNER JOIN MClientes b ON a.codclien = b.codclien INNER JOIN DFactura c ON a.numfactu = c.numfactu INNER JOIN MInventario d ON c.coditems = d.coditems Where a.fechafac between '$fechai' and '$fechaf' and c.coditems='$coditems'  ";

 }

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
  	
  } 
   
   if ($filter!=="") {
   
   }
   

    $query  .=" ORDER BY nombres asc ";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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
	    // $npo=sizeof($result->data);
	    // $tr = count($result->data);
	    $totalRegistros=$results->datalen;
	    $respuesta=  $results ;
 	}else
 	{
 		$totalRegistros=0;
 		$respuesta="";
 	} 

	echo json_encode($respuesta);