<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mssqlconn.php';
$arrayNPedido = array();
//$listado = mssqlConn::Listados();
function mostrarPO(){

  if(isset($_POST["meses"])){
	$meses =$_POST["meses"];
	$criterio1= $_POST["criterio1"];
	$criterio2= $_POST["criterio2"];
	$chk=$_POST["cbfecha"];
	$fecha=$_POST["fecha"];	
	}
 else{	  
	$meses=3;
	$criterio1=2.5;
	$criterio2=2;
    $fecha=$_POST["fecha"];
	}
      
	$dbmsql = mssqlConn::getConnection();

	//FUNCIONES DE LOS SELECTS

	 //Cancela Ordenes de compra
	 // if(isset($_POST["cancelarpo"])){
	 // 	if($_POST["cancelarpo"]!=''){
	 // 		$purchaseO=$_POST["cancelarpo"];
	 // 		cancelarPO($purchaseO);
	 // 	}
	 // }
	
	 if(isset($_POST["incorporar"])){
	 	if($_POST["incorporar"]!=''){
	 		$purchaseO=$_POST["incorporar"];
	 		incorporarPO($purchaseO);			
	 	}	 
	 }

	if(isset($_POST["desincor"])){
	 	if($_POST["desincor"]!=''){
	 		$purchaseO=$_POST["desincor"];
	 		desIncorporarPO($purchaseO);			
	 	}	 
	 } 
	//

	//Orden de compra
	$query="Select * from purchaseOM WHERE status = '1' and type='st' order by fecha";
	$npo=0;
	$lista  = array(); 
	$result=$dbmsql->query($query);
	foreach($result as $row)
	{
	    $lista[] = $row;
	}
	$npo=sizeof($lista);
        
        ///
         $in="";
         $coma=$npo-1;
    for ($j = 0; $j < $npo; $j++) 
    {
          $np=$lista[$j]['pon'];
            $in.= "'".$np."'";
             if($j< $coma){
                $in.=',';
            }
     }
            //var_dump($in);
        	//inicio orden de compras
          if( $in!=""){
			$compras = array();
            $query="Select p.coditems+'$'+p.purchaseOrder code,compra from purchaseorder p where p.purchaseOrder  in($in)";
            $result=$dbmsql->query($query);
            foreach($result as $fila){
	   				$compras[$fila['code']]=$fila['compra'];
			}
		  }
         	//fin orden de compra
			//inicio Promedio
		    $average = array();
		    if($chk!=null){
		    	//$query="SELECT coditems, SUM(cantidad) AS venta From VIEW_PO Where (fechafac >= DateAdd(Month, -$meses , '$fecha')) GROUP BY coditems order by coditems";		
		    	$query="Select p.relcod coditems ,sum(cd.cantidad*p.cantidad)/50 venta   from cma_dfactura  cd
				inner join presentacion p on cd.coditems= p.coditems
				where cd.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST')
				and (cd.fechafac   >= DateAdd(Month, -$meses , '$fecha'))
				group by  p.relcod  ";	

		    }else{
		    	$query="Select p.relcod coditems ,sum(cd.cantidad*p.cantidad)/50 venta   from cma_dfactura  cd
				inner join presentacion p on cd.coditems= p.coditems
				where cd.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST')
				and (cd.fechafac   >= DateAdd(Month, -3 , GETDATE()))
				group by  p.relcod  ";	
		    }			
			        
			$result=$dbmsql->query($query);
			foreach($result as $fila)
			{
				 //$average[$fila['coditems']]=round( ($fila['venta']/$meses),0);	
				 $average[$fila['coditems']]=  (round( ($fila['venta']/$meses),0)>0 ?  round( ($fila['venta']/$meses),0) : 1);	
			}
			//print_r( $average);
		    //fin promedio


	//Inicio Inventario
	$query="Select CODITEMS,DESITEMS, Existencia from MInventario WHERE  coditems='50GST'";
	$listado = mssqlConn::Listados($query);
	$inventario = json_decode($listado, true);
	$nin=sizeof($inventario);
	$totalRegistros=sizeof($inventario);
	//print_r($inventario[1]['DESITEMS']);
	//Fin Inventario
    
    //INICIO DE LA PAGINACION
    //limite de la consulta
    $regXPag=3;
    $pagina=false;

    if(isset($_GET['p'])){
    	$pagina=$_GET['p'];
    }

    if(!$pagina){
    	$inicio=0;
    	$pagina=1;
    }
    else{
    	$inicio($pagina-1)*$regXPag;
    }

    $totalPaginas=ceil($totalRegistros/$regXPag);

    $query .=" OFFSET ".$inicio." ROWS FETCH NEXT ".$regXPag." ROWS ONLY";    

    //FIN DE LA PAGINACION


        $colTotal=0;
        $arrayNPedido="";
		$tabla ="<table id='tabla-puor' class='table table-bordered table-hover table-condensed table-striped'>";
			$tabla .="<thead>";
				$tabla .="<tr>";
					$tabla .="<th>Producto</th>";
					$tabla .="<th>Inv</th>";
					if($npo>0){
						for ($i=0; $i < $npo; $i++) { 
							$tabla .="<th>".$lista[$i]['pon']."</th>";
						}
                                        }
						$tabla .="<th>Promedio</th>";
						$tabla .="<th>Total</th>";
						$tabla .="<th>Meses</th>";
						$tabla .="<th>Pedido?</th>";
						$tabla .="<th>Nuevo Pedido</th>";
						$tabla .="<th>Pedido Red</th>";
					
				$tabla .="</tr>";
			$tabla .="</thead>";
			$tabla .="<tbody>";

			    
				for ($i=0; $i < $nin; $i++) { 
					$cod=$inventario[$i]['CODITEMS'];
					$colTotal=$colTotal+$inventario[$i]['Existencia'];
					$tabla .="<tr id='$cod'>";
	                $tabla .="<td id='$cod'>".$inventario[$i]['DESITEMS']."</td>";   #producto
					$tabla .="<td id='desitems' field=''>".round($inventario[$i]['Existencia'])."</td>"; #Inventario
					if($npo>0)
					{
	                    for ($j = 0; $j < $npo; $j++) 
	                    {		                    	
                            $vav=0;
                            $xpon=$lista[$j]['pon'];
                            if ($compras[$inventario[$i]['CODITEMS'].'$'.$lista[$j]['pon']]!=null){
                               	$vav=$compras[$inventario[$i]['CODITEMS'].'$'.$lista[$j]['pon']]; #Orden de compra
                            }             	                                   
	                        $tabla .="<td class='edit' coditems='$cod' po='$xpon' field='pon-$cod-$xpon'>".$vav."</td>";
	                        $colTotal=$colTotal+$vav;                            
					    }
                                        }
					 $ave=$average[$inventario[$i]['CODITEMS']];
                     $tabla .="<td  id='av-$cod' field='average-$cod'>".$average[$inventario[$i]['CODITEMS']]."</td>"; #Promedio
					 $tabla .="<td  id='total-$cod' field='total-$cod'>".$colTotal."</td>"; #Total
                    //columna Meses
                     $colMeses=0;
					 if($colTotal !=0 && $average[$inventario[$i]['CODITEMS']]!=0)
					 {
                       $colMeses =round(($colTotal/$average[$inventario[$i]['CODITEMS']]),2);
					   $tabla .="<td id='meses-$cod' field='meses-$cod'>".$colMeses."</td>"; #Meses
					 }else
					 {
					 	$tabla .="<td id='meses-$cod' field='meses-$cod'>".$colMeses."</td>"; #Meses
					 }
					//columna Pedido
					 $colPedido=0;
					 if($colMeses<$criterio1)
					 {
					 	  if($colMeses<$criterio2)
					 	  {
					 	  	$colPedido=2;
					 	  }
					 	  else
					 	  {
					 	  	$colPedido=1;
					 	  }
					 }else
					 {
					 	$colPedido=0;
					 }
					 $tabla .="<td id='pedido-$cod' field='colpedido-$cod'>".$colPedido."</td>"; #Pedido?
					 //Columna nuevo pedido
					 $tabla .="<td id='newpedido-$cod' field='colnuevopedido-$cod'>".$average[$inventario[$i]['CODITEMS']]*$colPedido."</td>"; #Pedido?
					 //Columna pedido Redondeado
					 $colPedRounded=$average[$inventario[$i]['CODITEMS']]*$colPedido;
					 
					 if($colPedRounded>0){
					 	$colPedRounded=getRound($colPedRounded);

					 }
					 else
					 {
					 	$colPedRounded=0;
					 }
					 $tabla .="<td id='rounded-$cod' field='colrounded-$cod'>".$colPedRounded."</td>"; #Pedido?
					 //ARRAY DE NUEVO PEDIDO

					 //$arrayNPedido[];
                     //Limpia la variable de la columna total
					 $colTotal=0;

					//040118}
					$tabla .="</tr>";
				}          
				
			$tabla .="</tbody>";
	    $tabla .="</table>";
	    $respuesta = $tabla;
	return printf($respuesta);
}


