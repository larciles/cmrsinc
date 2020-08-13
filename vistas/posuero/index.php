<?php 
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
require "../../controllers/po_suerocontroller.php";
if(isset($_POST["meses"])){
	//var_dump($_POST);
}
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>CMA WEB</title>
	<link rel="stylesheet" href="../../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/ocestilos.css">	
    <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
    <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
</head>
<body>
	<header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
			<!-- <h1>Nuevos Pedidos</h1> -->

		</div>
	</header>
	<div class="container-fluid bootstrap-iso ">
		<section class="main row">
			<article class="col-md-2">

				<form action='index.php' method='post'>
					<div class="form-group">
						<label for="">Meses</label>
						<input type="text" id="meses" name="meses" class="form-control" value= <?php if(isset($_POST["meses"])){ echo $_POST["meses"];} else{ echo "3";}?>>
					</div>
					
					<div class="form-group">
						<label for="">Criterio #1</label>
						<input type="text" id="criterio1" name="criterio1" class="form-control" value=<?php if(isset($_POST["criterio1"])){ echo $_POST["criterio1"];} else{ echo "2.5";}?>>
					</div>
					
					<div class="form-group">
						<label for="">Criterio #2</label>
						<input type="text" id="criterio2" name="criterio2" class="form-control" value=<?php if(isset($_POST["criterio2"])){ echo $_POST["criterio2"];} else{ echo "2";}?>>
					</div>
					

					<div class="form-group">
						<label for="checkbox">
							A partir de otra fecha <input type="checkbox" name="cbfecha" id="cbfecha" <?php if(isset($_POST["cbfecha"])){echo "checked";}?> >
						</label>
					    <!--<input type="text" class="datepicker form-control" <?php if(!isset($_POST["cbfecha"])){echo "disabled";}?>  name="fecha" value=<?php if(isset($_POST["fecha"]) ){echo $_POST["fecha"];}?>>-->
                                                <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY" <?php if(!isset($_POST["cbfecha"])){echo "disabled";}?>  name="fecha" value=<?php if(isset($_POST["fecha"]) ){echo $_POST["fecha"];}?>>
                    </div>	

                    <hr> 
                    <div class="form-group"> 
                    <STRONG>Cancelar P.O </STRONG>       
                    </div>	             
                    	 <div class="row"> 
					    	<div class="form-group"> 

								<!-- <label for="">Cancelar P.O</label> -->
								<div class="col-md-7">
									<?php selectCancelarPO(); ?>
					 			</div>	

					 	 		<div class="col-md-4">
					 				<button type="button" class="btn cancela btn-danger">Cancelar</button>
								</div>		 	
							</div>					
						 </div>		 			
					<hr>

<!-- 					<div class="form-group"> 
						<label for="">Cancelar P.O</label>
						<div>
							<?php //selectCancelarPO(); ?>
					 	</div>	
					</div>	
 -->




<!-- 
					<div class="form-group"> 
						<label for="">Incorporar P.O al inventario</label>
						<div>
							<?php //selectIncorporarPO(); ?>
					 	</div>	
					</div>	

					<div class="form-group"> 
						<label for="">Desincorporar P.O del inventario</label>
						<div>
							<?php //selectDesincorporarPO(); ?>
					 	</div>	
					</div>	 -->
					<div class="form-group">	
					<button type="submit" id="submit" value="Submit"  class="btn btn-success">OK</button>
					</div>	

					<div class="form-group"> 
						<label for="">Convertir pedido a P.O</label>
						<div>
							<input type="text" id="newpo" name="newpo" class="form-control" placeholder="Nuevo # de P.O">
							<button type="button" id="btn-newpo" class="btn btn-primary">Ok P.O de pedido  </button>
					 	</div>	
					</div>	

					<div class="form-group"> 
						<label for="">P.O Manual</label>
						<div class='bmanual'>
							<input type="text" id="manualpo" name="manualpo" class="form-control" placeholder="Nuevo # P.O Manual">								
					 	</div>	
					 	<div class='bmanual'>
					 		<button type="button" id="btn-manualpo" class="btn btn-primary">Ok P.O Manual</button>
					 	</div>					 	
					</div>	

					<div class="form-group"> 						
						<div class=''>
					         <?php selectExcelPO()?>		
					 	</div>						 	
					</div>	

					<div class="form-group"> 					
						<button type="button" class="btn btn-primary btn-sm" id="pdfprint">
          				<span class="glyphicon glyphicon-print"></span> Imprimir
        				</button>
        			    <button type="button" class="btn btn-primary btn-sm"  id="btn-excel">
          				<!-- <span class="glyphicon glyphicon-export"></span> Excel -->
          				<span class=""><i class="fa fa-file-excel-o" aria-hidden="true"></i></span> Exportar
        				</button>	
					</div>	


					
				</form>				
			</article>
			<div class="container-fluid col-md-10 table-responsive">
      			<!-- <div class="container-fluid"> -->
					<!-- <aside class="col-md-10 form-group"> -->
						<?php mostrarPO(); ?>
					<!-- </aside> -->
				<!-- </div> -->
			</div>			
		</section>
	</div>

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>	
<script src="../../js/posuero.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script>
	window.onpageshow = function(evt) {
    // If persisted then it is in the page cache, force a reload of the page.
    if (evt.persisted) {
        document.body.style.display = "none";
        location.reload();
    }
};

$(document).ready(function(){

	})
</script>
</body>
</html>