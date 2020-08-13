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
    $codperfil=$_SESSION['codperfil'];

    $prninvoice=$_SESSION['prninvoice'];
    $autoposprn=$_SESSION['autoposprn'];
    $pathprn=$_SESSION['pathprn'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';
require_once '../../controllers/invoiceconditcontroller.php';

$numfactu = $_GET['fac'];


//var_dump($_GET);

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
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
        <?php include('payments.php'); ?> 
        <?php include('autorizacion.php'); ?> 

       
              <form name="returnserv" id="returnserv" action="../../clases/savedevservicios.php" method="POST">  

                <input  id="id_centro" type="hidden" name="id_centro" value="2">
                <input  id="workstation" type="hidden" name="workstation" value=<?php echo $workstation;?>>
                <input  id="ipaddress" type="hidden" name="ipaddress" value=<?php echo $ipaddress;?>>                
                <input  id="factura" type="hidden" name="factura"> 
                <input  id="codperfil"  type="hidden"  name="codperfil" value=<?php echo $codperfil; ?>>
                
                    <div class="container-fluid ">                           
                          <input  id="idusr" type="hidden" name="idusr" value=<?php echo $user;?>>
                           <div class="form-group col-sm-2">  
                                <label class="control-label">ID</label>     
                                <input type="text" name="idpaciente" id="idpaciente" class="form-control enterkey idpaciente" placeholder="Record ">
                          </div>
                          <div id="dividassoc" class="form-group col-sm-3">
                              <label class="control-label">Paciente</label>    
                              <input  id="idassochd" type="hidden" name="idassochd">
                              <select id="idassoc" name="idassoc" class="form-control enterkey" >
                                   <option value="" selected ></option>             
                              </select>
                          </div>
                          <div class="form-group col-sm-3">
                              <label class="control-label">Médico</label>    
                              <input  id="medicohd" type="hidden" name="medicohd">
                              <select id="medico" name="medico" class="form-control enterkey" >
                                   <option value="" selected ></option>             
                              </select>
                          </div>
                          <div class="col-sm-2">    
                              <label class="control-label">Emisión</label> 
                                <div class="form-group">  
                                    <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" readonly="readonly">
                                </div>
                          </div>
                          <div class="col-sm-1"> 
                               <label class="control-label"></label>
                               <div class="form-group">  
                                   
                               </div>
                                <!-- <p class="lead "  value="" id="devolucion">Devolución #<span id="id_dev" value=""</span></p>  -->
                                <!-- <input  id="medicohd" type="hidden" name="medicohd"> -->
                          </div>                    
                    </div>

                    <div class="container-fluid">  
                
                    <div class="container-fluid">  
                      <div class="table-responsive">  
                         <table class="table  table-hover table-condensed table-striped" id="dynamic_field">                                      
                              <thead class='thead-inverse'>
                                  <tr>
                                      <th class='titleatn' >Producto</th>
                                      <th class='titleatn' >Lista</th>               
                                      <!-- <th class='titleatn' >Seguro</th>  -->
                                      <th class='titleatn' >Cantidad</th>
                                      <th class='titleatn' >Precio</th>
                                      <!-- <th class='titleatn' >% - $</th> -->
                                      <th class='titleatn' >Descuento</th>
                                      <!-- <th class='titleatn' >Impuesto</th> -->
                                      <th class='titleatn' >Subtotal</th>
                                      <th class='titleatn' >Acción</th>                                                
                                  </tr>
                              </thead>
                              <tbody>
                                
                                <?php getDetailsInvoice();?>

                              </tbody>  
                         </table>  
                        
                       </div>  
                    </div>  
                

                     <!--  -->
                    <div class="row">
                      <!-- accepted payments column -->
                      <div class="col-xs-6">
                      
                      </div>
                      <!-- /.col -->
                      <div class="col-xs-6">
                        <p class="lead" id="fechaf">  <span id="invoicen" value=<?php echo $numfactu;?>>   Invoice # <?php echo $numfactu;?></span></p>
                        <input  id="numfactu" type="hidden" name="numfactu" value=<?php echo $numfactu;?>>
                        <div class="table-responsive">
                          <table class="table">
                            <tbody>
                              <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td id="tlsubototal" value="" name="tlsubototal">$0</td>
                                <input  id="frmsubtotal" type="hidden" name="frmsubtotal">
                              </tr>
                               <tr>
                                <th>
                                  <button id="lockdescuento" name="lockdescuento" data-toggle="modal" data-target="#authorization" style=""><i style="font-size:24px;color:red" class="fa fa-lock"></i></button>
                                  <input type="text" id="discprcntg" name="discprcntg" value=""   placeholder="Discount %" class="form-control name_list" style="float: right; width:85%;" />
                                </th>
                                <td><input type="text" id="discamount" name="discamount" value="$0" placeholder="Discount $" class="form-control name_list" /></td>
                              </tr>
                              <tr>
                                <th>Tax (10.5% + 1%):</th>
                                <td>$0</td>
                                <input  id="frmtax" type="hidden" name="frmtax">
                              </tr>
                              <tr>
                                <th>Shipping:</th>
                                <td id="shipping">$0</td>
                                <input  id="frmshipping" type="hidden" name="frmshipping">
                              </tr>
                              <tr>
                                <th>Total:</th>
                                <td id="tltotal" name="tltotal">$0</td>
                                <input  id="frmtotal" type="hidden" name="frmtotal">
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    <!-- this row will not appear when printing -->
                    <div class="row no-print">
                  <div class="col-xs-12">
                    <!-- <button class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button> -->
                    <!-- <button class="btn btn-success pull-right" data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button> -->
                    <button type="button" id="btnpaymnt" class="btn btn-success pull-right "  data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button> 
                    <!-- <button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button>  -->
                  </div>
                    </div>
                    <div class="form-group">
                    <div class="col-sm-2"> 
                        <input type="button" name="submit" id="submit" class="btn btn-info"  value="Guardar" />  
                    </div>
                    </div>
                    <div  class="form-group">
                    <div class="col-sm-2">
                          <!-- <button class="btn btn-primary" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button>   -->
                          <!-- <input type="button" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" />   -->
                    </div>
                    </div>
                    </div>
              </form>

      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script type="text/javascript" src="../../js/loader.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>

<script src="../../js/jquery.bootpag.min.js"></script>
<script src="../../js/invoicedit.js?v=110919"></script>