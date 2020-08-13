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
require "../../controllers/controlCitasControllers.php";
//var_dump($_GET);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>CMA WEB</title>
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/> -->
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
  <!--   // <script src="//code.jquery.com/jquery-1.10.2.js"></script> -->
</head>
<body>
 <!-- <div id="wait" <img src='../../img/demo_wait.gif' width="64" height="64" />></div> -->
	<div id="wait" style="display:none;width:69px;height:89px;position:absolute;top:50%;left:50%;padding:2px;"><img src='../../img/demo_wait.gif' width="64" height="64" /><br></div>
   <input  id="c_usuario" type="hidden" name="c_usuario" value=<?php echo $user ;?>>    
  <header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
			<!-- <h1>Nuevos Pedidos</h1> -->
		</div>
    <div class="loading">Loading&#8230;</div>
	</header>
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
            <li><a data-toggle="tab" href="#menu1">List</a></li>
            <li><a data-toggle="tab" href="#sin">Sin asistir</a></li>
            <li><a data-toggle="tab" href="#citados">Citados</a></li>
            <li><a data-toggle="tab" href="#confirmados">Confirmados</a></li>
            <li><a data-toggle="tab" href="#asistidos">Asistidos</a></li>
            <li><a data-toggle="tab" href="#noasistidos">No Asistidos</a></li>
        </ul>

	<!-- start modal -->
   <?php include('modalrecordconsultas.php'); ?> 

	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Nueva Cita</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row ">
          <div class="form-group col-sm-4">

           <!--  <label for="recipient-name" class="form-control-label">Recipient:</label> -->
            <input type="text" id="idpaciente" class="form-control" placeholder="Id / Record / Nombre">
          </div>
          <div class="form-group col-sm-5">
              <input  id="medicohd" type="hidden" name="medicohd">
              <select id="medico" name="medico" class="form-control" >
                   <option value="" selected ></option>             
              </select>
          </div>
          <div  class="form-group col-sm-3" id="fgfechacita">
                <div class="form-group">    
                    <input type="text" class="form-control" id="fechaNewApmt" placeholder="MM/DD/YYYY"   name="fechaNewApmt" >
                </div>    
          </div>
      </div>

      <div class="row">          
<!--           <div class="form-group">
            <label class="control-label col-sm-3" for="citaprevia">Cita pendiente</label>
            <div class="col-sm-3">
               <input id="citaprevia" type="text" class="form-control" name="citaprevia" placeholder="Cita pendiente" >
 -->              <!-- <input name="task_category[task_category_users_attributes][1][enable]" type="hidden" value="0"> -->
              <!-- <input class="form-control" id="task_category_task_category_users_attributes_1_enable" name="task_category[task_category_users_attributes][1][enable]" type="checkbox" value="true"> -->
<!--             </div>
          </div>
 -->
 <div class="citaprevia form-group">
           <label class="form-control-label" for="citaprevia" >Cita pendiente</label>          
          <div id="appdate" class="col-sm-3">              
              <input id="citaprevia" type="text" class="form-control" name="citaprevia" placeholder="Cita pendiente" >
          </div> 
</div>
      </div> 

      <div class="row dividassoc">
         <div class="form-group">
          <div id="dividassoc" class="form-group col-sm-6 has-warning">
              <input  id="idassochd" type="hidden" name="idassochd">
              <select id="idassoc" name="idassoc" class="form-control" >
                   <option value="" selected ></option>             
              </select>              
          </div>
          <p id='nameasoc'  style='opacity: 1; float:left;' class='clearfix text' >Pacientes viculados <span class="badge"></span></p>
        </div>
      </div>
      <div class="row">
         <div class="form-group col-sm-3">
          <input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Servicios" data-off="Control" id="tipocita" name="tipocita">
        </div>
        <div class="form-group col-sm-3">
          <input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Suero" data-off="Laser"  id="tiposervicio"  >
      </div>
        <div class="form-group col-sm-6">
             <input  id="citashd" type="hidden" name="citashd">
              <select id="citas" name="citas" class="form-control" >
                   <option value="" selected ></option>             
              </select>
          </div>
      </div>
	  
	  	 <div class="row" id="rowtipterapia" style="display: none;">
        <ul class="form-group col-sm-3">
             <li class="list-group-item">
                  MLS
                  <div class="material-switch pull-right">
                        <input id="mls" name="mls" type="checkbox"/>
                        <label for="mls" class="label-primary"></label>                  
                  </div>
                  <input type="text" id="getmls" class="form-control" placeholder="# terapias" style="margin-top: 10px; display: none;">
              </li>
         </ul>  
         <ul class="form-group col-sm-3">
             <li class="list-group-item">
                  HILT
                  <div class="material-switch pull-right">
                      <input id="hilt" name="hilt" type="checkbox"/>
                      <label for="hilt" class="label-primary"></label>                      
                  </div>
                  <input type="text" id="gethilt" class="form-control" placeholder="# terapias" style="margin-top: 10px; display: none;">
              </li>
         </ul> 

    </div>
	  
  <div class="row">
      <div class="col-md-12">  
                <div class="form-group">
            <label for="message-text" class="form-control-label">Observación:</label>
            <textarea class="form-control" id="messagetext"></textarea>
          </div>        
      </div>

    </div>
 
        </form>
      </div>
      <div class="modal-footer">

         <div  align="left"  id="erroralert" class="alert alert-danger alert-dismissable" style="Display: none;">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
          <p id="etext" align="left" ></p> 
         </div>

        <button type="button" class="btn btn-secondary" data-dismiss="modal">Salir</button>
        <button type="button" class="btn btn-primary" id="save">Guardar</button>
      </div>
    </div>
  </div>
