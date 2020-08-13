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

require('../../controllers/ClientesController.php');

require('../../controllers/MFacturaController.php');
require('../../controllers/CMA_MFacturaController.php');
require('../../controllers/MSSMFactController.php');

require('../../controllers/MnotacreditoController.php');
require('../../controllers/CMA_MnotacreditoController.php');
require('../../controllers/MSSMDevController.php');



$urlevento="";
if(isset($_POST['id'])){
   $Id=$_POST['id'];   
}

$clientescontroller = new ClientesController();

$mfacturacontroller = new MFacturaController();
$cmamfacturacontroller = new CMA_MFacturaController();
$mssfactcontroller = new MSSMFactController();

$mnotacreditocontroller = new MnotacreditoController();
$cmamnotacreditocontroller = new CMA_MnotacreditoController();
$mssdevcontroller = new MSSMDevController();



if (!isset($_POST['crud']) )
   // $medico = $clientescontroller->read($Id);
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
        <h1>Fusion de records</h1>

        <?php if (!isset($_POST['crud']) ) { ?>

            <form method="POST" class="item"   autocomplete="off" id="formadd">
                <div class="container"> 
                    <div class="row">
                    <div class="input-field form-group col-sm-6">
                        <label for="record_estable">Record Estable</label>
                        <input type="text" name="record_estable"  class="form-control enterkey " autocomplete="off" required>
                        
                    </div>

                    <div class="input-field form-group col-sm-6">
                        <label for="record_noestable">Record a Eliminiar</label>
                        <input type="text" name="record_noestable" class="form-control enterkey "  autocomplete="off" required>
                    </div>
                </div> 
        
        
                <div class="input-field ">
                    <input  class="button  add" type="submit" value="Fusionar">
                    <input type="hidden" name="id" value="<?php echo($medico[0]['Id']); ?>" >
                    <input type="hidden" name="Codmedico" value="<?php echo($medico[0]['Codmedico']); ?>" >
                    <input type="hidden" name="crud" value="set">
                </div>

                </div>
                
            </form>
        <?php }
            else  if (isset($_POST['crud']) ) {

                $recod_e= $_POST['record_estable'];
                $recod_i= $_POST['record_noestable'];

                $query= "select codclien from MClientes where Historia = '$recod_e' ";
                $r_e= $clientescontroller->readUDF($query); // record estable
                $query= "select codclien from MClientes where Historia = '$recod_i' ";
                $r_i= $clientescontroller->readUDF($query); // record inestable
                
                              
                          
                $set_data = array(
                    'codclien' =>  $r_e[0]['codclien']
                );

                $where_data = array(
                    'codclien' =>  $r_i[0]['codclien']
                );
                
                # NO TOCAR
                $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data

                );

                

                $mfacturacontroller->update($array_edit);
                $cmamfacturacontroller->update($array_edit);
                $mssfactcontroller->update($array_edit);
                $mnotacreditocontroller->update($array_edit);
                $cmamnotacreditocontroller->update($array_edit);
                $mssdevcontroller->update($array_edit);





                    $template = '
        <div class="container">
            <p class="item  add">Record funsionado! <b>%s</b> salvado</p>
        </div>
        <script>
            window.onload = function () {
                window.location.href = "cliente_fusionar.php";
            }
        </script>
    ';

    printf($template, $_POST['last_name']);

              }
              ?>

 <?php


?>
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
<script>  

</script>