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
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';
require "../../controllers/atencionconsultasController.php";
//var_dump($_GET);

if(isset($_GET['sltconsultas'])){
  $xcod=$_GET['sltconsultas'];
}

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA - LASER</title>
	<meta charset="UTF-8">
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
  <link rel="stylesheet" href="../../css/blinking.css">  
  <link rel='stylesheet' href='../../css/fullcalendar/fullcalendar.css' />
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/clock/dist/bootstrap-clockpicker.min.css"  type="text/css">
  <link rel="stylesheet" href="../../css/clock/assets/css/github.min.css"  type="text/css">


  <style>
  .fc-agendaDay-view tr {
    height: 40px;
}

th.ui-widget-header {
    font-size: 9pt;
   /* font-family: Verdana, Arial, Sans-Serif;*/
   font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; 
   font-weight: bold;
}
.fc-title{
    color: '#000000';
    font-size: 1.1em;
    
}

.fc-day-number{
 color: 'red'; 
}
/*
  body {
    margin: 40px 10px;
    padding: 0;
    font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
    font-size: 14px;
  }

  #calendar {
    max-width: 900px;
    margin: 0 auto;
  }*/

</style>

 </head>
	<body>
	  <header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
  <?php include('citasmodal.php'); ?> 
    <div class="row"> 
            <div class="container">

             <div class="form-group col-sm-2">
                 <label class="control-label"></label> 
                <div class="form-group">  
                <!-- <button type="submit"  class="btn btn-success  form-control" id="submit" value="Submit">OK</button> -->
                </div>
              </div>  
            <div class="form-group col-sm-2">    
                <label class="control-label"></label> 
                <div class="form-group">  
                    <input id="laser_type" checked data-toggle="toggle" data-on="MLS" data-off="HILT" data-onstyle="success" data-offstyle="primary" type="checkbox">   
                </div>
            </div>  
            <div class="form-group col-sm-2">
               <label class="control-label"></label>  
               <div class="form-group">
                  <div data-tip="Record / Nombre" >
                    <input type="text" class="form-control" name="valueToSearch" id='fcrecord' placeholder="Buscar">
                  </div>
               </div>
            </div>

             <div class="form-group col-sm-2">
                 <label class="control-label"></label>  
                <div class="form-group">
                    <button  type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#exampleModal" data-whatever="">Citar</button>
                </div>
              </div>
              <div class="form-group col-sm-2">
                 <label class="control-label"></label> 
                <div class="form-group">  
                <!-- <button type="submit"  class="btn btn-success  form-control" id="submit" value="Submit">OK</button> -->
                </div>
              </div>

              <div class="form-group col-sm-2" style="margin-bottom: 20px;padding-bottom: 15px;">
                 <div style='width: 112px; height: 62px;' class='clearfix' >
                   
                 </div>
              </div>
          </div>  
    </div>         

  <div id='resultado'></div>

  <div class="container-fluid">
      <div id='calendar'></div>
  </div>


	<script src='../../js/fullcalendar/jquery.min.js'></script>
  <script src="../../js/bootstrap.min.js"></script> 
 	<script src='../../js/fullcalendar/moment.min.js'></script>
	<script src='../../js/fullcalendar/fullcalendar.js'></script>
  <script src='../../js/fullcalendar/scriptfc.js'></script>
  <script src="../../js/bootstrap-toggle.min.js"></script>
  <script src="../../js/fullcalendar/laserterapia.js"></script>
  <script type="text/javascript" src="../../js/clock/dist/bootstrap-clockpicker.min.js"></script>
  <script src="../../js/bootstrap-datepicker.min.js"></script>


</body>
</html>

