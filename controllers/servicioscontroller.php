<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';
$arrayNPedido = array();


function mostrarProductos(){

	$dbmsql = mssqlConn::getConnection();
    //$totalRegistros=count( $results->data );
    $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 25;
    $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
    $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

    if(isset($_POST['linexpage']) && $_POST['linexpage']!=""){
    	$linexpage=$_POST['linexpage'];
    	if($linexpage>200)
    	{
    		$limit =200;
    	}
    	$limit =$linexpage;
    }
   

    	if(isset($_GET['valueToSearch']) && $_GET['valueToSearch']!="")
    	{
    		$filter=$_GET['valueToSearch'];
    		//$filter=str_replace(" ","%",$filter);
 			$qryRows  = "SELECT COUNT(*) num_rows FROM MInventario m  where  Prod_serv <>'p' and concat(coditems,desitems) like '%$filter%' " ;
       		$query    = "SELECT * FROM MInventario  where  Prod_serv <>'p' and concat(coditems,desitems) like '%$filter%' order by desitems ";
    	}
    	else
    	{
    	    $qryRows  = "SELECT COUNT(*) num_rows FROM MInventario m where Prod_serv <>'p'  " ;
    	    $query    = "SELECT * FROM MInventario  where  Prod_serv <>'p' order by desitems ";    	
        }

    if($page==0)
    {
        $page=1;
    }
  //
    $Paginator  = new Paginator( $dbmsql, $query,$qryRows );
    $tp =$Paginator-> getTotal();
    if ($tp >0) {
    	# code...
   
	    $lastPage=ceil( $tp/$limit );
	     if($page>$lastPage){
	         $page=$lastPage;
	     }
	    $results    = $Paginator->getData( $page, $limit,"mssql" );
	    $lastPage=ceil( $results->total/$limit );
	    if($page>ceil( $results->total/$limit )){
	        if($lastPage!=0){
	       $page=ceil( $results->total/$limit );
	       $results    = $Paginator->getData( $page, $limit );
	        }
	    }
	    // $npo=sizeof($result->data);
	    // $tr = count($result->data);
	    $totalRegistros=$results->datalen;
 	}else
 	{
 		$totalRegistros=0;
 	}
			$tabla ="<table id='tabla-smsrpl' class='table table-inverse table-inverted table-bordered table-hover table-condensed '>";
			$tabla .="<thead>";
				$tabla .="<tr>";
					$tabla .="<th>Producto</th>";
					$tabla .="<th  align='center'>Grupo</th>";		
					$tabla .="<th>Código</th>";		
					$tabla .="<th>Tipo</th>";		
					$tabla .="<th>Consultar/Editar</th>";
					// $tabla .="<th>Editar</th>";
					$tabla .="<th>Estado</th>";				
				$tabla .="</tr>";
			$tabla .="</thead>";
			$tabla .="<tbody>";
			if($totalRegistros>0){
				for ($i=0; $i <$totalRegistros; $i++) { 
					$tabla .="<tr id=".($results->data[$i]['coditems']).">";
						$tabla .="<td>".$results->data[$i]['desitems']."</td>";
						if (is_null($results->data[$i]['cod_subgrupo'])) {
						   $tabla .="<td align='right' >NO DEFINIDO</td>";
						}else{
						   $tabla .="<td align='right' class='cod_subgrupo'  >".$results->data[$i]['cod_subgrupo']."</td>";
						}
                        $tabla .="<td align='right' class='idprod'>".$results->data[$i]['coditems']."</td>";
                        $tabla .="<td align='right' class='prodserv'>".$results->data[$i]['Prod_serv']."</td>";
                        $tabla .="<td><button type='button' class='btn btn-info consultar' data-toggle='modal' data-target='.bd-productos-modal-lg'>Consultar</button></td>";
                        // $tabla .="<td><button class='btn btn-warning' data-toggle='modal'  data-target='.bd-productos-modal-lg'>Editar</button></td>";
                        if ($results->data[$i]['activo']=='1') {
                        	$tabla .="<td><input type='checkbox' class ='prodon' checked data-toggle='toggle' data-on='Activo' data-off='Inactivo' data-onstyle='success'></td>";
                        }else{
                        	$tabla .="<td><input type='checkbox' class ='prodon'  data-toggle='toggle' data-on='Activo' data-off='Inactivo' data-onstyle='success'></td>";
                        }                        
                        
					$tabla .="</tr>";
				}
			}			
			$tabla .="</tbody>";
			$tabla .="<tr>";
			  $tabla .="<thead>";
              		$tabla .="<th>Producto</th>";
					$tabla .="<th>Grupo</th>";
					$tabla .="<th>Código</th>";		
					$tabla .="<th>Tipo</th>";
					$tabla .="<th>Consultar/Editar</th>";
					$tabla .="<th>Estado</th>";	
					// $tabla .="<th>Editar</th>";		
			  $tabla .="</thead>";					
			$tabla .="</tr>";
	    $tabla .="</table>";
            if($totalRegistros>0){
	     echo "<div style='float:right;'>".$Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
            }
	   // $paginacion = $Paginator->createLinks( $links, 'pagiantion pagination-sm' );
	    $respuesta = $tabla ;
	echo $respuesta;
}