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


require('../../controllers/MedicosController.php');
require('../../controllers/EntregasController.php');
require('../../controllers/LoginpassController.php');
require('../../controllers/MInventarioController.php');


  #enfermeras
 $loginpassController= new LoginpassController();
 
 $emfermeras=$loginpassController->readUDF("Select id,login, concat(Nombre,' ' ,apellido) enfermera from loginpass Where activo=1 and codperfil='06' order by Nombre "); 

  if (count($emfermeras)) {
     $nurses="<option value=''>Todas(o)</option>";
     
     for ($i=0; $i <count($emfermeras) ; $i++) { 
       $nurses .="<option value=".$emfermeras[$i]['id']." login=".$emfermeras[$i]['login'].">".$emfermeras[$i]['enfermera']."</option>";
     }
  } 


  #medicos
  $medicosController = new MedicosController();
  $medicos=$medicosController->readUDF("Select Codmedico, concat(nombre,' ' ,apellido) medico from Mmedicos Where activo=1  order by nombre "); 

  if (count($medicos)>0) {
    $doctors="<option value=''>Todos</option>";

    for ($i=0; $i <count($medicos) ; $i++) { 
       $doctors .="<option value=".$medicos[$i]['Codmedico'].">".$medicos[$i]['medico']."</option>";
    }
  }

 #inventario
  $minventarioController = new MInventarioController();
  $invdisponible=$minventarioController->readUDF("Select coditems,desitems,existencia,entrega from MInventario where coditems in('exos01','cema01')");

  if (count($invdisponible)>0) {
    $productos="<option value=''>Todos</option>";
    for ($i=0; $i <count($invdisponible) ; $i++) { 
       $productos .="<option value=".$invdisponible[$i]['coditems'].">".$invdisponible[$i]['desitems']."</option>";
    }
  }
   
  
$fecha="";
$doc="";
$tratante="";
$product="";

if ( isset($_POST['sd']) && !empty($_POST['sd']) ) {
    $fecha=$_POST['sd'];
}


if (isset($_POST['ed']) && !empty($_POST['ed']) ) {
    $enddate=$_POST['ed'];
}

if ( isset($_POST['sltmed']) && !empty($_POST['sltmed']) ) {
    $doc=$_POST['sltmed'];            
    $xx=  $doctors;
    $doctors=str_replace($doc, $doc." selected",$xx);
}

if ( isset($_POST['nurse']) && !empty($_POST['nurse']) ) {
    $tratante=$_POST['nurse'];
    $nurses=str_replace($tratante, $tratante." selected",$nurses);
}

if ( isset($_POST['producto']) && !empty($_POST['producto']) ) {
    $product=$_POST['producto'];
    $productos=str_replace($product, $product." selected",$productos);
}

$entregasController = new EntregasController();

$qryEnt="Select i.desitems producto, sum( e.cantidad ) entrega  from entregas e
inner join MClientes c On c.codclien=e.codclien
inner join MInventario i On i.coditems=e.coditems
left join Mmedicos m On m.Codmedico=e.codmedico
left join loginpass l On l.id =e.idnurse
where e.borrado=0 ";

$query="Select c.nombres paciente,c.Historia record,i.desitems producto, e.cantidad , CONCAT(m.nombre,' ',m.apellido) medico, concat(l.Nombre,' ',l.apellido) tratante,e.horar hora,e.numfactu factura,e.id , e.usuario,e.nota ,CONVERT(varchar(11),fecha,101) fecha from entregas e
inner join MClientes c On c.codclien=e.codclien
inner join MInventario i On i.coditems=e.coditems
left join Mmedicos m On m.Codmedico=e.codmedico
left join loginpass l On l.id =e.idnurse
where e.borrado=0";


