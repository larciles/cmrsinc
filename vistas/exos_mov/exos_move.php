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

$urlevento="";
if(isset($_POST['codclien'])){
  $cod_clien=$_POST['codclien'];
  $urlevento=$_POST['urlevento'];
}
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
        <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
        <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
        <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
        <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
        <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
        <link rel="stylesheet" href="../../css/jquery-ui.css">
        <link rel="stylesheet" href="../../css/radio.css">
        <link rel="stylesheet" href="../../css/authorization.css">


 


        <!-- <link rel="stylesheet" href="../../css/alert.css"> -->
   
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
       <?php include('detalles.php'); ?>
        
                <div class="container-fluid">
                <div class="tblcontrol" style="padding-left: 10px; padding-right: 10px;">
                <table id='tbldetails' class='table table-bordered table-hover table-condensed table-striped'>  
  <thead class='thead-inverse'>
  <tr>
  <th class='titleatn' >Fecha</th>  
  <!-- <th class='titleatn' >Inicio</th>   -->
  <th class='titleatn' >Ventas</th>  
  <th class='titleatn' >Devolución</th>
  <th class='titleatn' >Ajuste + </th>
  <th class='titleatn' >Ajuste - </th>
  <!-- <th class='titleatn' >Compras</th>  
   <th class='titleatn' >Dev Compras</th>  
  <th class='titleatn' >Anulación</th>   
  
  <th class='titleatn' >Notas Ent.</th>
  <th class='titleatn' >Devolución</th>

  <th class='titleatn' >Existencia </th> -->

  
  </tr>
  </thead>
  <tbody id="move-exos-tbl">
  </tbody>
    <div class="col-md-12 text-center">
    <ul class="pagination" id="paginador"></ul>
  </div>
    <!-- FUNCIONA PERFECTAMENTE  -->
  <div id="otra"></div>  

                 


                </div>        
               </div>
       
      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>
<script src="../../js/jquery.bootpag.min.js"></script>

<script src="../../js/paginator.min.js"></script>

<!-- <script src="../../js/ajustes.js"></script>
<script src="../../js/listajustes.js"></script> -->
<script src="../../js/exos_move.js?v=1.0"></script>


<script>  

 </script>