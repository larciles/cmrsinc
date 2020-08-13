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
require_once '../../controllers/returneditprodcontroller.php';
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
        <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
        <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
        <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
        <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css">
        <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
        <link rel="stylesheet" href="../../css/jquery-ui.css">
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                   <?php include '../layouts/header.php';?>    
            </div>
        </header> 
        <?php include('../layouts/payments.php'); ?> 

        <!-- tabs -->
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
            <!-- <li><a data-toggle="tab" href="#menu1">List</a></li> -->
        </ul>
        <div class="tab-content">
          <div id="home" class="tab-pane fade in active">
          <!-- tabs -->          
              <form name="add_name" id="add_name">  

                    <input  id="frmsubtotal" type="hidden" name="frmsubtotal">
                    <input  id="frmtax" type="hidden" name="frmtax">
                    <input  id="frmshipping" type="hidden" name="frmshipping">
                    <input  id="frmtotal" type="hidden" name="frmtotal">
                    <input  id="taxilicuota" type="hidden" name="taxilicuota">
                    <input  id="numfactu" type="hidden" name="numfactu" value=<?php echo $numfactu;?>>
                    <input  id="devnumber" type="hidden" name="devnumber">

                    <input  id="workstation" type="hidden" name="workstation" value=<?php echo $workstation;?>>
                    <input  id="ipaddress" type="hidden" name="ipaddress" value=<?php echo $ipaddress;?>>

                    <div class="container-fluid ">                           
                          <input  id="idusr" type="hidden" name="idusr" value=<?php echo $user;?>>
                           <div class="form-group col-sm-2">  
                                <label class="control-label">ID</label>     
                                <input type="text" id="idpaciente" class="form-control idpaciente enterkey" placeholder="Id / Record / Nombre">
                          </div>
                          <div id="dividassoc" class="form-group col-sm-3">
                              <label class="control-label">Paciente</label>    
                              <input  id="idassochd" type="hidden" name="idassochd">
                              <select id="idassoc" name="idassoc" class="form-control enterkey" >
                                   <option value="" selected ></option>             
                              </select>
                          </div>
                          <div class="form-group col-sm-2">
                              <label class="control-label">Médico</label>    
                              <input  id="medicohd" type="hidden" name="medicohd">
                              <select id="medico" name="medico" class="form-control enterkey" >
                                   <option value="" selected ></option>             
                              </select>
                          </div>

         <!--                   <div id="dividasegurohd" class="form-group col-sm-2">
                              <label class="control-label">Seguro</label>    
                              <input  id="idasegurohd" type="hidden" name="idassochd">
                              <select id="seguro" name="seguro" class="form-control" >
                                   <option value="" selected ></option>             
                              </select>
                          </div> -->
                          
                          <div class="col-sm-2">    
                              <label class="control-label">Emisión</label> 
                                <div class="form-group">  
                                    <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" readonly="readonly">
                                </div>
                          </div>  
                          <div class="col-sm-2"> 
                               <label class="control-label"></label>
                               <div class="form-group">  
                                   
                               </div>
                                <p class="lead "  value="" name="devolucion" id="devolucion">Devolución #<span id="id_dev" value=""</span></p> 
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
                                      
                                      <!-- <th class='titleatn' >Dosis</th> -->
                                      <!-- <th class='titleatn' >Sugerido</th> -->

                                      <th class='titleatn' >Cantidad</th>
                                      <th class='titleatn' >Precio</th>
                                      <!-- <th class='titleatn' >% - $</th> -->
                                      <th class='titleatn' >Descuento</th>
                                      <th class='titleatn' >Impuesto</th>
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
                        <p class="lead">Payment Methods:</p>
                        <img src="../../img/visa.png" alt="Visa">
                        <img src="../../img/mastercard.png" alt="Mastercard">
                        <img src="../../img/american-express.png" alt="American Express">
                        <!-- <img src="../../img/paypal.png" alt="Paypal"> -->
                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;"></p>
                      </div>
                      <!-- /.col -->
                      <div class="col-xs-6">
                        <p class="lead" id="fechaf">  <span id="invoicen">   Invoice # 123455</span></p>

                        <div class="table-responsive">
                          <table class="table">
                            <tbody>
                              <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td id="tlsubototal" name="tlsubototal">$0</td>
                              </tr>
                               <tr>
                                <th><input type="text" id="discprcntg" name="discprcntg" value=""   placeholder="Discount %" class="form-control name_list" /></th>
                                <td><input type="text" id="discamount" name="discamount" value="0" placeholder="Discount $" class="form-control name_list" /></td>
                              </tr>
                              <tr>
                                <th>Tax (10.5% + 1%)  : <input checked data-toggle="toggle" data-size="small" type="checkbox" id="taxapply"></th>
                                <td id="tlimpuesto" name="tlimpuesto" value="" >0</td>
                              </tr>
                              <tr>
                                <th>Shipping:</th>
                                <td><input type="text" id="shipping" name="shipping" value="0" placeholder="shipping $" class="form-control name_list shipping" /></td>                       
                              </tr>
                              <tr>
                                <th>Total:</th>
                                <td id="tltotal" name="tltotal">$0</td>
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
                    <!-- disabled<button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button>  -->
                  </div>
                    </div>
                    <div  class="form-group" style="float: right;margin-right: 171px;margin-top: -36px;">
                    <div class="col-sm-2"> 
                        <input type="button" name="submit" id="submit" class="btn btn-info" value="Guardar" />  
                    </div>
                    </div>
                    <div  class="form-group">
                    <div class="col-sm-2">
                          <!-- <button class="btn btn-primary" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button>   -->
                        <input type="button" style="display:none" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" />  
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
                <div class"tblcontrol" style="padding-left: 10px; padding-right: 10px;">
                <?php include('listreturn.php');?>
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
<script src="../../js/returneditprod.js"></script>
<script src="../../js/payments.js"></script>
<script>  
</script>