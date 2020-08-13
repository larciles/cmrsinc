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
require('../../controllers/CMA_MFacturaController.php');
require('../../controllers/CMA_DFacturaController.php');
require('../../controllers/MInventarioController.php');
require('../../controllers/TipoprecioController.php');
require('../../controllers/ClientesController.php');
require('../../controllers/MedicosController.php');
require('../../controllers/ExclusivosController.php');
require('../../controllers/CMA_DnotacreditoController.php');
require('../../controllers/CMA_MnotacreditoController.php');



$mediacontroller = new MediosController();

$cmamfacturacontroller = new CMA_MFacturaController();

$cmadfacturacontroller = new CMA_DFacturaController();

$cminventariocontroller = new MInventarioController();

$tipopreciocontroller = new TipoprecioController();

$clientescontroller = new ClientesController();

$medicoscontroller = new MedicosController();

$cmaDnotacreditoController= new CMA_DnotacreditoController();
$cmaMnotacreditoController= new CMA_MnotacreditoController();


$factura="";
$codmedico="";
$disabled='disabled';
$subtotal="";

$detfLen=0;
$long=0;

$fecha=DATE('m/d/Y');

if (isset($_GET['return']) ) {
   $numfactu=$_GET['numero'];
   $res=$cmamfacturacontroller->readUDF("Select *, REPLACE(CONVERT(CHAR(15), fechafac, 101), '', '-') AS fecha from cma_mfactura where numfactu='$numfactu' " );
   $long=0;
   if (is_array($res)) {
      $long=sizeof($res);
   }
   if ( sizeof($res)>0 ) {
      $factura=$res[0]['numfactu'];
      $codclien=$res[0]['codclien'];
      $codmedico=$res[0]['codmedico'];
      $total=$res[0]['total'];
     // $fecha=$res[0]['fecha'];
      $media=$res[0]['medios'];

      $porcentaje= $res[0]['Alicuota'];
      $descuento = $res[0]['descuento'];
      $subtotal=$res[0]['subtotal'];


      $detf = $cmadfacturacontroller->readUDF("Select * from cma_dfactura where numfactu='$factura' " );
      $detfLen=sizeof($detf);

   }
}elseif (isset($_GET['edicion']) || isset($_GET['?edicion'])) {
  $return=$_GET['numero'];
  $res=$cmaDnotacreditoController->readUDF("Select *, REPLACE(CONVERT(CHAR(15), fechanot, 101), '', '-') AS fecha from CMA_Mnotacredito where numnotcre='$return' " );
  $long=0;
  if (is_array($res)) {
      $long=sizeof($res);
  }

  if ( sizeof($res)>0 ) {
      $return=$res[0]['numnotcre'];
      $codclien=$res[0]['codclien'];
      $codmedico=$res[0]['codmedico'];
      $fecha=$res[0]['fechanot'];
      $media=$res[0]['medios'];
      $total=$res[0]['totalnot'];

      $subtotal=$res[0]['subtotal'];

      $porcentaje= $res[0]['alicuota'] ;
      $descuento = $res[0]['descuento'];

      $detf =$cmaMnotacreditoController->readUDF("Select * from CMA_Dnotacredito where numnotcre='$return' " );
      $detfLen=sizeof($detf);
   }  
}


