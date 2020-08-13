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
require('../../controllers/KitController.php');
require('../../controllers/DisponibleController.php');
require('../../controllers/MInventarioController.php');


if ( isset($_POST['search_cliente']) && $_POST['search_cliente']!=""  ) {
       
  $search_cliente=trim($_POST['search_cliente']);


  $cmaMFController   = new CMA_MFacturaController();
  $query="select CONVERT(varchar(11),m.fechafac,101) fecha,pa.codclien,pa.nombres,pa.Historia,mi.clase,mi.desitems,mi.kit,df.coditems, df.cantidad,m.* from cma_MFactura m inner join cma_DFactura df on df.numfactu=m.numfactu inner join MInventario mi on df.coditems=mi.coditems inner join MClientes pa on pa.codclien=m.codclien where m.numfactu in(select d.numfactu from cma_DFactura d where coditems in(select i.coditems from MInventario i where i.clase in('cm','xsomas') and i.activo=1)) and m.statfact=3 and mi.clase in('cm','xsomas') and m.numfactu='$search_cliente'";
 
$facturasM=$cmaMFController->readUDF($query);
if (is_array($facturasM) && count($facturasM)>0) {
  $codmedico="000";
  if ($facturasM[$i]['Codmedico']!="") {
     $codmedico=$facturasM[0]['Codmedico'];
  }
  for ($i=0; $i <count($facturasM) ; $i++) { 
       $set_data = array(
        'coditems' =>$facturasM[$i]['coditems']
       ,'cantidad' =>$facturasM[$i]["cantidad"]
       ,'codclien' =>$facturasM[$i]["codclien"]
       ,'numfactu' =>$facturasM[$i]["numfactu"]    
       ,'fechafac' =>$facturasM[$i]["fecha"]
       ,'horareg'  =>$facturasM[$i]["horareg"]
       ,'usuario'  =>$facturasM[$i]["usuario"] 
       ,'cod_grupo'=>$facturasM[$i]['cod_grupo']
       ,'Codmedico'=>$codmedico
       ,'kit'      =>$facturasM[$i]['kit']
      );

      addSaveCantDisponible( $set_data );
  }
   
}


                // if ($kit=='1') {

                //     addSaveCantDisponible($_POST["producto"][$i] ,$_POST["cantidad"][$i],$_POST["idassoc"],$set_data );
                // }

}

//------------------------------------------------------------------

 function addSaveCantDisponible($set_data){
    $kitcontroller = new KitController();
    $disponibleController = new DisponibleController();
    $minventarioController = new MInventarioController();
   
    $qty     = $set_data['cantidad']; 
    $coditems= $set_data['coditems']; 

    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    for ($i=0; $i <$len ; $i++) { 
        $cantidad=$qty;
        $dis= $kit[$i]['disminuir'];
        if (!is_null( $dis ) ) {
            $cantidad=$cantidad*$dis;
        }
        $codikit= $kit[$i]['codikit'];
        $hora=$set_data['horareg'];

        $cod_subgrupo="";

        $prodInfo     =$minventarioController->readUDF( "select * from Minventario Where coditems='$codikit' " );
        if (count($prodInfo)>0) {
         $cod_subgrupo=$prodInfo[0]['cod_subgrupo'];
         $cod_grupo=$prodInfo[0]['cod_grupo'];
        }



        $set_datad = array(
           'coditems'    =>$codikit
          ,'cantidad'    =>$cantidad
          ,'codclien'    =>$set_data['codclien']
          ,'numfactu'    =>$set_data['numfactu']   
          ,'fecha'       =>$set_data['fechafac'] 
          ,'horar'       =>$set_data['horareg']
          ,'usuario'     =>$set_data['usuario']
          ,'fecreg'      =>Date('Y-m-d h:m:s')
          ,'cod_subgrupo'=>$cod_subgrupo
          ,'cod_grupo'   =>$cod_grupo
          ,'codmedico'   =>$set_data['Codmedico']
          ,'kit'         =>$set_data['kit']
          ,'quedan'      =>$cantidad
        
         );


         $where_data = array(
            'numfactu' => $set_data['numfactu'],
            'coditems' => $codikit 
         );

         $array_edit = array(              
            'where' => $where_data
         );

        $respuesta =  $disponibleController->readWhere($array_edit);

        if (count($respuesta)>0) {
            $existencia = $respuesta[0]['cantidad'];
            $existencia = $existencia+$qty;

            $set_data = array(
               'cantidad' => $existencia
               ,'quedan'  => $existencia
            );

            $where_data = array(
                'coditems' =>   $codikit
               ,'numfactu'    =>$set_datad['numfactu'] 
            );

            $array_edit = array(
                'data'  => $set_data,                    
                'where' => $where_data
            );

            $disponibleController->update($array_edit);
        }else{
            $disponibleController->create($set_datad);
        }     
        
    }
}


?>
<pre>
  <?php print_r($query); ?>
  <?php print_r($set_data); ?>
</pre>

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

         <div class="control-group col-sm-2">
          <label class="control-label"></label> 
          <div class="form-group">
            <div data-tip="Id, Record, Nombres" >
              <input type="text" class="form-control" name="search_cliente" placeholder="Factura" autocomplete="off" value="<?php echo($record);?>">
  
              <input type="hidden" id="h_codclien" name="h_codclien" value="<?php echo($codclien) ?>">
            </div>
          </div>
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
		<h4>Record de Entregas</h4>

			   <table class="table table-inverse tb-entrega
			    ">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Fecha</th>
      <th scope="col">Hora</th>
      <th scope="col">Factura</th>
      <th scope="col">Producto</th>
      <th scope="col">Cantidad</th>     
      <th scope="col">Paciente</th>     
      <th scope="col">Record</th>
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
      <td><?php echo($facturasM[$i]['fecha']) ?></td>
      <td><?php echo($facturasM[$i]['horareg']) ?></td>
      <td class="factura"><?php echo($facturasM[$i]['numfactu']) ?></td>
      <td><?php echo($facturasM[$i]['desitems']) ?></td>
      <td class="cantidad"><?php echo($facturasM[$i]['cantidad']) ?></td>
      <td class="cantidad"><?php echo($facturasM[$i]['nombres']) ?></td>
      <td class="cantidad"><?php echo($facturasM[$i]['Historia']) ?></td>
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