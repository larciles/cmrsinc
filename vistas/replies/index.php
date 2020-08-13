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
 require "../../controllers/repliesController.php";
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
	<title>SMS Replies</title>
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
			<h1>SMS REPLIES</h1>
		</div>
	</header>
	<div class="container-fluid bootstrap-iso personalizado">
	<div class="row">
		<form action='index.php' method='post'>
			<div class="col-sm-2">
				<div class="form-group">					
					    <input type="text" class="form-control" id="from" placeholder="MM/DD/YYYY" name="from" value=<?php if(isset($_POST["from"]) ){echo $_POST["from"];}?>>
                 </div>
			</div>	

			<div class="col-sm-2">
				<div class="form-group">
						<input type="text" class="form-control" id="to" placeholder="MM/DD/YYYY" name="to" value=<?php if(isset($_POST["to"]) ){echo $_POST["to"];}?>>
                </div>				
			</div>	

			<div class="col-sm-2">				
				<div class="checkbox form-group">
                    	<div class="custom-control custom-checkbox">
    						<label>
      							<input type="checkbox" class="custom-control-input" id="chkstop" name="chkstop" <?php if(isset($_POST['chkstop'])){ echo 'checked';} ?> >Excluir Stop
    						</label>
  						</div>
                </div>
			</div>	

			<div class="col-sm-2">
				<div class="form-group">	
					<button type="submit" id="submit" value="Submit" class="btn btn-success  form-control" >OK</button>
				</div>									
			</div>	

			<div class="col-sm-2">
				<div class="form-group"> 					
					<button type="button" class="btn btn-primary  form-control btn-sm" id="pdfprint">
          				<span class="glyphicon glyphicon-print"></span> Imprimir
        			</button>        		
				</div>
			</div>	

			<!-- Default unchecked -->

		</form>		
	</div>
	</div>	
	<div class="container-fluid bootstrap-iso personalizado">
		
			
			<div class="table-responsive">
      			
					
					<?php mostrarSMS();?>					
					
				
			</div>			
		
	</div>
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>	
	
	<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>		
    <script type="text/javascript" src="../../js/formden.js"></script>
    
    <script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
    <script src="../../js/smsreplies.js"></script>
</body>
</html>