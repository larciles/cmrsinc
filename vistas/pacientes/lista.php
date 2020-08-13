<?php
 error_reporting(E_ERROR | E_WARNING | E_PARSE);
 set_time_limit(0);
 session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
   $user=$_SESSION['username'];
}
 require_once '../../models/user_model.inc.php';
 require "../../controllers/pacientesControllers.php";
 //var_dump($_GET);

 if (version_compare(phpversion(), '5.4.0', '<')) {
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();   
    }
 }


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Pacientes</title>
	<meta charset="UTF-8">
    
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	
<!-- 	<link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
	<link rel="stylesheet" href="../../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/ocestilos.css">
    <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
    <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css"> -->

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
        <link rel="stylesheet" href="../../css/alert.css">
        <style type="text/css">
  			<?php include("../../css/personalizado.php") ?>
 		</style>
    
</head>
<body>
	<header>
		<div class="container-fluid1">
			<?php include '../layouts/header.php';?>
			<!-- <h1>Pacientes</h1> -->
		</div>
	</header>
	<!-- MODAL STARTS CONSULTA RECORD -->
	<?php include('recordconsultasmodal.php'); ?>	
	<!-- MODAL ENDS CONSULTA RECORD -->
	<!-- modalnewappmt.php -->
	<?php include('modalnewappmt.php'); ?>		
	<!-- modalnewappmt.php  -->
	<div class="container-fluid bootstrap-iso personalizado">
		<section class="main">
			<div class="row"> 
			<article>

				<form action='lista.php' method='post'>
					<div class="control-group col-md-2">
					 </div>
					<div class="form-group col-md-2">
						<label class="control-label"></label>  
						<input type="text" class="form-control" name="valueToSearch" placeholder="Buscar">
			 	    </div>

			 	    <div class="form-group col-md-2">
			 	    	<label class="control-label">Líneas por página</label>  
						<input type="text" class="form-control" name="linexpage" id="linexpage"  value=<?php  if(isset($_POST['linexpage']) ){echo $_POST['linexpage'] ;}?> >
			 	    </div>

					<div class="control-group col-md-2">
					    <label class="control-label"></label>  	
						<button class="btn btn-primary btn-default btn-block" type="submit" id="submit" value="Submit">OK</button>
					</div>	


					<div class="control-group col-md-2">
					    <label class="control-label"></label>  	
						<!-- <button class="btn btn-primary btn-default btn-block" type="submit" id="submit" value="Submit">OK</button> -->
						<a href="create.php" class="btn btn-primary btn-default btn-block"><span class="glyphicon glyphicon-plus" aria-label="Left Align"></span>  Paciente</a>
					</div>	
									
				</form>		
<!-- glyphicon glyphicon-plus
				<?php #include('recordconsultasmodal.php'); ?>
				<a href="recordconsultasmodal.php#recordConsModal" class="btn btn-default btn-sm" data-toggle="modal">Edit</a>
 -->

			</article>
			</div>
			<div class="container-fluid table-responsive">
      			<div class="container-fluid ">
					<aside class="col-md-12 form-group">
					<?php mostrarPacientes();?>					
					</aside>
				</div>
			</div>			
		</section>
	</div>
	<script type="text/javascript" src="../../js/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="../../js/bootstrap.min.js"></script>	
	<script type="text/javascript" src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>		
    <script type="text/javascript" src="../../js/formden.js"></script>
    <script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../../js/pacienteslist.js"></script>
    <script type="text/javascript" src="../../js/jquery.redirect.js"></script>
    <script type="text/javascript" src="../../js/pacientemodalappmt.js"></script>    
    <script type="text/javascript" src="../../js/bootstrap-toggle.min.js"></script>
</body>
</html>