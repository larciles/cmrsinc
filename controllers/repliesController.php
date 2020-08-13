<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mssqlconn.php';
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
  $dbmsql = mssqlConn::getConnection();
	$dbconn = MysqlConn::getConnection_my();
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
           
            if($from!="--"){
    		$qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies WHERE  date(sms_received) between '$from' and '$to' " ;
          	$query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies  WHERE  date(sms_received) between '$from' and '$to' ";

            if ( isset( $_POST['chkstop'] )){
                $qryRows = "SELECT COUNT(*) num_rows FROM fr_replies WHERE  date(sms_received) between '$from' and '$to' and sms_body not like '%stop%'" ;
                $query  = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies  WHERE  date(sms_received) between '$from' and '$to' and sms_body not like '%stop%' ";
            }  
        }else{

           $qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies " ;
            $query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies ";
             if ( isset( $_POST['chkstop'] )){
                 $qryRows    = "SELECT COUNT(*) num_rows FROM fr_replies WHERE sms_body not like '%stop%' " ;
            $query      = "SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies WHERE sms_body not like '%stop%' ";
             } 
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
 
    $recnum=$results->total;

    $lastPage=ceil( $results->total/$limit );
    if($page>ceil( $results->total/$limit )){
        if($lastPage!=0){
            $page=ceil( $results->total/$limit );
            $results    = $Paginator->getData( $page, $limit );
        }
    }
    //$dbmsql = MysqlCmaConn::getConnection();
    
    $mysqlQuery="SELECT count(*) stops from fr_replies where  date(sms_received) between '$from' and '$to' And sms_body like '%stop%'";  
    $resmsq = MysqlConn::Listados($mysqlQuery);
    $_obj = json_decode($resmsq, true);
    $stops=$_obj[0]['stops'];

    // $npo=sizeof($result->data);
    // $tr = count($result->data);
    $totalRegistros=$results->datalen;
             

			$tabla ="<table id='tabla-smsrpl' class='table table-hover table-condensed table-striped'>";
			$tabla .="<thead>";
				$tabla .="<tr>";
					$tabla .="<th>Teléfono</th>";
          $tabla .="<th>Record</th>";
          $tabla .="<th>Paciente</th>";
          $tabla .="<th>Mensaje recibido</th>";          
          $tabla .="<th>Usuario</th>";
          $tabla .="<th>Última cita</th>";
          $tabla .="<th>Observación</th>";
					$tabla .="<th>Fecha</th>";					
				$tabla .="</tr>";
			$tabla .="</thead>";
			$tabla .="<tbody>";
			if($totalRegistros>0){
				for ($i=0; $i <$totalRegistros; $i++) { 
					$tabla .="<tr>";
					// $results->data[$i]
					// $results->data[$i]['sent_dt']

          $id_pac=substr($results->data[$i]['sms_from'],1,3).'-'.substr($results->data[$i]['sms_from'],4,3).'-'.substr($results->data[$i]['sms_from'],7,4);
          $pacientDat=getPatientName($id_pac);
          $pacient="Este Numero esta asociado a mas de una persona";
          $recondN="";
          if(is_array($pacientDat)){
             $pacient=$pacientDat[0]['nombres'];
             $recondN=$pacientDat[0]['Historia'];
             $usuario=$pacientDat[0]['Usuario'];
             $observacion=$pacientDat[0]['observacion'];
             $fecha_cita=$pacientDat[0]['fecha_cita'];
          } 

					$tabla .="<td>".$id_pac."</td>"; //telefono
          $tabla .="<td>".$recondN."</td>"; //record
          $tabla .="<td>".$pacient."</td>"; //Paciente
          $tabla .="<td>".$results->data[$i]['sms_body']."</td>"; //Mensaje
          
          $tabla .="<td>".$usuario."</td>"; //Usuario
          
          $tabla .="<td>".$fecha_cita."</td>"; //Ultima cita
					$tabla .="<td>".$observacion."</td>"; //Observacion

					$tabla .="<td>".$results->data[$i]['sms_received']."</td>"; //Fecha
					$tabla .="</tr>";
				}
			}			
			$tabla .="</tbody>";
			$tabla .="<tr>";
			 $tabla .="<th>Teléfono</th>";
       $tabla .="<th>Record</th>";
       $tabla .="<th>Paciente</th>";
       $tabla .="<th>Mensaje recibido</th>";       
       $tabla .="<th>Usuario</th>";
       $tabla .="<th>Última cita</th>";
       $tabla .="<th>Observación</th>";
       $tabla .="<th>Fecha</th>";          
			$tabla .="</tr>";
  	        $tabla .="</table>";

             $table .="<div class='row'>";    
             $table .="<div style='float:left; padding-left: 10px; padding-right: 10px;'>";
             $table .=" <button type='button' id='spncitados' class='btn btn-active'>Total # <span  class='badge'>".$recnum."</span></button>";
             //$table .=" <button type='button' id='confirmado' class='btn btn-active'>Confirmados <span class='badge'></span></button>";
             $table .=" <button type='button' id='btnasis' class='btn btn-danger'>Total 'Stop' # <span id='spnasistidos' class='badge'>".$stops."</span></button> ";
//             $table .=" <button type='button' id='btnnuev' class='btn btn-active'>Nuevos <span class='badge'>".$nuevos."</span></button> ";
//             $table .=" <button type='button' id='btnctrl' class='btn btn-active' data-tip='Solo Consultas, no inluye servicios' >Control <span class='badge'>".$control."</span></button> ";

//             $table .=" <button type='button' id='btnarrived' class='btn btn-active' data-tip='Pacientes presentes en el centro' >Presentes <span class='badge'>".$arrived."</span></button> ";
// ;
            $table .="</div>";
            $table .="</div>";
            
        echo $table;
	    echo $Paginator->createLinks( $links, 'pagination pagination-sm' );
	   // $paginacion = $Paginator->createLinks( $links, 'pagiantion pagination-sm' );
	    $respuesta = $tabla.$paginacion ;
	return printf($respuesta);
}


function getPatientName($idpaciente){
  
  $dbmsql = mssqlConn::getConnection();
  $query="Select  a.nombres,a.Historia,CONVERT(VARCHAR(10), b.fecha_cita,126) fecha_cita,b.id, c.observacion, c.Usuario  from mclientes a 
 inner join (  SELECT codclien,MAX(ID) AS ID, MAX(fecha_cita)  fecha_cita FROM mconsultas GROUP BY codclien ) b on a.codclien=b.codclien
  inner join mconsultas c On c.Id=b.ID
where a.Cedula='$idpaciente' ";
  $listado = mssqlConn::Listados($query);
  $paciente= json_decode($listado, true);
  $lenpa = sizeof($paciente);
  if ($lenpa!==1) {
    $paciente="";
  }

  return $paciente;
}

?>
