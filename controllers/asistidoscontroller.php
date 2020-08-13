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

     $qryRows  = "SELECT COUNT(*) num_rows   FROM VIEW_repconsultas3 a inner join  VIEW_repconsultasNCitas b ON a.codclien=b.codclien where a.asistido=3 and fecha_cita between '$fechai' and  '$fechaf' " ;
     $query    = "SELECT a.nombres, a.telfhabit, a.Medicos, a.Historia,  CONVERT(varchar(11),a.fecha_cita,101) fecha_cita, a.activa, a.asistido, a.usuario,CONVERT(varchar(11),a.ProximaCita,101) ProximaCita,a.observacion, b.NumeroCitas FROM VIEW_repconsultas3 a inner join  VIEW_repconsultasNCitas b ON a.codclien=b.codclien where a.asistido=3 and fecha_cita between  '$fechai' and  '$fechaf'  ";
	 $query    = "SELECT a.nombres, a.telfhabit, a.Medicos, a.Historia, a.celular,  CONVERT(varchar(11),a.fecha_cita,101) fecha_cita, a.activa, a.asistido, a.usuario,CONVERT(varchar(11),a.ProximaCita,101) ProximaCita,a.observacion, b.NumeroCitas FROM VIEW_repconsultas3 a inner join  VIEW_repconsultasNCitas b ON a.codclien=b.codclien where a.asistido=3 and fecha_cita between  '$fechai' and  '$fechaf'  ";

 }

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
  	
  } 
   
   if ($filter!=="") {
   
   }
   

    $query  .=" ORDER BY  a.usuario,  a.nombres  ";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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