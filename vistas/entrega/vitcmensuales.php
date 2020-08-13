<?php 
header('Access-Control-Allow-Origin: *');  
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
ini_set('memory_limit', '1024M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}
 
require('../../controllers/CMA_MFacturaController.php');	 
require('../../clases/KitClass.php');  
$meses=['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] ;
// var_dump($_POST);
if ( isset($_POST['sd']) && $_POST['sd']!="" && isset($_POST['ed']) && $_POST['ed']!="" ) {
       
  $startday=$_POST['sd'];
  $endday=$_POST['ed'];

  $kitClass= new KitClass();
  $cmaMFController   = new CMA_MFacturaController();
 //Esta bueno puede utilizarse incluye laser
 $query=" select  CONVERT(varchar(11),a.fechafac,101) fecha,pa.nombres,pa.Historia,              mi.clase,mi.desitems,mi.kit, df.coditems, df.cantidad,  MONTH(a.fechafac) AS mes,  year(a.fechafac) AS year,a.numfactu,a.fechafac,a.codmedico, a.codclien, a.total, a.statfact,a.medico, a.mediconame, a.company
from view_suero_laser a
inner join view_suero_laser_details df on df.numfactu=a.numfactu 
inner join MInventario mi on df.coditems=mi.coditems 
 inner join MClientes pa on pa.codclien=a.codclien 
where a.fechafac between '$startday' and '$endday' and ( mi.clase in ('s') or  df.coditems in ('pck2aa','pck1ai')  )and a.statfact=3  and a.total>0
order by a.numfactu desc
";



$query=" select  CONVERT(varchar(11),a.fechafac,101) fecha,
   pa.nombres,
            pa.Historia,
              mi.clase,
            mi.desitems,
            mi.kit, df.coditems, df.cantidad,  MONTH(a.fechafac) AS mes,  year(a.fechafac) AS year ,
 a.numfactu,a.fechafac,a.codmedico, a.codclien, a.total, a.statfact,a.medico, a.mediconame, '' as company
from cma_mFactura a
inner join cma_DFactura df on df.numfactu=a.numfactu 
inner join MInventario mi on df.coditems=mi.coditems 
 inner join MClientes pa on pa.codclien=a.codclien 
where a.fechafac between '$startday' and '$endday' and ( mi.clase in ('s') or  df.coditems in ('pck2aa','pck1ai')  )and a.statfact=3  and a.total>0
order by a.fechafac desc
";
 
 




$facturasM=$cmaMFController->readUDF($query);


}



?>
<!-- <pre>
  <?php //print_r($query); ?>
  <?php //print_r($facturasM); ?>
</pre> -->

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
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">


    <style type="text/css">
    	.inventario{
    		height: 8rem;
    		width: 50%;
    		background: lightgreen;
    	}

    	.productos{
    		height: 8rem;
    		width: 50%;
    		background: lightblue;
    	}
      .title{
        text-align: center;
      }
    </style>
</head>
<body>
	<header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
	<div class="container-fluid">
    <div class="container">

  <form name="vita-c" id="vita-c"  method="POST">

         <div class="col-sm-2">   
          </div>
      <div class="col-sm-3">    
        <label class="control-label"></label> 
        <div class="form-group">  
     
                <input class="datepicker form-control" id="sd" autocomplete="off" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy"  name="sd" value="<?php echo($startday) ?>">
            </div>
      </div>

      <div class="col-sm-3">    
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
  </div>


  <div class="separador"></div>
	<div class="row record-entrega container-fluid ">
		<h4 class="title"> Salidas Vitamina C </h4>

			   <table class="table table-inverse tb-entrega
			    ">
  <thead>
    <tr>
      <th scope="col">Mes</th>
      <th scope="col">Vitamina C</th>      
      <th scope="col">Vit C-Cintron</th>
      <th scope="col">Total Vitamina C</th>
      
      
   <!--    <th scope="col">Médico</th>
     <th scope="col">Usuario</th> -->
    </tr>
  </thead>
  <tbody>
  	<?php 
  		if (is_array($facturasM ) && count($facturasM )>0 ) {
        $total_xsomas=0;
        $total_cm=0;

        $total_mes_amni=0;
        $total_mes_cm=0;
        $total_cintron_amni=0;
        $total_cintron_cm=0;

        $total_G_cintron_amni=0;
        $total_G_cintron_cm=0;
        
        $startmonth=$facturasM[0]['mes'];
        $startyear=$facturasM[0]['year'];        

  		  for ($i=0; $i <count($facturasM) ; $i++) { 
          
  		      $cant=$kitClass->findKit($facturasM[$i]['coditems'],$facturasM[$i]['cantidad']);
            $key_xso = array_column($cant, '25GST');
            
            $total_xsomas +=$key_xso[0];
          

           if ($startmonth==$facturasM[$i]['mes']) {
              $total_mes_amni+=$key_xso[0];
               
              if($facturasM[$i]['medico']=="Cintron") {                
                 $total_cintron_amni+=$key_xso[0];
                 $total_G_cintron_amni+=$key_xso[0];
              }
           }else{
             ?>



             <tr id="">
              <th class="codigo"  scope="row"><?php echo($meses[$startmonth].' '.$startyear) ?></th>
              
              <td><?php echo($total_mes_amni) ?></td>              
              <td><?php echo($total_cintron_amni) ?></td>              
              <td><?php echo($total_mes_amni-$total_cintron_amni) ?></td>
              
             </tr>

              <?php
                 if(!is_null($facturasM[$i]['mes']) ){
                   $startmonth=$facturasM[$i]['mes'];
                   $startyear=$facturasM[$i]['year']; 
                 } 
             

              $total_mes_amni=0;
              $total_mes_cm=0;
              $total_cintron_amni=0;
              $total_cintron_cm=0;
               
              $total_mes_amni+=$key_xso[0];              

              if($facturasM[$i]['medico']=="Cintron") {                
                 $total_cintron_amni+=$key_xso[0];                
                 $total_G_cintron_amni+=$key_xso[0];

              }
           }


  	 ?>

      

	<?php } }


      if ($total_xsomas>0 || $total_cm>0) {
       ?>
           <tr id="">
              <th class="codigo"  scope="row"><?php echo($meses[$startmonth].' '.$startyear) ?></th>
              <td><?php echo($total_mes_amni) ?></td>              
              <td><?php echo($total_cintron_amni) ?></td>              
              <td><?php echo($total_mes_amni-$total_cintron_amni) ?></td>
             </tr>

       <tr>

            <th>Total</th>
        <!-- total amnisome -->
         <th align="center"><?php echo($total_xsomas) ?></th>         
         <th align="center"><?php echo($total_G_cintron_amni) ?></th>         
         <th align="center"><?php echo($total_xsomas-$total_G_cintron_amni) ?></th>
         <!-- total cm -->


          

       </tr>
       <?php

      }
   ?>


  </tbody>
</table>	

	</div>



	</div>

</body>
</html>
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



<script type="text/javascript">
	
  // fecha
  
  $('.datepicker').datepicker({
    todayHighlight: true,
    autoclose:true
  });

</script>