 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

 $dbmaster ='MSSMDev';
 $dbdetails='MSSDDev';
 $idempresa='3';

 $number = count($_POST["subtotal"]);  
 if($number > 0)  
 {  

  $invoiceNumber    = $_POST["factura"];

  $codclien    = $_POST["idassoc"];
  $codmedico   = $_POST["medico"];
  $fechafac    = $_POST["fecha"];
  $usuario     = $_POST["idusr"];
  
  $subtotal    = $_POST["frmsubtotal"];
  $descuento   = ($_POST["discamount"] == "" ? "0" : $_POST["discamount"] );
  $alicuota    = ($_POST["discprcntg"] == "" ? "0" : $_POST["discprcntg"] ); 
  $TotImpuesto = ($_POST["frmtax"] == "" ? "0" : $_POST["frmtax"] ) ;
  $frmshipping = ($_POST["frmshipping"] == "" ? "0" : $_POST["frmshipping"] ); 
  $total       = $_POST["frmtotal"];
  $iva =0;
  $impuesto=0;

  $workstation = $_POST["workstation"];
  $ipaddress    = $_POST["ipaddress"];
 

 // round(1.95583, 2);


  $seguro      = $_POST["insurance"][0];

  //$invoiceNumber = saveMaster($fechafac,$codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress );
  $invoiceNumber = saveMaster($invoiceNumber,$fechafac,$codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  'LASERPC1', $ipaddress ,$dbmaster );

  $query="DELETE from ".$dbdetails." Where numnotcre= '$invoiceNumber' ";
  mssqlConn::insert($query);

  for($i=0; $i<$number; $i++)  
  {   $subtotal    = $_POST["subtotal"][$i];
       if(trim($_POST["subtotal"][$i] != ''))  
       {           
            $coditems    = $_POST["serv"][$i];           
            $listaprecio = $_POST["listaprecio"][$i]; 
            //$seguro      = $_POST["insurance"][$i];
            $cantidad    = $_POST["cantidad"][$i];
            $precio      = $_POST["precio"][$i];
            $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]); 
            $tax         = ($_POST["tax"][$i] == "" ? "0" : $_POST["tax"][$i] );
            $subtotal    = $_POST["subtotal"][$i]; 
            $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );
            if ($coditems !=="") {
            saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro, $workstation, $ipaddress ,$dbdetails );
          }
       }  
  }  
      echo json_encode($invoiceNumber); // print_r($_POST);  
 }  
 else  
 {  
      echo "";  
 }  

function getInvoiceNumber(){
  $invoiceNumber=1;
  $query="SELECT * from empresa where id_centro='$idempresa' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $invoiceNumber = $result[0]['UltimaFactura']; 
     $invoiceNumber++;
  }
  $invoiceNumber =  str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET UltimaFactura ='$invoiceNumber' where id_centro='$idempresa' ";
  mssqlConn::insert($query);
 return  $invoiceNumber;
}


function updateLastClientPurchase($codclien,$invoiceNumber,$fechafac){
  $query="UPDATE MClientes SET numnotcre='$invoiceNumber',fechafac='$fechafac ' where codclien ='$codclien' ";
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
  }

  return  $array;
}

function saveMaster($invoiceNumber,$fechafac,$codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress,$dbmaster){
 
  $query="SELECT *  from ". $dbmaster."  WHERE numnotcre = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    $hora =  date("H:i:s", time()); 
    $query="UPDATE  ". $dbmaster." Set codclien = '$codclien' "
    								.",codmedico ='$codmedico' " 
    								.",fecreg = '$fechafac' "
    								.",horareg = '$hora' "     								
    								.",subtotal = '$subtotal' "
    								.",totalnot = '$total' "
    								.",TotImpuesto = '$TotImpuesto' "
    								.",descuento = '$descuento' "
    								.",alicuota = '$alicuota' "
    								.",iva = '$iva'"
    								.",impuesto = '$impuesto' WHERE numnotcre = '$invoiceNumber'  ";
  mssqlConn::insert($query);
  }else{

     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  ". $dbmaster." (numnotcre,fechanot,codclien,codmedico,recipe,usuario,fecreg,horareg,statfact,cancelado,monto_abonado,tipopago,codseguro,plazo,vencimiento,subtotal,total,TotImpuesto,descuento,alicuota,iva,impuesto,workstation,ipaddress)"
                            . " Values ('$invoiceNumber','$fechafac','$codclien','$codmedico','$recipe','$usuario','$fechafac','$hora','1','0','0','0','$seguro','0' ,'$fechafac','$subtotal','$total','$TotImpuesto','$descuento','$alicuota','$iva','$impuesto',  '$workstation', '$ipaddress' )";
       mssqlConn::insert($query);
      }
return $invoiceNumber;
}


 function saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro,  $workstation, $ipaddress ,$dbdetails){

//  $query="SELECT *  from cma_MFactura  WHERE numfactu = '$invoiceNumber' ";
//  $res = mssqlConn::Listados($query);
//  $result = json_decode($res, true);
//  if($len>0){
//     if ($usuario!==$result[0]['usuario'] || $result[0]['statfact']=='3') {
//       return;
//     }
// }

$prodInfo     = getProductsData($coditems);
$aplicaiva    = $prodInfo['aplicaiva'] ;
$aplicadcto   = $prodInfo['aplicadcto'] ;
$aplicacommed = $prodInfo['aplicacommed'];
$aplicacomtec = $prodInfo['aplicacomtec'];
$costo        = $prodInfo['costo'];


$hora =  date("H:i:s", time()); 
$query="INSERT INTO ".$dbdetails."    (numnotcre        ,fechanot    ,coditems   ,cantidad    ,precunit ,codtipre      ,usuario   ,fecreg     ,horareg, aplicaiva  ,aplicadcto   ,aplicacommed  ,aplicacomtec     ,costo   ,monto_imp ,descuento ,porcentaje       ,workstation    , ipaddress,tipoitems) "
        . "                VALUES  ('$invoiceNumber','$fechafac','$coditems', '$cantidad','$precio','$listaprecio','$usuario','$fechafac','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','$tax'     ,'$descuento','$detaialprcnt', '$workstation', '$ipaddress','M')";

        mssqlConn::insert($query);
 }
?> 