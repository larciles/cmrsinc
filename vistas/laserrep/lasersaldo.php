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
		    <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
        <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
        <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
        <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
        <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
        <link rel="stylesheet" href="../../css/jquery-ui.css">

         <style type="text/css">
           #div1.linea{
float:left;
 
}
#div2{
float:left;
}
#div33{
clear:both;
background:#120FFF;
}


    </style>


      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
        <div class="container">

    <div class="row"> 
          <div class="col-sm-7">  
             <div class="pagination"> </div>
        </div>
                <div class="col-sm-2"> 
           <label class="control-label"></label>  
           <div class="form-group">
              <input id="startdate" class="datepicker form-control" data-date-format="mm/dd/yyyy" placeholder="Fecha inicial">
           </div>
        </div>
        <div class="col-sm-2"> 
           <label class="control-label"></label>  
           <div class="form-group">
              <input id="finishdate" class="datepicker form-control" data-date-format="mm/dd/yyyy"  placeholder="Fecha final">
           </div>
        </div>
        <div class="control-group col-sm-3">
               <label class="control-label"></label>  
               <div class="form-group">
                <!-- data-tip="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" -->
                  <div>
                    <input type="text" class="form-control" id ="search" name="search" placeholder="Buscar paciente">
                  </div>
               </div>
        </div>

   </div>
<div class="row totales" style="display: none;">
      <div class="col-sm-2"> 
           <label class="control-label">Laser vendidos</label>  
           <div class="form-group">
              <p id="lpack"></p>
           </div>
      </div>
      <div class="col-sm-2"> 
           <label class="control-label">Laser aplicados</label>  
           <div class="form-group">
              <p id="lapli"></p>
           </div>
      </div>
      <div class="col-sm-2"> 
           <label class="control-label">Laser pendientes</label>  
           <div class="form-group">
              <p id="lpend"></p>
           </div>
      </div>

</div>
<table id='tblLista' class='table table-bordered table-hover table-condensed table-striped'>  
  <thead class='thead-inverse'>
  <tr>
  
  <th class='titleatn' >Paciente</th>               
  <th class='titleatn' >Lasers Pack</th>  
  <th class='titleatn' >Lasers Aplicado</th>
  <th class='titleatn' >Lasers Restantes</th>  
  <th class='titleatn' >Record #</th>  
  </tr>
  </thead>
  <tbody>
  </tbody>
</table>

              
    
  </div>
  

    <footer></footer>
      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js"></script>
<script src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>
<script src="../../js/jquery.bootpag.min.js"></script>


<script src="../../js/lasersaldo.js"></script>

 <script>  

 </script>