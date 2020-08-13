<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
session_start();
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

$dbmsql = mssqlConn::getConnection();


function getConsultas(){
    $query="SELECT * from  MInventario where (prod_serv='P' or  prod_serv='I') or Inventariable='1' order by desitems ";
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


if (isset($_GET['idcompra'])) {
    
  $numfactu=$_GET['idcompra'];
 //$xtable=   getDetailsInvoice($numfactu,$consultas);
  
}
 $query  = "SELECT  * FROM MCompra Where factcomp ='$numfactu' " ;
 $res = mssqlConn::Listados($query);
 $results = json_decode($res, true);
 $facclose=$results[0]['facclose'];

 $query  = "SELECT  * FROM DCompra Where factcomp ='$numfactu' " ;
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
      $tabla .='<td><input type="text" name="cantidad[]"   value='.$results[$i]["cantidad"].' placeholder="" class="form-control cantidad enterkey" /></td>';

            if ($i>0) {     
              if ( $facclose=='1') {
                      $tabla .=' <td><button type="button"  class="btn btn-danger">*</button></td>';
                } else{
                    $tabla .=' <td><button type="button" name="remove" id='.$i.' class="btn btn-danger btn_remove">X</button></td>';
                }    
              
            }else{              
              if ($facclose=='1') {
                $tabla .='<td><button type="button" name="" id="" class="btn btn-success ">NO More</button></td>';              
              } else {
                $tabla .='<td><button type="button" name="add" id="add" class="btn btn-success enterpass enterkey">Add More</button></td>';             
              }
              
              
            }

        $tabla .="</tr>";
    }
  } 

 } 
      echo $tabla;
}