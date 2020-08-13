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

    require('../../controllers/ClientesController.php');
    require('../../controllers/CMA_MFacturaController.php');
	require('../../controllers/CMA_DFacturaController.php');
	require('../../controllers/MedicosController.php');
	require('../../controllers/KitController.php');
	require('../../controllers/MInventarioController.php');
	require('../../controllers/DisponibleController.php');
	require('../../controllers/EntregasController.php');
	

	$minventarioController = new MInventarioController();
	$cmaMFController   = new CMA_MFacturaController();
	$cmaDFController   = new CMA_DFacturaController();
	$medicosController = new MedicosController();
	$kitController     = new KitController();
	$disponibibleController =new DisponibleController();

	$entregasController = new EntregasController();

  //  var_dump($_POST);


	//   

	


    if (isset($_POST['search_cliente']) && $_POST['search_cliente']!="") {

     #SAVE
     if (isset($_POST['h_id-producto']) && !empty($_POST['h_id-producto']) ) {
         
        $prod_cod= trim($_POST['h_id-producto']);
        $clie_cod= trim($_POST["h_codclien"]);
     	$entreg=$disponibibleController->readUDF("Select * from disponible Where codclien='$clie_cod' and coditems='$prod_cod' and quedan>0 ");



     	if (count($entreg)>0) {

     		$quedan=$entreg[0]['quedan'];

     		if ($quedan >=$_POST['entregaqty'] ) {
     		 
     		    $quedan=$quedan-$_POST['entregaqty']; 

	     		$set_data = array(
	        	 'coditems' =>$prod_cod
	       		,'cantidad'	=>$_POST['entregaqty']      	
	       		,'codclien' =>$_POST["h_codclien"]
	       		,'numfactu' =>$entreg[0]['numfactu']
	      		,'fecha'    =>date('Y-m-d')
	      		,'horar'    =>date('H:i:s')
	      		,'usuario'  =>$user
	      		,'fecreg'   =>date('Y-m-d H:i:s')
	      		,'record'   =>$_POST['search_cliente']
	      		,'quedan'   =>$quedan
	      		,'codmedico'=>$entreg[0]['codmedico']   
	    	    );	
	    	    $se= $entregasController->create($set_data);


	    	    #actualiza entregas

	    	    $set_data = array(
            	'quedan' =>  $quedan
        		);

        		$where_data = array(
            	'coditems' => $prod_cod
            	,'numfactu'=> $entreg[0]['numfactu']
        		);

        		$array_edit = array(
            	'data'  => $set_data,                    
            	'where' => $where_data
        		);
        		$ue=$disponibibleController->update($array_edit);



        		// inventario

	            $inv=$minventarioController->readUDF("Select entrega as quedan From MInventario Where coditems='$prod_cod' ");

                if (count($inv)>0) {
                    $quedan=$inv[0]['quedan'];
                    $quedan=$quedan-$_POST['entregaqty'];

                    $set_data = array(
                    'entrega' =>  $quedan
                    );

                    $where_data = array(
                    'coditems' => $prod_cod
                    );

                    $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data
                    );
                    $ue=$minventarioController->update($array_edit);  
                }
     		}
     	} 

   	 
     	
     }  

    	//**************************************************************

    	$record= trim($_POST['search_cliente']);
    	$clientescontroller = new ClientesController();
    	$cliente=$clientescontroller->readUDF("SELECT nombres,codclien,Cedula from  MClientes where Historia='$record' ");
    	if (count($cliente)>0) {
    	   $nombre=	$cliente[0]['nombres'];
    	   $codclien = $cliente[0]['codclien']; 

    

    	  
    	   $facturasM=$cmaMFController->readUDF("Select numfactu,codmedico,fechafac From cma_MFactura where codclien='$codclien' and statfact=3 and numfactu in (select numfactu from cma_DFactura where cod_subgrupo in ('cel madre','BLOQUEO'))");
           $foudIt="";
           $arrayDisponibles = array();  
           if (count($facturasM)>0) {
           	  $foudIt="0";
           	  for ($i=0; $i <count($facturasM) ; $i++) { 

           	  	  $numfactu=$facturasM[$i]['numfactu'];
           	  	  $codmedico=$facturasM[$i]['codmedico'];
                  if ( empty($codmedico) || is_null($codmedico)) {
                                   $codmedico="000";
                                   }

           	  	  $medico=$medicosController->readUDF("Select Codmedico , CONCAT(Nombre,' ' , apellido)  medico from Mmedicos where Codmedico= '$codmedico'");
           	  	  $medicoName=$medico[0]['medico'];



           	  	  $details=$cmaDFController->readUDF("select numfactu,fechafac,cantidad,horareg,coditems, CONVERT(varchar(11),fechafac,101)  fecha from cma_DFactura where numfactu='$numfactu' and cod_subgrupo in ('cel madre','BLOQUEO')");

           	  	  if (count($details)>0) {
                              for ($j=0; $j <count($details) ; $j++) {            	  	  	 	  

           	  	  	 	  $numfactu=$details[$j]['numfactu'];
           	  	  	 	  $fecha=$details[$j]['fecha'];
           	  	  	 	  $cantidad=$details[$j]['cantidad'];
           	  	  	 	  $hora=$details[$j]['horareg'];
           	  	  	 	  $coditems=$details[$j]['coditems'];

           	  	  	 	  $prod_vend=$kitController->readUDF("select k.*, i.desitems from kit k inner join MInventario i On i.coditems=codikit where k.coditems='$coditems' ");
                        if (count($prod_vend)>0) {
                        	for ($k=0; $k <count($prod_vend) ; $k++) { 
                        		
                        		$disminuir=$prod_vend[$k]['disminuir'];
                        		$producto =$prod_vend[$k]['desitems'];
                        		$codikit  =$prod_vend[$k]['codikit'];

                        		 if ( !is_null($prod_vend[$k]['disminuir']) ) {
                        		 	 $venta=$cantidad;
                        		 	 $cantidad=$venta * $disminuir ;
                        		 }

                        		 $candispo=0;

                        		 $dispo=$disponibibleController->readUDF("Select * from disponible Where numfactu='$numfactu ' and coditems='$codikit' ");

                        		 if (count($dispo)>0) {
                        		 	$candispo=$dispo[0]['quedan'];
                        		 }
                

		           	  	  	 	  $arrayTMP = array('numfactu' =>$numfactu 
		                                            ,'fecha'   =>$fecha 
		                                            ,'cantidad'=>$cantidad
		                                            ,'hora'    =>$hora
		                                            ,'medico'  =>$medicoName
		                                            ,'codikit' =>$codikit
		                                            ,'producto'=>$producto
		                                            ,'disponib'=>$candispo
		           	  	  	 	  				);

		           	  	  	 	  $arrayDisponibles[]=$arrayTMP;

                        	}
                        }

                       

           	  	  	 }
           
           	  	  }
           	  	
           	  }
           }
    	   
          #record de entregas
     	  // 
     	 $recordent=$entregasController->readUDF("select e.id, e.coditems, i.desitems , CONVERT(varchar(11),e.fecha,101) fecha, e.horar, e.cantidad,e.numfactu  from entregas e inner join MInventario i On i.coditems=e.coditems  where e.borrado<>'1' and e.record='$record' ");
     	 if (count($recordent)>0) {
     	 	$arrayEntregas = array();
     	 	for ($i=0; $i <count($recordent) ; $i++) { 

     	 		$arrayTMP = array(
     	 		 'fecha'   =>$recordent[$i]['fecha'] 
     	 		,'hora'    =>$recordent[$i]['horar'] 
     	 		,'factura' =>$recordent[$i]['numfactu'] 
     	 		,'cantidad'=>$recordent[$i]['cantidad'] 
     	 		,'producto'=>$recordent[$i]['desitems'] 
     	 		,'coditems'=>$recordent[$i]['coditems'] 
     	 		,'id'      =>$recordent[$i]['id']    
     	 		 );
     	 		$arrayEntregas[] =$arrayTMP;
     	 	}
     	 }

    	}

    }

    $prod_name="";
    $real_inv="";
	$entrega_inv="";
    
     if ( isset( $_POST['idproducto'] ) && !empty($_POST['idproducto'])) {
        
     	$idproducto=trim($_POST['idproducto']);
     	$inventario= $minventarioController->readUDF("Select existencia,entrega,desitems from MInventario where coditems='$idproducto'");
    	if (count($inventario)>0) {
    		$foudIt="1";
    		$prod_name  =$inventario[0]['desitems'];
    		$real_inv   =$inventario[0]['existencia'];
    		$entrega_inv=$inventario[0]['entrega'];
    	}

     }
}

