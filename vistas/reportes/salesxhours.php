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

//var_dump($_GET);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css"> 
  <link rel="stylesheet" href="../../css/styles.css" >



<meta http-equiv="Expires" content="0">
 
<meta http-equiv="Last-Modified" content="0">
 
<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
 
<meta http-equiv="Pragma" content="no-cache">

<style>
.democlass {
  background: #00ffbf;
  font-weight: bold;
  font-size: 18px;
}

.triste {
  background: #bcc0ca;
  font-weight: bold;
  
}
</style>

 </head>
<body>
<header>
  <div class="container-fluid ">
    <?php include '../layouts/header.php';?>
  </div>
</header>
<div id="loading-screen" style="display:none" >
    <img src="../../img/lg.comet-spinner.gif">
</div>
<div class="row">
    <div class="container">

       <div class="col-sm-1">
      </div>
      <div class="col-sm-2">
      </div>
      <div class="col-sm-2">    
        <label class="control-label"></label> 
        <div class="form-group">  
              <!-- <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" value=""> -->
                <input class="datepicker form-control" id="sd" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy" name="sd">
            </div>
      </div>
      <!-- <input id="monday" type="hidden"  name="monday">
      <div class="col-sm-2">    
        <label class="control-label"></label> 
        <div class="form-group">  
              <input class="datepicker form-control" id="ed" placeholder="Fecha final"  name="ed" value="">
            </div>
      </div>



 -->
      <div class="form-group col-sm-2">
            <label class="control-label"></label> 
            <div class="form-group">  
                  <button type="button"  class="btn btn-success form-control" id="submit" value="Submit">OK</button>
            </div>
        </div>  
    </div>
</div>
 
<div class="container-fluid" id="charts">
 

    <div class="row">
         <div class="form-group col-sm-4">
         </div>
    </div>

    <div class="row">
          <div class="container">
        

      <div  style="margin-left: auto; margin-right: auto;" >
        <div id="chart_div" style="margin-left: auto; margin-right: auto;"></div>
      </div>
       
          </div>
    </div>



</div>
</body>
<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../js/loader.js"></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/salesxhours.js?ver=20190405"></script>
</html>