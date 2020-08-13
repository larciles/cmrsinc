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
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';
require_once '../../controllers/comprasloadpo.php';

if (isset($_GET['idpo']) && $_GET["idpo"]!='') {
 $po_number = $_GET["idpo"]; 
}
//var_dump($po_number);
$urlevento="";
if(isset($_POST['codclien'])){
  $cod_clien=$_POST['codclien'];
  $urlevento=$_POST['urlevento'];
}
?>
   <html>  
      <head>            
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
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
        <link rel="stylesheet" href="../../css/authorization.css">
        <link rel="stylesheet" href="../../css/alert.css">
   
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
       <!--  <?php //include('payments.php'); ?> 
        <?php //include('recordconsultasmodal.php'); ?> 
        <?php //include('autorizacion.php'); ?> 
        <?php // include('modalvoid.php'); ?>  -->
       

        <!-- tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
            <li><a data-toggle="tab" href="#menu1">List</a></li> 
        </ul>
        <div class="tab-content">
          <div id="home" class="tab-pane fade in active">
          <!-- tabs -->          
              <div class="alert alert-danger alert-dismissible" id="danger-alert" style="display: none;">
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                  <strong>ES NECESARIO!</strong> que coloque una nota o referencia para la compra.
              </div>
              <div class="alert alert-danger alert-dismissible" id="producto-alert" style="display: none;">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>ELIJA UN PRODUCTO</strong> debe elegir un producto para a agregar.
              </div> 

              <div class="alert alert-danger alert-dismissible" id="cantidad-alert" style="display: none;">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>SIN CANTIDAD</strong> debe colocar  una cantidad al producto para agregar.
              </div>                

              <form name="add_name" id="add_name" action='compras.php' method='get'>  
                   

                    <div class="container-fluid "> 
                          <input  id="factura"  type="hidden" name="factura"> 
                          <input  id="transfer" type="hidden" name="transfer">                          
                          <input  id="idusr" type="hidden" name="idusr" value=<?php echo $user;?>>
                           <div class="form-group col-sm-2">  
                                <label class="control-label">ID</label>     
                                <input type="text" id="idpo" name="idpo" class="form-control " placeholder="PO - Invoice"  <?php if (isset($_GET['idpo']) && $_GET["idpo"]!='') { echo "value="."'$po_number'";}?>>
                          </div>
                          <div class="form-group col-sm-2">
                              <label class="control-label">&nbsp</label> 
                              <div class="form-group">  
                                <button type="submit"  class="btn btn-success  form-control" id="submit" value="Submit">OK</button>
                              </div>
                          </div>
                          <div class="form-group col-sm-2">  
                                <label class="control-label">Proveedor</label>     
                                  <select id="idprove" name="idprove" class="form-control enterkey" >
                                  <option value="" selected ></option>             
                              </select>
                          </div>
                          <div class="form-group col-sm-3">  
                                <label class="control-label">Nota</label>     
                                <input type="text" id="idnota" name="idnota" class="form-control " placeholder="Nota" maxlength="255">
                          </div>


                          <div class="col-sm-2">    
                              <label class="control-label">Entrada</label> 
                                <div class="form-group">  
                                    <input type="text" class="form-control datepicker" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" >
                                </div>
                          </div>                      

                          <div class="form-group col-sm-1">  
                                <label class="control-label">Compra#</label>     
                                <input type="text" id="compran" name="compran" class="form-control " maxlength="6" >
                          </div>


                    </div>
                    <div class="container-fluid">  
                
                    <div class="container-fluid">  
                      <div class="table-responsive">  
                         <table class="table  table-hover table-inverse table-condensed" id="dynamic_field">                                      
                              <thead class='thead-inverse'>
                                  <tr>
                                      <th class='titleatn' >Producto</th>
                                      <th class='titleatn' >Cantidad</th>
                                      
                                  </tr>
                              </thead>
                              <tbody>
                                <?php
                                if (isset($_GET['idpo']) && $_GET['idpo']!='') {
                                    getDetailsInvoice();
                                } else { ?>
                                   <tr id='1'>
                                  
                                      <td >
                                        <select id="serv1" name="serv[]" class="form-control service enterpass enterkey" >
                                          <option value="" selected ></option>             
                                        </select>
                                        <!-- <input type="hidden"  id="coditems1" name="coditems[]" value="" class="coditems" /> -->
                                      </td>                                      
                                      <td><input type="text" name="cantidad[]"  value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" /></td>                                      
                                      
                                      <td><button type="button" name="add" id="add" class="btn btn-success enterpass enterkey">Add More</button></td>  
                                  </tr>
                                 <?php } ?>




                              </tbody>  
                         </table>  
                        
                       </div>  
                    </div>  
                

                    <div class="row no-print">
                  <div class="col-xs-12">
                    
                    <div class="col-sm-2"> 
                        <input type="button" name="saveinv" id="saveinv" class="btn btn-info" value="Guardar y Transferir al Inventario" />  
                    </div>
                  </div>

                    </div>

                    <div  class="form-group">
                    <div class="col-sm-2">

                    </div>
                    </div>
                    </div>
              </form>
          <!-- tabs -->
          </div>

          <div id="menu1" class="tab-pane fade">
              <!-- <h3>List</h3>
               <p>Some content in menu 1.</p> -->
              <!-- tab 2 -->
                <div class="container-fluid">
                <div class="tblcontrol" style="padding-left: 10px; padding-right: 10px;">
                 <?php include('listcompras.php');?> 
                </div>        
               </div>
              <!-- /tab 2 -->

              
          </div>

        </div>
        <!-- tabs -->
      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>
<script src="../../js/jquery.bootpag.min.js"></script>
<script src="../../js/compras.js?v=20200813"></script>
<script src="../../js/listacompras.js"></script>
<script src="../../js/sweetalert2.js"></script>

<script>  

 </script>