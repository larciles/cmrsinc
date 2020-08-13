<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$number = count($_POST["subtotal"]);  
if($number > 0){  
	$id_centro  = $_POST['id_centro'];

    if ($id_centro =="2") {
    	$masterTable ="CMA_Mnotacredito";
    	$detailsTable ="CMA_Dnotacredito";
    	$tipoitems="S";
    }elseif ($id_centro =="3") {
      $masterTable ="MSSMDev";
      $detailsTable ="MSSDDev";
      $tipoitems="M";
    }


  $workstation = $_POST["workstation"];
  $ipaddress    = $_POST["ipaddress"];
 
	$codclien = $_POST['idassoc'];
	$codmedico = $_POST['medico'];
	$usuario = $_POST['idusr'];
	$fecha = $_POST['fecha'];
	$subtotal = $_POST['frmsubtotal'];
	$total = $_POST['frmtotal'];
	$TotImpuesto = $_POST['frmtax'];
	$descuento = $_POST['discamount'];
	$alicuota = $_POST['discprcntg'];	
	$iva = 0;
	$impuesto = 0;	
	$seguro      = $_POST["seguro"][0];
	$numfactu = $_POST['numfactu'];	
	$fecha = date("Y-m-d");

	$docnumber= saveMaster($masterTable,$id_centro,$codclien,$codmedico,$usuario,$fecha,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,$numfactu,$workstation,$ipaddress);
	


	for($i=0; $i<$number; $i++)  
    {  
       if(trim($_POST["subtotal"][$i] != ''))  
       {           
            $coditems    = $_POST["serv"][$i];           
            $listaprecio = $_POST["listaprecio"][$i]; 
            $seguro      = $_POST["seguro"][$i];
            $cantidad    = $_POST["cantidad"][$i];
            $precio      = $_POST["precio"][$i];
            $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]); 
            $tax         = ($_POST["tax"][$i] == "" ? "0" : $_POST["tax"][$i] );
            $subtotal    = $_POST["subtotal"][$i]; 
            $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );

            saveDetails($detailsTable,$tipoitems, $docnumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fecha,$descuento,$tax,$detaialprcnt,$workstation,$ipaddress );
       }  
  }  


}

echo json_encode($docnumber);
//print_r($varpost) ;



function saveMaster($dbtable,$id_centro,$codclien,$codmedico,$usuario,$fecha,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,$numfactu,$workstation,$ipaddress){
 
  $number=getReturnNumber($id_centro);
  
  $query="SELECT *  from ".$dbtable."  WHERE numnotcre = '$number' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    
  }else{

     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  ".$dbtable." (numnotcre,fechanot,codclien,    codmedico,    usuario,   fecreg, horareg,statnc,cancelado,monto_abonado,tipopago,codseguro,  subtotal,   totalnot,TotImpuesto,  descuento,  alicuota,      iva, tipo,codtiponotcre,saldo,tasadesc,monto,numfactu,workstation,ipaddress)"
                            . " Values (    '$number','$fecha','$codclien','$codmedico','$usuario','$fecha','$hora', '1',     '0',        '0',         '0',   '$seguro','$subtotal','$total','$TotImpuesto','$descuento','$alicuota','$iva','04', 1,            0,'$alicuota',0,'$numfactu','$workstation','$ipaddress')";
       mssqlConn::insert($query);
      }
return $number;
}

 function saveDetails($detailsTable,$tipoitems,$docnumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fecha,$descuento,$tax,$detaialprcnt,$workstation,$ipaddress ){

$prodInfo     = getProductsData($coditems);
$aplicaiva    = $prodInfo['aplicaiva'] ;
$aplicadcto   = $prodInfo['aplicadcto'] ;
$aplicacommed = $prodInfo['aplicacommed'];
$aplicacomtec = $prodInfo['aplicacomtec'];
$costo        = $prodInfo['costo'];
$kit          = $prodInfo['kit'];

if ($kit=='1') {
    findKit($coditems,$cantidad);
}

$hora =  date("H:i:s", time()); 
$query="INSERT INTO ".$detailsTable."   (numnotcre,       fechanot,     coditems,  cantidad ,    precunit, codtipre        ,usuario  ,fecreg     ,horareg, aplicaiva,aplicadcto ,aplicacommed,aplicacomtec,costo,monto_imp ,descuento,porcentaje,tipoitems,workstation,ipaddress)                             "
        . "                VALUES  ('$docnumber','$fecha','$coditems', '$cantidad','$precio','$listaprecio','$usuario','$fecha','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','$tax','$descuento','$detaialprcnt','$tipoitems','$workstation','$ipaddress')";
        mssqlConn::insert($query);
 }

 function getProductsData($coditems){
  $array;
  $query="SELECT * from  MInventario where coditems='$coditems' ";
  $res = mssqlConn::Listados($query);  
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){ 
    $array['aplicaiva']    = $result[0]['aplicaiva'];
    $array['aplicadcto']   = $result[0]['aplicadcto'];
    $array['aplicacommed'] = $result[0]['aplicacommed'];
    $array['aplicacomtec'] = $result[0]['aplicacomtec'];
    $array['costo']        = $result[0]['costo'];
    $array['kit']          = $result[0]['kit'];
  }

  return  $array;
}


function getReturnNumber($id_centro){
  $number=1;
  $query="SELECT ultimocredito from empresa where id_centro='$id_centro' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $str = $result[0]['ultimocredito']; 
     $number = (double)$str;
     $number++;
  }
  $number =  str_pad($number, 6, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET ultimocredito ='$number' where id_centro='$id_centro' ";
  mssqlConn::insert($query);
 return  $number;
}


 function findKit($coditems,$qty){
  $query="SELECT * from kit where coditems='$coditems' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  for ($i=0; $i <$len ; $i++) { 
      $cantidad= $qty;
      $dis= $result[$i]['disminuir'];
      if (!is_null( $dis ) ) {
         $cantidad=$cantidad*$dis;
      }
      $codikit= $result[$i]['codikit'];
      inventoryUpdate($codikit,$cantidad);
  }
}

function inventoryUpdate($coditems,$qty){
  $query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  mssqlConn::insert($query);
}


?>