function convertPedToPO($pon){
	$dbmsql = mssqlConn::getConnection();
	$fecha=date("Y-m-d");	
	$query="INSERT INTO purchaseOM (pon,fecha,Status,type) VALUES('$pon','$fecha',1,'st')";
}

function cancelarPO($pon){
	$dbmsql = mssqlConn::getConnection();
	$query="Update purchaseOM Set Status = 2 WHERE pon = '$pon' and status='1' and type='st' ";
	$query="delete purchaseOM WHERE pon = '$pon'";
	$result=$dbmsql->query($query);
    
    $query="delete purchaseorder   where purchaseOrder= '$pon' ";
    $result=$dbmsql->query($query);
}

function incorporarPO($pon){
	$dbmsql = mssqlConn::getConnection();
	$query="Select coditems,compra from purchaseorder WHERE purchaseOrder = '$pon' and status='1' ";	
	$result=$dbmsql->query($query);
        try{
            foreach($result as $row)
			{
				$coditems=$row['coditems'];
				$cantidad=$row['compra'];
				$query="Update MInventario Set existencia=existencia+$cantidad WHERE coditems = '$coditems' ";
                $result=$dbmsql->query($query);
			}		
			try {
				$query="Update purchaseOM Set Status=4 WHERE pon='$pon' and status='1'";
				$result=$dbmsql->query($query);        		
			} 
			catch (Exception $e) {				
			}
			
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }		
}

