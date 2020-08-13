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
require "../../controllers/servicioscontroller.php";
//var_dump($_GET);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
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
  <link rel="stylesheet" href="../../css/radio.css">
 </head>
<body>
 
	
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
	<div class="row container">	
				<form id='frmctlc' action='productos.php' method='get'>

            <div class="control-group col-sm-4">
               <label class="control-label"></label>  
               <div class="form-group">
                    <input type="text" class="form-control" name="valueToSearch" placeholder="Buscar">
               </div>
            </div>

            <div class="control-group col-sm-6">                  
               <label class="control-label"></label> 
             </div>
             <div class="form-group col-sm-2">
                 <label class="control-label"></label>  
                <div class="form-group">
                     <button type="button" id="prodnew" class="btn btn-primary" data-toggle="modal" data-target=".bd-productos-modal-lg">Crear servicio</button>
                </div>
              </div>

				</form>				
	</div>



	<div class="row">	
		<div class="container-fluid">
      <div class"tblcontrol" style="padding-left: 10px; padding-right: 10px;">
        <?php mostrarProductos();?> 
      </div>
			
		</div>
	</div>	



<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/serviciosinvent.js"></script>
<script src="../../js/scriptpdf.js"></script>


</body>
</html>