$invdisponible=$minventarioController->readUDF("Select coditems,desitems,existencia,entrega from MInventario where coditems in('exos01','cema01')");

?>

<!DOCTYPE html>
<html lang="en">
<head>
	
	<title>CMA WEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

	<link rel="stylesheet" href="../../css/bootstrap.min.css"/>
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
    </style>
</head>
<body>
	<header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
	<div class="container-fluid">
		<form name="invoice" id="invoice"  method="POST">
    	<div class="row">			
    
     			<input type="hidden" id="foudIt" name="foudIt" value="<?php echo($foudIt) ?>"> 
    
				<div class="control-group col-sm-2">
					<label class="control-label"></label>	
					<div class="form-group">
						<div data-tip="Id, Record, Nombres" >
							<input type="text" class="form-control" name="search_cliente" placeholder="Record" autocomplete="off" value="<?php echo($record);?>">
	
							<input type="hidden" id="h_codclien" name="h_codclien" value="<?php echo($codclien) ?>">
						</div>
					</div>
				</div>

				<div class="control-group col-sm-2">
					<label class="control-label"></label>	
					<div class="form-group">
	  					<input type="submit" name="submit" id="submit" class="btn btn-info" value="Busca" />  
	 				</div> 
	 			</div> 

				<div class="control-group col-sm-3">
					<label class="control-label"></label>	
					<div class="form-group">
						<div data-tip="" >
							<input type="text" class="form-control" name="nombre" id="nombre" placeholder="" autocomplete="off" value="<?php echo($nombre)?>">
						</div>
					</div>
				</div>


 	</div>   	<!-- fin 1er row -->

	<div class="row">
		<div class=" col-sm-6 inventario"> 
			<h4>Entrega de Inventario</h4>	 
			<div class="">

				<div class="control-group col-sm-3">
					<label class="control-label"></label>	
					<div class="form-group">
						<div data-tip="" >
							<input type="text" class="form-control" name="idproducto" placeholder="ID producto" autocomplete="off" >
							<input type="hidden" id="h_id-producto" name="h_id-producto" value="<?php # echo($idproducto) ?>">
							<input type="hidden" id="control-producto" name="control-producto" value="<?php  echo($idproducto) ?>">
						</div>
					</div>
				</div>


				<div class="control-group col-sm-4">
					<label class="control-label"></label>	
					<div class="form-group">
						<div data-tip="" >
							<input type="text" class="form-control" placeholder="producto"  autocomplete="off" readonly="" value="<?php echo($prod_name) ?>">
						</div>
					</div>
				</div>


				<div class="control-group col-sm-2">
					<label class="control-label"></label>	
					<div class="form-group">
						<div data-tip="" >
							<input type="text" class="form-control" id="entregaqty" name="entregaqty" placeholder="Salida" value="1" autocomplete="off">
							<input type="hidden" id="focused_element" value="">
						</div>
					</div>
				</div>
			</div>

		</div>

		 <div class="productos col-sm-6">
		 	<table  class="table table-dark collapsed">
		 		<thead>
    				<tr>
    					<th scope="col">Producto</th>
      					<th scope="col">Real</th>
      					<th scope="col">Entrega</th>
    				</tr>
  				</thead>
  				<tbody>
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

  </div>


   </form>


		

		<!-- fin entrega de inventario -->

	<div class="row">
	   <h4>Disponibilidad</h4>	 
	   <table class="table table-dark collapsed">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Fecha</th>
      <th scope="col">Hora</th>
      <th scope="col">Producto</th>
      <th scope="col">Factura</th>
      <th scope="col">Cantidad</th>
      <th scope="col">Disponibible</th>
      <th scope="col">Médico</th>     
    </tr>
  </thead>
  <tbody>
  	<?php
  	   if (is_array($arrayDisponibles)) {
  	    
  	  
  		if (count($arrayDisponibles)>0) {  				
  			
  	 		for ($i=0; $i <count($arrayDisponibles); $i++) {
  	 			$id=$arrayDisponibles[$i]['numfactu'].'-'.$arrayDisponibles[$i]['codikit'].'-'.$i+1;   	
  	 		 
  	?>
  	

		    <tr>
		      <th id="<?php echo($id) ?>" scope="row"><?php echo $i+1;?></th>
		      <td><?php echo($arrayDisponibles[$i]['fecha'])  ?></td>
		      <td><?php echo($arrayDisponibles[$i]['hora'])  ?></td>    
		      <td><?php echo($arrayDisponibles[$i]['producto'])  ?></td>  
		      <td><?php echo($arrayDisponibles[$i]['numfactu'])  ?></td>      
		      <td><?php echo($arrayDisponibles[$i]['cantidad'])  ?></td>
		      <td><?php echo($arrayDisponibles[$i]['disponib'])  ?></td>            
		      <td><?php echo($arrayDisponibles[$i]['medico'])  ?></td>            
		    </tr>
	<?php 
    
    		}	
		}
	 }
     ?>

  </tbody>
