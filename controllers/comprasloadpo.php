<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
session_start();
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

$dbmsql = mssqlConn::getConnection();


function getConsultas(){
    $query="SELECT * from  MInventario where prod_serv='P' or Inventariable='1' order by desitems ";
  	$res = mssqlConn::Listados($query);
  	$result = json_decode($res, true);
	return $result;
}




function getDetailsInvoice(){
	$consultas=getConsultas();



 if(isset($_SESSION['username'])){ 
      $user=$_SESSION['username'];
      $workstation=$_SESSION['workstation'];
      $ipaddress=$_SESSION['ipaddress'];
      $codperfil=$_SESSION['codperfil'];
 }


if (isset($_GET['idpo'])) {
    
	$numfactu=$_GET['idpo'];
 //$xtable=   getDetailsInvoice($numfactu,$consultas);
	
}
 $query  = "SELECT  * FROM purchaseOM Where pon ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $statf=$results[0]['statfact'];

 $query  = "SELECT * FROM purchaseorder Where purchaseOrder ='$numfactu' and compra >0 " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $len = sizeof($results);
 if($len>0){

	if($len>0){
		for ($i=0; $i <$len; $i++) { 
			
            #SERVIO O PRODUCTO
            $tabla .="<tr id=$i>";
            	
            $tabla .="<td ><select id='serv' name='serv[]' class='form-control service enterkey'   >";
  
			for ($j=0; $j < sizeof($consultas); $j++) { 
				if($consultas[$j]['coditems']==$results[$i]['coditems']){
					$tabla .="<option selected value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}else{
					$tabla .="<option value=".$consultas[$j]['coditems'].">".$consultas[$j]['desitems']."</option>";
				}				
			}
			
			$tabla .="</select></td>";
			$tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["compra"].' placeholder="" class="form-control cantidad enterkey" /></td>';

            if ($i>0) {            	
            	$tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove">X</button></td>';
            }else{            	
            	$tabla .='<td><button type="button" name="add" id="add" class="btn btn-success enterpass enterkey">Add More</button></td>';            	
            }

		    $tabla .="</tr>";
		}
	}	

 } 
      echo $tabla;
}