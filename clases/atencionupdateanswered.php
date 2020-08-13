<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$codconsulta = $_POST['codconsulta'];
$codclien    = $_POST['codclien'];
//$fecha_cita  = date("Y-m-d"); //$_POST['fecha_cita'];
$coditems    = $_POST['coditems'];
$asistido    = $_POST['asistido'];
$horain      = $_POST['horain'];
$classnop    = $_POST['classnop'];
  date_default_timezone_set("America/Puerto_Rico");
  $fecha_cita =date("Y-m-d");
  if (isset($_POST['codperfil']) ) {
   if ($_POST['codperfil']=='01' || $_POST['codperfil']=='06') {
      $fecha_cita =$_POST['datesltd'];  
   }   
 }

$clcod = explode("-",$codclien );
$codclien =$clcod[0];
if ($codconsulta=='07') {
	$query="SELECT * from mconsultasS where codclien='$codclien' and fecha_cita='$fecha_cita'  ";
} else {
	$query="SELECT * from mconsultas  where codclien='$codclien' and fecha_cita='$fecha_cita' ";
}

 $result = mssqlConn::Listados($query);

 $haycitas = json_decode($result, true);
 $nin=sizeof($haycitas);
 if ($nin>0) {
	 if ($codconsulta=='07') {
		
		if($classnop ==''){
        $query="UPDATE  mconsultasS  SET answered ='' where codclien='$codclien' and fecha_cita='$fecha_cita' ";
                                
      }else{
         	 $query="UPDATE mconsultasS  SET answered = '$classnop' where codclien='$codclien' and fecha_cita='$fecha_cita'   ";
         }  
            
	} else {
		
      if($classnop ==''){
            $query="UPDATE mconsultas  SET answered = ''  where codclien='$codclien' and fecha_cita='$fecha_cita' ";
                     
        }else{
        	$query="UPDATE mconsultas  SET answered = '$classnop' where codclien='$codclien' and fecha_cita='$fecha_cita' ";
        }
	}

	$result = mssqlConn::insert($query);                  
 }
echo $result;