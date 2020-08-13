<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../db/mssqlconn.php';
require_once '../clases/paginator.class.php';

  $fecha=$_POST['fecha'];

  $dbmsql = mssqlConn::getConnection();
  //$dbTable ="";
  $field   =" a.numfactu, a.nombres, CONVERT(VARCHAR(10),a.fechafac,101) fechafac, a.subtotal, a.descuento, a.total monto, a.statfact, a.usuario, a.TotImpuesto, a.monto_flete, a.doc,a.workstation,b.nombre, b.abreviatura , a.usuario";
  $from    =" From VentasDiariasMSS a INNER JOIN   MDocumentos b ON a.doc = b.codtipodoc ";
  $where   =" Where a.fechafac ='$fecha' and statfact=3"; 
  $groupBy ="  ";
  $orderBy =" Order by a.numfactu  desc";


  if (  (isset($_POST['search']) && $_POST['search']!="")  && (isset($_POST['fecha']) && $_POST['fecha']!="")   ) {
 
  }else if (isset($_POST['search']) && $_POST['search']!="") {
    
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