if ( $long>0 ) {
    

    $invetario=$cminventariocontroller->readUDF("select * from MInventario where Prod_serv in('s','c','f')  and cod_grupo='004' and cod_subgrupo IN ('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and activo=1 order by orden, desitems " );

    $baremo=$tipopreciocontroller->readUDF("SELECT * from  tipoprecio order by codtipre");

    $cliente=$clientescontroller->readUDF("SELECT * from  MClientes where codclien='$codclien' ");

    $medico=$medicoscontroller->readUDF("SELECT *, CONCAT(nombre,' ', apellido)  medico from  Mmedicos where Codmedico='$codmedico' ");

    $media=$mediacontroller->readUDF("SELECT * from  Medios where codigo='$media' ");

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
                 <?php include '../layouts/payform.php';?>    
            </div>
        </header> 
        <?php include('recordconsultasmodal.php'); ?> 

                  <form name="facturacion" id="facturacion" method="post">  

                    <input  id="frmsubtotal" type="hidden" name="frmsubtotal">
                    <input  id="frmtax" type="hidden" name="frmtax">
                    <input  id="frmshipping" type="hidden" name="frmshipping">
                    <input  id="frmtotal" type="hidden" name="frmtotal">
                    <input  id="taxilicuota" type="hidden" name="taxilicuota">
                    <input  id="numfactu" type="hidden" name="numfactu" value="<?php if (isset($_GET['return']) ) echo( $numfactu ) ;?>">
                    <input  id="delallrecords" type="hidden" name="delallrecords">
                    <input  id="desxmonto" type="hidden" name="desxmonto">
                    <input  id="urlevento" type="hidden" name="urlevento" value=<?php echo $urlevento;?>>
              
                    <input  id="md" type="hidden"   value=<?php echo $codmedico;?>>
                    <input  id="edicion" name="edicion" type="hidden"   value="false">

    

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
                            
                              <label class="control-label">Return #</label> 
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

                    <div class="container-fluid">  
                
                    <div class="container-fluid">  
                      <div class="table-responsive yes">
                         <table class="table  table-hover table-condensed table-striped" id="dynamic_field">                                      
                              <thead class='thead' style='background-color:#DA6465;'>
                                  <tr>
                                      <th class='titleatn' >Producto</th>
                                      <th class='titleatn' >Lista</th>               
                                     

                                      <th class='titleatn' >Cantidad</th>
                                      <th class='titleatn' >Precio</th>
                                     
                                      <th class='titleatn' >Descuento</th>
                                      
                                      <th class='titleatn' >Subtotal</th>
                                      <th class='titleatn' ><button type="button" name="add" id="add" class="btn btn-success add  enterkey">Add More</button></th>                                                
                                  </tr>
                              </thead>
                              <tbody>  

                                <?php 
                                if (isset($detf) && $detfLen >0 ) {
                                 // $disabled='';
                                
                                for ($ii=0; $ii <$detfLen ; $ii++) {      ?>


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
                                <td>
                                  <input type="text" id="tlsubototal" name="tlsubototal" value="<?php if( $long>0 ) echo( number_format((float)  $subtotal  ,  2, '.', '')  ) ; ?>" class="form-control name_list" style="text-align:right;" readonly /> 
                                </td>
                              </tr>
                               <tr>

                                <?php

                                  if ($long>0 && $porcentaje>0) {
                                     
                                    ?>
                                      <th>
                                        <input type="text" id="discprcntg" name="discprcntg" value="<?php if( $long>0 ) echo( number_format((float) $porcentaje,2, '.', '')  ) ; ?>"  style="text-align:right;" placeholder="Discount %" class="form-control name_list" <?php ?> readonly/>
                                      </th>
                                <td>
                                  <input type="text" id="discamount" name="discamount" value="<?php if($long>0 ) echo( number_format((float)  $descuento ,  2, '.', '')  ) ; ?>" style="text-align:right;" placeholder="Discount $" class="form-control name_list" />
                                </td>

                                   <?php
                                  } else {
                                      
                                    ?>
                                  <th>
                                    <input type="text" id="discprcntg" name="discprcntg" value=""  style="text-align:right;" placeholder="Discount %" class="form-control name_list" <?php ?> autocomplete="off" readonly/>
                                  </th>
                                <td>
                                  <input type="text" id="discamount" name="discamount" value="" style="text-align:right;" placeholder="Discount $" class="form-control name_list" autocomplete="off" />
                                </td>
                                 <?php    
                                  }
                                  

                                ?>


                               


                              </tr>

                              <tr>
                                <th>Total:</th>
                                <td><input type="text"  id="tltotal" name="tltotal"  value="<?php if($long>0 ) echo( number_format((float) $total ,  2, '.', '')  ) ; ?>" style="text-align:right;" class="form-control name_list" readonly /></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                       
                    </div>
                     
                    <div class="row no-print">
                       <div class="col-md-12 col-md-offset-3">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                     <input type="button" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" style="" /> 
                                            <span class="btn-separator"></span>
                                      <input type="button" name="deleteall" id="deleteall" class="btn btn-warning delete-row" value="Eliminar todo"   style="float:Left;" />
                                             <span class="btn-separator"></span>
                                    <button type="button" id="btnpaymnt" class="btn btn-success " <?php echo ($disabled)?> data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button>
                                           <span class="btn-separator"></span>
                                    <input type="button" name="savei" id="savei" class="btn btn-info" value="Guardar"  />
                                </div>
        
    <!--               <div class="col-xs-12">
                      <div class="col-sm-2">
                          <input type="button" name="deleteall" id="deleteall" class="btn btn-warning delete-row" value="Eliminar todo"   style="float:Left;" />
                      </div>
                    
                    <button type="button" id="btnpaymnt" class="btn btn-success pull-right " <?php //echo ($disabled)?> data-toggle="modal" data-target="#paymentsModal" data-whatever="@mdo"><i class="fa fa-credit-card"></i> Submit Payment</button> 
                    
                  </div>
                    </div>
                    <div  class="form-group" style="float: right;margin-right: 171px;margin-top: -36px;">
                    <div class="col-sm-2"> 
                        <input type="button" name="savei" id="savei" class="btn btn-info" value="Guardar"   />  
                    </div>
                    </div>
                    <div  class="form-group">
                    <div class="col-sm-2">
                          
                        <input type="button" name="nueva" id="nueva" class="btn btn-primary pull-left" value="Nueva factura" style=" margin-top: -47px;display:none" />  
                    </div> -->
                    </div>
                    </div>
              </form>
        
        
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

<script src="../../js/msserviciosret.js"></script>
<script src="../../js/msserviciospaymentsret.js"></script>

<script src="../../js/consultasvoid.js"></script>


<script>  

 </script>