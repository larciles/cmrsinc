<?php
if(!isset($_POST['action'])) {
	print json_encode(0);
	return;

	
}else{
	require_once '../db/mssqlconn.php';
	
    $dbmsql = mssqlConn::getConnection();

if($_POST['action']=='update_field_data'){
	$user = json_decode($_POST['contact']);
	var_dump($_POST);
	print_r($_POST);
	$post =$_POST;
        $rr=json_decode($_POST, true);
        $res=json_decode($_POST['producto'], true);

        $coditem=$res['id'];
        $pon=$res['po'];
        $cant=$res['newvalue'];
        $average=$res['average'];
        $criterio1=$res['criterio1'];
        $criterio2=$res['criterio2'];
        
        if($coditem!=null && $pon!=null){
           if (isset($_POST['type'])) {
           	    $type = $_POST['type'];
           	    $query="SELECT compra FROM purchaseorder  where coditems='$coditem' and purchaseOrder='$pon' and type='$type' ";
           } else{
                $query="SELECT compra FROM purchaseorder  where coditems='$coditem' and purchaseOrder='$pon' ";
            }

           $res = mssqlConn::Listados($query);
	       $res = json_decode($res, true);
	       $nin=sizeof($res);
	       if($nin==0){
	       	    $fecha=date("Y-m-d");
	       	    $hora=date("h:i:sa");
	       	    $status="1";
	       	    if (isset($_POST['type'])) {
	       	    	$type = $_POST['type'];
	       	    	$query="INSERT INTO purchaseorder (coditems,compra,fecha,purchaseOrder,hora,status,type) VALUES('$coditem','$cant','$fecha','$pon','$hora',$status,'$type')";
	       	    } else {
	       	    	$query="INSERT INTO purchaseorder (coditems,compra,fecha,purchaseOrder,hora,status) VALUES('$coditem','$cant','$fecha','$pon','$hora',$status)";
	       	    }
	       	    
	         	
	       }else{
	       		$query="UPDATE purchaseorder SET compra =$cant where coditems='$coditem' and purchaseOrder='$pon' ";
	       }
        	
        	mssqlConn::insert($query);
        	
        	$total=round(calTotal($coditem,$dbmsql));
        	$meses=round(($total/$average),2);
        	$pedido=calPedido($meses,$criterio1,$criterio2);
        	$nuevopedido=$average*$pedido;
        	$pRedondeado=getRound($nuevopedido);


        }
        //$age = array("Peter"=>"35", "Ben"=>"37", "Joe"=>"43");
        $rowCal = array("total"=>$total
        				,"meses"=>$meses
        				,"pedido"=>$pedido
        				,"nuevopedido"=>$nuevopedido
        				,"redondeado"=>$pRedondeado); 
        //$rowCal[]=$total; //total
        //$rowCal[]=$meses; //meses
        //$rowCal[]=$pedido; //pedido
        //$rowCal[]=$nuevopedido; //nuevo pedido
        //$rowCal[]=$pRedondeado; //redondeado

        $output = json_encode($rowCal);
        echo $output;	
}elseif($_POST['action']=='pedidoAOrden'){
	/*
	CODGIO QUE CREA UNA ORDEN DE COMPRA PARTIENDO DEL PEDIDO SUGERIDO 
	FUNCIONA CON AJAX
	ARCHIVOS INVOLUCADRO EN ESTE PROCESO:
	index.php (CAMBIARA SU NOMBRE O CARPETA)
	script.js
	*/
	//Variables que viajan por POST
   	$purchaseorder =$_POST["pon"];
	$productos= $_POST["productos"];
    $lenght= count($productos);
     
    //GUARDA EL ARCHIVO MAESTRO 
    try {
    	$fecha=date("Y-m-d");	    
	    $status="1";
	    if (isset($_POST['type'])) {
	    	$type=$_POST['type'];
	    	$query="INSERT INTO purchaseOM (pon,fecha,status,type) VALUES('$purchaseorder','$fecha',$status,'$type')";
	    } else {
	    	$query="INSERT INTO purchaseOM (pon,fecha,status) VALUES('$purchaseorder','$fecha',$status)";
	    }
	    
        
        $result=$dbmsql->query($query);
    } catch (Exception $e) {
            	
     }        
     //GUARDA EL DETALLE: LOS PRODUCTOS, CANTIDAD Y ORDEN DE COMPRA
	for ($i=0; $i <$lenght ; $i++) { 
	   	$coditems=$productos[$i]['coditems'];
	   	$compra=$productos[$i]['qty'];
	   	$fecha=date("Y-m-d");
	    $hora=date("h:i:sa");
	    $status="1";

	    try {
	    	if (isset($_POST['type'])) {
	    		$type=$_POST['type'];
	    		$query="INSERT INTO purchaseorder (purchaseorder,coditems,compra,fecha,hora,status,type) VALUES('$purchaseorder','$coditems','$compra','$fecha','$hora',$status,'$type')";
	    	} else {
	    		$query="INSERT INTO purchaseorder (purchaseorder,coditems,compra,fecha,hora,status) VALUES('$purchaseorder','$coditems','$compra','$fecha','$hora',$status)";
	    	}
	    	
	     	
	   		$result=$dbmsql->query($query);	   		
	    } catch (Exception $e) {
	    	
	    }
	   	
	}
	echo "true";	
}elseif($_POST['action']=='ordenManual'){
	/*
	CODGIO QUE CREA UNA ORDEN DE COMPRA MANUAL
	FUNCIONA CON AJAX
	ARCHIVOS INVOLUCADRO EN ESTE PROCESO:
	index.php (CAMBIARA SU NOMBRE O CARPETA)
	script.js
	*/
	$purchaseorder =$_POST["pon"];	     
    //GUARDA EL ARCHIVO MAESTRO 
    try {
    	$fecha=date("Y-m-d");	    
	    $status="1";
        if (isset($_POST['type'])) {
        	$type=$_POST['type'];
        	$query="INSERT INTO purchaseOM (pon,fecha,status,type) VALUES('$purchaseorder','$fecha',$status,'$type')";
        } else {
        	$query="INSERT INTO purchaseOM (pon,fecha,status) VALUES('$purchaseorder','$fecha',$status)";
        }
        
        

        $result=$dbmsql->query($query);
    } catch (Exception $e) {
            	
     }        

}
}

function calTotal($coditem,$dbmsql){
	$query="Select * from purchaseOM WHERE status = '1' order by fecha";
	$npo=0;
	$lista;
	$result=$dbmsql->query($query);
	foreach($result as $row){
	    $lista[] = $row;
	}
	$npo=sizeof($lista);

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

    $query="Select sum(compra)+(Select i.existencia from MInventario i WHERE i.coditems = '$coditem' ) compra from purchaseorder p where p.purchaseOrder in($in) and coditems = '$coditem' ";
    $result=$dbmsql->query($query);
    foreach($result as $fila){
		$compras=$fila['compra'];
	}
 return $compras;
}

function calPedido($colMeses,$criterio1,$criterio2){
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
 return  $colPedido;
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