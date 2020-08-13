
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.3/css/materialize.min.css">
<link rel="stylesheet" href="./public/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">  
<link rel="stylesheet" href="./public/dist/css/adminlte.min.css">
<!-- <link rel="stylesheet" href="./public/libs/materialize.min.css"> -->


<style>
	body {
			background-image: '';
		}

</style>
<div id="fade">
   <div class="drawing" id="loading">
      <div class="loading-dot"></div>
   </div>
</div> 

<script>
	 document.getElementById('fade').style.display='block'
</script>
<style>
	.orden{
		display: inline-block;
		float: left;
	}
</style>
<?php 


$filtro='		<form method="POST"  >
  <div class="form-group col-sm-3 col s3">
    <label for="idcustomer">ID</label>
    <input type="text" id="idcustomer" name="idcustomer"  class="form-control idcustomer orden" placeholder="" >
  </div>
  <div class="form-group col-sm-3 col s3"  id="div-cliente" style="display:none">
    <label for="idcustomer">ID</label>
    <select id="idassoc" name="idassoc" class="form-control enterkey orden" >
      <option value="" selected ></option>
    </select>
  </div>
  <div class="form-group col-sm-3 col s3"> 
  <label for="idcustomer">Fecha</label>
    <input type="text" name="fecha" id="fecha" placeholder="" class="form-control fecha enterkey orden" autocomplete="off" >
  </div>
  <div class="form-group col-sm-3 col s3"> 
   
    </br>
    <input class="btn payment" type="submit" value="Ok">
  </div>   </form>';


print('<h4 class="p1">Solicitud de Información</h4>');
 
require('./controllers/MasterInvoiceController.php');
$controller = new MasterInvoiceController();
$query="";
$query="SELECT *, a.plazo -DATEDIFF( CURDATE() ,  (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) )  remain FROM masterinvoice a where status=1 ";
if ( isset( $_POST['idcustomer'] )  ) {
   
	$customerid= $_POST['idcustomer'];
	$date="";
	if ( !empty( $_POST['fecha'] ) ) {
	   $_date = explode('/',$_POST['fecha']);
       $date  = $_date[2].'-'.$_date[0].'-'.$_date[1];	
	}	

	if (!empty($customerid) && !empty($date)) {
   	    $query="SELECT *, a.plazo -DATEDIFF( CURDATE(), (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) ) remain FROM masterinvoice a Where a.phone='$customerid' AND date='$date' and a.status=1 ";
    }else if ( !empty($customerid) ) {	
		$query="SELECT *, a.plazo -DATEDIFF( CURDATE(), (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) ) remain FROM masterinvoice a Where a.phone='$customerid'  and a.status=1  ";
	}else if ( !empty($date) ) {	
		$query="SELECT *, a.plazo -DATEDIFF( CURDATE(), (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) ) remain FROM masterinvoice a  Where date='$date' and a.status=1 ";
	}	
}

$limit=25;
if (empty($query)) {
	$response= $controller->pagination($limit,10,"pagination");
}else{
    $response= $controller->paginationUDF($limit,10,"pagination",$query);
}

$res=$response[1];
$linksPages=$response[0];
 
