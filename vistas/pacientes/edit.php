<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
   $user=$_SESSION['username'];
   $authorization=$_SESSION['codperfil'];
   $controlcita=$_SESSION['controlcita'];
   $clase=$_SESSION['clase'];
}
require_once '../../models/user_model.inc.php';
if (isset($_POST['dob'])) { 
  //   $_POST['dob'] ;
  //echo    $nombre= $_POST['empleado'] ;
  //   $newDate = date("d-m-Y", strtotime($_POST['dob']));
  //   $dbo=date($nombre);
  // //  var_dump($newDate);
  //   // var_dump($dbo);
   //var_dump($_POST['empleado']);
}

if (isset($_POST['urlorigen'])) {
	$urlorigen=$_POST['urlorigen'];
} 

$disabled="";
if ($clase=="cc") {
	$disabled="readonly";	
}
    ?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
 	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">		
	<link rel="stylesheet" href="../../css/bootstrap.min.css"/>
	<link rel="stylesheet" href="../../css/ocestilos.css">
    <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
    <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">     
    <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
</head>
<body>
	<header>
		<div class="container-fluid">
			<?php include '../layouts/header.php';?>
			<h1>Pacientes</h1>
		</div>
	</header>
	<div class="container">
		<form  name="formcl" id="formcl" method="post"  autocomplete="off" action="../../controllers/pacientesSave.php">
			<input  id="operacion" type="hidden" name="operacion" value="edit">

			<input  id="urlorigen" type="hidden" name="urlorigen" value="<?php if (isset($_POST['urlorigen'])) { echo $_POST["urlorigen"] ; } ?>">

			

			<div class="row">
			  <div class="col-sm-6">
			  	<label for="apellidos" class="col-4 col-form-label">Apellidos</label>
			    <input type="text" class="form-control" placeholder="Apellidos" id="apellidos" name="apellidos" <?php echo $disabled;?> value="<?php if (isset($_POST['apellidos'])) { printf( $_POST['apellidos']); } ?>">
			  </div>

			  <div class="col-sm-6">
			    <label for="nombres" class="col-4 col-form-label">Nombres</label>
			    <input type="text" class="form-control" placeholder="Nombres" id="nombres" name="nombres" <?php echo $disabled;?> value="<?php if (isset($_POST['nombre'])) { echo $_POST["nombre"] ; } ?>">
			  </div>			  
			</div>
		
			<div class="row">
				<div class="form-group">
			  		<div class="col-sm-3">
			    		<label for="id" class="col-3 col-form-label">ID</label>
			    		<input type="text" class="form-control" placeholder="ID" id="id" name="id" <?php echo $disabled;?> value=<?php if (isset($_POST['cedula'])) { echo $_POST['cedula']; } ?>>
			  		</div>

					<div class="col-sm-3">
					     <label for="phone" class="col-3 col-form-label">Teléfono</label>
			  			<input type="text" class="form-control" placeholder="Teléfono" id="phone" <?php echo $disabled;?> name="phone" maxlength="10" value=<?php if (isset($_POST['telefono'])) { echo $_POST['telefono']; } ?>>
			  		</div>		
			  			  		
				    <div class="control-group col-sm-3">
				        <label class="control-label"></label>			   
			        	<label class="control-label">Médico</label>  
			        	<!-- <option disabled selected="selected">Médico</option>   -->
			        	<input  id="medicohd" type="hidden" name="medicohd" <?php echo $disabled;?> value=<?php if (isset($_POST['medico'])) { echo $_POST['medico']; } ?>>
			        	<select id="medico" name="medico" class="form-control" >
			            	<option value="" selected ></option>             
			        	</select>
				    </div>
				   <div class="control-group col-sm-3">
				      	<label for="email">Email address</label>
    					<input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value=<?php if (isset($_POST['email'])) { echo $_POST['email']; } ?>>
				    </div> 
				</div>				
			</div>
			
			<div class="row">

				<div class="col-sm-2">
				     <label for="phone2" class="col-3 col-form-label">Teléfono2</label>
			  		<input type="text" class="form-control" placeholder="Teléfono" id="phone2" <?php echo $disabled;?> name="phone2" maxlength="10" value=<?php if (isset($_POST['telefono2'])) { echo $_POST['telefono2']; } ?>>
			  	</div>	

			    <div class="control-group col-sm-2">
			        <label class="control-label">Sexo</label> 
			        <div>
			        	 <input class="form-control"  type="checkbox" <?php if (isset($_POST['sexo'])) { if($_POST['sexo']=="0"){echo 'checked';} } ?>  data-toggle="toggle" data-on="Femenino" data-off="Masculino" data-onstyle="success" data-offstyle="primary" id="sexo" name="sexo">
			  
			        </div>    
			    </div>
			    <div class="control-group col-sm-2">
			        <label class="control-label">Nacimiento</label>  
			        <div class="input-group date" data-provide="datepicker">
			            <input type="text" class="form-control" id="dob" name="dob" value=<?php echo date("m/d/Y", strtotime($_POST['dob']));?>>
			            <div class="input-group-addon">
			                <span class="glyphicon glyphicon-th"></span>
			            </div>
			        </div>      
			    </div>
			    <div class="control-group col-sm-3">
			        <label class="control-label">Address Line 1</label>    
			            <input id="address-line1"  name="address-line1" type="text" placeholder="address line 1"
			            class="form-control" value="<?php if (isset($_POST['dirl1'])) { echo $_POST['dirl1']; } ?>">
			            <p class="help-block">St address, P.O. box, company name, c/o</p>        
			    </div>
			    <!-- address-line2 input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">Address Line 2</label>    
			            <input id="address-line2" name="address-line2"  type="text" placeholder="address line 2"
			            class="form-control" value="<?php if (isset($_POST['dirl2'])) { echo $_POST['dirl2']; } ?>">
			            <p class="help-block">Apartment, suite , unit, building, floor, etc.</p>  
			    </div>
			</div>



			<div class="row">
			    <div class="control-group col-sm-3">                      
			        <label for="record" class="col-3 col-form-label">Record No.</label>
			        <input type="text" class="form-control" placeholder="Número de record" id="record" <?php echo $disabled;?> name="record" value=<?php if (isset($_POST['record'])) { echo $_POST['record']; } ?>>
			    </div>
			    <div class="control-group col-sm-3">
			        <label class="control-label">Cliente Desde</label> 
			        <div class="input-group date" >
			            <input type="text" class="form-control" disabled id="desde" <?php echo $disabled;?> name="desde" value=<?php echo date("m/d/Y", strtotime($_POST['desde']));?>>
			            <div class="input-group-addon">
			                <span class="glyphicon glyphicon-th"></span>
			            </div>
			        </div>
			    </div>
			    <!-- city input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">City / Town</label>      
			            <input id="city" name="city" type="text" placeholder="city" class="form-control"  value="<?php if (isset($_POST['ciudad'])) { echo $_POST['ciudad']; } ?>">
			        <p class="help-block"></p>      
			    </div>

			    <!-- region input-->
			    <div class="control-group col-sm-3">
			        <label class="control-label">State</label>
			        <input  id="statecohd" type="hidden" name="statecohd"  value=<?php if (isset($_POST['state'])) { echo $_POST['state']; } ?>>     
			        <select id="region" name="region" class="form-control" >
			            <option value="" selected="selected">(please select a State)</option>                
			        </select>      
			    </div>
