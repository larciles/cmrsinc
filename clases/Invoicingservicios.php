 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

if (isset($_POST["name"])) {
  $number = count($_POST["name"]); 
}else{
   $number =0;
}
  
 
 // if (is_array($var_name)){

 // }

 if($number > 0)  
 {  

  $codclien    = $_POST["idassoc"];
  $codmedico   = $_POST["medico"];

  
  if (isset($_POST["medicohd"]) && $_POST["medicohd"]!="") {
    $codmedico   = $_POST["medicohd"];
  }


  $fechafac    = $_POST["fecha"];
  $usuario     = $_POST["idusr"];
  
  $subtotal    = $_POST["frmsubtotal"];
  $descuento  = ($_POST["discamount"] == "" ? "0" : $_POST["discamount"] );
  $alicuota  = ($_POST["discprcntg"] == "" ? "0" : $_POST["discprcntg"] ); 
  $TotImpuesto = ($_POST["frmtax"] == "" ? "0" : $_POST["frmtax"] ) ;
  $frmshipping = ($_POST["frmshipping"] == "" ? "0" : $_POST["frmshipping"] ); 
  $total       = $_POST["frmtotal"];
  $iva =0;
  $impuesto=0;

  $workstation = $_POST["workstation"];
  $ipaddress    = $_POST["ipaddress"];
  $medio      = trim($_POST["medio"], " ");
 

  $factura = $_POST["factura"];

  // round(1.95583, 2);
  $xserv = $_POST["coditems"][0];
  $workstation ="LASERTERAPYPC";
  if( strpos($xserv,'ST') ) { 
      $workstation  = $_POST["workstation"];
  }
  $seguro      = $_POST["insurance"][0];

  if ($factura!=="") {
       deleteRecords($factura);
  }  


  if (isset($_POST['delallrecords']) && $_POST['delallrecords']=='1') {

    $invoiceNumber = saveMaster($fechafac,$codclien,$codmedico,$usuario,$seguro,0,0,0,0,$alicuota,$iva,$impuesto,   $workstation, $ipaddress,$medio,$factura );

    //calculaImpuestos($invoiceNumber,$subtotal,$descuento,$TotImpuesto);
 }else{ 
  //$invoiceNumber = saveMaster($fechafac,$codclien,$codmedico,$usuario,$fechafac,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress );

    $invoiceNumber = saveMaster($fechafac,$codclien,$codmedico,$usuario,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,   $workstation, $ipaddress,$medio,$factura );

    for($i=0; $i<$number; $i++)  
    {  
        if(trim($_POST["name"][$i] != ''))  
        {           
             $coditems    = $_POST["coditems"][$i];           
             $listaprecio = $_POST["codprecio"][$i]; 
             $seguro      = $_POST["insurance"][$i];
             $cantidad    = $_POST["cantidad"][$i];
             $precio      = $_POST["precio"][$i];
             $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]); 
             $tax         = ($_POST["tax"][$i] == "" ? "0" : $_POST["tax"][$i] );
             $subtotal    = $_POST["name"][$i]; 
             $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );
             $descuento=( ($precio*$cantidad )* $detaialprcnt ) /100;
             if ( $coditems  !=="") {
                saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro, $workstation, $ipaddress  );
             }
            
        }  
    } 
    } 
      echo json_encode($invoiceNumber); // print_r($_POST);  

 }  
 else{  

    $factura = $_POST["factura"];
    $codclien    = $_POST["idassoc"];
    $codmedico   = $_POST["medico"];
    $fechafac    = $_POST["fecha"];
    $usuario     = $_POST["idusr"];
  
       saveMaster($fechafac,$codclien,$codmedico,$usuario,$seguro,0,0,0,0,0,0,0,   '', '','',$factura );

 }  

function getInvoiceNumber(){
  $invoiceNumber=1;
  $query="SELECT * from empresa where id_centro='2' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $invoiceNumber = $result[0]['UltimaFactura']; 
     $invoiceNumber++;
  }
  $invoiceNumber =  str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET UltimaFactura ='$invoiceNumber' where id_centro='2' ";
  mssqlConn::insert($query);
 return  $invoiceNumber;
}


