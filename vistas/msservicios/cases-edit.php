<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> 
<link rel="stylesheet" href="./public/css/estilos.css">
<!-- <link rel="stylesheet" href="./public/libs/materialize.min.css"> -->
<?php
  header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  header("Connection: close");
   require('./controllers/MasterInvoiceController.php');
   require('./controllers/DetailsInvoiceController.php');
   require('./controllers/ProductsController.php');
//if( $_POST['r'] == 'cases-edit' && $_SESSION['role'] == 'Admin' && (isset($_POST['crud']) and $_POST['crud']  == 'set') ) {   

  require('./controllers/DeadlinesController.php');
  $deadlinescontroller = new DeadlinesController();
  $plazos = $deadlinescontroller->read();

  if( $_POST['r'] == 'cases-edit' && (isset($_POST['crud']) and $_POST['crud']  == 'set') ) {     
      
        $masterinvoicecontroller = new MasterInvoiceController();
        $dicontroller = new DetailsInvoiceController();
       
        $customer = explode(',',  $_POST['idassoc']);
        $customerid =$customer[0];
        $dicontroller->deleteCaso($_POST['caso'],$customerid);
    
        $new = array(
              'caso'    => $_POST['caso'], 
              'idcustomer'=> $_POST['idcustomer'], 
              'prod_id' => $_POST['prod'], 
              'idassoc' => $_POST['idassoc'],
              'paciente'=> $_POST['paciente'], 
              'record'  => $_POST['record'],       
              'fechaser'=> $_POST['fechaser'],
              'date'    => $_POST['fecha'],
              'bc'      => $_POST['barcode'],
              'plazo'   => $_POST['plazo']

            );

       $dicontroller->create($new);
       $masterinvoicecontroller->update($new);
       $template = '
         <div class="container">
            <p class="item  add">Caso # <b>%s</b> fue editado</p>
         </div>
         <script>
            window.onload = function () {
               reloadPage("cases")
            }
         </script>
      ';    
      printf($template,$_POST['caso']);

} else if( $_POST['r'] == 'cases-edit' && !isset($_POST['crud']) ){
  
  $micontroller = new MasterInvoiceController();
  $dicontroller = new DetailsInvoiceController();
  $productscontroller = new ProductsController(); //read
  $prod= $productscontroller->read();
  $res = $micontroller->readCasos($_POST['caso'], trim($_POST['customerid']) , trim($_POST['barcode']) );
  $resd =$dicontroller->readCasos($_POST['caso'], trim($_POST['customerid']) , trim($_POST['barcode']) );

  $infoCustomer = array();
  $record=$res[0]['record'];
  array_push($infoCustomer, $res[0]['customerid'],str_replace(" ","_",$res[0]["customername"]),$res[0]['record']);

?>


<nav>
   <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Edición Caso</a>
      <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Profile</a>
      <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</a>
   </div>
</nav>
<div class="tab-content" id="nav-tabContent">
   <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
      <!-- inv -->
      <div class="" >
         <form name="invoice" id="invoice" method="POST">
            <div class="row">


            
               <div class="form-group col-sm-1">  
                  <label class="control-label">ID</label>     
                  <input type="text" id="idcustomer" name="idcustomer"  class="form-control idcustomer" value="<?php echo( $res[0]['phone'])  ?>" placeholder="ID" required readonly >
               </div>
                <div id="dividassoc" class="form-group col-sm-2">
                  <label class="control-label">Cliente</label> 
                  <select id="idassoc" name="idassoc" class="form-control enterkey" required readonly >
                      <?php?>
                     <option value="<?php echo(implode(",", $infoCustomer)); ?>" selected ><?php echo( $res[0]['customername']) ?></option>
                  </select>
               </div>
               <div id="divcaso" class="form-group col-sm-1"> 
                   <label class="control-label" for="caso" >Caso</label>                       
                   <input type="text" id="caso" name="caso"  class="form-control enterkey caso" placeholder="# Caso" value="<?php echo( $res[0]['caso'])  ?>" required readonly>
               </div>
               <div id="divpaciente" class="form-group col-sm-2">
                    <label class="control-label" for="paciente" >Paciente</label>   
                    <input type="text" id="paciente" name="paciente"  class="form-control enterkey paciente" placeholder="Paciente" value="<?php echo( $res[0]['patientname']) ?>" required>
               </div>
               <div id="divrecord" class="form-group col-sm-2">
                    <label class="control-label" for="record" >Record</label>   
                    <input type="text" id="record" name="record"  class="form-control enterkey record" placeholder="# Record / Factura" value="<?php echo( $res[0]['record']) ?>" required>
               </div>


              <div id="dividplazo" class="form-group col-sm-2">
                  <label class="control-label">Vencimiento</label> 
                  <select id="plazo" name="plazo" class="form-control enterkey" required >
                    <option value="0" selected > Vencimiento </option>
                    <?php 
                        for ($x = 0; $x < count($plazos); $x++) {

                          if ( $res[0]['plazo']==$plazos[$x]['plazo']) {
                               echo  '<option value='.$plazos[$x]['plazo'].' selected >'.$plazos[$x]['descripcion'].'</option>';
                          } else {
                               echo  '<option value='.$plazos[$x]['plazo'].' >'.$plazos[$x]['descripcion'].'</option>';
                          }
                           
                         
                        } 
                    ?>
                    
                  </select>
               </div>



               <div class="form-group col-sm-2">
                    <?php
                       $fecha_caso= explode('-',$res[0]['date']);
                       $casedate=$fecha_caso[1].'/'.$fecha_caso[2].'/'.$fecha_caso[0];
                    ?>
                    <label class="control-label" for="fecha" >Fecha</label>
                    <input type="text" id="fecha" name="fecha"  class="form-control fecha" value="<?php echo( $casedate ) ?>" required readonly> 
               </div> 
               
 
            </div>
            <div class="alert" style='display:none'>
               <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
               <strong>Advertencia!</strong> Este paciente ya tiene al menos una factura hoy.
            </div>
            <div class="container-fluid">
               <div class="container-fluid">
                  <div class="table-responsive">
                     <table class="table  table-hover table-inverse table-condensed" id="dynamic_field">
                        <thead class='thead-inverse table-success'>
                           <tr>
                              <th class='titleatn' >Comentario</th>        
                              <th class='titleatn' >Fecha Servicio</th>
                              <th class='titleatn' ><button type="button" name="add" id="add" class="btn btn-success add enterkey">Add More</button></th>
                           </tr>
                        </thead>
                        <tbody>

                           <?php 
                              for ($i=0; $i <count($resd) ; $i++) { ?>
                                    <tr id= <?php echo($i); ?> >

                                      <td >
                                        <select id="addr<?php echo($i); ?>" name="prod[]" class="form-control products enterpass enterkey" required>
                                          <option value="">Select Customer</option>
                                   <?php 
                                       $found=false;
                                       for ($j=0; $j <count($prod) ; $j++) { 
                                         if ($prod[$j]['name']==$resd[$i]['documento']) {  
                                            $found=true;                                 
                                            $data = array();
                                            array_push($data, str_replace(" ","_",$prod[$j]["name"]) ); 
                                     ?>       
                                            <option value=<?php echo(implode(",", $data)); ?> selected ><?php echo($prod[$j]['name']); ?></option>
                                     <?php                                                 
                                         } else {
                                            $data = array();
                                            array_push($data,str_replace(" ","_",$prod[$j]["name"])); 
                                      ?>  
                                          <option value=<?php echo(implode(",", $data)); ?>  ><?php echo($prod[$j]['name']); ?></option>
                                     <?php 
                                         }                           
                                       }  

                                       if(!$found){
                                            $data = array();
                                            array_push($data, str_replace(" ","_",$resd[$i]['documento']) ); 
                                        ?>
                                         <option value=<?php echo(str_replace(" ","_",$resd[$i]["documento"])); ?> selected  ><?php echo($resd[$i]['documento']); ?></option>
                                       <?php  }                                          
                                        
                                        ?> 
                                           <option value="nl"> Nuevo </option>  
                                         </select><input list="browsers" name="nuevo" id="inptaddr<?php echo($i); ?>"  class="form-control nl" style="display:none">
                                      </td>

                              <td class="fecs">
                                <?php
                                     $fecha_ser= explode('-',$resd[$i]['servicedate']);
                                     $servicedate=$fecha_ser[1].'/'.$fecha_ser[2].'/'.$fecha_ser[0];
                                ?>
                                <input type="text" name="fechaser[]" id="fechaser<?php echo($i);?>" value=<?php echo($servicedate); ?>     class="form-control fechaser enterkey" />
                              </td>
                               <td><button type="button" name="remove" id="<?php echo($i); ?>" class="btn btn-danger btn_remove">X</button></td> 
                           </tr>
                             <?php   } 
                           ?>
                             

                        </tbody>
                     </table>
                  </div>
               </div>
 
               <div class="row container-fluid">

                  <div class="row clearfix" style="margin-top:20px">
                     <div class="pull-right">
                        <table class="table table-bordered table-hover" id="tab_logic_total">
                           <thead>
                              <tr>
                              </tr>
                           </thead>
                           <tbody>

                              <tr>
                              </tr>

                           </tbody>
                        </table>
                     </div>
                  </div>
               
               </div>
             
               <div class="row no-print">
                  <div class="col-xs-12">
 
                  </div>
               </div>
               <div  class="form-group" style="float: right;margin-right: 171px;margin-top: -36px;">
                  <div class="col-sm-2">

                     <input type="hidden" name="r" value="cases-edit">
                     <input type="hidden" name="crud" value="set">
                     <input type="hidden" name="id" value= "%s"> 
                     <input type="hidden" name="barcode" value="<?php echo( $res[0]['barcode'])  ?>">
                     <input type="submit" name="submit" id="submit" class="btn btn-info" value="Guardar" />  
                  </div>
               </div>
               <div  class="form-group">
                  <div class="col-sm-2">
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
   <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">...</div>
   <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">...</div>
</div>
<div id="fade">
   <div class="drawing" id="loading">
      <div class="loading-dot"></div>
   </div>
</div>
<?php 
 } else {
   $controller = new ViewController();
   $controller->load_view('error401');
}
 
?>
 <script src="./public/js/jquery-3.3.1.min.js" ></script>
 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script> 
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
 <script src="./public/js/JsBarcode.all.min.js"></script>
 <script src="./public/js/invoiceprint.js" ></script>

<script src="./public/js/invoicingedit.js"></script>
<script>
   $( document ).ready(function() {  
     $(".navbar").find(".active").removeClass("active");    
     $("#cases").addClass("active")
  })
</script>