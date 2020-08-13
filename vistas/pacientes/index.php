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
 ?>


<!DOCTYPE html>
<html>
<head>
	<title></title>
 	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/ocestilos.css">
    <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
    <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css"> 
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
   
</head>
<body>
		<header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
			<!-- <h1>Nuevos Pedidos</h1> -->

		</div>
	</header>
	<div class="container">
		<form  name="formcl" id="formcl" method="post"  autocomplete="off" action="">
			<div class="row">
			  <div class="col-sm-6">
			  	<label for="apellidos" class="col-4 col-form-label">Apellidos</label>
			    <input type="text" class="form-control" placeholder="Apellidos" id="apellidos" name="apellidos">
			  </div>

			  <div class="col-sm-6">
			    <label for="nombres" class="col-4 col-form-label">Nombres</label>
			    <input type="text" class="form-control" placeholder="Nombres" id="nombres" name="nombres">
			  </div>			  
			</div>
		
			<div class="row">
				<div class="form-group">
			  		<div class="col-sm-3">
			    		<label for="id" class="col-3 col-form-label">ID</label>
			    		<input type="text" class="form-control" placeholder="ID" id="id" name="id">
			  		</div>

					<div class="col-sm-3">
					     <label for="phone" class="col-3 col-form-label">Teléfono</label>
			  			<input type="text" class="form-control" placeholder="Teléfono" id="phone" name="phone" maxlength="10">
			  		</div>		
			  			  		
				    <div class="control-group col-sm-6">
				        <label class="control-label"></label>			   
			        	<label class="control-label">Médico</label>        
			        	<select id="medico" name="medico" class="form-control">
			            	<option value="" selected="selected">Médico</option>             
			        	</select>
				    </div>
				   <!--  <div class="control-group col-sm-3">
				        <label class="control-label"></label>        
				    </div> -->
				</div>				
			</div>
			
			<div class="row">
			    <div class="control-group col-sm-3">
			        <label class="control-label">Sexo</label> 
			        <div>
			        	 <input class="form-control" type="checkbox" checked data-toggle="toggle" data-on="Femenino" data-off="Masculino" data-onstyle="success" data-offstyle="primary" id="sexo" name="sexo">
			        </div>

			       
			       <!--   <select id="country" name="country" class="form-control selectpicker">
			            <option value="" selected="selected">Sexo</option> 
			              <option>Femenio</option>
			              <option>Masculino</option>               
			        </select>     -->          
			    </div>
			    <div class="control-group col-sm-3">
			        <label class="control-label">Nacimiento</label>  
			        <div class="input-group date" data-provide="datepicker">
			            <input type="text" class="form-control" id="dob" name="dob">
			            <div class="input-group-addon">
			                <span class="glyphicon glyphicon-th"></span>
			            </div>
			        </div>      
			    </div>
			    <div class="control-group col-sm-3">
			        <label class="control-label">Address Line 1</label>    
			            <input id="address-line1"  name="address-line1" type="text" placeholder="address line 1"
			            class="form-control">
			            <p class="help-block">St address, P.O. box, company name, c/o</p>        
			    </div>
			    <!-- address-line2 input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">Address Line 2</label>    
			            <input id="address-line2" name="address-line2" type="text" placeholder="address line 2"
			            class="form-control">
			            <p class="help-block">Apartment, suite , unit, building, floor, etc.</p>  
			    </div>
			</div>



			<div class="row">
			    <div class="control-group col-sm-3">                      
			        <label for="record" class="col-3 col-form-label">Record No.</label>
			        <input type="text" class="form-control" placeholder="Número de record" id="record" name="record">
			    </div>
			    <div class="control-group col-sm-3">
			        <label class="control-label">Cliente Desde</label> 
			        <div class="input-group date" >
			            <input type="text" class="form-control" disabled id="desde" name="desde">
			            <div class="input-group-addon">
			                <span class="glyphicon glyphicon-th"></span>
			            </div>
			        </div>
			    </div>
			    <!-- city input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">City / Town</label>      
			            <input id="city" name="city" type="text" placeholder="city" class="form-control">
			        <p class="help-block"></p>      
			    </div>

			    <!-- region input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">State / Province / Region</label>       
			            <input id="region" name="region" type="text" placeholder="state / province / region"
			            class="form-control">
			            <p class="help-block"></p>      
			    </div>
 			</div>

 			<div class="row">   
			    <div class="control-group col-sm-3">
			       <label class="control-label">Código</label>        
			       <input id="codecl" name="codecl" type="text" placeholder="" class="form-control">
			       <p class="help-block"></p>  
			    </div>

			   	<div class="control-group col-sm-3">
			       <label class="control-label">Usuario</label>        
			       <input id="usuario" name="usuario" type="text" placeholder="Usuario" class="form-control">
			       <p class="help-block"></p>  
			    </div>

			    <div class="control-group col-sm-3">
			        <label class="control-label">Zip / Postal Code</label>        
			        <input id="postal-code" name="postal-code" type="text" placeholder="zip or postal code" class="form-control">
			        <p class="help-block"></p>        
			    </div>
			    <!-- country select -->
			    <div class="control-group col-sm-3">
			        <label class="control-label">Country</label>        
			        <select id="country" name="country" class="form-control">
			            <option value="" selected="selected">(please select a country)</option>                
			        </select>      
			    </div>
			</div>
			<div class="row">
				   <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox" checked data-toggle="toggle" data-on="Activo" data-off="Inactivo" data-onstyle="success" data-offstyle="danger" id="activo" name="activo">        
			        </div>    

			        <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox" checked data-toggle="toggle" data-on="Empleado" data-off="No empleado" data-onstyle="success" data-offstyle="primary" id="empleado" name="usuario">        
			        </div>

			        <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox" checked data-toggle="toggle" data-on="Vigente" data-off="Fallecido" data-onstyle="success" data-offstyle="danger" id="vigente" name="vigente">
			        </div>
			</div>

		</form>


	</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="../../js/jquery.maskedinput.min.js"></script>
<script src="../../js/pacientes.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script src="../../js/bootstrap-datepicker.min.js"></script>
 
</body>
</html>