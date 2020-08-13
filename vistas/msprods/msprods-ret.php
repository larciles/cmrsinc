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
    $access=$_SESSION['access'];
    $codperfil=$_SESSION['codperfil'];

    $prninvoice=$_SESSION['prninvoice'];
    $autoposprn=$_SESSION['autoposprn'];
    $pathprn=$_SESSION['pathprn'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

require('../../controllers/MediosController.php');
require('../../controllers/MFacturaController.php');
require('../../controllers/DFacturaController.php');
require('../../controllers/MInventarioController.php');
require('../../controllers/TipoprecioController.php');
require('../../controllers/ClientesController.php');
require('../../controllers/MedicosController.php');
require('../../controllers/ExclusivosController.php');
require('../../controllers/DnotacreditoController.php');
require('../../controllers/MnotacreditoController.php');



$mediacontroller = new MediosController();

$facturacontroller = new MFacturaController();

$dfacturacontroller = new DFacturaController();

$cminventariocontroller = new MInventarioController();

$tipopreciocontroller = new TipoprecioController();

$clientescontroller = new ClientesController();

$medicoscontroller = new MedicosController();

$dnotacreditoController= new DnotacreditoController();
$mnotacreditoController= new MnotacreditoController();


$total=0;
$factura="";
$codmedico="";
$disabled='disabled';
$res=[];
 $detf =[];
if (!isset($_GET['new']) ) {
 
if (isset($_GET['return']) ) {
   $numfactu=$_GET['numero'];
   $res=$facturacontroller->readUDF("Select *, REPLACE(CONVERT(CHAR(15), fechafac, 101), '', '-') AS fecha from mfactura where numfactu='$numfactu' " );
   $long=0;
   if (is_array($res)) {
      $long=sizeof($res);
   }
   if ( sizeof($res)>0 ) {
      $factura=$res[0]['numfactu'];
      $codclien=$res[0]['codclien'];
      $codmedico=$res[0]['codmedico'];
      $total=$res[0]['total'];
      $fecha=date("m/d/Y");//$res[0]['fecha'];
      $media=$res[0]['medios'];

      $porcentaje= $res[0]['Alicuota'];
      $descuento = $res[0]['descuento'];
      $subtotal=$res[0]['subtotal'];
      $originguser=$res[0]['usuario'];

    



      $detf = $dfacturacontroller->readAllW(array('numfactu'=>$factura));
      $detfLen=sizeof($detf);

   }
}elseif (isset($_GET['edicion']) || isset($_GET['?edicion'])) {
  $return=$_GET['numero'];
  $res=$mnotacreditoController->readUDF("Select *, REPLACE(CONVERT(CHAR(15), fechanot, 101), '', '-') AS fecha from Mnotacredito where numnotcre='$return' " );
  $long=0;
  if (is_array($res)) {
      $long=sizeof($res);
  }

  if ( sizeof($res)>0 ) {
      $return=$res[0]['numnotcre'];
      $codclien=$res[0]['codclien'];
      $codmedico=$res[0]['codmedico'];
      $fecha=$res[0]['fecha'];
      $media=$res[0]['medios'];
      $total=$res[0]['totalnot'];

   
      $subtotal=$res[0]['subtotal'];

      $porcentaje= $res[0]['alicuota'] ;
      $descuento = $res[0]['descuento'];

      $detf =$dnotacreditoController->readAllW(array('numnotcre'=>$return) );
      $detfLen=sizeof($detf);
   }  
}


if ( $long>0 ) {
    

    $invetario=$cminventariocontroller->readUDF("SELECT  b.CapsulasXUni, a.* from  MInventario a left join NTPRODUCTOS b on a.coditems=b.Cod_prod where a.prod_serv='p' and a.activo = 1 order by  a.desitems " );

    $baremo=$tipopreciocontroller->readUDF("SELECT * from  tipoprecio order by codtipre");

    $cliente=$clientescontroller->readUDF("SELECT * from  MClientes where codclien='$codclien' ");

    $medico=$medicoscontroller->readUDF("SELECT *, CONCAT(nombre,' ', apellido)  medico from  Mmedicos where Codmedico='$codmedico' ");

    $media=$mediacontroller->readUDF("SELECT * from  Medios where codigo='$media' ");

}
}

//var_dump($_GET);
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

        <style type="text/css">
          .btn-separator:after {
                content: ' ';
                display: block;
                float: left;
                background: #ADADAD;
                margin: 0 10px;
                height: 34px;
                width: 1px;
            }
        </style>
   
      </head>  
      <body>  
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>
                 <?php include '../layouts/payments.php';?>  
                 <?php include('../layouts/modalvoid.php'); ?>   
            </div>
        </header> 
        <?php include('recordconsultasmodal.php'); ?> 

                    <div class="container-fluid ">     
        <ul class="nav nav-pills">
            <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
            <!-- <li><a data-toggle="tab" href="#menu1">Lista de facturas</a></li> -->
        </ul>
         </div>
        <div class="tab-content">
          <div id="home" class="tab-pane fade in active">

                  <form name="facturacion" id="facturacion" method="post">  

                    <input  id="frmsubtotal" type="hidden" name="frmsubtotal">
                    <input  id="frmtax" type="hidden" name="frmtax">
                    <input  id="frmshipping" type="hidden" name="frmshipping">
                    <input  id="frmtotal" type="hidden" name="frmtotal">
                    <input  id="tax_p" type="hidden" name="tax_p">
                    <input  id="numfactu" type="hidden" name="numfactu" value="<?php if (isset($_GET['return']) ) echo( $numfactu ) ;?>">
                    <input  id="delallrecords" type="hidden" name="delallrecords">
                    <input  id="desxmonto" type="hidden" name="desxmonto">
                    <input  id="urlevento" type="hidden" name="urlevento" value=<?php echo $urlevento;?>>                    
                    <input  id="atn_cciente" type="hidden" name="atn_cciente" value=<?php echo $cod_clien;?>>                    
                    <input  id="access" type="hidden" name="access" value=<?php echo $access;?>>
                    <input  id="codperfil" type="hidden" name="codperfil" value=<?php echo $codperfil;?>>                    
                    <input  id="workstation" type="hidden" name="workstation" value=<?php echo $workstation;?>>
                    <input  id="ipaddress" type="hidden" name="ipaddress" value=<?php echo $ipaddress;?>>
                    <input  id="grupo"  type="hidden"  name="grupo" value="SUEROTERAPIA">
                    <input  id="prninvoice" type="hidden" name="prninvoice" value=<?php echo $prninvoice;?>>
                    <input  id="autoposprn" type="hidden" name="autoposprn" value=<?php echo $autoposprn;?>>
                    <input  id="pathprn" type="hidden" name="pathprn" value=<?php echo $pathprn;?>>
                    <input  id="md" type="hidden"   value=<?php echo $codmedico;?>>
                    <input  id="edicion" name="edicion" type="hidden"   value="false">

                    <input  id="originguser" type="hidden" name="originguser" value=<?php echo $originguser;?>>

                   

                    <div class="container-fluid ">                           
                          <input  id="idusr" type="hidden" name="idusr" value=<?php echo($user);?>>
                           <div class="form-group col-sm-2">  
                                <label class="control-label">ID</label>     
                                <input type="text" id="idpaciente" class="form-control idpaciente enterkey" placeholder="Id / Record / Nombre" autocomplete="off">
                          </div>
                          <div id="dividassoc" class="form-group col-sm-2">
                              <label class="control-label">Paciente</label>    
                              <input  id="idassochd" type="hidden" name="idassochd">
                              <select id="idassoc" name="idassoc" class="form-control enterkey" >
                                   <option value="<?php echo($cliente[0]['codclien'])?>" selected ><?php echo($cliente[0]['nombres']) ?></option>             
                              </select>
                          </div>
                          <div class="form-group col-sm-2">
                              <label class="control-label">Médico</label>    
                              <input  id="medicohd" type="hidden" name="medicohd">
                              <select id="medico" name="medico" class="form-control enterkey" >
                                   <option value="<?php echo( $medico[0]['Codmedico'])?>" selected ><?php echo( $medico[0]['medico'])?></option>             
                              </select>
                          </div>
                          <div class="form-group col-sm-2">
                              <label class="control-label">Referido por</label>    
                              <input  id="mediox" type="hidden" name="mediox">
                              <select id="medio" name="medio" class="form-control enterkey" >
                                   <option value="<?php echo( $media[0]['codigo'])?>" selected ><?php echo( $media[0]['medio'])?></option>             
                              </select>
                          </div>

                         
                          
                          <div class="col-sm-2">    
                              <label class="control-label">Emisión</label> 
                                <div class="form-group">  
                                    <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha"  value="<?php echo($fecha) ?>" readonly="readonly">
                                </div>
                          </div>     
                          <div class="col-sm-1">    
                            
                              <label class="control-label">RETURN #</label> 
                                <div class="form-group">  
                                    <input type="text" class="form-control" id="numero" name="numero" value="<?php echo($return) ?>" >
                                </div>
                           
                          </div>     
                          <div class="col-sm-1">    
                                    <label class="control-label">Historial</label> 
                                      <div class="form-group">  
                                          <input type="button" class="form-control btn btn-primary pull-lef" id="historial"   value="Historial" data-toggle="modal" data-target="#recordConsModal" data-whatever="@mdo">                                    
                                      </div>
                                </div>                                        
                          </div>
                    <div class="alert" style='display:none'>
                        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
                        <strong>Advertencia!</strong> Este paciente ya tiene al menos una factura hoy.
                    </div>
                    <div class="container-fluid">  
                
                    <div class="container-fluid">  
                      <div class="table-responsive yes">
                         <table class="table  table-hover table-condensed table-striped" id="dynamic_field">                                      
                          <!-- style='background-color:#259B9A;' -->
                              <thead class='thead' >
                                  <tr>
                                      <th class='titleatn' >Producto</th>
                                      <th class='titleatn' >Lista</th> 

                                      <th class='titleatn' >Dosis</th>
                                      <th class='titleatn' >Sugerido</th>             
                                     

                                      <th class='titleatn' >Cantidad</th>
                                      <th class='titleatn' >Precio</th>
                                     
                                      <th class='titleatn' >Descuento</th>

                                      <th class='titleatn' >Impuesto</th>
                                      
                                      <th class='titleatn' >Subtotal</th>
                                      <th class='titleatn' ><button type="button" name="add" id="add" class="btn btn-success add  enterkey">Add More</button></th>                                                
                                  </tr>
                              </thead>
                              <tbody>  

                                <?php 
                                if (isset($detf) && sizeof($detf) >0 ) {
                                  $disabled='';
                                
                                for ($ii=0; $ii <sizeof($detf) ; $ii++) {      ?>


                                  <tr id="<?php echo($ii)?>">
                                      
                                      <td><select name="producto[]"  class="form-control service  enterkey" >

                                       <?php
                                        for ($ip=0; $ip <sizeof($invetario) ; $ip++) {      


                                          if ($detf[$ii]['coditems']==$invetario[$ip]['coditems']) {   ?>
                                             <option value="<?php echo($invetario[$ip]['coditems'])?>" selected ><?php echo($invetario[$ip]['desitems'])?></option>
                                          <?php  } else {            ?>
                                            
                                                <option value="<?php echo($invetario[$ip]['coditems'])?>"  ><?php echo($invetario[$ip]['desitems'])?></option>
                                          <?php    }                                     
                                             
                                        }
                                       
                                       ?> 
                                     </select></td>

                                      <td>
                                        <select name="listaprecio[]"     class="form-control enterkey pricelist">

                                       <?php 
                                       for ($ib=0; $ib < sizeof($baremo) ; $ib++) { 
                                            if ($baremo[$ib]['codtipre']==$detf[$ii]['codtipre']) { ?>
                                                 <option value="<?php echo($baremo[$ib]['codtipre'])?>" selected><?php echo( $baremo[$ib]['destipre'])  ?> </option>                                                   
                                            <?php  } else {            ?>
                                                 <option value="<?php echo($baremo[$ib]['codtipre'])?>" ><?php echo( $baremo[$ib]['destipre'])  ?></option>      
                                            <?php    }                                          
                                        } ?>
                                      </select>
                                       </td>


                             <td>
                              <input type="text"  name="dosis[]" value="<?php 
                              if( isset( $detf[$ii]['dosis'] ) ){
                                echo( number_format((float)  $detf[$ii]['dosis'] , 2, '.', '')  );
                              }else{
                                echo("1");
                              } ?>" style="text-align:right;"  pattern="^[0-9]+([0-9]+)?$" placeholder="Dosis" class="form-control dosis numbersOnly enterpass  enterkey" />
                             </td>
                             <td>
                              <input type="text"  name="sugerido[]" value="<?php 
                              if( isset( $detf[$ii]['cant_sugerida'] ) ){
                                   echo(number_format((float)  $detf[$ii]['cant_sugerida'] , 2, '.', '') );
                              }else{
                                echo("1");
                              }?>" style="text-align:right;"  pattern="^[0-9]+([0-9]+)?$" placeholder="Cantidad sugeridad" class="form-control sugerido numbersOnly enterpass enterkey" />
                            </td>

                                      

                                      <td class="qty">
                                        <input  name="cantidad[]"   type="text"    value="<?php echo($detf[$ii]['cantidad'])?>" pattern="^[0-9]+([0-9]+)?$" style="text-align:center;" placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" autocomplete="off"/>
                                      </td>
                                      <td>
                                        <input  name="precio[]"     type="text" value="<?php echo( number_format((float)  $detf[$ii]['precunit'] ,  2, '.', '')  )?>"   style="text-align:right;"  placeholder="precio" class="form-control precio enterkey" autocomplete="off" />
                                      </td>
                                      <td>
                                        <input  name="descuento[]"  type="text" 
                                        value="<?php echo( number_format((float)  $detf[$ii]['descuento'] , 2, '.', '')  )?>"   style="text-align:right;"  placeholder="Descuento" class="form-control " readonly /> 
                                      </td>


            <td>
              <input type="text" name="tax[]" value="<?php echo( number_format((float)  $detf[$ii]['monto_imp'] , 2, '.', '')  )?>"  style="text-align:right;" readonly="readonly"  placeholder="Impuesto" class="form-control " />
            </td>


                                      <td>
                                        <input  name="Subtotal[]"   type="text" value="<?php echo( number_format((float)   ($detf[$ii]['cantidad']*$detf[$ii]['precunit'])- $detf[$ii]['descuento']  , 2, '.', '')  )?>"  style="text-align:right;"   placeholder="Subtotal" class="form-control name_list subtotal" readonly />
                                      </td>
                                      <td><button name="remove"       type="button"   class="btn btn-danger btn_remove" id="<?php echo($ii)?>">X</button></td>
                   
                                  </tr>
                                 
                                <?php   }    }
                               ?>




                              </tbody>  
                         </table>  
                        
                       </div>  
                    </div>  
                

                    
                    <div class="row">
                      
                      <div class="col-xs-6">
                        <p class="lead">Payment Methods:</p>
                        <img src="../../img/visa.png" alt="Visa">
                        <img src="../../img/mastercard.png" alt="Mastercard">
                        <img src="../../img/american-express.png" alt="American Express">
                       
                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;"></p>
                      </div>
                       
                      <div class="col-xs-6">
                        

                        <div class="table-responsive">
                          <table class="table">
                            <tbody>
                              <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td><input type="text" id="tlsubototal" name="tlsubototal" value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $res[0]['subtotal']  ,  2, '.', '')  ) ; ?>" class="form-control name_list" style="text-align:right;" readonly /> </td>
                              </tr>
                               <tr>

                                <?php

                                  if (sizeof($res)>0 && $res[0]['alicuota']>0) {
                                     
                                    ?>
                                      <th><input type="text" id="discprcntg" name="discprcntg" value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $res[0]['Alicuota']  ,  2, '.', '')  ) ; ?>"  style="text-align:right;" placeholder="Discount %" class="form-control name_list discount" <?php ?> readonly/></th>
                                <td><input type="text" id="discamount" name="discamount" value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $res[0]['descuento']  ,  2, '.', '')  ) ; ?>" style="text-align:right;" placeholder="Discount $" class="form-control name_list discount" /></td>

                                   <?php
                                  } else {
                                      
                                    ?>
                                  <th>
                                    <input type="text" id="discprcntg" name="discprcntg" value=""  style="text-align:right;" placeholder="Discount %" class="form-control name_list discount" <?php ?> autocomplete="off" readonly/>
                                  </th>
                                <td>
                                  <input type="text" id="discamount" name="discamount" value="" style="text-align:right;" placeholder="Discount $" class="form-control name_list discount" autocomplete="off" /></td>
                                 <?php    
                                  }
                                  

                                ?>
                              </tr>


                              <tr>
                                <th>Subtotal - Descuento</th>
                                <td><input type="text" id="stmdct" name="stmdct" value="<?php if(sizeof($res)>0 ) echo( number_format( (float)  $res[0]['subtotal'] -(float)$res[0]['descuento']  ,  2, '.', '')  ) ; ?>" style="text-align:right;"  class="form-control name_list stmdct" readonly /></td>                       
                              </tr>

                              <tr>
                                <th>Tax (10.5% + 1%)  : <input checked data-toggle="toggle" data-size="small" type="checkbox" id="taxapply" name="taxapply" ></th>
                                  <td >
                                    <input type="text"  id="tlimpuesto" name="tlimpuesto" value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $res[0]['TotImpuesto']  ,  2, '.', '')  ) ; ?>" style="text-align:right;" class="form-control name_list" readonly />

                                  </td>
                               </tr>

                               <tr>
                                <th>Shipping:</th>
                                <td><input type="text" id="shipping" name="shipping" value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $res[0]['monto_flete']  ,  2, '.', '')  ) ; ?>" placeholder="shipping $" class="form-control name_list shipping" style="text-align:right;" /></td>
                              </tr>
                              



                              <tr>
                                <th>Total:</th>
                                <td><input type="text"  id="tltotal" name="tltotal"  value="<?php if(sizeof($res)>0 ) echo( number_format((float)  $total + (float)  $res[0]['monto_flete'] ,  2, '.', '')  ) ; ?>" style="text-align:right;" class="form-control name_list" readonly /></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                       
                    </div>
                     
                    <div class="row no-print">
                          <div class="col-md-12 ">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                     <input type="button" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" style="" /> 
                                            <span class="btn-separator"></span>
                                      <input type="button" name="deleteall" id="deleteall" class="btn btn-warning delete-row" value="Eliminar todo"   style="float:Left;" />
                                             <span class="btn-separator"></span>
                                    <button type="button" id="btnpaymnt" class="btn btn-success " <?php echo ($disabled)?> data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button>
                                           <span class="btn-separator"></span>
                                    <input type="button" name="savei" id="savei" class="btn btn-info" value="Guardar"  />
                                     <span class="btn-separator"></span>
                                     <input type="button" name="printfac" id="printfac" class="btn btn-info" value="Imprimir" style="display:none;" />
                                </div>
        


						<!-- 	      <div  class="form-group col-sm-2"> 
                                        <input type="button" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" style="" />  
                                  </div>


                                  <div class="form-group col-sm-2">
                                        <input type="button" name="deleteall" id="deleteall" class="btn btn-warning delete-row" value="Eliminar todo"   style="float:Left;" />
                                  </div>


								  <div class="form-group col-sm-2" style="float: right;margin-right: 171px;">
										<button type="button" id="btnpaymnt" class="btn btn-success pull-right " <?php //echo ($disabled)?> data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button>     
								  </div>



                                  <div  class="form-group" style="float: right;margin-right: 171px;">
                                        <input type="button" name="savei" id="savei" class="btn btn-info" value="Guardar" style="display:none;" />  
                                  </div>
                               -->
							</div>
                          </div>
                     </div>
              </form>
             </div>  

             <div id="menu1" class="tab-pane fade">              
                <div class="container-fluid">
                    <div class="tblcontrol" style="padding-left: 10px; padding-right: 10px;">
                        <?php// include('list.php');?>
                    </div>        
                </div>
             </div>
        </div>
        
      </body>  
 </html>  

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js"></script>
<script src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-switch.min.js"></script>

<script src="../../js/jquery.bootpag.min.js"></script>

<script src="../../js/msprodsret.js?v=1332"></script>
<script src="../../js/msprodsretpayments.js"></script>
<!-- <script src="../../js/listprodinvoice.js"></script> -->
<!-- <script src="../../js/productosvoid.js"></script> -->
<script src="../../js/getsalesrecord.js"></script>



<script>  

 </script>