function desIncorporarPO($pon){
	$dbmsql = mssqlConn::getConnection();
	$query="Select * from purchaseorder  WHERE purchaseOrder = '$pon' and status='1'";	
	$result=$dbmsql->query($query);
	try{
		 foreach($result as $row){
		 	$coditems=$row['coditems'];
			$cantidad=$row['compra'];
		 	$query="Update MInventario Set existencia=existencia-$cantidad  WHERE coditems = '$coditems'";
		 	$result=$dbmsql->query($query);
		 }
		 try{
		 	$query="Update purchaseOM Set Status =1 WHERE pon = '$pon' and status='4'";	
		 	$result=$dbmsql->query($query);
		 }
		 catch(Exception $e) {
		 }
	} 
	catch(Exception $e) {
	}
	
}

function selectCancelarPO(){	  
    $dbmsql = mssqlConn::getConnection();
	$query="Select * from purchaseOM WHERE status = '1' and type='st' order by fecha";	        
	$npo=0;
	$lista;
	$result=$dbmsql->query($query);

	$select ="<select id='cancelar' name='cancelar'  class='form-control'>";
	    $select .="<option value=''>- - -</option>";
		foreach($result as $row)
		{
			$select .="<option value='".$row["pon"]."'>".$row["pon"]."</option>";		    
		}
	$select .="</select>";
	return printf($select);
}


function selectIncorporarPO(){	  
    $dbmsql = mssqlConn::getConnection();
	$query="Select * from purchaseOM WHERE status = '1' order by fecha";	        
	$npo=0;
	$lista;
	$result=$dbmsql->query($query);

	$select ="<select id='incorporar' name='incorporar'  class='form-control'>";
	    $select .="<option value=''>- - -</option>";
		foreach($result as $row)
		{
			$select .="<option value='".$row["pon"]."'>".$row["pon"]."</option>";		    
		}
	$select .="</select>";
	return printf($select);
}

function selectDesincorporarPO(){	  
    $dbmsql = mssqlConn::getConnection();
	$query="Select * from purchaseOM  WHERE status = '4'  order by fecha";	        
	$npo=0;
	$lista;
	$result=$dbmsql->query($query);

	$select ="<select id='desincor' name='desincor' >";
	    $select .="<option value=''>- - -</option>";
		foreach($result as $row)
		{
			$select .="<option value='".$row["pon"]."'>".$row["pon"]."</option>";		    
		}
	$select .="</select>";
	return printf($select);
}


function selectExcelPO(){	  
    $dbmsql = mssqlConn::getConnection();
	$query="Select * from purchaseOM WHERE status = '1' and type='st' order by fecha";	        
	$npo=0;
	$lista;
	$result=$dbmsql->query($query);

	$select ="<select id='excelapo' name='excelapo'  class='form-control' >";
	    $select .="<option value=''>- - -</option>";
		foreach($result as $row)
		{
			$select .="<option value='".$row["pon"]."'>".$row["pon"]."</option>";		    
		}
	$select .="</select>";
	return printf($select);
}



function getRound($x){
	//intval(4.2)
$m = intval($x / 1000); #Son las unidades de millar
$c = intval(($x - $m * 1000) / 100); #Las centenas igual pero restandole los millares
$d = intval(($x - $m * 1000 - $c * 100) / 10); #Las decenas igual pero restando los millares y centenas
$u = intval(($x - $m * 1000 - $c * 100 - $d * 10) / 1);  #Las unidades restandole todo lo anterior. No haria falta dividir por 1, pero por estetica

if($x <= 0){
	$z = 0;
}
elseif ($x < 100 && $x > 0) {
	$z = 100;
}
else
{
	if(($d == 5 || $d == 0) && $u==0 )
	{
		 $z = $x;
	}
	else
	{
		$z = $x + (50 - ($x % 50));
	}
}   
return $z;
}


?>