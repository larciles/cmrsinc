<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

$dbmsql = mssqlConn::getConnection();


function getConsultas(){
    $query="SELECT * from  MInventario where prod_serv in ('S','c') order by desitems";
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

function getDetails(){
	$consultas=getConsultas();
	$tipoPrecios=getTipoPrecio();
	$seguro=getSeguro();

if (isset($_GET['fac'])) {
    
	$numfactu=$_GET['fac'];
 //$xtable=   getDetailsInvoice($numfactu,$consultas);
	
}
 
 $query  = "SELECT  * FROM CMA_Mnotacredito Where numnotcre ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $resMast = json_decode($res, true);
 
 
 
 $query  = "SELECT  * FROM CMA_Dnotacredito Where numnotcre ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $len = sizeof($results);
 if($len>0){

	if($len>0){
		for ($i=0; $i <$len; $i++) { 
			
            #SERVICIO O PRODUCTO
			$tabla .="<td ><select id='serv' name='serv[]' class='form-control service readonly'   >";
			for ($j=0; $j < sizeof($consultas); $j++) { 
                            $X= $consultas[$j]['coditems'];
                            $Y=$results[$i]['coditems'];
				if($consultas[$j]['coditems']==$results[$i]['coditems']){
					$tabla .="<option selected value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}else{
				//	$tabla .="<option value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}				
			}
			$tabla .="</select></td>";

			#LISTA DE PRECIO APLICADA
			$tabla .="<td ><select id='tpre' name='listaprecio[]'  class='form-control'   >";
			for ($j=0; $j < sizeof( $tipoPrecios); $j++) { 
				if( $tipoPrecios[$j]['codtipre']==$results[$i]['codtipre']){
					$tabla .="<option selected value=". $tipoPrecios[$j]['codtipre'].">". $tipoPrecios[$j]['destipre']."</option>";
				}else{
				//	$tabla .="<option value=". $tipoPrecios[$j]['codtipre'].">". $tipoPrecios[$j]['destipre']."</option>";
				}				
			}
			$tabla .="</select></td>";

            #EMPRESA DE SEGURO
			$tabla .="<td ><select id='tseg1'  name='seguro[]' class='form-control'   >";
			for ($j=0; $j < sizeof( $seguro); $j++) { 
				if( $seguro[$j]['codseguro']==$resMast[0]['codseguro']){
					$tabla .="<option selected value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
				}else{
					//$tabla .="<option value=". $seguro[$j]['codseguro'].">". $seguro[$j]['seguro']."</option>";
				}				
			}
			$tabla .="</select></td>";

			#CANTIDAD 
			$tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad" /></td>';

			#PRECIO
			$tabla .='<td><input type="text" name="precio[]"     value='.$results[$i]["precunit"].' placeholder="" class="form-control " /></td>';

			#DESCUENTO
			$detalleporcen=0;
			if (!is_null($results[$i]["porcentaje"])) {
				$detalleporcen=$results[$i]["porcentaje"];
			}
			$tabla .='<td><input type="text"   name="descuento[]"    value='.$results[$i]["descuento"].' placeholder="" class="form-control  " />
			              <input type="hidden" name="detaialprcnt[]" value='.$detalleporcen.' class="detaialprcnt" /></td>';

			#IMPUESTO
			$tabla .='<td><input type="text" name="tax[]"        value='.$results[$i]["monto_imp"].' placeholder="" class="form-control  " /></td>';

			#SUBTOTAL
			$subtotal =($results[$i]["cantidad"]* $results[$i]["precunit"])-$results[$i]["descuento"];
			$tabla .='<td><input type="text" name="subtotal[]"        value='.$subtotal.' placeholder="" class="form-control  " /></td>';

		    $tabla .="</tr>";
		}
	}	

 } 
      echo $tabla;
}