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
require "../../controllers/atencionController.php";
//var_dump($_GET);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <!-- 	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/> -->
  

  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
	<link rel="stylesheet" href="../../css/bootstrap-select.min.css">	
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <!-- <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet"> -->
  <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
   <link rel="stylesheet" href="../../css/estiloscontrolc.css?v=111119" />
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
	<ul class="nav nav-tabs" id="list">
            <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
            <li><a data-toggle="tab" href="#menu1" >List</a></li>
    </ul>
	
   <!-- MODAL STARTS CITAS  -->
   <?php include('citasmodal.php'); ?> 
   <?php include('alertamodal.php'); ?> 
   <?php include('clientesmodal.php'); ?> 
  <!-- MODAL ENDS CITAS  -->
  <div class="tab-content">
  <div id="home" class="tab-pane fade in active">
	<div class="row">	
			
				<form id='frmctlc' action='atencion.php' method='get'>
            
          <input  id="xconfirm" type="hidden"  name="xconfirm">
          <input  id="xasist" type="hidden"  name="xasist">
          <input  id="xnuevos" type="hidden"  name="xnuevos">
          <input  id="xcontrol" type="hidden"  name="xcontrol">
          <input  id="xarrived" type="hidden"  name="xarrived">
		  
		  

          <div class="container">
    				<div class="col-sm-2">		
    						<label class="control-label"></label>	
    						<div class="form-group">	
                   	<input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" value=<?php if(isset($_GET['fecha'])){echo $_GET['fecha']; ;}?> >
                </div>
    				</div>	
    				<div class="control-group col-sm-2">
    					 <label class="control-label"></label>	
    					 <div class="form-group">
                  <div data-tip="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" >
    						    <input type="text" class="form-control" name="valueToSearch" placeholder="Buscar">
    			 	      </div>
               </div>
    				</div>

    				<div class="control-group col-sm-2">			       		   
    		       <label class="control-label"></label>  
    		       <!-- <option disabled selected="selected">Médico</option>   -->
    		       <input  id="sltconsultashd" type="hidden"  name="sltconsultashd" value=<?php if(isset($_GET['sltconsultas'])){echo $_GET['sltconsultas'];}?>>               
    		       <select id="sltconsultas" name="sltconsultas" class="form-control" >
    		           	<option value="" selected ></option>             
    		       </select>
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
                  <button type="submit"  class="btn btn-success  form-control" id="submit" value="Submit">OK</button>
                </div>
              </div>

              <div class="form-group col-sm-2" style="margin-bottom: 20px;padding-bottom: 15px;">
                 <div style='width: 112px; height: 62px;' class='clearfix' >
                   <div id='piechart' style='width: 225px; height: 125px;' class='clearfix'></div>
                 </div>
              </div>
          </div>	
				</form>				
	</div>

  <div class="row"> 
  <!-- <div id='piechart' style='width: 225px; height: 125px;'></div> -->
  </div>

	<div class="row">	
<!-- 
 <button type="button" class="btn btn-primary">Citados <span class="badge">7</span></button>
  <button type="button" class="btn btn-success">Confirmados <span class="badge">3</span></button>    
  <button type="button" class="btn btn-danger">Asistidos <span class="badge">5</span></button>  

 -->		<div class="container-fluid">
      <div class"tblcontrol" style="padding-left: 10px; padding-right: 10px;">
        <?php mostrarCitas();?>
      </div>
			
		</div>
	</div>	
</div>	


        <div id="menu1" class="tab-pane fade">
            <div class="container-fluid">
                <div class="row">
                    <form> 
                    <div class"tblcontrol" style="padding-left: 10px; padding-right: 10px;">
              <div class="control-group col-sm-2">
                  <label class="control-label"></label> 
                  <div class="form-group">
                            <div  data-toggle="tooltip" data-placement="top" title="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" >
                        <input type="text" id="patient" class="form-control patient" placeholder="Id / Record / Nombre">
                            </div>

                    </div>
                </div>
                <div id="divpatientassoc" class="form-group col-sm-3">
                               <label class="control-label">Paciente</label>    
                                 <input  id="patientassochd" type="hidden" name="patientassochd">
                                 <select id="patientassoc" name="patientassoc" class="form-control" >
                                     <option value="" selected ></option>             
                                 </select>
                            </div>

                            <div class="form-group col-sm-2">
                        <label class="control-label"></label> 
                        <div class="form-group">  
                            <button type="button"  class="btn btn-success form-control" id="okRec" value="Submit">Buscar</button>
                        </div>
                      </div>

                    </div>               
          </form>
                </div>   
                <div class="row">  
                    <?php include('pacientesrecord.php');?>
                 </div>   
            </div> 
        </div>
</div>
<!-- <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script> -->
<script src="../../js/jquery-3.1.1.min.js"></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>	 -->
<script src="../../js/bootstrap.min.js"></script> 
<!-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> -->
<script type="text/javascript" src="../../js/loader.js"></script>

<script src="../../js/colResizable-1.6.min.js"></script>

<script src="../../js/pacientesrecord.js"></script>
<script src="../../js/atencion.js"></script>
<script src="../../js/scriptpdf.js"></script>

<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<!-- <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script> -->
<script src="../../js/bootstrap-toggle.min.js"></script>
<!-- <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->
<script src="../../js/jquery-ui.js"></script>

<script src="../../js/jquery.confirm.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.3/js/bootstrap-select.min.js"></script>
<script src="../../js/jquery.redirect.js"></script>
</body>
</html>