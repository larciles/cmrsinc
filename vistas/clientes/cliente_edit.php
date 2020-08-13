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
require('../../controllers/MedicosController.php');

//var_dump($_GET);
$urlevento="";
if(isset($_POST['id'])){
   $Id=$_POST['id'];   
}

$medcontroller = new MedicosController();

if (!isset($_POST['crud']) )
    $medico = $medcontroller->read($Id);

  


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
        <h1>Médicos Editar</h1>

        <?php if (!isset($_POST['crud']) ) { ?>

            <form method="POST" class="item"   autocomplete="off" id="formadd">
                <div class="row">
                    <div class="input-field form-group col-sm-6">
                        <label for="name">Nombre</label>
                        <input type="text" name="name"  class="form-control enterkey nombre" value="<?php echo($medico[0]['nombre']); ?>" autocomplete="off" required>
                        
                    </div>

                    <div class="input-field form-group col-sm-6">
                        <label for="last_name">Apellido</label>
                        <input type="text" name="last_name" class="form-control enterkey apellido" value="<?php echo($medico[0]['apellido']); ?>" autocomplete="off" required>
                    </div>
                </div> 
        
        
                <div class="input-field ">
                    <input  class="button  add" type="submit" value="Guardar">
                    <input type="hidden" name="id" value="<?php echo($medico[0]['Id']); ?>" >
                    <input type="hidden" name="Codmedico" value="<?php echo($medico[0]['Codmedico']); ?>" >
                    <input type="hidden" name="crud" value="set">
                </div>
            </form>
        <?php }
            else  if (isset($_POST['crud']) ) {
                          
                $set_data = array(
                    'nombre' =>  $_POST['name'], 
                    'apellido' =>  $_POST['last_name']
                );

                $where_data = array(
                      'Id' =>   $_POST['id'],
                      'Codmedico'=>   $_POST['Codmedico'],
                );

                $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data

                );

                $medcontroller->update($array_edit);


                    $template = '
        <div class="container">
            <p class="item  add">Médico Agregado al sistemas <b>%s</b> salvado</p>
        </div>
        <script>
            window.onload = function () {
                window.location.href = "index.php";
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