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
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

    require('../../controllers/CMA_MFacturaController.php');
    require('../../controllers/CMA_DFacturaController.php');
    require('../../controllers/KitController.php');
    require('../../controllers/MInventarioController.php');


    $minventarioController = new MInventarioController();
    $cmaMFController       = new CMA_MFacturaController();
    $cmaDFController       = new CMA_DFacturaController();
    $kitController         = new KitController();

    if (isset($_POST['sd'] ) && !empty($_POST['sd']) && isset($_POST['ed'] ) && !empty($_POST['ed'])) {

      $startday=$_POST['sd'];
      $endday=$_POST['ed'];
 

      $query="
Select fechafac,d.coditems,cantidad,d.kit ,CONVERT(varchar(11),fechafac,101) fecha, i.desitems from  cma_DFactura d
inner join MInventario i On i.coditems=d.coditems
where numfactu in(Select numfactu from cma_MFactura where fechafac between '$startday' and '$endday' and statfact =3  and numfactu in ( Select numfactu from cma_DFactura where coditems in(select inv.coditems from MInventario inv where activo=1 and inv.coditems in('CTLI002','LI002') or cod_subgrupo='SUEROTERAPIA' ) group by numfactu ) and total>0) and d.coditems in(select mi.coditems from MInventario mi where activo=1 and mi.coditems in('CTLI002','LI002') or cod_subgrupo='SUEROTERAPIA') 
union all
Select fechafac,f.coditems,cantidad,i.kit ,CONVERT(varchar(11),fechafac,101) fecha, i.desitems
from  MSSDFact f
inner join MInventario i On i.coditems=f.coditems 
where numfactu in(Select numfactu from MSSMFact where fechafac between '$startday' and '$endday' and statfact =3  and numfactu in ( Select numfactu from MSSDFact where coditems in(select coditems from MInventario where activo=1 and coditems in('CTLI002','LI002') or cod_subgrupo='SUEROTERAPIA' ) group by numfactu ) and total>0) and f.coditems in(select coditems from MInventario where activo=1 and coditems in('CTLI002','LI002') or cod_subgrupo='SUEROTERAPIA') order by fechafac ";
    }
   $vitclista= $cmaDFController->readUDF($query);
   
   $arrayVitac = array();       
   if (count($vitclista)>0) {
       for ($i=0; $i <count($vitclista) ; $i++) { 
        
        $cantidad=$vitclista[$i]['cantidad']; 
        $coditems=$vitclista[$i]['coditems'];
        $fecha=$vitclista[$i]['fecha'];
        $item=$vitclista[$i]['desitems'];
        $kit=$vitclista[$i]['kit'];
        
        




        if ($kit=='1') {
            $prod_vend=$kitController->readUDF("select k.* from kit k where k.coditems='$coditems' ");
            if (count($prod_vend)>0) {
              for ($j=0; $j <count($prod_vend) ; $j++) { 

                if ($prod_vend[$j]['codikit']=='25GST') {
                    $cantidad=$prod_vend[$j]['disminuir']*$vitclista[$i]['cantidad'];
                }

                
              }

              
            }

        }

        $arrayTemp = array('cantidad' => $cantidad 
                          ,'producto' => $item);

        $arrayVitac[$fecha][]=$arrayTemp ; 
         //$arrayVitac[]=[$fecha] ; 
 
       

  
            
       }

       $aqui=$arrayVitac;


//        foreach ($arrayVitac as $key => $value) {
//     // $arr[3] will be updated with each value from $arr...
//     echo "{$key} => {$value} ";
//     $total=0;
//     for ($i=0; $i <count($value) ; $i++) { 
//       echo $value[$i]['cantidad'];
//       $total+=$value[$i]['cantidad']; 
//     }
//     print_r($arr);
// }



   }


 ?>
 <!-- <pre> -->
<!--    <?php  #print_r($arrayVitac)?>
   <?php  # print_r(count($arrayVitac[0]))?> -->
<!--  </pre> -->
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css"> 
  <style type="text/css">
    .tit{
      text-align: center;
    }
  </style>
 </head>
<body>
<header>
  <div class="container-fluid">
    <?php include '../layouts/header.php';?>
  </div>
</header>
<div class="row">
    <div class="container">

  <form name="vita-c" id="vita-c"  method="POST">

         <div class="col-sm-2">   
          </div>
      <div class="col-sm-2">    
        <label class="control-label"></label> 
        <div class="form-group">  
     
                <input class="datepicker form-control" id="sd" autocomplete="off" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy"  name="sd" value="<?php echo($startday) ?>">
            </div>
      </div>

      <div class="col-sm-2">    
        <label class="control-label"></label> 
        <div class="form-group">  
              <input class="datepicker form-control" id="ed" autocomplete="off"  placeholder="Fecha final"  name="ed" value="<?php echo($endday) ?>">
            </div>
      </div>

      <div class="col-sm-1"> 
 
      </div> 


      <div class="form-group col-sm-2">
            <label class="control-label"></label> 
            <div class="form-group">  
                  <button type="submit"  class="btn btn-primary form-control" id="submit" value="Submit">OK</button>
            </div>
        </div> 

 </form>
</div>
<div class="row">  
 
</div>  
 
<div class="container-fluid" id="charts">

  <div class="row">
    <div class="titulo-3">
       <h4 class="tit">Vitamina C</h4> 
    </div>
      
     <table class="table table-dark collapsed ">
  <thead class="head-t3">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Fecha</th>    
      <th scope="col">Producto</th> 
      <th scope="col">Cantidad</th>       
    </tr>
  </thead>
  <tbody class="body-3">
    <?php
           $general=0; 
           foreach ($arrayVitac as $key => $value) {  

                $total=0;
                for ($i=0; $i <count($value) ; $i++) {                 
                  $total+=$value[$i]['cantidad']; 
                }
                $general+=$total;

    ?>
    

        <tr>
          <th id="<?php  #echo($id) ?>" scope="row"><?php  #echo $i+1;?></th>
          <td><?php  echo($key)  ?></td>
          <td><?php  echo("25g SUEROTERAPIA")  ?></td>  
          <td><?php  echo($total)  ?></td>             
          
        </tr>
  <?php 
    
         } 
   
     ?>

         <tr>
          <th scope="row">#</th>
          <td></td>
          <td></td>  
          <td><?php  echo($general)  ?></td>             
          
        </tr>

  </tbody>
</table>
  
  </div> 








</div> 

<div class="row">  
  <span></span>
  <h2 id ="total" style="width:100%;text-align:center;position:relative;"></h2>
</div>
 

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<!-- <script src="../../js/bloqueorep.js"></script> -->

</body>
</html>

<script type="text/javascript">
  
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

</script>