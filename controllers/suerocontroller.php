<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  
  $dbmsql = mssqlConn::getConnection();
  $dbTable="VIEW_CMA_Mfactura_4 a";
  $field="distinct numfactu,CONVERT(VARCHAR(10),a.fechafac,110) fechafac, a.nombres, a.Medico,a.statfact,a.subtotal,a.descuento,a.total,a.id,a.Status,a.numnotcre,a.usuario,a.codmedico,a.Historia ,
 (Select Case When sum(b.monto) IS NULL then 0 else  sum(b.monto)  End from Mpagos b Where b.numfactu=a.numfactu and b.tipo_doc='01' and id_centro=2 ) paid ";

  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fecha']) && $_POST['fecha']!="")   ) {
         
     $filter=$_POST['search'];
     $fecha=$_POST['fecha'];
     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  and CONCAT( numfactu,nombres,Medico,historia) like '%$filter%' " ;
     $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  fechafac ='$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')   and   CONCAT( numfactu,nombres,Medico,historia) like '%$filter%'  ";

  }else if (isset($_POST['search']) && $_POST['search']!="") {

     $filter=$_POST['search'];
     $len = strlen($filter);
     $isnum = is_numeric($filter);
     if (is_numeric($filter) && $len>=6 ) {
        $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." WHERE numfactu = '$filter' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') " ;
        $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  numfactu='$filter' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  "; 
     }else{
        $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." Where CONCAT(numfactu,nombres,Medico,historia) like '%$filter%' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  " ;
        $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  CONCAT(numfactu,nombres,Medico,historia) like '%$filter%' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  "; 
     }
      
 }else if(isset($_POST['fecha']) && $_POST['fecha']!=""){

     $fecha=$_POST['fecha'];
     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') " ;
     $query    = "SELECT ".$field." FROM ".$dbTable." Where fechafac ='$fecha' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') ";

 }else{

     $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable."  Where cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  " ;
     $query    = "SELECT ".$field." FROM ".$dbTable." Where cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER')  ";
 }
  

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
  	
  } 
   
   if ($filter!=="") {
   
   }
   

    $query  .=" ORDER BY id DESC";  	//MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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