if( empty($res) ) {
 print('
 	<form method="POST"  >
 	<div class="row"> 
    
			
          <div class="form-group col-sm-3">
          	<input type="text" id="idcustomer" name="idcustomer"  class="form-control idcustomer orden" placeholder="ID" >
      		</div>
      		<div class="form-group col-sm-3"  id="div-cliente" style="display:none"> 
      		 	<select id="idassoc" name="idassoc" class="form-control enterkey orden" >
              <option value="" selected ></option>
            </select>
          </div>
          <div class="form-group col-sm-3"> 
            <input type="text" name="fecha" id="fecha" placeholder="Fecha" class="form-control fecha enterkey orden" autocomplete="off" >
          </div>
          <div class="form-group col-sm-3"> 
          	<input class="button payment" type="submit" value="Ok">
          </div>
      
    
  </div>  			
  </form>
 	');
    $template_users = '

	<div class="item">
      
		<table class="table  table-hover table-inverse table-condensed">
		     <thead class="table-success"">
			<tr>
				<th>Caso #</th>
				<th>Cliente</th>
				<th>Fecha</th>
				<th>Usuario</th>
				<th>Link</th>	
				<th>Vencimiento</th>
				<th>Restan</th>			
				<th colspan="2">
					<form method="POST">
						<input type="hidden" name="r" value="cases-add">
						<input class="button btn  add" type="submit" value="Nuevo Caso">
					</form>
				</th>
			</tr>
			 </thead>';

} else {
 print( '<div class="row uno"> 
 			 <div class="form-group col-sm-4">'
 				.$linksPages .
 			'</div>
 			 <div class="form-group col-sm-8">'
 				.$filtro.
 			'</div>
        </div>
    ');

 print('
 	<form method="POST"  >
 	<div class="row"> 
    
		<!--	
          <div class="form-group col-sm-3">
          	<input type="text" id="idcustomer" name="idcustomer"  class="form-control idcustomer orden" placeholder="ID" >
      		</div>
      		<div class="form-group col-sm-3"  id="div-cliente" style="display:none"> 
      		 	<select id="idassoc" name="idassoc" class="form-control enterkey orden" >
              <option value="" selected ></option>
            </select>
          </div>
          <div class="form-group col-sm-3"> 
            <input type="text" name="fecha" id="fecha" placeholder="Fecha" class="form-control fecha enterkey orden" autocomplete="off" >
          </div>
          <div class="form-group col-sm-3"> 
          	<input class="button payment" type="submit" value="Ok">
          </div>
    -->  
    
  </div>  			
  </form>
 	');

   //              <div id="dividassoc" class="form-group col-sm-2">
   //                <label class="control-label">Cliente</label> 
                  
   //             </div>

   $template_users = '

	<div class="item">
      
		<table class="table  table-hover table-inverse table-condensed">
		     <thead class="table-success"">
			<tr>
				<th>Caso #</th>
				<th>Cliente</th>
				<th>Paciente</th>
				<th>Fecha</th>
				<th>Usuario</th>
				<th>Link</th>
				<th>Vencimiento</th>
				<th>Restan</th>					
				<th colspan="2">
					<form method="POST">
						<input type="hidden" name="r" value="cases-add">
						<input class="button btn  add" type="submit" value="Nuevo Caso">
					</form>
				</th>
			</tr>
			 </thead>';


	for ($n=0; $n < count($res); $n++) { 
		$template_users .= '

			<tr>
				<td>' . $res[$n]['caso'] . '</td>
				<td>' . $res[$n]['customername'] . '</td>
				<td>' . $res[$n]['patientname'] . '</td>
				<td>' . $res[$n]['date'] . '</td>
				<td>' . $res[$n]['user'] . '</td>
				<td> 
				<form method="POST">
				     <input type="hidden" name="idrespuesta" value="xxx">
					<a href="respuestas?idrespuesta=' . $res[$n]['barcode'] . '"  onclick="document.respuestas.submit();"   >' . $res[$n]['barcode'] . '</a>
				</form>
				</td> 
				<td>' . $res[$n]['plazo'] . '</td>
				<td>' . $res[$n]['remain'] . '</td>
				<td>
					<form method="POST">
						<input type="hidden" name="r" value="cases-edit">
						<input type="hidden" name="caso" value="' . $res[$n]['caso'] . '">
						<input type="hidden" name="customerid" value="' . $res[$n]['customerid'] . '">
						
						<input type="hidden" name="barcode" value="' . $res[$n]['barcode'] . '">
						
						<input class="button btn btn-primary edit" type="submit" value="Editar">
					</form>
				</td>
				<td style="display:none;">
					<form method="POST">
						<input type="hidden" name="r" value="cases-pay">
						<input type="hidden" name="caso" value="' . $res[$n]['caso'] . '">
						<input class="button payment" type="submit" value="Payment">
					</form>
				</td>
				<td>
					<!--<form method="POST"> -->
						<input type="hidden" name="r" value="cases-prn">
						<input type="hidden" name="caso" value="' . $res[$n]['caso'] . '">
						<input type="hidden" name="times" value="1">						
						<input class="button btn btn-success print" id="'.$res[$n]['barcode'].'" type="submit" value="Imprimir">
				<!--	</form> -->
				</td>

				<td style="display:none;">
					<form method="POST">
						<input type="hidden" name="r" value="cases-void">
						<input type="hidden" name="caso" value="' . $res[$n]['caso'] . '">
						<input class="button  void" type="submit" value="Void">
					</form>
				</td>
			</tr>
		'; 
	}

	$template_users .= '
		</table>
	</div>
	';

	//print($template_users);
}
print($template_users);
?>

 <script src="./public/js/jquery-3.3.1.min.js" ></script>
 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script> 
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
 <script src="./public/js/JsBarcode.all.min.js"></script>
 <script src="./public/js/invoiceprint.js" ></script>


<script>
	document.addEventListener("DOMContentLoaded", function(event) {
   document.getElementById('fade').style.display='none';
	});

	    var date_input=$('#fecha');
    date_input.datepicker({
    format: 'mm/dd/yyyy',
    todayHighlight: true,
    autoclose: true,
  })

  $( document ).ready(function() {  
     $(".navbar").find(".active").removeClass("active");    
     $("#cases").addClass("active")
  })

</script>