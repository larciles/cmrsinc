<?php
require_once '../db/mssqlconn.php';
$dbmsql = mssqlConn::getConnection();

$cambioUsr=false;

if (isset($_POST['operacion'])) {

      $urlorigen="";
      if (isset($_POST['urlorigen'])) {
         $urlorigen = $_POST['urlorigen'];
      }
     
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


      $telefono2 = $_POST['phone2'];  
      $telefono2 =  str_replace("(","",$telefono2);
      $telefono2 =  str_replace(")","-",$telefono2);
      $telefono2 =  str_replace(" ","",$telefono2);

          if ( $state =="" || is_null($state) ) {
              $state =0;
          }
          


          if(isset($_POST['usuariocl'])){
             $usuariocl =  $_POST['usuariocl'];
             $cambioUsr=true;
          }
         

         

          $sexo ="1";
          if(isset($_POST['sexo'])){
            $sexo ="0"; //Dama
          }
          
          $activo ="1";
          if(isset( $_POST['activo'])){
              $activo ="0";
          }
          
           $vigente ="1";
          if( isset($_POST['vigente'])){
              $vigente ="0";
          }
          
          $empleado="0";
          if (isset($_POST['empleado'])) { 
              $empleado="1";
          }


  if ($_POST['operacion']="edit") {
          $codclien = $_POST['codecl'];
     if ($nombre!=='' && $apellidos!=='') {

           if ( $cambioUsr) {
             $query ="Update MClientes set nombre = '$nombre',apellido = '$apellidos',nombres = '$nombres'
                ,Cedula =  '$cedula' ,telfhabit = '$telefono',codmedico = '$medico',NACIMIENTO = '$dob'
                ,Historia = '$record' ,celular = '$usuariocl',direccionH = '$dirl1' ,codpostal = '$codpostal'
                ,Pais = '$pais' ,ESTADO = '$state' ,Eaddress = '$dirl2' ,hCiudad = '$ciudad'
                ,email = '$email',sexo = '$sexo' ,inactivo = '$activo' ,exonerado = '$empleado' ,fallecido = '$vigente', telfofic='$telefono2'  Where codclien='$codclien'  ";
            
           } else {
            $query ="Update MClientes set nombre = '$nombre',apellido = '$apellidos',nombres = '$nombres'
                ,Cedula =  '$cedula' ,telfhabit = '$telefono',codmedico = '$medico',NACIMIENTO = '$dob'
                ,Historia = '$record' ,direccionH = '$dirl1' ,codpostal = '$codpostal'
                ,Pais = '$pais' ,ESTADO = '$state' ,Eaddress = '$dirl2' ,hCiudad = '$ciudad'
                ,email = '$email',sexo = '$sexo' ,inactivo = '$activo' ,exonerado = '$empleado' ,fallecido = '$vigente', telfofic='$telefono2'  Where codclien='$codclien'  ";
           }
             $result = mssqlConn::insert($query);
      }
  }
  if ($urlorigen=="") {
    header('Location: ../vistas/pacientes/lista.php');
  }else{
    header('Location: ../vistas/atencion/atencion.php');
  }
  
}
?>