if ( !empty($fecha) && !empty($doc) && !empty($tratante) && !empty($product) ) {
  $sql =" And fecha between '$fecha' and '$enddate' and e.codmedico='$doc' and e.idnurse='$tratante' And e.coditems='$product' ";
} else if (  !empty($fecha) && !empty($doc) && !empty($tratante) )  {
  $sql =" And fecha between '$fecha' and '$enddate' and e.codmedico='$doc' and e.idnurse='$tratante' ";
} else if (  !empty($fecha) && !empty($doc) && !empty($product)  )  {
  $sql =" And fecha between '$fecha' and '$enddate' and e.codmedico='$doc' and e.coditems='$product' ";
} else if (  !empty($fecha) && !empty($tratante) && !empty($product) )  {
  $sql =" And fecha between '$fecha' and '$enddate' and e.idnurse='$tratante' And e.coditems='$product' ";
}else if  (  !empty($fecha) && !empty($doc) ) {
  $sql =" And fecha between '$fecha' and '$enddate' and e.codmedico='$doc' ";
}else if  (  !empty($fecha) && !empty($tratante)) {
  $sql =" And fecha between '$fecha' and '$enddate' and e.idnurse='$tratante' ";
}else if  (  !empty($fecha) && !empty($product)) {
  $sql =" And fecha between '$fecha' and '$enddate' And e.coditems='$product' ";  
}else if  (  !empty($fecha)) {
  $sql =" And fecha between '$fecha' and '$enddate' ";  
} elseif  ( empty($fecha) && !empty($doc) && !empty($tratante) && !empty($product) ) {
  $sql =" And e.codmedico='$doc' and e.idnurse='$tratante' And e.coditems='$product' ";
} else if ( empty($fecha) && !empty($doc) && !empty($tratante) )  {
  $sql =" And e.codmedico='$doc' and e.idnurse='$tratante' ";
} else if ( empty($fecha) && !empty($doc) && !empty($product)  )  {
  $sql =" And e.codmedico='$doc' and e.coditems='$product' ";
} else if ( empty($fecha) && !empty($tratante) && !empty($product) )  {
  $sql =" And e.idnurse='$tratante' And e.coditems='$product' ";
}else if  ( empty($fecha) && !empty($doc) ) {
  $sql =" And e.codmedico='$doc' ";
}else if  ( empty($fecha) && !empty($tratante)) {
  $sql =" And e.idnurse='$tratante' ";
}else if  ( empty($fecha) && !empty($product)) {
  $sql =" And e.coditems='$product' ";  
}else{
  $hoy=Date('Y-m-d');
  $sql =" And fecha='$hoy'  "; 
}
 
$qryEnt.= $sql." group by i.desitems";
$query.=$sql." order by e.fecha desc";


 // echo "<pre>";
 // echo ($query);
 // echo "</pre>";

$entregasprod=$entregasController->readUDF( $qryEnt );
$todayDeliv=$entregasController->readUDF( $query );
 ?>
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
    .buscar{
      padding-top: 6px;
    }
    .tr-head-inv{
      background: #87d6d8;
    }

    .tr-head-ent{
      background:#b3ec9c;
    }

    .tr-head-salidas{
      background: #5f6366;
    }

          .table-striped tbody tr:nth-of-type(odd) {
        background-color: #3b4847;
      }

      .table-active, .table-active>td, .table-active>th, .table-hover tbody tr:hover {
    background-color: #4aa0a0;
    }

    .total-ent{
      background-color: #2d2a5c!important;
    }

  </style> 
 </head>