<!-- 			    <div class="control-group col-sm-3">
			        <label class="control-label">State / Province / Region</label>       
			            <input id="region" name="region" type="text" placeholder="state / province / region"
			            class="form-control">
			            <p class="help-block"></p>      
			    </div> -->
 			</div>

 			<div class="row">   
			    <div class="control-group col-sm-3">
			       <label class="control-label">Código</label>  
			       <input id="codecl" name="codecl" type="hidden" placeholder="" value=<?php if (isset($_POST['codclien'])) { echo $_POST['codclien']; } ?>>      
			       <input id="codcl"  name="codcl" disabled type="text" placeholder="" class="form-control" value=<?php if (isset($_POST['codclien'])) { echo $_POST['codclien']; } ?>>
			       <p class="help-block"></p>  
			    </div>

			   	<div class="control-group col-sm-3">
			   	   <input  id="authorization" type="hidden" name="authorization" value=<?php  echo $authorization; ?>> 
			       <label class="control-label">Usuario</label>        
			       <input id="usuariocl" name="usuariocl" disabled type="text" placeholder="Usuario" class="form-control" value="<?php if (isset($_POST['usuariocl'])) { echo $_POST['usuariocl']; } ?>">
			       <p class="help-block"></p>  
			    </div>

			    <div class="control-group col-sm-3">
			        <label class="control-label">Zip / Postal Code</label>        
			        <input id="postal-code" name="postal-code" type="text" placeholder="zip or postal code" class="form-control" value=<?php if (isset($_POST['codpostal'])) { echo $_POST['codpostal']; } ?>>
			        <p class="help-block"></p>        
			    </div>
			    <!-- country select -->
			    <div class="control-group col-sm-3">
			        <label class="control-label">Country</label>
			        <input  id="paiscohd" type="hidden" name="paiscohd" value=<?php if (isset($_POST['pais'])) { echo $_POST['pais']; } ?>>     
			        <select id="country" name="country" class="form-control">
			            <option value="" selected="selected">(please select a country)</option>                
			        </select>      
			    </div>
			</div>
			<div class="row">
				   <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox" <?php if (isset($_POST['activo'])) { if($_POST['activo']=="0"){echo 'checked';} } ?> data-toggle="toggle" data-on="Activo" data-off="Inactivo" data-onstyle="success" data-offstyle="danger" id="activo" name="activo">        
			        </div>    

			        <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox"  <?php if (isset($_POST['empleado'])) { if($_POST['empleado']=="1"){echo 'checked';} } ?> data-toggle="toggle" data-on="Empleado" data-off="No empleado" data-onstyle="success" data-offstyle="info" id="empleado" name="empleado">        
			        </div>

			        <div class="control-group col-sm-4">
			             <label class="control-label"></label>
			             <input type="checkbox"  <?php if (isset($_POST['vigente'])) { if($_POST['vigente']=="0"){echo 'checked';} } ?>  data-toggle="toggle" data-on="Vigente" data-off="Fallecido" data-onstyle="success" data-offstyle="danger" id="vigente" name="vigente">
			        </div>
			</div>
			<div class="row">
				<br><br>
				<div class="control-group col-sm-3">
				</div>
<!-- 				<div class="control-group col-sm-4">
			  		<input type="submit" class="btn btn-primary" value="Guardar">
				</div> -->
				<?php 
                     //  if ($authorization=="01" || $controlcita!=="1") { 
                           echo '<div class="control-group col-sm-4">' ;
                           echo '<input type="submit" class="btn btn-primary" value="Guardar">' ;
                           echo '</div>' ;
                      // }
				?>
				<div class="control-group col-sm-4">
			  		<button id="previa" type="button" class="btn btn-primary">Cancelar</button>
				</div>		
			</div>
		</form>
	</div>


<script type="text/javascript" src="../../js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="../../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/pacientes.js"></script>
<script type="text/javascript" src="../../js/bootstrap-toggle.min.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
 
</body>
</html>