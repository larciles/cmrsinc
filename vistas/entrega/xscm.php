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
//var_dump($_POST);
if ( isset($_POST['sd']) && $_POST['sd']!="" && isset($_POST['ed']) && $_POST['ed']!="" ) {
       
  $startday=$_POST['sd'];
  $endday=$_POST['ed'];

  $kitClass= new KitClass();
  $cmaMFController   = new CMA_MFacturaController();


  if (isset($_POST['titulo'])) {
      $query="select CONVERT(varchar(11),m.fechafac,101) fecha,pa.nombres,pa.Historia,mi.clase,mi.desitems,mi.kit,df.coditems, df.cantidad,m.* from cma_MFactura m inner join cma_DFactura df on df.numfactu=m.numfactu inner join MInventario mi on df.coditems=mi.coditems inner join MClientes pa on pa.codclien=m.codclien where m.numfactu in(select d.numfactu from cma_DFactura d where coditems in(select i.coditems from MInventario i where i.clase in('cm','xsomas') and i.activo=1)) 
and m.statfact=3 and mi.clase in('cm','xsomas') and m.fechafac between '$startday' and '$endday' and m.total>0 order by m.fechafac desc";


$query=" select  CONVERT(varchar(11),a.fechafac,101) fecha,
   pa.nombres,
            pa.Historia,
              mi.clase,
            mi.desitems,
            mi.kit, df.coditems, df.cantidad,  MONTH(a.fechafac) AS mes ,
 a.numfactu,a.fechafac,a.codmedico, a.codclien, a.total, a.statfact,a.medico, a.mediconame
from cma_MFactura a
inner join cma_DFactura df on df.numfactu=a.numfactu 
inner join MInventario mi on df.coditems=mi.coditems 
 inner join MClientes pa on pa.codclien=a.codclien 
where a.fechafac between '$startday' and '$endday'  and mi.clase in('cm','xsomas') and a.statfact=3  and a.total>0
order by a.fechafac desc";
 
  }else{
      $query="select CONVERT(varchar(11),m.fechafac,101) fecha,pa.nombres,pa.Historia,mi.clase,mi.desitems,mi.kit,df.coditems, df.cantidad,m.* from cma_MFactura m inner join cma_DFactura df on df.numfactu=m.numfactu inner join MInventario mi on df.coditems=mi.coditems inner join MClientes pa on pa.codclien=m.codclien where m.numfactu in(select d.numfactu from cma_DFactura d where coditems in(select i.coditems from MInventario i where i.clase in('cm','xsomas') and i.activo=1)) 
and m.statfact=3 and mi.clase in('cm','xsomas') and m.fechafac between '$startday' and '$endday' order by m.fechafac desc";

$query=" select  CONVERT(varchar(11),a.fechafac,101) fecha,
   pa.nombres,
            pa.Historia,
              mi.clase,
            mi.desitems,
            mi.kit, df.coditems, df.cantidad,  MONTH(a.fechafac) AS mes ,
 a.numfactu,a.fechafac,a.codmedico, a.codclien, a.total, a.statfact,a.medico, a.mediconame
from cma_MFactura a
inner join cma_DFactura df on df.numfactu=a.numfactu 
inner join MInventario mi on df.coditems=mi.coditems 
 inner join MClientes pa on pa.codclien=a.codclien 
where a.fechafac between '$startday' and '$endday'  and mi.clase in('cm','xsomas') and a.statfact=3  and a.total>0
order by a.fechafac desc";
 
  }



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



      <div class="col-sm-2">
             <label class="control-label"></label> 
          <div class="form-group">
              <input type="checkbox" class="titulo" name="titulo" id="titulo" checked data-toggle="toggle" data-on="Todos" data-off="Ventas" data-onstyle="success" data-offstyle="info">
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
		<h4 class="title"> Salidas AMNISOMAS Y CM</h4>

			   <table class="table table-inverse tb-entrega
			    ">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Fecha</th>
      <th scope="col">Hora</th>
      <th scope="col">Factura</th>
      <th scope="col">Producto</th>
      <!-- <th scope="col">Venta</th>      -->
      <th scope="col">AMNISOMAS</th>     
      <th scope="col">CM</th>     
      <th scope="col">Paciente</th>     
      <th scope="col">Record</th>
      <th scope="col">Médico</th>
   <!--    <th scope="col">Médico</th>
     <th scope="col">Usuario</th> -->
    </tr>
  </thead>
  <tbody>
  	<?php 
  		if (is_array($facturasM ) && count($facturasM )>0 ) {
        $total_xsomas=0;
        $total_cm=0;
  		  for ($i=0; $i <count($facturasM) ; $i++) { 
  		      $cant=$kitClass->findKit($facturasM[$i]['coditems'],$facturasM[$i]['cantidad']);
            // var_dump($cant);
            // var_dump($facturasM[$i]['numfactu']);
            $key_xso = array_column($cant, 'EXOS01');
            $key_cms = array_column($cant, 'CEMA01');
            // var_dump($key_xso);

           $total_xsomas +=$key_xso[0];
           $total_cm+=$key_cms[0]; 
  	 ?>
    <tr id="">
      <th class="codigo" codigo="<?php echo($coditems) ?>" scope="row"><?php echo($i+1) ?></th>
      <td><?php echo($facturasM[$i]['fecha']) ?></td>
      <td><?php echo($facturasM[$i]['horareg']) ?></td>
      <td class="factura"><?php echo($facturasM[$i]['numfactu']) ?></td>
      <td><?php echo($facturasM[$i]['desitems']) ?></td>
      <!-- <td class="cantidad"><?php  //echo($facturasM[$i]['cantidad']) ?></td> -->

       <td class="cantidad"><?php echo($key_xso[0]) ?></td>
       <td class="cantidad"><?php echo($key_cms[0]) ?></td>


      <td class="cantidad"><?php echo($facturasM[$i]['nombres']) ?></td>
      <td class="cantidad"><?php echo($facturasM[$i]['Historia']) ?></td>
      <td class="cantidad"><?php echo($facturasM[$i]['medico']) ?></td>
      <!-- <td><button type="button" class="btn btn-warning delete">Eliminar</button></td> -->
    </tr>
	<?php } }
      if ($total_xsomas>0 || $total_cm>0) {
       ?>
       <tr>
         <th></th>
         <th></th>
         <th></th>
         <th></th>
       
         <th>Total</th>

         <th align="center"><?php echo($total_xsomas) ?></th>
         <th align="center"><?php echo($total_cm) ?></th>

         <th></th>  
         <th></th>
         <th></th>

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