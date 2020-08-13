 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

 $number = count($_POST["subtotal"]);  
 if($number > 0)  
 {  

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
  $numfactu    = $_POST["numfactu"];
  $iva =0;
  $impuesto=0;

  $workstation = $_POST["workstation"];
  $ipaddress    = $_POST["ipaddress"];
 

 // round(1.95583, 2);


  $seguro      = $_POST["insurance"][0];

  //$invoiceNumber = saveMaster($fechafac,$codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress );
  $invoiceNumber = saveMaster($codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  'LASERPC1', $ipaddress,$numfactu );

  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["subtotal"][$i] != ''))  
       {           
            $coditems    = $_POST["serv"][$i];           
            $listaprecio = $_POST["codprecio"][$i]; 
            $seguro      = $_POST["insurance"][$i];
            $cantidad    = $_POST["cantidad"][$i];
            $precio      = $_POST["precio"][$i];
            $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]); 
            $tax         = ($_POST["tax"][$i] == "" ? "0" : $_POST["tax"][$i] );
            $subtotal    = $_POST["subtotal"][$i]; 
            $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );

            saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro, $workstation, $ipaddress  );
       }  
  }  
      echo json_encode($invoiceNumber); // print_r($_POST);  
 }  
 else  
 {  
      echo "Please Enter Name";  
 }  

function getInvoiceNumber(){
  $invoiceNumber=1;
  $query="SELECT * from empresa where id_centro='3' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $invoiceNumber = $result[0]['UltimoCredito']; 
     $invoiceNumber++;
  }
  $invoiceNumber =  str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET UltimoCredito ='$invoiceNumber' where id_centro='3' ";
  mssqlConn::insert($query);
 return  $invoiceNumber;
}


function updateLastClientPurchase($codclien,$invoiceNumber,$fechafac){
 // $query="UPDATE MClientes SET numfactu='$invoiceNumber',fechafac='$fechafac ' where codclien ='$codclien' ";
 // mssqlConn::insert($query);
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
    $array['kit']         = $result[0]['kit'];
  }

  return  $array;
}

function saveMaster($codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress,$numfactu ){
 
  $invoiceNumber=getInvoiceNumber();
  updateLastClientPurchase($codclien,$invoiceNumber,$fechafac);


  $query="SELECT *  from MSSMDev  WHERE numnotcre = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    
  }else{

     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $fecha=date("Y-m-d");
     $query="INSERT INTO  MSSMDev (numnotcre,fechanot,codclien,codmedico,usuario,fecreg,horareg,statnc,cancelado,monto_abonado,tipopago,codseguro,subtotal,totalnot,TotImpuesto,descuento,alicuota,iva,impuesto,workstation,ipaddress,monto,tasadesc,saldo,tipo,numfactu)"
                            . " Values ('$invoiceNumber','$fechafac','$codclien','$codmedico','$usuario','$fecha','$hora','1','0','0','0','$seguro','$subtotal','$total','$TotImpuesto','$descuento','$alicuota','$iva','$impuesto',  '$workstation', '$ipaddress',0,0,0,'04','$numfactu' )";
       mssqlConn::insert($query);
      }
return $invoiceNumber;
}


function inventoryUpdate($coditems,$qty){
  $query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  mssqlConn::insert($query);
}

function findKit($coditems,$qty){
  $query="SELECT * from kit where coditems='$coditems' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  for ($i=0; $i <$len ; $i++) { 
      $codikit= $result[$i]['codikit'];
      inventoryUpdate($codikit,$qty);
  }
}

 function saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro,  $workstation, $ipaddress ){



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
$query="INSERT INTO MSSDDev   (numnotcre        ,fechanot    ,coditems   ,cantidad    ,precunit ,codtipre      ,usuario   ,fecreg     ,horareg, aplicaiva  ,aplicadcto   ,aplicacommed  ,aplicacomtec     ,costo   ,monto_imp ,descuento ,porcentaje       ,workstation    , ipaddress,tipoitems) "
        . "                VALUES  ('$invoiceNumber','$fechafac','$coditems', '$cantidad','$precio','$listaprecio','$usuario','$fechafac','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','$tax'     ,'$descuento','$detaialprcnt', '$workstation', '$ipaddress','M')";
        mssqlConn::insert($query);
 }
?> 