function updateLastClientPurchase($codclien,$invoiceNumber,$fechafac){
  $query="UPDATE MClientes SET numfactu='$invoiceNumber',fechafac='$fechafac ' where codclien ='$codclien' ";
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
    $array['kit']         = $result[0]['kit'];
  }

  return  $array;
}

function saveMaster($fechafac,$codclien,$codmedico,$usuario,$seguro,$subtotal,$total,$TotImpuesto,$descuento,$alicuota,$iva,$impuesto,  $workstation, $ipaddress,$medio , $factura){
 
  $date = DateTime::createFromFormat('m/d/Y H:i:s', $fechafac.' '.date("H:i:s", time()));
  $fecreg= $date->format('Y-m-d H:i:s');
  
  if ($factura!=="") {
    $invoiceNumber=$factura;
  } else {
    $invoiceNumber=getInvoiceNumber();
    updateLastClientPurchase($codclien,$invoiceNumber,$fechafac);
  }
  

  $query="SELECT *  from cma_MFactura  WHERE numfactu = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $query="UPDATE  cma_MFactura  SET codclien ='$codclien',codmedico = '$codmedico',subtotal = '$subtotal' ,descuento = '$descuento',Alicuota='$alicuota',TotImpuesto = '$TotImpuesto',total = '$total',iva='$iva',codseguro= '$seguro',workstation='$workstation',ipaddress='$ipaddress',horareg='$hora',medios='$medio' Where numfactu='$invoiceNumber' " ;
       mssqlConn::insert($query);
  }else{

     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  cma_MFactura (numfactu,fechafac,codclien,codmedico,recipe,usuario,fecreg,horareg,statfact,cancelado,monto_abonado,tipopago,codseguro,plazo,vencimiento,subtotal,total,TotImpuesto,descuento,alicuota,iva,impuesto,workstation,ipaddress,medios)"
                            . " Values ('$invoiceNumber','$fechafac','$codclien','$codmedico','$recipe','$usuario','$fecreg','$hora','1','0','0','0','$seguro','0' ,'$fechafac','$subtotal','$total','$TotImpuesto','$descuento','$alicuota','$iva','$impuesto',  '$workstation', '$ipaddress','$medio' )";
       mssqlConn::insert($query);
      }
return $invoiceNumber;
}


 function saveDetails($invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax,$detaialprcnt,$seguro,  $workstation, $ipaddress ){

 

$date = DateTime::createFromFormat('m/d/Y H:i:s', $fechafac.' '.date("H:i:s", time()));
$fecreg= $date->format('Y-m-d H:i:s'); 
//$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
//$fecreg = $now->format($fechafac.".u");


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
$query="INSERT INTO cma_DFactura   (numfactu        ,fechafac    ,coditems   ,cantidad    ,precunit ,codtipre      ,usuario   ,fecreg     ,horareg, aplicaiva  ,aplicadcto   ,aplicacommed  ,aplicacomtec     ,costo   ,monto_imp ,descuento,  codseguro  ,procentaje    ,percentage     ,workstation    , ipaddress) "
        . "                VALUES  ('$invoiceNumber','$fechafac','$coditems', '$cantidad','$precio','$listaprecio','$usuario','$fecreg','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','$tax'     ,'$descuento','$seguro','$detaialprcnt','$detaialprcnt', '$workstation', '$ipaddress')";
        mssqlConn::insert($query);
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
  $query="UPDATE MInventario SET  existencia = existencia - $qty Where coditems ='$coditems' ";
  mssqlConn::insert($query);
}

 function deleteRecords($invoiceNumber){
   $query="SELECT * from cma_DFactura  WHERE numfactu = '$invoiceNumber' ";
   $res = mssqlConn::Listados($query);
   $result = json_decode($res, true);
   $len = sizeof($result);
   if($len>0){
      for ($i=0; $i <=  $len-1 ; $i++) { 
          $qty = $result[$i]['cantidad']; 
          $coditems = $result[$i]['coditems']; 
          $qty = $qty*-1;

          $prodInfo     = getProductsData($coditems);
          $kit          = $prodInfo['kit'];
 
          if ($kit=='1') {
             findKit($coditems,$qty);
          }else{
             inventoryUpdate($coditems,$qty);
          }
          
          $query="DELETE FROM cma_DFactura WHERE numfactu = '$invoiceNumber' and coditems = '$coditems'";
          mssqlConn::insert($query);
      } 
   }

 }

?> 