</div>
	<!-- finish modal -->

<!-- Alert Starts -->
 <!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h3>Paciente no existe!. Desea crearlo?</h3>
      </div>
      <div class="modal-footer">
        <button type="button" id="closebtn" class="btn btn-secondary" data-dismiss="modal">No</button>
        <button type="button" id="crtnewpaciente" class="btn btn-primary">Si</button>
      </div>
    </div>
  </div>
</div>
<!-- alerts ends -->
<!--  CLIENTES BRIEF STARTS -->
<div class="modal fade" id="briefModal" tabindex="-1" role="dialog" aria-labelledby="briefModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="briefModalLabel">Nuevo paciente vista rápida</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>

          <div class="row ">
            <div class="form-group col-sm-6">
              <label for="apellido" class="form-control-label">Apellidos:</label>
              <input type="text" class="form-control" id="apellido" required>
            </div>
            <div class="form-group  col-sm-6">
              <label for="name" class="form-control-label">Nombres:</label>
              <input type="text" class="form-control" id="name" required></textarea>
            </div>

          </div>
          <div class="row ">
            <div class="form-group col-sm-6">
              <label for="idcl" class="form-control-label">Id:</label>
              <input type="text" class="form-control" id="idcl" required>
            </div>
            <div class="form-group  col-sm-6">
              <label for="phonecl" class="form-control-label">Télefono:</label>
              <input type="text" class="form-control" id="phonecl" required></textarea>
            </div>
          </div> 
		  <div class="row ">
              <div class="control-group col-sm-6">
                <label class="control-label">Usuario</label>        
                <input id="usuariocl" name="usuariocl" type="text" placeholder="Usuario" class="form-control">
                <p class="help-block"></p>  
              </div>
          </div>   
		  
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closecl" class="btn btn-secondary" data-dismiss="modal">Salir</button>
        <button type="button" id="savecl" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!--  ENDS  CLIENTES BRIEF -->
<div class="tab-content">
<div id="home" class="tab-pane fade in active">
	<div class="row">	
		
			<form id='frmctlc' action='control.php' method='get'>
              <input  id="xconfirm" type="hidden"  name="xconfirm">
              <input  id="xasist" type="hidden"  name="xasist">
              <input  id="xnuevos" type="hidden"  name="xnuevos">
              <input  id="xcontrol" type="hidden"  name="xcontrol">
          		<div class="container">
    				<div class="col-sm-2">		
    						<label class="control-label"></label>	
    						<div class="form-group">	
                   	<input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY" name="fecha" value=<?php if(isset($_GET['fecha'])){echo $_GET['fecha']; ;}?> >
                </div>
    				</div>	
    				<div class="control-group col-sm-2">
    					 <label class="control-label"></label>	
    					 <div class="form-group">
                  <div  data-toggle="tooltip" data-placement="top" title="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" >
    						    <input type="text" class="form-control" name="valueToSearch" placeholder="Value To Search" >
                  </div>

    			 	   </div>
    				</div>
    				<div class="control-group col-sm-2" >
    					<label class="control-label"></label>
              <div class="form-group">
    			    <input class="form-control" type="checkbox"  <?php if (isset($_GET['confirmar'])) { echo 'checked';} ?>  data-toggle="toggle" data-on="Confirmar" data-off="Sin confirmar" data-onstyle="success" data-offstyle="info" id="confirmar" name="confirmar"  >        
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
                    <button  type="button" class="btn btn-success form-control" data-toggle="modal" data-target="#exampleModal" data-whatever="">Citar</button>
                </div>
              </div>

              <div class="form-group col-sm-2">
                 <label class="control-label"></label> 
                <div class="form-group">  
                  <button type="submit"  class="btn btn-success form-control" id="submit" value="Submit">OK</button>
                </div>
              </div>
          </div>	

				</form>
				
			
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
        <div id="sin" class="tab-pane fade">
            <div class="container-fluid">
                <div class="row">
                    <form> 
                        <div class"tblsin" style="padding-left: 10px; padding-right: 10px;">
                            <div class="control-group col-sm-2">
                                <label class="control-label"></label> 
                                <div class="form-group">
                                  
                                        <input type="text" class="form-control" id="fechasin" placeholder="MM/DD/YYYY" name="fechasin"  >
                                  
                                </div>
                            </div>
                            
                            <div class="form-group col-sm-2">
                                <label class="control-label"></label> 
                                <div class="form-group">  
                                    <button type="button"  class="btn btn-info form-control" id="okRec" value="Submit">Buscar</button>
                                </div>
                            </div>
                        </div>               
                    </form>
                </div>   
                <div class="row">  
                    <?php include('sinasistir.php');?>
                 </div>   
            </div> 
      </div>  
      <div id="citados" class="tab-pane fade">   <?php include('citados.php');?> </div>
      <div id="confirmados" class="tab-pane fade">  <?php include('confirmados.php');?>      </div>
      <div id="asistidos" class="tab-pane fade">   <?php include('asistidos.php');?>      </div>
      <div id="noasistidos" class="tab-pane fade"> <?php include('noasistidos.php');?>   </div>

</div>	

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/pacientesrecord.js"></script>
<script src="../../js/controlcitas.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/sinasistir.js"></script>
<script src="../../js/repcitados.js"></script>
<script src="../../js/repconfirmados.js"></script>
<script src="../../js/repasistidoscc.js"></script>
<script src="../../js/repnoasistidos.js"></script>
</body>
</html>