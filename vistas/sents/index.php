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
 require "../../controllers/sentsController.php";
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
	<title>SMS Enviados</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
<!--	<link rel="stylesheet" href="../../css/datepicker.css">-->
	<link rel="stylesheet" href="../../css/ocestilos.css">
       
        <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
        <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
	    <style type="text/css">
	    <?php include("../../css/personalizado.php") ?>
	    </style>
        
</head>
<body>
	<header>
		<div class="container-fluid1">
			<?php include '../layouts/header.php';?>
			<h1>SMS Enviados</h1>
		</div>
	</header>
	<div class="container-fluid bootstrap-iso ">
		<section class="main row personalizado">
			<article class="col-md-2">
				<form action='index.php' method='post'>
					<div class="form-group">					
						<!-- <label for="desde">Desde</label> -->
						<!--<input type="text" class="datepicker form-control" id="from" name="from" placeholder="Desde" <?php #if(isset($_POST['from'])){ echo "value=".$_POST['from']; } ?> >-->
					        <input type="text" class="form-control" id="from" placeholder="MM/DD/YYYY" name="from" value=<?php if(isset($_POST["from"]) ){echo $_POST["from"];}?>>
                    </div>
					<div class="form-group">			
					    <!-- <label for="hasta">Hasta</label>	 -->	
						<!--<input type="text" class="datepicker form-control" id="to" name="to" placeholder="Desde" <?php #if(isset($_POST['to'])){ echo "value=".$_POST['to']; } ?> >-->
					        <input type="text" class="form-control" id="to" placeholder="MM/DD/YYYY" name="to" value=<?php if(isset($_POST["to"]) ){echo $_POST["to"];}?>>
                    </div>
           <!--          <div class="form-group">
                    	<div class="checkbox">
    						<label>
      							<input type="checkbox" id="chkstop" name="chkstop" <?php #if(isset($_POST['chkstop'])){ echo 'checked';} ?> >Excluir Stop
    						</label>
  						</div>
                    </div> -->
					<div class="form-group">	
					<button type="submit" id="submit" value="Submit">OK</button>
					</div>	

					<div class="form-group">	
					<label for="hasta">Enviados # </label>
					<div id="enviados" float="right"></div>	
					</div>	
					
				</form>		
<!-- 				<div class="form-group"> 					
					<button type="button" class="btn btn-primary btn-sm" id="pdfprint">
          				<span class="glyphicon glyphicon-print"></span> Imprimir
        			</button>        		
				</div>	 -->
		
			</article>
			<div class="table-responsive">
      			<div class="container">
					<aside class="col-md-10 form-group">
					<?php mostrarSMS();?>					
					</aside>
				</div>
			</div>			
		</section>
	</div>
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>	
	<!--<script src="../../js/datepicker.js"></script>-->
	<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>		
        <script type="text/javascript" src="../../js/formden.js"></script>
        <!-- Include Date Range Picker -->
        <script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
        <script src="../../js/smsreplies.js"></script>
		<script src="../../js/smssents.js"></script>   
</body>
</html>