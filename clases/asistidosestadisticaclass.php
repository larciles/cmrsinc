<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  
  $dbmsql = mssqlConn::getConnection();
  $dbTable="VIEW_CMA_Mfactura_1";
  $field=" COUNT(a.CITADOS) citados ,a.usuario, (Select count(b.ASISTIDOS) asistido ";

  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fechai']) && $_POST['fechai']!="")   && (isset($_POST['fechaf']) && $_POST['fechaf']!="")   ) {
         
      // $filter=$_POST['search'];
      // $fechai=$_POST['fechai'];
      // $fechaf=$_POST['fechaf'];
      // $qryRows  = "SELECT COUNT(*) num_rows FROM view_Laser_00 a inner join MClientes d on a.codclien=d.codclien  where a.fechafac  between '$fechai' and '$fechaf' and nombres like '%$filter%' group by a.codclien  " ;
      // $query    = "SELECT ".$field." FROM view_Laser_00 a  inner join MClientes d on a.codclien=d.codclien where a.fechafac  between '$fechai' and '$fechaf' and nombres like '%$filter%'  group by a.codclien,d.nombres  ";     
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
     $qryRows  = "SELECT COUNT(*) num_rows FROM  VIEW_mconsultas_02  a where a.fecha_cita between '$fechai' and '$fechaf' group by a.usuario " ;
     $query    = "SELECT ".$field." FROM  VIEW_mconsultas_02 b where b.fecha_cita between '$fechai' and '$fechaf' and b.ASISTIDOS='Asistido' and a.usuario=b.usuario)  asistido from VIEW_mconsultas_02  a where a.fecha_cita between '$fechai' and '$fechaf' group by a.usuario ";
     $qryRows  = "SELECT   count(*)  num_rows FROM VIEW_repconsultas3 a  where a.fecha_cita between  '$fechai' and '$fechaf' and( a.confirmado=2 or a.asistido=3) group by a.usuario " ;
        $query    = "SELECT  a.usuario, ISNULL ( (SELECT count(*) xasisconfirm    FROM VIEW_repconsultas3 b  where a.usuario=b.usuario and b.fecha_cita between  '$fechai' and '$fechaf'  and b.confirmado=2 group by b.usuario),0) confirmado "
      ." ,ISNULL ( (SELECT count(*) asisconfirm  FROM VIEW_repconsultas3 b  where a.usuario=b.usuario and b.asistido=3 and b.fecha_cita between   '$fechai' and '$fechaf' and(b.confirmado=2 and b.asistido=3) group by b.usuario),0) asisconfirm "
      ." ,ISNULL ( (SELECT count(*) asisnoconfirm  FROM VIEW_repconsultas3 b  where a.usuario=b.usuario and b.asistido=3 and b.fecha_cita between   '$fechai' and '$fechaf' and(b.confirmado=0 and b.asistido=3) group by b.usuario),0) asisnoconfirm "
      ." ,max( concat(c.Nombre, ' ',c.apellido)) nombre " 
      ." FROM VIEW_repconsultas3 a "
      ." inner join loginpass c ON a.usuario=c.login "
      ." where a.fecha_cita between '$fechai' and '$fechaf' group by a.usuario ";        

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
   

    $query  .=" ORDER BY confirmado desc ";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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