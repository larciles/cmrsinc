

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
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

//var_dump($_GET);
require_once "../../controllers/MpagosController.php";
$consultas= new MpagosController();
$productos= new MpagosController();
$laser= new MpagosController();
$suero= new MpagosController();
$total = new MpagosController();



$fecha=DATE('m/d/Y');
if (isset($_GET["efechai"]) && $_GET["efechai"]!="") {

    $fecha=$_GET["efechai"];
    
    #CONSULTAS

    $query="SELECT   SUM( a.total) monto ,ISNULL ( d.desTipoTargeta , 'EXCENTO') modopago  , max(d.codforpa) codforpa
       FROM  VentasDiariasCMACST a  
       INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc  
       LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu  
       WHERE       a.fechafac ='$fecha' AND   a.cod_subgrupo = 'CONSULTA' and  a.statfact=3  and d.desTipoTargeta is  not null
       GROUP BY desTipoTargeta   Order by codforpa ";

    $c=$consultas->readUDF($query);   


    #PRODUCTOS
    $p=$productos->readUDF("SELECT sum(monto) monto, modopago,codforpa FROM VIEWPagosPR_W7  where fechapago='$fecha' and statfact=3  group by modopago,codforpa  order by codforpa");

    #LASER
    $query="SELECT   sum(a.monto) monto, a.modopago,a.codforpa 
     FROM     VIEWpagosPRMSS_W7 a  
     WHERE    a.statfact <> '2' AND a.id_centro = '3' AND  a.fechapago = '$fecha'  
     group by  a.modopago,a.codforpa  order by a.codforpa ";

     $l=$laser->readUDF($query);

     #SUERO
     $query="SELECT sum(monto) monto, modopago,codforpa FROM VIEWpagosPRCMACST_1  where fechapago='$fecha' and statfact=3 and id_centro = '2' and cod_subgrupo In('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') group by modopago,codforpa  order by codforpa";

     $s=$suero->readUDF($query);


     #TOTAL
     $query="SELECT sum(a.monto)  monto , a.modopago  FROM cuadrefinal_view a WHERE a.fechafac ='$fecha' group by a.codforpa, modopago order by codforpa";
     $t=$total->readUDF($query);
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
        <link rel="stylesheet" href="../../css/estiloscontrolc.css?v=20190919" />
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

                .enterkey:not([size]) {
                width: 10em; 
                }
                #totalscash{
                width: 15.2em; 
                }

                .addons{
                min-width: 5em; 
                text-align:right;
                }

                .r-addons{
                min-width: 8em; 
                text-align:right;
                }

                .shadow{
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                display: block;
                overflow: hidden;
                width: 100% ;
                height: 100% ;
                background-color: rgba(0, 0, 0); /* fall back */
                background-color: rgba(0, 0, 0, 0.7);
                /*background-image: url('images/preloading.gif');  /* path to your new spinner */
                background-repeat: no-repeat;
                background-size: 60px 60px;
                background-position: center center;

                }

                .loader {
                border: 16px solid #f3f3f3;
                border-radius: 50%;
                border-top: 16px solid #3498db;
                width: 120px;
                height: 120px;
                -webkit-animation: spin 2s linear infinite; /* Safari */
                animation: spin 2s linear infinite;
                }

                /* Safari */
                @-webkit-keyframes spin {
                0% { -webkit-transform: rotate(0deg); }
                100% { -webkit-transform: rotate(360deg); }
                }

                @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
                }


                .xspin{

                position: fixed;
                z-index: 999999;

                position: absolute;
                top: 50%;
                left: 50%;
                margin-top: -50px;
                margin-left: -50px;
                width: 100px;
                height: 100px;
                }​


          </style>


      </head>  
      <body>  

        <header>
            <div class="container-fluid ">
                 <?php include '../layouts/header.php';?>    
            </div>
        </header> 
        <div class="shadow">
            <div class="xspin">   
                   <div id="loader" class="loader"></div>
            </div>       
        </div>


        <div class="container-fluid">
            <form action="#" method="GET" id="cuadreform">
            <div class="row ">
                        <div class="col-sm-2" style="margin-top: 9px;">    
                        <label class="control-label"></label> 
                          <div class="form-group">  
                              <div class="input-group date" >
                              <input type="text" class="form-control" id="efechai" placeholder="MM/DD/YYYY"   name="efechai" value="<?php  echo( $fecha ); ?>" autocomplete="off">                    
                              <div class="input-group-addon"></div>
                             </div>
                          </div>
                    </div>  

                               <div class="form-group col-sm-2"  style="margin-top: 9px;">
                 <label class="control-label"></label> 
                <div class="form-group">  
                  <button type="submit"  class="btn btn-success  form-control" id="submit" value="Submit">OK</button>
                </div>
              </div>
            </div>

            <div class="row contenedor-cuadre">
                <div class="col-sm-3 cuadregd-consultas cuadre-comun">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-sm cuadreg-consultas consultas-color cuadre-comun">          
                        <thead>
                            <tr>
                                <th style="text-align:center;" colspan="2">Consultas</th>          
                            </tr>
                        </thead> 
                        <tbody> 
                            <?php 
                            if (isset($c)) {                                
                               $total_t_c=0;
                               $total_g_c=0;
                               if (sizeof($c)>0) {
                                 for ($i=0; $i <sizeof($c) ; $i++) { 
                                     print("    
                                       <tr>                                
                                            <th style= 'width:50%'>".$c[$i]['modopago']."</th>
                                            <td style='text-align:right;' >". number_format((float) $c[$i]['monto'],2)."</td>
                                        </tr>
                                      ");

                                     if ($c[$i]['modopago']!="CASH" && $c[$i]['modopago']!="CHECK") {
                                         $total_t_c=$total_t_c+$c[$i]['monto'];
                                     }
                                     $total_g_c=$total_g_c+$c[$i]['monto'];
                                 }
                                 if ($total_t_c>0) {
                                      print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Tarjetas</th>
                                            <td style='text-align:right;' >". number_format((float) $total_t_c,2)."</td>
                                        </tr>
                                      ");                                      
                                  } 

                                  print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Consultas</th>
                                            <td style='text-align:right;' >". number_format((float) $total_g_c,2)."</td>
                                        </tr>
                                      ");
                                       
                               }
                             }  
                            ?>               
                             
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="col-sm-3">
                <div class="table-responsive cuadregd-productos cuadre-comun">
                    <table class="table table-hover table-condensed cuadreg-productos table-sm productos-color cuadre-comun" >          
                        <thead>
                            <tr>
                                <th style="text-align:center;" colspan="2">Productos</th>          
                            </tr>
                        </thead> 
                        <tbody> 
                            <?php 
                            if (isset($p)) {                                
                               $total_t_p=0;
                               $total_g_p=0;
                               if (sizeof($c)>0) {
                                 for ($i=0; $i <sizeof($p) ; $i++) { 
                                     print("    
                                       <tr>                                
                                            <th style= 'width:50%'>".$p[$i]['modopago']."</th>
                                            <td style='text-align:right;' >". number_format((float) $p[$i]['monto'],2)."</td>
                                        </tr>
                                      ");


                                     if ($p[$i]['modopago']!="CASH" && $p[$i]['modopago']!="CHECK") {
                                         $total_t_p=$total_t_p+$p[$i]['monto'];
                                     }
                                     $total_g_p=$total_g_p+$p[$i]['monto'];
                                 }
                                 if ($total_t_p>0) {
                                      print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Tarjetas</th>
                                            <td style='text-align:right;' >". number_format((float) $total_t_p,2)."</td>
                                        </tr>
                                      ");                                      
                                  }
                                  print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Productos</th>
                                            <td style='text-align:right;' >". number_format((float) $total_g_p,2)."</td>
                                        </tr>
                                      ");                                
                                       
                               }
                             }  
                            ?>               
                             
                        </tbody>
                    </table>
                 </div>
                 </div>

                 <div class="col-sm-3 cuadregd-laser cuadre-comun">   
                 <div class="table-responsive">
                    <table class="table table-hover table-condensed cuadreg-laser table-sm laser-color cuadre-comun"   >          
                        <thead>
                            <tr>
                                <th style="text-align:center;" colspan="2">Laser</th>          
                            </tr>
                        </thead> 
                        <tbody> 
                            <?php 
                            if (isset($l)) {                                
                               $total_t_l=0; 
                               $total_g_l=0; 
                               if (sizeof($l)>0) {
                                 for ($i=0; $i <sizeof($l) ; $i++) { 
                                     print("    
                                       <tr>                                
                                            <th style= 'width:50%'>".$l[$i]['modopago']."</th>
                                            <td style='text-align:right;' >". number_format((float) $l[$i]['monto'],2)."</td>
                                        </tr>
                                      ");

                                     if ($l[$i]['modopago']!="CASH" && $l[$i]['modopago']!="CHECK") {
                                         $total_t_l=$total_t_l+$l[$i]['monto'];
                                     }
                                     $total_g_l=$total_g_l+$l[$i]['monto'];
                                 }                               
                                 if ($total_t_l>0) {
                                      print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Tarjetas</th>
                                            <td style='text-align:right;' >". number_format((float) $total_t_l,2)."</td>
                                        </tr>
                                      ");
                                      
                                  }

                                   print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Laser</th>
                                            <td style='text-align:right;' >". number_format((float) $total_g_l,2)."</td>
                                        </tr>
                                      ");       
                               }
                             }  
                            ?>               
                             
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="col-sm-3 suero-exterior ">
                <div class="table-responsive cuadregd-suero-exo cuadre-comun">
                    <table class="table table-hover table-condensed cuadreg-suero-exo table-sm suero-exo-color cuadre-comun" >          
                        <thead>
                            <tr>
                                <th style="text-align:center;" colspan="2">Suero-EXO</th>          
                            </tr>
                        </thead> 
                        <tbody> 
                            <?php 
                            if (isset($s)) {                                
                               $total_t_s=0;
                               $total_g_s=0;
                               if (sizeof($s)>0) {
                                 for ($i=0; $i <sizeof($s) ; $i++) { 
                                     print("    
                                       <tr>                                
                                            <th style= 'width:50%'>".$s[$i]['modopago']."</th>
                                            <td style='text-align:right;' >". number_format((float) $s[$i]['monto'],2)."</td>
                                        </tr>
                                      ");

                                     if ($s[$i]['modopago']!="CASH" && $s[$i]['modopago']!="CHECK") {
                                         $total_t_s=$total_t_s+$s[$i]['monto'];
                                     }
                                     $total_g_s= $total_g_s+$s[$i]['monto'];
                                 }                               
                                if ($total_t_s>0) {
                                      print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Tarjetas</th>
                                            <td style='text-align:right;' >". number_format((float) $total_t_s,2)."</td>
                                        </tr>
                                      ");
                                      
                                  }
                                  print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Suero-EXO</th>
                                            <td style='text-align:right;' >". number_format((float) $total_g_s,2)."</td>
                                        </tr>
                                      ");       
                               }
                             }  
                            ?>               
                             
                        </tbody>
                    </table>
                </div>
                </div>

            </div>    
            <div class="row"></div>
                <div class="col-sm-4"></div>       
                <div class="col-sm-4 cuadregd-total cuadre-comun ">
                <div class="table-responsive">
                    <table class="table table-hover table-condensed cuadreg-total table-sm">          
                        <thead>
                            <tr>
                                <th style="text-align:center;" colspan="2">TOTAL GENERAL</th>          
                            </tr>
                        </thead> 
                        <tbody> 
                            <?php 
                            if (isset($t)) {                                
                               $total_t_t=0;
                               $total_g_t=0;
                               if (sizeof($s)>0) {
                                 for ($i=0; $i <sizeof($t) ; $i++) { 
                                     print("    
                                       <tr>                                
                                            <th style= 'width:50%'>".$t[$i]['modopago']."</th>
                                            <td style='text-align:right;' ><strong>". number_format((float) $t[$i]['monto'],2)."</strong></td>
                                        </tr>
                                      ");

                                     if ($t[$i]['modopago']!="CASH" && $t[$i]['modopago']!="CHECK") {
                                         $total_t_t=$total_t_t+$t[$i]['monto'];
                                     }
                                     $total_g_t= $total_g_t+$t[$i]['monto'];
                                 }                               
                                if ($total_t_t>0) {
                                      print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total Tarjetas</th>
                                            <td style='text-align:right;' ><strong>". number_format((float) $total_t_t,2)."</strong></td>
                                        </tr>
                                      ");
                                      
                                  }
                                  print("    
                                       <tr>                                
                                            <th style= 'width:50%'>Total GENERAL</th>
                                            <td style='text-align:right;' ><strong>". number_format((float) $total_g_t,2)."</strong></td>
                                        </tr>
                                      ");       
                               }
                             }  
                            ?>               
                             
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="col-sm-4"></div>       
            </form>
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
<script src="../../js/cuadregeneral.js?v=5305"></script>
<script src="../../js/detallecuadreprod.js?v=07"></script>
<script src="../../js/jquery.tablesorter.min.js"></script>


 <script>  
$('#cuadreform').submit(function() {    
    document.getElementsByClassName ('shadow')[0].style.display =""
    // document.getElementsByClassName ('xspin')[0].style.display =""
    // document.getElementById('loader').style.display =""
});
 </script>