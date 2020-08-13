<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once '../db/mssqlconn.php';
$dbmsql = mssqlConn::getConnection();
if(isset($_POST['apellidos'])){

if ($_SESSION['controlcita']=='1') {
   $usuario=$_SESSION['username'];
} else {
   $usuario=randoUser();
}

    
  $nombre = $_POST['nombre'];
  $apellidos = $_POST['apellidos'];
  $nombres = $_POST['apellidos'].' '.$_POST['nombre'];
  $cedula =  $_POST['cedula'];		 	  
  $telefono = $_POST['telefono'];	
  $usuariocl = $_POST['usuariocl'];  
  $recordn = $_POST['recordn'];  
   
  $sexo ="0";
  $empleado="0";
  $activo ="0";    
  $vigente ="0";
 

  $query = "Select UltimoCliente from Empresa Where id=1";
  $result = mssqlConn::Listados($query);
  $codLasCli = json_decode($result, true);
  if (sizeof($codLasCli)>0){
    $codclien=$codLasCli[0]['UltimoCliente'];
    $codclien=$codclien+1;
    $query = "Update Empresa Set UltimoCliente='$codclien' Where id=1";
    $result = mssqlConn::insert($query);
    $fecha=date("Y-m-d");
    $query="Insert Into MClientes (nombre ,apellido ,nombres,Cedula ,telfhabit,sexo,inactivo,exonerado,fallecido,codclien,CliDesde,celular,usuario,Historia ,direccionH)  Values('$nombre','$apellidos','$nombres','$cedula','$telefono','$sexo', '$activo','$empleado','$vigente','$codclien','$fecha','$usuario','$usuario','$recordn','PR' )";
    $result = mssqlConn::insert($query);
  }
}
       


function randoUser(){
  $query = "Select * from loginpass where controlcita=1";
  $result = mssqlConn::Listados($query);
  $usuario = json_decode($result, true);
  $max = sizeof($usuario);
  $min =0;

     if (function_exists('random_int')):        
       return  $usuario[random_int($min, $max)]['login'] ; // more secure
    elseif (function_exists('mt_rand')):
       return $usuario[mt_rand($min, $max)]['login'] ; // faster
    endif;
    return $usuario[rand($min, $max)]['login'] ; // old
}



function id()
{
 // add limit
$id_length = 20;

// add any character / digit
$alfa = "abcdefghijklmnopqrstuvwxyz1234567890";
$token = "";
for($i = 1; $i < $id_length; $i ++) {

  // generate randomly within given character/digits
  @$token .= $alfa[rand(1, strlen($alfa))];

}    
return $token;
}