</table>
	
	</div> 
   <div class="separador"></div>
	<div class="row record-entrega table-inverse ">
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
      <th scope="col">Eliminar</th>     
      <!-- <th scope="col">Médico</th>
      <th scope="col">Enfermera(o)</th> -->
    </tr>
  </thead>
  <tbody>
  	<?php 
  		if (is_array($arrayEntregas ) && count($arrayEntregas )>0 ) {
  		  for ($i=0; $i <count($arrayEntregas) ; $i++) { 
  		  	$coditems=$arrayEntregas[$i]['coditems'];
  	 ?>
    <tr id="<?php echo($arrayEntregas[$i]['id']) ?>">
      <th class="codigo" codigo="<?php echo($coditems) ?>" scope="row"><?php echo($i+1) ?></th>
      <td><?php echo($arrayEntregas[$i]['fecha']) ?></td>
      <td><?php echo($arrayEntregas[$i]['hora']) ?></td>
      <td class="factura"><?php echo($arrayEntregas[$i]['factura']) ?></td>
      <td><?php echo($arrayEntregas[$i]['producto']) ?></td>
      <td class="cantidad"><?php echo($arrayEntregas[$i]['cantidad']) ?></td>
      <td><button type="button" class="btn btn-warning delete">Eliminar</button></td>
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
<script type="text/javascript">
	//js= JavaScript =0
	//jq= Jquery     =1
	var js_or_jq="0";
	var is_codclien=undefined;
	if (js_or_jq==1) {
        is_codclien=$("input[name='search_cliente']" ).val()        
	}else{

		  const input = document.querySelectorAll('input[name$="search_cliente"]')[0];
		  is_codclien=input.value
           
          var foudIt = document.querySelectorAll('#foudIt')[0].value;  
		  if (foudIt=="0") { 
   
		  var prodcod = document.querySelectorAll('input[name$="idproducto"]')[0];
  		  prodcod.focus();
  		  $('#focused_element').val('idproducto');
  		  prodcod.select();
		  }else if (foudIt=="1"){
		   const prodcant = document.querySelectorAll('input[name$="entregaqty"]')[0];
		   var prodcod = document.querySelectorAll('input[name$="idproducto"]')[0];
  		  // prodcant.focus();
  		   $('#focused_element').val('entregaqty');
  		    prodcant.select();
  		    // prodcod.focus();
  		    prodcod.select();
	
		  }else{

		   const searcli = document.querySelectorAll('input[name$="search_cliente"]')[0];
  		   searcli.focus();
  		   $('#focused_element').val('search_cliente');
  		   searcli.select()
		  	
		  }
		  
		
	}
	if (is_codclien!="" && is_codclien!=undefined) {
	
	}

   

	$('#entregaqty').focus(function() {
    $('#focused_element').val($(this).attr('id'));
	});
	

   $('#invoice').submit(function(e) {    
      var x = document.getElementById("entregaqty");    
   });
	
 $('.delete').on("click", function(){
      var ele=$(this);
      let codigo = ele.parent().siblings('th.codigo').attr('codigo');
      let cantidad = ele.parent().siblings('td.cantidad').text();
      let factura = ele.parent().siblings('td.factura').text();
      let id = ele.parent().parent().attr('id');
      $('#'+id+'').remove(); 
      console.log();

          
          let params = 'del=1&coditems='+codigo+'&id='+id+'&cantidad='+cantidad+"&factura="+factura;             
          url='../../handler/EntregasHandler.php';
          var api = new  XMLHttpRequest();
          api.open('POST',url,true);
          api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
          api.send(params);
          api.onreadystatechange = function(){
            if(this.readyState === 4 && this.status === 200){
                
                console.log(this.responseText);
                $('#submit').click();                                
          }   
        }


  })
 
	// 

	const pcode = document.querySelector('input[name="idproducto"]');

pcode.addEventListener('focus', (event) => {
  event.target.style.background = 'pink';    
});

pcode.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
         if (pcode.value=="") {
         	event.preventDefault();
         	const prodcant = document.querySelectorAll('input[name$="entregaqty"]')[0];
 			prodcant.focus();
 			prodcant.select();
         }
    }
});

const prodcant = document.querySelectorAll('input[name$="entregaqty"]')[0];
prodcant.addEventListener("keydown", function(e){
  var cdp=document.getElementById('control-producto').value 	
  document.getElementById('h_id-producto').value=cdp
})

</script>