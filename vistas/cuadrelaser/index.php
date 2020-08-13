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
    $access  = $_SESSION['access'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

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

        <style type="text/css">
           #div1.linea{
            float:left;
             
            }
            #div2{
            float:left;
            }
            #div33{
            clear:both;
           
            }
            .enterkey{
                margin-right: 50px;
                width: 70px;
            }
            .addon-end{
                width: 100px;
            }
            .hasDatepicker{

            }
          </style>


      </head>  
      <body>  
        <input  id="access" type="hidden" name="access" value=<?php echo $access;?>>
        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 

        <ul class="nav nav-tabs">
             
            <li class="active"><a data-toggle="tab" href="#home">Cash</a></li>
            <li><a data-toggle="tab" href="#menu1">Reporte</a></li>
            <!-- <li><a data-toggle="tab" href="#menu2">Menu 2</a></li> -->
        </ul>
        <div class="tab-content">
               <div id="wait" style="display:none;width:69px;height:89px;position:absolute;top:50%;left:50%;padding:2px;"><img src='../../img/demo_wait.gif' width="64" height="64" /><br></div>

            <div id="home"  class="tab-pane fade in active container">
              <?php include 'cash.php';?>    
            </div>

            <div id="menu1" class="tab-pane fade">
              <?php include 'consultassalesdetalle.php';?>   
            </div>
            <div id="menu2" class="tab-pane fade">
              <h3>Menu 2</h3>
              <p>Some content in menu 2.</p>
            </div>

        </div>

        <div>
            <article>
            </article>
        </div>
        <div>
            <article>
            </article>
        </div>
  

        <footer></footer>
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
<script src="../../js/cuadrelaser.js?v=0714"></script>

<script src="../../js/detallecuadrelaser.js"></script>


<script src="../../js/jquery.tablesorter.min.js"></script>


 <script>  

 </script>