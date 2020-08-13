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

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <!-- 	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/> -->
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/bootstrap-select.min.css">	
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <!-- <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet"> -->
  <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
   <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <!--  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  
 
 </head>
<body>
 
	<!-- <div id="wait" style="display:none;width:69px;height:89px;position:absolute;top:50%;left:50%;padding:2px;"><img src='../../img/demo_wait.gif' width="64" height="64" /><br></div> -->
  <header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>



  <ul class="nav nav-tabs" id="tabs">
      <li class="active"><a data-toggle="tab" href="#home">Ventas por Item</a></li>
      <li><a data-toggle="tab" href="#menu1">Detallado</a></li>              
  </ul>
 
  <div class="tab-content">  
    <div id="home" class="tab-pane fade in active">
      <div class="row" style="padding-left: 10px;"> 
    	      <div class="col-sm-2">		
    			  	<label class="control-label"></label>	
    			 	 		<div class="form-group">	
                   	<input type="text" class="form-control" id="fechai" placeholder="MM/DD/YYYY"   name="fechai" >                    
                </div>
    				</div>	

            <div class="col-sm-2">    
                <label class="control-label"></label> 
                <div class="form-group">  
                    <input type="text" class="form-control" id="fechaf" placeholder="MM/DD/YYYY"   name="fechaf" >
                    
                </div>
            </div>  
            <div class="col-sm-2"> 
                <label class="control-label"></label> 
                <div class="form-group">  
                  <select class="form-control" id="sltprod">
                      <option value=""></option>
                  </select>
                </div>
            </div> 

            <div class="form-group col-sm-2">
                <label class="control-label"></label> 
                <div class="form-group">  
                  <button type="button"  class="btn btn-primary form-control" id="submit" value="Submit">OK</button>
                </div>
             </div>
             <div class="pagination" style=" float: right; margin-top: 1px; padding-right: 20px;"> </div>      
	    </div>

      <div class="row">       
        <p  id="titulo" align="center" style="font-size:18px;" >Ventas por Item</p>
      </div> 

	    <div class="row"  style= 'padding-left: 20px;padding-right: 18px; '>	
            
        <table id='tblLista' class='table table-bordered table-hover table-condensed table-striped' >  
            <thead class='thead-inverse'>
                <tr>
                      <th>#</th>
                      <th class='titleatn' >Paciente</th>                      
                      <th class='titleatn' >Teléfono</th>               
                      <th class='titleatn' >Cantidad</th>                      
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>			
		 </div> 
	</div>
  <div id="menu1" class="tab-pane fade">
      <?php include 'detalleitems.php';?>    
  </div>    

</div>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js"></script>
<script src="../../js/colResizable-1.6.min.js"></script>

<script src="../../js/scriptpdf.js"></script>

<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js"></script>
<script src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/jquery.bootpag.min.js"></script>
<script src="../../js/ventasxitem.js"></script>
<!-- <script src="../../js/asistidosestadisticas.js"></script> -->

</body>
</html>