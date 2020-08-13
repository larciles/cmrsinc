 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

 $number = count($_POST["name"]);  
 if($number > 0)  
 {  

  $codclien    = $_POST["idassoc"];
  $codmedico   = $_POST["medico"];
  $fechafac    = $_POST["fecha"];
  $usuario     = $_POST["idusr"];
  
  $frmsubtotal = $_POST["frmsubtotal"];
  $discamount  = $_POST["discamount"];
  $discprcntg  = $_POST["discprcntg"]; 
  $frmtax      = $_POST["frmtax"];
  $frmshipping = $_POST["frmshipping"];
  $frmtotal    = $_POST["frmtotal"];


  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["name"][$i] != ''))  
       {           
            $coditems    = $_POST["coditems"][$i] ;           
            $listaprecio = $_POST["codprecio"][$i] ; 
            $seguro      = $_POST["insurance"][$i] ;
            $cantidad    = $_POST["cantidad"][$i] ;
            $precio      = $_POST["precio"][$i] ;
            $descuento   = $_POST["descuento"][$i] ;
            $tax         = $_POST["tax"][$i] ;
            $subtotal    = $_POST["name"][$i] ; 
       }  
  }  
      echo print_r($_POST);  
 }  
 else  
 {  
      echo "Please Enter Name";  
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
  $array[];
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

function saveMaster(){
 
  $invoiceNumber=getInvoiceNumber();
  updateLastClientPurchase($codclien,$invoiceNumber,$fechafac);

  $invoiceNumber=1;
  $query="SELECT *  from cma_MFactura  WHERE numfactu = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    
  }else{

     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  cma_MFactura (numfactu,fechafac,codclien,codmedico,recipe,usuario,fecreg,horareg,statfact,cancelado,monto_abonado,tipopago,codseguro,plazo,vencimiento)"
                            . " Values ('$invoiceNumber','$fechafac','$codclien','$codmedico','$recipe','$usuario','$fechafac','$hora','1','0','0','0','$seguro','0' ,'$fechafac')";

      }
  }


 function saveDetails($usuario,$invoiceNumber,$coditems, $cantidad, $precio,$listaprecio,$usuario,$fechafac,$descuento,$tax ){
 
 $txtimpuesto = 0;
 $txtdescuento = 0;
 $TxtSubtotal = 0;

 $query="SELECT *  from cma_MFactura  WHERE numfactu = '$invoiceNumber' ";
 $res = mssqlConn::Listados($query);
 $result = json_decode($res, true);
 if($len>0){
    if ($usuario!==$result[0]['usuario'] || $result[0]['statfact']=='3') {
      return;
    }
}

$prodInfo     = getProductsData($coditems);
$aplicaiva    = $prodInfo['aplicaiva'] ;
$aplicadcto   = $prodInfo['aplicadcto'] ;
$aplicacommed = $prodInfo['aplicacommed'];
$aplicacomtec = $prodInfo['aplicacomtec'];
$costo        = $prodInfo['costo'];

$hora =  date("H:i:s", time()); 
$query="INSERT INTO cma_DFactura   (numfactu        ,fechafac    ,coditems   ,cantidad    ,precunit  ,codtipre      ,usuario   ,fecreg     ,horareg, aplicaiva,aplicadcto ,aplicacommed,aplicacomtec,costo,monto_imp ,descuento,codseguro,procentaje,percentage)                             "
        . "                VALUES  ('$invoiceNumber','$fechafac','$coditems', '$cantidad','$precio','$listaprecio','$usuario','$fechafac','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','0',$descuento, $tax,$tax)";
 }
 ?> 