<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$valtype = $_POST['valtype'];
$codmedico = $_POST['codmedico'];
$codclien = $_POST['codclien'];
$fecha = $_POST['fecha'];

$cambiar_en_ambos=1;

if($valtype == "consultas"){
	//$query="UPDATE MClientes Set  codmedico='$codmedico' where codclien='$codclien' ";
	//$result = mssqlConn::insert($query);
    	
    if ($cambiar_en_ambos==1) {
    	 $query="SELECT codmedico FROM Mconsultass where codclien='$codclien' and fecha_cita='$fecha' ";
    	 $res = mssqlConn::Listados($query);
		 $result = json_decode($res, true);
		 try {
		 	$len = sizeof($result);
		 	if ($len>0) {
		 		$medico = $result[0]['codmedico'];
		 		if ( trim($medico) =="000") {
		 			$query="UPDATE Mconsultass Set codmedico='$codmedico' where codclien='$codclien' and fecha_cita='$fecha'";
		 			$result = mssqlConn::insert($query);
		 		}
		 	}
		 	
		 } catch (Exception $e) {
		 	
		 }    	
    }
	$query="UPDATE Mconsultas Set  codmedico='$codmedico' where codclien='$codclien' and fecha_cita='$fecha'";

}else{
  
    $query="SELECT codmedico FROM MClientes where codclien='$codclien' ";
    $result = mssqlConn::Listados($query);
    $obj = json_decode($result, true);
    $lenObj = sizeof($obj); 	
    if ($lenObj <=0) {
 	  // $query="UPDATE MClientes Set  codmedico='$codmedico' where codclien='$codclien' ";
 	  // $result = mssqlConn::insert($query);
    }
	$query="UPDATE Mconsultass Set  codmedico='$codmedico' where codclien='$codclien' and fecha_cita='$fecha' ";
}
$result = mssqlConn::insert($query);


$query="SELECT count(*) ncitas from Mconsultas where codclien='$codclien' and fecha_cita<'$fecha'  ";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);

try {
	$ncitas = $result[0]['ncitas'];
	if($ncitas == "0") {
            $query="UPDATE cma_MFactura Set codmedico='$codmedico' where codclien='$codclien' and fechafac='$fecha' ";
            $result = mssqlConn::insert($query);		
            $query="UPDATE MClientes Set codmedico='$codmedico' where codclien='$codclien'  ";
            $result = mssqlConn::insert($query);
	}	
} catch (Exception $e) {
	
}

try {
	$query="UPDATE cma_MFactura Set codmedico='$codmedico' where codclien='$codclien' and fechafac='$fecha' and codmedico='000' ";
	$result = mssqlConn::insert($query);
	
} catch (Exception $e) {
	
}


$query="SELECT codmedico from Mconsultas where codclien='$codclien' and  ( codmedico='000' or  codmedico='') ";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);

if ($len>0) {
    try {
     $query="UPDATE Mconsultas Set  codmedico='$codmedico' where codclien='$codclien'  and  ( codmedico='000' or  codmedico='')  ";
     $result = mssqlConn::insert($query);
    } catch (Exception $e) {
		
    }	
} 


$query="SELECT codmedico from Mconsultass where codclien='$codclien' and  ( codmedico='000' or  codmedico='') ";
$res = mssqlConn::Listados($query);
$result = json_decode($res, true);
$len = sizeof($result);

if ($len>0) {
	try {
	    $query="UPDATE Mconsultass Set  codmedico='$codmedico' where codclien='$codclien'  and  ( codmedico='000' or  codmedico='') ";		
            $result = mssqlConn::insert($query);
	} catch (Exception $e) {
			
	}	
} 

/*
July 27 2020
*/
	 $query="SELECT codmedico FROM MClientes where codclien='$codclien' ";
   $result = mssqlConn::Listados($query);
   $obj = json_decode($result, true);
   $lenObj = sizeof($obj); 	
   if ($lenObj >0) {
    	
    	 $db_codmed = $obj[0]['codmedico'];

    	if ( $db_codmed=="000" || is_null($db_codmed) ) {
         $query="UPDATE MClientes Set  codmedico='$codmedico' where codclien='$codclien' ";
 	  		 $result = mssqlConn::insert($query);		
    	}
   } 	
 	  
    