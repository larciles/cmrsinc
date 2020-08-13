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
    $codperfil=$_SESSION['codperfil'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';
require "../../controllers/productoscontroller.php";
//var_dump($_GET);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
	<meta charset="UTF-8">
	<!-- <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/bootstrap-select.min.css">	
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" /> -->

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css">
 </head>
<body>
 
	<!-- <div id="wait" style="display:none;width:69px;height:89px;position:absolute;top:50%;left:50%;padding:2px;"><img src='../../img/demo_wait.gif' width="64" height="64" /><br></div> -->
   <input  id="codperfil"  type="hidden"  name="codperfil" value=<?php echo $codperfil; ?>>
  <header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
   <!-- MODAL STARTS CITAS  -->
   <?php include('modalproductos.php'); ?> 
 <!--   <?php ///include('alertamodal.php'); ?> 
   <?php //include('clientesmodal.php'); ?>  -->
  <!-- MODAL ENDS CITAS  -->
	<div class="row">	
			
				<form id='frmctlc' action='productos.php' method='get'>
          <div class="container">
<!--     				<div class="col-sm-2">		
    						<label class="control-label"></label>	
    						<div class="form-group">	
                   	<input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" value=<?php //if(isset($_GET['fecha'])){echo $_GET['fecha']; ;}?> >
                </div>
    				</div>	 -->
    				<div class="control-group col-sm-2">
    					 <label class="control-label"></label>	
    					 <div class="form-group">
    						    <input type="text" class="form-control" name="valueToSearch" placeholder="Buscar">
    			 	   </div>
    				</div>

    				<div class="control-group col-sm-2">			       		   
    		       <label class="control-label"></label>  
   
<!--     		       <input  id="sltconsultashd" type="hidden"  name="sltconsultashd" value=<?php// if(isset($_GET['sltconsultas'])){echo $_GET['sltconsultas'];}?>>
    		       <select id="sltconsultas" name="sltconsultas" class="form-control" >
    		           	<option value="" selected ></option>             
    		       </select> -->
    				 </div>
             <div class="form-group col-sm-2">
                 <label class="control-label"></label>  
                <div class="form-group">
                <button type="button" id="prodnew" class="btn btn-primary" data-toggle="modal" data-target=".bd-productos-modal-lg">Nuevo</button>
                </div>
              </div>
<!--               <div class="form-group col-sm-2">
                 <label class="control-label"></label> 
                <div class="form-group">  
                  <button type="submit"  class="btn btn-primary form-control" id="submit" value="Submit">OK</button>
                </div>
              </div> -->

              <div class="form-group col-sm-2">
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
        <?php mostrarProductos();?> 
      </div>
			
		</div>
	</div>	



<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script type="text/javascript" src="../../js/loader.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/productos.js"></script>
<script src="../../js/scriptpdf.js"></script>


</body>
</html>