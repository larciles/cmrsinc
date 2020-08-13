<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

$dbmsql = mssqlConn::getConnection();


function getConsultas(){
    $query="SELECT * from  MInventario where prod_serv='P' and activo = 1 order by desitems";
  	$res = mssqlConn::Listados($query);
  	$result = json_decode($res, true);
	return $result;
}

function getTipoPrecio(){
	$query="SELECT * from  tipoprecio order by codtipre";
	$res = mssqlConn::Listados($query);
  	$result = json_decode($res, true);
	return $result;
}

function getAreThereDetails(){
 $numfactu=$_GET['fac'];

 $query  = "SELECT  * FROM DFactura Where numfactu ='$numfactu' " ;

 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $len = sizeof($results);
 return $len;
}

function getDetailsInvoice(){
	$consultas=getConsultas();
	$tipoPrecios=getTipoPrecio();
	$codperfil=$_SESSION['codperfil'];

if (isset($_GET['fac'])) {
    
	$numfactu=$_GET['fac'];
 //$xtable=   getDetailsInvoice($numfactu,$consultas);
	
}

 $query  = "SELECT  * FROM MFactura Where numfactu ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $statf=$results[0]['statfact'];

 $query  = "SELECT  * FROM view_dfactura_1 Where numfactu ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $len = sizeof($results);
 if($len>0){

	if($len>0){
		for ($i=0; $i <$len; $i++) { 
			$tabla .="<tr id=$i>";
	            #SERVIO O PRODUCTO
			    if ($statf=='2' || $statf=='3' && $codperfil!=='01') {
					$tabla .="<td ><select id='serv' name='serv[]' class='form-control service enterkey readonly' disabled >";
				}else {
					$tabla .="<td ><select id='serv' name='serv[]' class='form-control service enterkey'   >";
				}	

				for ($j=0; $j < sizeof($consultas); $j++) { 					
					if($consultas[$j]['coditems']==$results[$i]['coditems']){
						$tabla .="<option selected value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']." (".floor($consultas[$j]['existencia']).")"."</option>";
					}else{
						$tabla .="<option value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']." (".floor($consultas[$j]['existencia']).")"."</option>";
					}				
				}				
				$tabla .="</select>";
				$tabla .="<input type='hidden' name='coditems[]'     value=".$results[$i]["coditems"]."     class='coditems' />";
				$tabla .="<input type='hidden' name='aplicadcto[]'   value=".$results[$i]["aplicadcto"]."   class='aplicadcto' />";
				$tabla .="<input type='hidden' name='aplicaComMed[]' value=".$results[$i]["aplicacommed"]." class='aplicaComMed' />";
				$tabla .="<input type='hidden' name='aplicaComTec[]' value=".$results[$i]["aplicacomtec"]." class='aplicaComTec' />";
				$tabla .="<input type='hidden' name='costo[]'        value=".$results[$i]["costo"]."        class='costo' />";
				$tabla .="<input type='hidden' name='apptax[]'       value=".$results[$i]["aplicaiva"]."    class='coditems' />";
				$tabla .="</td>";
				

				#LISTA DE PRECIO APLICADA
				if ($statf=='2' || $statf=='3' && $codperfil!=='01') {
					$tabla .="<td ><select id='tpre' name='listaprecio[]'  class='form-control enterkey readonly'  disabled >";
				}else{
					$tabla .="<td ><select id='tpre' name='listaprecio[]'  class='form-control enterkey'   >";
				}	
					
				for ($j=0; $j < sizeof( $tipoPrecios); $j++) { 
					if( $tipoPrecios[$j]['codtipre']==$results[$i]['codtipre']){
						$tabla .="<option selected value=". $tipoPrecios[$j]['codtipre'].">". $tipoPrecios[$j]['destipre']."</option>";
					}else{
					    $tabla .="<option value=". $tipoPrecios[$j]['codtipre'].">". $tipoPrecios[$j]['destipre']."</option>";
					}				
				}
				$tabla .="</select><input type='hidden'  name='codprecio[]' value='' class='codprecio' /></td>";



	            #DOSIS
	            if ($statf=='2' || $statf=='3' && $codperfil!=='01') {
	            	$tabla .="<td class='readonly' disabled><input disabled type='text' id ='' name='dosis[]' value=".$results[$i]["dosis"]." placeholder='Dosis' class='form-control dosis numbersOnly enterpass enterkey' /></td>";
				}else{
					$tabla .="<td><input type='text' id ='' name='dosis[]' value=".$results[$i]["dosis"]." placeholder='Dosis' class='form-control dosis numbersOnly enterpass enterkey' /></td>";
				}

	            #SUGERIDA
	            if ($statf=='2' || $statf=='3' && $codperfil!=='01') {
	            	$tabla .="<td><input disabled type='text' id ='sugerido1' name='sugerido[]' value=".$results[$i]["cant_sugerida"]." placeholder='Cantidad sugeridad' class='form-control sugerido numbersOnly enterpass enterkey' /></td>";
				}else{
					$tabla .="<td><input type='text' id ='sugerido1' name='sugerido[]' value=".$results[$i]["cant_sugerida"]." placeholder='Cantidad sugeridad' class='form-control sugerido numbersOnly enterpass enterkey' /></td>";
				}
				#SEGURO
				// $tabla .="<td ><select id='tseg1'  name='seguro[]' class='form-control'   >";
				// for ($j=0; $j < sizeof( $seguro); $j++) { 
				// 	if( $seguro[$j]['codseguro']==$results[$i]['codseguro']){
				// 		$tabla .="<option selected value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
				// 	}else{
				// 		//$tabla .="<option value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
				// 	}				
				// }
				// $tabla .="</select></td>";

				#CANTIDAD 
				if ($statf=='2' || $statf=='3' && $codperfil!=='01') {
					$tabla .='<td><input disabled type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad numbersOnly enterkey" /></td>';
				}else{
					$tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad numbersOnly enterkey" /></td>';
				}	

				#PRECIO
				$price = number_format((float)$results[$i]["precunit"], 2, '.', '');	
				$tabla .='<td align="right"><input type="text" name="precio[]"     value='.$price.' placeholder="" class="form-control " style="text-align:right;" readonly /></td>';

				#DESCUENTO
			    $discount =  number_format((float)$results[$i]["descuento"], 2, '.', '');	
				$detalleporcen=0;
				if (!is_null($results[$i]["procentaje"])) {
					$detalleporcen=$results[$i]["procentaje"];
				}
				$tabla .='<td><input type="text"   name="descuento[]"    value='.$discount.' placeholder="" class="form-control" style="text-align:right;" readonly />
				              <input type="hidden" name="detaialprcnt[]" value='.$detalleporcen.' class="detaialprcnt" /></td>';

				#IMPUESTO
	            $impuesto =  number_format((float)$results[$i]["Impuesto"], 2, '.', '');			              
				$tabla .='<td><input type="text" name="tax[]"        value='.$impuesto.' placeholder="" class="form-control" style="text-align:right;" readonly /></td>';

				#SUBTOTAL
				$subtotal =(($results[$i]["cantidad"]* $results[$i]["precunit"])+$results[$i]["Impuesto"])-$results[$i]["descuento"];
				$subtotal = number_format((float)$subtotal, 2, '.', '');
				$tabla .='<td><input type="text" name="name[]"        value='.$subtotal.' placeholder="" class="form-control" style="text-align:right;" readonly/></td>';

				#ACTION
	            if ($i>0) {
	            	if ($statf=='2') {
	            	   $tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove" disabled>X</button></td>';
	            	}else{
	            	   $tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove">X</button></td>';
	            	} 
	            }else{
	            	if ($statf=='2') {
	            	  	$tabla .='<td><button type="button" name="add" id="add" class="btn btn-success add enterpass" disabled>Add More</button></td>';
	            	}else{
	            		$tabla .='<td><button type="button" name="add" id="add" class="btn btn-success add enterpass enterkey">Add More</button></td>';
	            	}
	            }

		    $tabla .="</tr>";
		}
	}	

 } 
      echo $tabla;
}