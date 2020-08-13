<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../db/mssqlconn.php';
$dbmsql = mssqlConn::getConnection();
if(isset($_POST['nombres'])){
    
  $nombre = $_POST['nombres'];
  $apellidos = $_POST['apellidos'];
  $nombres = $_POST['apellidos'].' '.$_POST['nombres'];
  $cedula =  $_POST['id'];		 	  
  $medico = $_POST['medico'];
  $dob = $_POST['dob'];
  $record = $_POST['record'];		 
  $dirl1 = $_POST['address-line1'];
  $codpostal = $_POST['postal-code'];
  $pais = $_POST['country'];
  $state = $_POST['region'];		  
  $dirl2 = $_POST['address-line2'];
  $ciudad = $_POST['city'];
  $email = $_POST['email'];
  $telefono = $_POST['phone'];	
  $telefono =  str_replace("(","",$telefono);
  $telefono =  str_replace(")","-",$telefono);
  $telefono =  str_replace(" ","",$telefono);

  $controlcita = $_POST['controlcita']; 

  $usuario = $_POST['xusr'];

  $usuariocl =  $_POST['usuariocl'];
  if($usuariocl=="")
  {
      $usuariocl = $usuario ;

  }

  if ( $controlcita!=='1' ) {
      $query = "Select * from loginpass where controlcita=1 and activo=1";
      $result = mssqlConn::Listados($query);
      $usuario = json_decode($result, true);
      $max = sizeof($usuario);
      $min =0;

      if (function_exists('random_int')):        
           $usuariocl = $usuario[random_int($min, $max)]['login'] ; // more secure
      elseif (function_exists('mt_rand')):
           $usuariocl = $usuario[mt_rand($min, $max)]['login'] ; // faster
      endif;
           $usuariocl = $usuario[rand($min, $max)]['login'] ; // old
  }
  




  $telhaba2 = $_POST['phone2'];
  $telhaba2 =  str_replace("(","",$telhaba2);
  $telhaba2 =  str_replace(")","-",$telhaba2);
  $telhaba2 =  str_replace(" ","",$telhaba2);
  

  $sexo ="1";
  if(isset($_POST['sexo'])){
            $sexo ="0"; //Dama
  }          
  $empleado="0";
  if (isset($_POST['empleado'])) { 
     $empleado="1";
  }      
  $activo ="0";    
  $vigente ="0";




  // $query = "Select * from MClientes Where nombre ='$nombre' and apellido ='$apellidos' and Cedula = '$cedula'";
  // $result = mssqlConn::Listados($query);
  // $res = json_decode($result, true);
  // if (sizeof($res)==0){

      $query = "Select UltimoCliente from Empresa Where id=1";
      $result = mssqlConn::Listados($query);
      $codLasCli = json_decode($result, true);
      if (sizeof($codLasCli)>0){
         $codclien=$codLasCli[0]['UltimoCliente'];
         $codclien=$codclien+1;
         $query = "Update Empresa Set UltimoCliente='$codclien' Where id=1";
         $result = mssqlConn::insert($query);
         $fecha=date("Y-m-d");
         $query="Insert Into MClientes (nombre ,apellido ,nombres,Cedula ,telfhabit,codmedico
         ,NACIMIENTO,Historia,celular,direccionH,codpostal,Pais,ESTADO,Eaddress,hCiudad
         ,email,sexo,inactivo,exonerado,fallecido,codclien,CliDesde,telfofic, usuario )  Values('$nombre','$apellidos'"
         . ",'$nombres','$cedula','$telefono','$medico','$dob','$record','$usuariocl','$dirl1','$codpostal'"
         . ",'$pais','$state','$dirl2','$ciudad','$email','$sexo', '$activo','$empleado','$vigente','$codclien'"
         . ",'$fecha','$telhaba2','$usuariocl')";
         $result = mssqlConn::insert($query);
     }
  //}
        
       
header('Location: ../vistas/pacientes/lista.php?idp='.$cedula);
}