<body>
<header>
  <div class="container-fluid">
    <?php include '../layouts/header.php';?>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
    <div class="filtro">
      <form  name="list-display" id="list-display"  method="POST">

          <div class="col-sm-2">    
            <label class="control-label">Desde</label> 
              <div class="form-group">  
                <input class="datepicker form-control" id="sd" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy" name="sd" autocomplete="off" value="<?php echo($fecha) ?>">
              </div>
          </div>

          <div class="col-sm-2">    
            <label class="control-label">Hasta</label> 
              <div class="form-group">  
                <input class="datepicker form-control" id="ed" placeholder="Fecha final"  data-date-format="mm/dd/yyyy" name="ed" autocomplete="off" value="<?php echo($enddate) ?>">
              </div>
          </div>


          <div class="col-sm-2">    
            <label class="control-label">Médico</label> 
              <div class="form-group">  
                <select class="form-control" id="sltmed" class="sltmed" name="sltmed">
                 <?php echo($doctors) ?>
              </select>
              </div>
          </div>

          <div class="col-sm-2">    
            <label class="control-label">Enfermera(o)</label> 
              <div class="form-group">  
                <select class="form-control" id="nurse" class="nurse" name="nurse" >
               <?php echo $nurses; ?>
              </select>
              </div>
          </div>

          <div class="col-sm-2">    
            <label class="control-label">Producto</label> 
              <div class="form-group">  
                <select class="form-control" id="producto" class="producto" name="producto">
                <?php echo($productos) ?>
              </select>
              </div>
          </div>

          <div class="control-group col-sm-2 buscar">
            <label class="control-label"></label> 
            <div class="form-group">
              <input type="submit" name="submitok" id="submitok" class="btn btn-info submitok" value="Busca" />  
            </div> 
          </div> 




      </form>
    </div>
  </div>

  <div class="row">
  </div>
  <div class="row">
    <div class="col-sm-6">   
      <table class="table table-info tb-entrega">
      <tr class="tr-head-inv">
        <th>Producto</th>
        <th>Inventario Real</th> 
        <th>Invetario Entregas</th> 
      </tr>
                <tbody class="body-inventario">
            <?php 
            if (is_array($invdisponible) && count($invdisponible)>0) {
              for ($i=0; $i <count($invdisponible) ; $i++) {
            
             ?>
            <tr>
                <th scope="row"><?php  echo  $invdisponible[$i]['desitems']; ?></th>
                <td><?php echo (int)$invdisponible[$i]['existencia'] ;?></td>
                <td><?php echo (int)$invdisponible[$i]['entrega'] ;?></td>
              </tr> 
            <?php } } ?>
          </tbody>

    </table>
  </div>
    <div class="col-sm-6">
      <table class="table table-success tb-entrega">
        <tr class="tr-head-ent">
        <th>Producto</th>
        <th>Total Entregado</th> 
      </tr>
          <tbody class="body-inventario">
            <?php 
            if (is_array($entregasprod) && count($entregasprod)>0) {
              for ($i=0; $i <count($entregasprod) ; $i++) {
            
             ?>
            <tr>
                <th scope="row"><?php  echo  $entregasprod[$i]['producto']; ?></th>
                
                <td><?php echo (int)$entregasprod[$i]['entrega'] ;?></td>
              </tr> 
            <?php } } ?>
          </tbody>
    </table>
    
    </div>


  </div>
  <div class="row">
  </div>
  <div class="row">
    <div class="tabla">
      <div class="display-entregas">
        <table class="table table-inverse table-striped table-hover tb-entrega">
          <thead>
            <tr class="tr-head-salidas">
              <th scope="col">#</th>
              <th scope="col">Paciente</th>
              <th scope="col">Record</th>
              <th scope="col">Producto</th>
              <th scope="col">Cantidad</th>
              <th scope="col">Médico</th>
              <th scope="col">Enfermera(o)</th>
              <th scope="col">Nota</th>
              <th scope="col">Fecha</th>
              <th scope="col">Hora</th>
              <th scope="col">Factura</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              if (isset($todayDeliv) && count($todayDeliv) > 0) {
                  $jmpdate =$todayDeliv[0]['fecha'];
                  $total =0;
                for ($i=0; $i <count($todayDeliv) ; $i++) { 
                    if ($jmpdate==$todayDeliv[$i]['fecha']) {
                        $total +=(int)$todayDeliv[$i]['cantidad'];
                    }else
                    {
                      ?>
                      <tr class="total-ent">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>Total entregado</th>
                        <th><?php echo(  $total ) ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                      </tr>
                      <?php
                       $jmpdate =$todayDeliv[$i]['fecha'];
                      $total =(int)$todayDeliv[$i]['cantidad'];
                    }
              ?>
              <tr id="<?php echo($todayDeliv[$i]['id'])  ?>">

                <th><?php echo($i+1) ?></th>
                <td class="tr-ent paciente"><?php  echo($todayDeliv[$i]['paciente'])  ?></td>
                <td class="tr-ent record"><?php  echo($todayDeliv[$i]['record'])  ?></td>
                <td class="tr-ent producto"><?php  echo($todayDeliv[$i]['producto'])  ?></td>
                <td class="tr-ent cantidad"><?php  echo($todayDeliv[$i]['cantidad'])  ?></td>
                <td class="tr-ent medico"><?php  echo($todayDeliv[$i]['medico'])  ?></td>
                <td class="tr-ent tratante"><?php  echo($todayDeliv[$i]['tratante'])  ?></td>
                <td class="tr-ent tratante"><?php  echo($todayDeliv[$i]['nota'])  ?></td>
                <td class="tr-ent fecha"><?php  echo($todayDeliv[$i]['fecha'])  ?></td>
                <td class="tr-ent hora"><?php  echo($todayDeliv[$i]['hora'])  ?></td>
                <td class="tr-ent factura"><?php  echo($todayDeliv[$i]['factura'])  ?></td>
              </tr>
           <?php } }
?>
                      <tr class="total-ent">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th> Total entregado</th>
                        <th><?php echo(  $total ) ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                      </tr>
                      <?php
                       $jmpdate =$todayDeliv[$i]['fecha'];
                      $total =(int)$todayDeliv[$i]['cantidad'];

            ?>
          </tbody>
        </table>
       </div>
      </div>
    </div>
 
 

  </div>

</div> 



<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>

<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>



</body>
</html>
<script type="text/javascript">
  $('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

</script>