<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

$dbmsql = mssqlConn::getConnection();


function getConsultas(){
    $query="SELECT * from  MInventario where prod_serv='M' and activo = 1 order by desitems";
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

function getSeguro(){
	$query="SELECT * from  mseguros where status=1 order by codseguro";
	$res = mssqlConn::Listados($query);
  	$result = json_decode($res, true);
	return $result;
}

function getDetailsInvoice(){
	$consultas=getConsultas();
	$tipoPrecios=getTipoPrecio();
	$seguro=getSeguro();

if (isset($_GET['fac'])) {
    
	$numfactu=$_GET['fac'];
 //$xtable=   getDetailsInvoice($numfactu,$consultas);
	
}
 $query  = "SELECT  * FROM MSSMDev Where numnotcre ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $statf=$results[0]['statnc'];

 $query  = "SELECT  * FROM MSSDDev Where numnotcre ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $len = sizeof($results);
 if($len>0){

	if($len>0){
		for ($i=0; $i <$len; $i++) { 
			
            #SERVIO O PRODUCTO
            $tabla .="<tr id=$i>";
            	;
            if ($statf=='2' || $statf=='3') {
            		$tabla .="<td ><select id='serv' name='serv[]' class='form-control service readonly' disabled  >";
            	}else{
            		$tabla .="<td ><select id='serv' name='serv[]' class='form-control service enterkey'   >";
            	}	
			
			
			for ($j=0; $j < sizeof($consultas); $j++) { 
				if($consultas[$j]['coditems']==$results[$i]['coditems']){
					$tabla .="<option selected value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}else{
					$tabla .="<option value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}				
			}
			$tabla .="<input type='hidden' id='coditems$i' name='coditems[]' value=".$results[$i]['coditems']." class='coditems' />";
			$tabla .="</select></td>";

			#LISTA DE PRECIO APLICADA
			if ($statf=='2' || $statf=='3') {
				$tabla .="<td ><select id='tpre' name='listaprecio[]'  class='form-control' disabled  >";
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
			$tabla .="<input type='hidden' id='codprecio$i' name='codprecio[]' value=".$results[$i]['codtipre']." class='codprecio' />";		
			$tabla .="</select></td>";

            #EMPRESA DE SEGURO
			// $tabla .="<td ><select id='tseg1'  name='seguro[]' class='form-control'   >";
			// for ($j=0; $j < sizeof( $seguro); $j++) { 
			// 	if( $seguro[$j]['codseguro']==$results[$i]['codseguro']){
			// 		$tabla .="<option selected value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
			// 	}else{
			// 		$tabla .="<option value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
			// 	}				
			// }
			// $tabla .="</select></td>";

			#CANTIDAD 
			if ($statf=='2' || $statf=='3') {
				$tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad" disabled /></td>';
			}else{
				$tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad enterkey" /></td>';
			}	

			#PRECIO
			$price = number_format((float)$results[$i]["precunit"], 2, '.', '');
			if ($statf=='2' || $statf=='3') {
				$tabla .='<td><input type="text" name="precio[]" id=price'.$i.' readonly="readonly"   value='.$price.' placeholder="" class="form-control " style="text-align:right;"  disabled /></td>';
			}else{
				$tabla .='<td><input type="text" name="precio[]" id=price'.$i.'    value='.$price.' placeholder="" class="form-control precio enterkey" style="text-align:right;"   /></td>';
			}
			#DESCUENTO
			$detalleporcen=0;
			$discount=number_format((float)$results[$i]["descuento"], 2, '.', '');

			if (!is_null($results[$i]["porcentaje"])) {
				$detalleporcen=$results[$i]["porcentaje"];
			}
			if ($statf=='2' || $statf=='3') {
				$tabla .='<td><input type="text"   name="descuento[]" value='.$discount.' placeholder="" class="form-control" style="text-align:right;"  disabled/>
			              <input type="hidden" name="detaialprcnt[]"  value='.$detalleporcen.' class="detaialprcnt"  /></td>';
			}else{
					$tabla .='<td><input type="text"   name="descuento[]"    value='.$discount.' placeholder="" class="form-control" style="text-align:right;" readonly/>
			              <input type="hidden" name="detaialprcnt[]" value='.$detalleporcen.' class="detaialprcnt" /></td>';
			}
			#IMPUESTO
			$tax=number_format((float)$results[$i]["monto_imp"], 2, '.', '');
			if ($statf=='2' || $statf=='3') {
				$tabla .='<td><input type="text" name="tax[]"        value='.$tax.' placeholder="" class="form-control" style="text-align:right;" disabled/></td>';
			}else{
				$tabla .='<td><input type="text" name="tax[]"        value='.$tax.' placeholder="" class="form-control" style="text-align:right;" readonly/></td>';
			}
			#SUBTOTAL
			$subtotal =($results[$i]["cantidad"]* $results[$i]["precunit"])-$results[$i]["descuento"];
			$subtotal =number_format((float)$subtotal, 2, '.', '');
			if ($statf=='2' || $statf=='3') {				
				$tabla .='<td><input type="text" name="subtotal[]"        value='.$subtotal.' placeholder="" class="form-control" style="text-align:right;" disabled/></td>';
			}else{				
				$tabla .='<td><input type="text" name="subtotal[]"        value='.$subtotal.' placeholder="" class="form-control" style="text-align:right;" readonly /></td>';
			}

            if ($i>0) {
            	if ($statf=='2' || $statf=='3') {
            	   $tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove" disabled>X</button></td>';
            	}else{
            	   $tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove">X</button></td>';
            	} 
            }else{
            	if ($statf=='2' || $statf=='3') {
            	  	$tabla .='<td><button type="button" name="add" id="add" class="btn btn-success enterpass" disabled>Add More</button></td>';
            	}else{
            		$tabla .='<td><button type="button" name="add" id="add" class="btn btn-success enterpass enterkey">Add More</button></td>';
            	}
            }
            $tabla .='<td><input type="hidden" name="name[]" value='.$subtotal.'  readonly="readonly" placeholder="Subtotal" class="form-control name_list subtotal" /></td>';
		    $tabla .="</tr>";
		}
	}	

 } 
      echo $tabla;
}