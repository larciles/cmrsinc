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
require('../../controllers/MInventarioController.php');


 #inventario
  $minventarioController = new MInventarioController();
  $invdisponible=$minventarioController->readUDF("Select coditems,desitems,existencia,entrega from MInventario where coditems in('exos01','cema01')");

  if (count($invdisponible)>0) {
    $productos="<option value=''>Todos</option>";
    for ($i=0; $i <count($invdisponible) ; $i++) { 
       $productos .="<option value=".$invdisponible[$i]['coditems'].">".$invdisponible[$i]['desitems']."</option>";
    }
  }


  $cmaMFController   = new CMA_MFacturaController();
  $queryt="select  i.desitems, d.coditems, Sum(d.quedan) restan from disponible d 
inner join MClientes c on c.codclien=d.codclien
inner join MInventario i on i.coditems=d.coditems
where d.quedan>0 and d.cod_subgrupo in('EXOSOMAS','BLOQUEO','CEL MADRE')
group by i.desitems, d.coditems";

  $query="select c.nombres,c.Historia,i.desitems, CONVERT(varchar(11),d.fecha,101) fechax, d.* from disponible d 
inner join MClientes c on c.codclien=d.codclien
inner join MInventario i on i.coditems=d.coditems
where d.quedan>0 and d.cod_subgrupo in('EXOSOMAS','BLOQUEO','CEL MADRE')";
 
if ( isset($_POST['producto']) && !empty($_POST['producto']) ) {
    $product=$_POST['producto'];
    $productos=str_replace($product, $product." selected",$productos);

      $queryt="select i.desitems, d.coditems, Sum(d.quedan) restan from disponible d 
inner join MClientes c on c.codclien=d.codclien inner join MInventario i on i.coditems=d.coditems where d.quedan>0 and d.coditems in('$product') group by i.desitems, d.coditems";

      $query="select c.nombres,c.Historia,i.desitems, CONVERT(varchar(11),d.fecha,101) fechax, d.* from disponible d 
inner join MClientes c on c.codclien=d.codclien inner join MInventario i on i.coditems=d.coditems where d.quedan>0 and d.coditems in('$product')";

}
	$facturasM=$cmaMFController->readUDF($query);
    $porentregartotal=$cmaMFController->readUDF($queryt);




// }



?>
<!-- <pre>
  <?php //print_r($query); ?>
  <?php //print_r($facturasM); ?>
</pre>
 -->
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

          .tr-head-ent{
      background:#b3ec9c;
    }


      .table-striped tbody tr:nth-of-type(odd) {
        background-color: #3b4847;
      }

      .table-active, .table-active>td, .table-active>th, .table-hover tbody tr:hover {
    background-color: #4aa0a0;
    }

    </style>
</head>
<body>
	<header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
	<div class="">
    <div class="container-fluid">

  <div class="row">
    <div class="filtro col-sm-6">
      <form  name="list-display" id="list-display"  method="POST">
  
          <div class="col-sm-3">    
            <label class="control-label">Producto</label> 
              <div class="form-group">  
                <select class="form-control" id="producto" class="producto" name="producto">
                <?php echo($productos) ?>
              </select>
              </div>
          </div>

          <div class="control-group col-sm-3 buscar">
            <label class="control-label"></label> 
            <div class="form-group">
              <input type="submit" name="submitok" id="submitok" class="btn btn-info submitok" value="Busca" />  
            </div> 
          </div> 




      </form>
    </div>
          <div class="col-sm-6">
      <table class="table table-success tb-entrega">
        <tr class="tr-head-ent">
        <th>Producto</th>
        <th>Total Por Entregar</th> 
      </tr>
          <tbody class="body-inventario">
            <?php 
            if (is_array($porentregartotal) && count($porentregartotal)>0) {
              for ($i=0; $i <count($porentregartotal) ; $i++) {
            
             ?>
            <tr>
                <th scope="row"><?php  echo  $porentregartotal[$i]['desitems']; ?></th>
                
                <td><?php echo (int)$porentregartotal[$i]['restan'] ;?></td>
              </tr> 
            <?php } } ?>
          </tbody>
    </table>
    
    </div>

  </div>
</div>      
  </div>

<div class="row">

</div>
  <div class="separador"></div>
	<div class="row record-entrega container-fluid ">
		<h4 class="title">  AMNISOMAS Y CM POR ENTREGAR  </h4>

			   <table class="table table-inverse table-striped table-hover tb-entrega
			    ">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Paciente</th>
      <th scope="col">Record</th>
      <th scope="col">Producto</th>
      <th scope="col">Adquirido</th>     
      <th scope="col">X Entregar</th>     
      <th scope="col">Factura</th>
      <th scope="col">Fecha</th>
   <!--    <th scope="col">Médico</th>
     <th scope="col">Usuario</th> -->
    </tr>
  </thead>
  <tbody>
  	<?php 
  		if (is_array($facturasM ) && count($facturasM )>0 ) {
  		  for ($i=0; $i <count($facturasM) ; $i++) { 
  		  	 
  	 ?>
    <tr id="">
      <th class="codigo" codigo="<?php echo($coditems) ?>" scope="row"><?php echo($i+1) ?></th>
      <td><?php echo($facturasM[$i]['nombres']) ?></td>
      <td><?php echo($facturasM[$i]['Historia']) ?></td>
        <td><?php echo($facturasM[$i]['desitems']) ?></td>
        <td class="cantidad"><?php echo($facturasM[$i]['cantidad']) ?></td>
        <td class="cantidad"><?php echo($facturasM[$i]['quedan']) ?></td>
      <td class="factura"><?php echo($facturasM[$i]['numfactu']) ?></td>
    
      
      <td class="cantidad"><?php echo($facturasM[$i]['fechax']) ?></td>
 
      <!-- <td><button type="button" class="btn btn-warning delete">Eliminar</button></td> -->
    </tr>
	<?php } } ?>


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