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

//var_dump($_GET);
$urlevento="";
if(isset($_POST['codclien'])){
   $cod_clien=$_POST['codclien'];
   $urlevento=$_POST['urlevento'];
}

$medcontroller = new ClientesController();



$query="SELECT *  FROM MClientes  where fallecido=0 order by nombres ";
$where='fallecido=0';
if (isset($_POST['all'])) {
    $query="SELECT *  FROM MClientes  order by nombres ";
    $where="";
}

$limit=25;
$response= $medcontroller->paginationUDF($limit,10,"pagination",$query,$where);

$result=$response[1];
$linksPages=$response[0];

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
        <h1>Pacientes</h1>


 <?php
      if( empty($result) ) {
    if (isset($_POST['idcustomer'])) {
        $fStr=$_POST['idcustomer'];
        print( '
        <div class="container">
            <p class="item  error">No se encontraron resultados para </p> <h3>'.$fStr.'</h3>
        </div>
        <form method="POST">
            <input class="button btn btn-primary" type="submit" value="Ok">
        </form>
    ');

    }else{
            print( '
        <div class="container">
            <p class="item  error">No hay Clientes</p>
        </div>
        <form method="POST">
            <input type="hidden" name="r" value="customers-add">
            <input class="button  btn  add btn-success" type="submit" value="Agregar">
        </form>
    ');
    }

} else {
    print( '<div class="row uno"> 
             <div class="form-group col-sm-4">
             <br/>'
                .$linksPages .
            '</div>
             <div class="form-group col-sm-8">'
                .$filtro.
            '</div>
        </div>
    ');
?>
  
    <div class="item">
        <table class="table table-hover table-condensed">
        <thead class="table-success">
            <tr>
                <th>#</th>
                <th>Médico</th>
                <th>Id</th>             
                <th colspan="1">
                    <form method="POST" action="medicos_add.php">
                        <input type="hidden" name="r" value="medicos-add">
                        <input class="button  btn  add btn-success" type="submit" value="Agregar">
                    </form>
                </th>
                <th>
                    <form method="POST">
                        <?php
                          if (!isset($_POST['all'])) {    ?>
                              <input type="hidden" name="all" value="medicos-all">
                              <input class="button  btn  add btn-warning" type="submit" value="Todos">
                        <?php 
                          }else{   ?>
                              <input class="button  btn  add btn-warning" type="submit" value="Activos">
                        <?php    }   ?>


                    </form>    
                </th>
            </tr>
            </thead>
    <?php
    for ($n=0; $n < count($result); $n++) { 
        ?>
        
            <tr>
                <td><?php echo $n+1;?> </td> 
                <td> <?php echo($result[$n]['nombres']) ; ?></td>
                <td> <?php echo($result[$n]['Historia']);  ?> </td>                
                <td>
                    <form method="POST"  action="medicos_edit.php">
                        <input type="hidden" name="r" value="medicos-edit">
                        <input type="hidden" name="id" value=<?php echo($result[$n]['Id']); ?>>
                        <input class="button btn btn-primary edit" type="submit" value="Editar">
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:block" action="medicos_del.php">
                        <input type="hidden" name="del" value="medicos-delete">
                        <input type="hidden" name="Id" value=<?php echo($result[$n]['Id']); ?>>
                        <input type="hidden" name="activo" value=<?php echo($result[$n]['activo']); ?>>
                        <?php if ($result[$n]['activo']=='1') {                               
                             ?>
                              <input class="button btn btn-success delete" type="submit" value="Activo">
                            <?php 
                        }else{
                             ?>
                            <input class="button btn btn-danger delete" type="submit" value="Inactivo">    
                            <?php 
                        } ?>
                        
                    </form>
                </td>
            </tr>
   <?php         
        
    }
?>
   
        </table>
<?php            
   
}

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