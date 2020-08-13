<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mysqlconn.php';
require_once '../../clases/paginator.class.php';

 if (version_compare(phpversion(), '5.4.0', '<')) {
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();   
    }
 }


//from here
$post_fields = array( 'from', 'to','chkstop' );        
$form_data = array();
        
foreach ( $post_fields as $key ){
   if ( isset( $_POST[$key] )){
        $form_data[$key] = $_POST[$key];
     }
}   

  if ( isset( $_POST['chkstop'] )){
      $cb=$_POST['chkstop'];
  }
        
if (!empty( $form_data ) && !isset( $_SESSION['form_data'] )){
     $_SESSION['form_data'] = serialize( $form_data );
}else
{
    if ( isset( $_POST['from'])  &&  isset($_POST['to'] )){
        $_SESSION['form_data'] = serialize( $form_data );
    }
}
        
if ( isset( $_SESSION['form_data'] ) && !empty( $_SESSION['form_data'] ) &&  empty( $form_data ) ){
    $form_data = unserialize( $_SESSION['form_data'] );           
             
    foreach($form_data as $key => $value)
    {
        $_POST[ $key] =$value;
    }
}
 
       
//to here



$desde="";
$hasta="";
function mostrarSMS(){
	$dbconn = MysqlConn::getConnection();
    //$totalRegistros=count( $results->data );


    $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 25;
    $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
    $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;
   
    if(isset($_GET['go']))
    {
        $from = $_GET['from'];
        $to = $_GET['to'];
        
        $from = explode('/', $from);
        $from  = "$from[2]-$from[0]-$from[1]";      
    
        $to = explode('/', $to);
        $to  = "$to[2]-$to[0]-$to[1]";     
        
        $qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies WHERE date(sms_received) between '$from' and '$to' " ;
        $query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies WHERE  date(sms_received) between '$from' and '$to' ";
    }
    else
    {   
    	if(isset($_POST['from']))
    	{
    		$from = $_POST['from'];
    		$from = explode('/', $from);
    		$from  = "$from[2]-$from[0]-$from[1]";

            $to = $_POST['to'];
            $to = explode('/', $to);
        	$to  = "$to[2]-$to[0]-$to[1]";  
           

    		$qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies WHERE  date(sms_received) between '$from' and '$to' " ;
          	$query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies  WHERE  date(sms_received) between '$from' and '$to' ";

            if ( isset( $_POST['chkstop'] )){
                $qryRows = "SELECT COUNT(*) num_rows FROM fr_replies WHERE  date(sms_received) between '$from' and '$to' and sms_body not like '%stop%'" ;
                $query  = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies  WHERE  date(sms_received) between '$from' and '$to' and sms_body not like '%stop%' ";
            }  

    	}else{
    		$qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies " ;
          	$query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies ";
    	}
    }
    if($page==0)
    {
        $page=1;
    }

    $Paginator  = new Paginator( $dbconn, $query,$qryRows );
    $results    = $Paginator->getData( $page, $limit );
    $lastPage=ceil( $results->total/$limit );
    if($page>ceil( $results->total/$limit )){
        if($lastPage!=0){
       $page=ceil( $results->total/$limit );
       $results    = $Paginator->getData( $page, $limit );
        }
    }
    $npo=sizeof($result->data);
    $tr = count($result->data);
    $totalRegistros=$results->datalen;

			$tabla ="<table id='tabla-smsrpl' class='table table-bordered table-hover table-condensed table-striped'>";
			$tabla .="<thead>";
				$tabla .="<tr>";
					$tabla .="<th>Teléfono</th>";
					$tabla .="<th>Mensaje</th>";											
					$tabla .="<th>Fecha</th>";					
				$tabla .="</tr>";
			$tabla .="</thead>";
			$tabla .="<tbody>";
			if($totalRegistros>0){
				for ($i=0; $i <$totalRegistros; $i++) { 
					$tabla .="<tr>";
					// $results->data[$i]
					// $results->data[$i]['sent_dt']
					$tabla .="<td>".$results->data[$i]['sms_from']."</td>"; //telefono
					$tabla .="<td>".$results->data[$i]['sms_body']."</td>"; //Mensaje
					$tabla .="<td>".$results->data[$i]['sms_received']."</td>"; //Fecha
					$tabla .="</tr>";
				}
			}			
			$tabla .="</tbody>";
			$tabla .="<tr>";
			$tabla .="<th>Teléfono</th>";
			$tabla .="<th>Mensaje</th>";											
			$tabla .="<th>Fecha</th>";					
			$tabla .="</tr>";
	    $tabla .="</table>";
	     echo $Paginator->createLinks( $links, 'pagination pagination-sm' );
	   // $paginacion = $Paginator->createLinks( $links, 'pagiantion pagination-sm' );
	    $respuesta = $tabla.$paginacion ;
	return printf($respuesta);
}

?>
