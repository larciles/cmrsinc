<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
ini_set('memory_limit', '1024M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");    
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['controlcita'];
    $codperfil2=$_SESSION['codperfil'];
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
	
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
	<link rel="stylesheet" href="../../css/bootstrap-select.min.css">	
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
 <!--  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">   -->
  <link rel="stylesheet" href="../../css/fontawesome5/css/all.css" >
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css?v=1219" /> 
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  <link rel="stylesheet" href="../../css/alert.css">
  <link rel="stylesheet" href="../../css/messenger.css">
  <style type="text/css">
  <?php include("../../css/personalizado.php") ?>
  </style>
  
</head>
<body>
 <!-- <div id="wait" <img src='../../img/demo_wait.gif' width="64" height="64" />></div> -->
	<div id="wait" style="display:none;width:69px;height:89px;position:absolute;top:50%;left:50%;padding:2px;"><img src='../../img/demo_wait.gif' width="64" height="64" /><br></div>
   <input  id="c_usuario" type="hidden" name="c_usuario" value=<?php echo $user ;?>>    
   <input  id="codperfil" type="hidden" name="codperfil" value=<?php echo $codperfil ;?>>
   <input  id="codperfil2" type="hidden" name="codperfil2" value=<?php echo $codperfil2 ;?>>    
   <input  id="codmedico2" type="hidden" name="codmedico2" value=<?php echo $codperfil2 ;?>>    
  <header>
		<div class="container-fluid1 ">
			<?php include '../layouts/header.php';?>
			<!-- <h1>Nuevos Pedidos</h1> -->
		</div>
    <div class="loading">Loading&#8230;</div>
	</header>
  <div id="tabs">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home" id="1">Home</a></li>
            <li><a data-toggle="tab" href="#menu1" id="2">List</a></li>
            <li><a data-toggle="tab" href="#sin" id="3">Sin asistir</a></li>
            <li><a data-toggle="tab" href="#citados" id="4">Citados</a></li>
            <li><a data-toggle="tab" href="#confirmados" id="5">Confirmados</a></li>
            <li><a data-toggle="tab" href="#asistidos" id="6">Asistidos</a></li>
            <li><a data-toggle="tab" href="#noasistidos" id="7">No Asistidos</a></li>

            <li><a data-toggle="tab" href="#servcitados" id="8">Servicios Citados</a></li>
            <li><a data-toggle="tab" href="#servasistidos" id="9">Servicios Asistidos</a></li>
            <li><a data-toggle="tab" href="#servnoasistidosst" id="10">Servicios No Asistidos ST</a></li>
            <li><a data-toggle="tab" href="#servnoasistidosla" id="11">Servicios No Asistidos Laser</a></li>
            <li><a data-toggle="tab" href="#assales" id="12">Nuevos Asistidos sin compras</a></li>

            <li><a data-toggle="tab" href="#cemacitados" id="13">Células Madres Citados</a></li>
            
        </ul>
</div>
	<!-- start modal -->
   <?php include('modalrecordconsultas.php'); ?> 
   <?php include('../layouts/citasmodal.php'); ?> 
   <?php include('../sms/sms.php'); ?> 

   <!-- <div class="shadow" style="display: none;"> -->
        <div class="spinner xspin"  style="display: none;" >
              <div class="rect1"></div>
              <div class="rect2"></div>
              <div class="rect3"></div>
              <div class="rect4"></div>
              <div class="rect5"></div>
        </div>
<!-- </div> -->
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

       <div class="alert" style='display:none' id="alert">
             <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
             <strong>Advertencia !!! </strong> <span class="test"></span>
        </div>  
		
			<form id='frmctlc' action='control.php' method='get'>
              
              <input id="xasist" type="hidden"  name="xasist">
              <input id="xnuevos" type="hidden"  name="xnuevos">
              <input id="xconfirm" type="hidden"  name="xconfirm">
              <input id="xcontrol" type="hidden"  name="xcontrol">
              <input id="character" type="hidden"  name="character">

              <input id="fecha_noasis_1" type="hidden"  name="fecha_noasis_1" value=<?php if(isset($_GET['fecha_noasis_1'])){echo $_GET['fecha_noasis_1']; ;}?>>
              <input id="fecha_noasis_2" type="hidden"  name="fecha_noasis_2" value=<?php if(isset($_GET['fecha_noasis_2'])){echo $_GET['fecha_noasis_2']; ;}?>>
              
          		<div class="container">
    				<div class="col-sm-2">		
    						<label class="control-label"></label>	
    						<div class="form-group">	
                   	<input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY" autocomplete="off" name="fecha" value=<?php if(isset($_GET['fecha'])){echo $_GET['fecha']; ;}?> >
                </div>
    				</div>	
    				<div class="control-group col-sm-2">
    					 <label class="control-label"></label>	
    					 <div class="form-group">
                  <div  data-toggle="tooltip" data-placement="top" title="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" >
    						    <input type="text" class="form-control" name="valueToSearch" placeholder="Value To Search" autocomplete="off">
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
                  <button type="submit"  class="btn btn-primary form-control" id="submit" value="Submit">OK</button>
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
      <div class="tblcontrol" style="padding-left: 10px; padding-right: 10px;">
        <?php mostrarCitas();?>
      </div>
			
		</div>
	</div>	
	</div>	

        <div id="menu1" class="tab-pane fade">
            <div class="container-fluid">
                <div class="row">
                    <form> 
                	 	<div class="tblcontrol" style="padding-left: 10px; padding-right: 10px;">
							<div class="control-group col-sm-2">
    					 		<label class="control-label"></label>	
    					 		<div class="form-group" style="margin-top: 9px;">
                  					<div  data-toggle="tooltip" data-placement="top" title="" >
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
                				<div class="form-group" style="margin-top: 9px;">  
                  					<button type="button"  class="btn btn-success form-control" id="okRec" value="Submit">Buscar</button>
                				</div>
              				</div>

                		</div>               
					</form>
                </div>   
                  <div>  
                <ul class="pagination"> 
    <li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li> 
    <li><a href="#">A</a></li> 
    <li class="active"><a href="#">B <span class="sr-only">(current)</span></a></li> 
    <li><a href="#">C</a></li> 
    <li><a href="#">D</a></li> 
    <li><a href="#">E</a></li>
    <li><a href="#">F</a></li>
    <li><a href="#">G</a></li>
    <li><a href="#">H</a></li>
    <li><a href="#">I</a></li>
    <li class="disabled"><span class="hint--bottom hint--always" data-hint="Disabled button when no record to show">J</span></li>
    <li class="disabled"><span>K</span></li>
    <li><a href="#">L</a></li>
    <li><a href="#">M</a></li>
    <li><a href="#">N</a></li>
    <li><a href="#">O</a></li>
    <li><a href="#">P</a></li>
    <li><a href="#">Q</a></li>
    <li><a href="#">R</a></li>
    <li><a href="#">S</a></li>
    <li><a href="#">T</a></li>
    <li><a href="#">U</a></li>
    <li><a href="#">V</a></li>
    <li class="disabled"><a href="#">W</a></li>
    <li class="disabled"><a href="#">X</a></li>
    <li class="disabled"><a href="#">Y</a></li>
    <li><a href="#">Z</a></li>
    <li><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li> 
  </ul> 
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
                        <div class="tblsin" style="padding-left: 10px; padding-right: 10px;">
                            <div class="control-group col-sm-2">
                                <label class="control-label"></label> 
                                <div class="form-group" >
                                  
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
      <div id="citados" class="tab-pane fade">     <?php include('citados.php');?>     </div>
      <div id="confirmados" class="tab-pane fade"> <?php include('confirmados.php');?> </div>
      <div id="asistidos" class="tab-pane fade">   <?php include('asistidos.php');?>   </div>
      <div id="noasistidos" class="tab-pane fade"> <?php include('noasistidos.php');?> </div>

      <!-- SERVICIOS -->
            
      <div id="servcitados"       class="tab-pane fade"> <?php include('servcitados.php');?> </div>
      <div id="servasistidos"     class="tab-pane fade"> <?php include('servasistidos.php');?> </div>
      <div id="servnoasistidosst" class="tab-pane fade"> <?php include('servnoasistidosst.php');?> </div>
      <div id="servnoasistidosla" class="tab-pane fade"> <?php include('servnoasistidosla.php');?> </div>
      <div id="assales"           class="tab-pane fade"> <?php include('assales.php');?></div>
      <div id="cemacitados" class="tab-pane fade">     <?php include('cemacitados.php');?>     </div>

</div>	

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>

<script src="../../js/jquery.bootpag.min.js"></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/sweetalert2.js"></script>
<script src="../../js/pacientesrecord.js"></script>
<script src="../../js/controlcitas.js?v=1219"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/sinasistir.js"></script>
<script src="../../js/repcitados.js"></script>
<script src="../../js/repconfirmados.js"></script>
<script src="../../js/repasistidoscc.js"></script>
<script src="../../js/repnoasistidos.js"></script>

<script src="../../js/repservcitados.js"></script>
<script src="../../js/repserasistidoscc.js"></script>
<script src="../../js/repserstnoasistidos.js"></script>
<script src="../../js/repserlanoasistidos.js"></script>
<script src="../../js/assales.js"></script>
<script src="../../js/repcemacitados.js"></script>

<script src="../../js/sms.js"></script>


</body>
</html>