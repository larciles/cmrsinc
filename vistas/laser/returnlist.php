<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

//var_dump($_GET);

 ?>
   <html>  
      <head>            
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
        <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
        <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
        <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
        <link rel="stylesheet" href="../../css/jquery-ui.css">
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
    <div class="row"> 
          <div class="col-sm-7">  
             <div class="pagination"> </div>
        </div>
        <div class="control-group col-sm-3">
               <label class="control-label"></label>  
               <div class="form-group">
                <!-- data-tip="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" -->
                  <div>
                    <input type="text" class="form-control" id ="search" name="search" placeholder="Buscar">
                  </div>
               </div>
        </div>
        <div class="col-sm-2"> 
           <label class="control-label"></label>  
           <div class="form-group">
              <input class="datepicker form-control" data-date-format="mm/dd/yyyy">
           </div>
        </div>
   </div>
   <div class="tab-content">
        <table id='tblLista' class='table table-bordered table-hover table-condensed table-striped'>  
          <thead class='thead-inverse'>
          <tr>
          <th class='titleatn' >Factura</th>
          <th class='titleatn' >Paciente</th>               
          <th class='titleatn' >Fecha</th>
          <th class='titleatn' >Status</th> 
          <th class='titleatn' >Monto</th>
          <th class='titleatn' >Consultar</th>
          <!-- <th class='titleatn' >Editar</th> -->
          <th class='titleatn' >Anular</th>
          <!-- <th class='titleatn' >Devolver</th>          -->
          </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
   </div>
        <!-- tabs -->
      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script type="text/javascript" src="../../js/loader.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>

<script src="../../js/jquery.bootpag.min.js"></script>

<!-- <script src="../../js/consultas.js"></script> -->
<script src="../../js/returnlistaconsultas.js"></script>
 <script>  

 </script>