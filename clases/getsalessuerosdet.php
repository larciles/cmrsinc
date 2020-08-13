<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  $fecha=$_POST['fecha'];

  $dbmsql = mssqlConn::getConnection();
  $dbTable =" VIEW_CMA_Mfactura_1 ";
  $field   =" convert(varchar(10), cast(a.fechafac as date), 101)  fechapago ,a.numfactu, a.nombres, a.subtotal, a.descuento, a.total monto, a.statfact, a.TotImpuesto, a.monto_flete , a.usuario";
  $from    =" FROM  VentasDiariasCMACST1 a INNER JOIN MDocumentos  b ON a.doc = b.codtipodoc  ";
  $where   =" WHERE a.fechafac between '$fecha' AND '$fecha' AND a.cod_subgrupo = 'SUEROTERAPIA' and  a.statfact=3 "; 
  $groupBy =" Group by DesTipoTargeta ";
  $orderBy =" Order by  a.numfactu ASC ";


  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fecha']) && $_POST['fecha']!="")   ) {
         
     // $filter=$_POST['search'];
     // 
     // $qryRows  = "SELECT COUNT(*) num_rows FROM ".$dbTable." Where fechafac ='$fecha'  and CONCAT( numfactu,nombres,Medico,Status,total) like '%$filter%' " ;
     // $query    = "SELECT ".$field." FROM ".$dbTable." WHERE  fechafac ='$fecha'  and   CONCAT( numfactu,nombres,Medico,Status,total) like '%$filter%'  ";

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
      
 }else if(isset($_POST['fecha']) && $_POST['fecha']!=""){

    $qryRows  = "SELECT COUNT(*) num_rows ".$from.$where ;
    $query    = "SELECT ".$field.$from.$where  ;

 }
  

 $limit      = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
 $page       = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
 $links      = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

   if (isset($_POST['fecha']) && $_POST['fecha']!="") {
    
  } 
   
   if ($filter!=="") {
   
   }
   

  $query  .= $orderBy;   //MUY IMPORTANTE SIN ESTO NO FUNCIONA LA PAGINACION EN MSSQL


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