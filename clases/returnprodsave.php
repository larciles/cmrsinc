 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

 $number = count($_POST["serv"]);  
 if($number > 0)  
 {  

  
  $numfactu    = trim($_POST["numfactu"]," ");
  $codclien    = trim($_POST["idassoc"]," ");  //codclien
  $codmedico   = trim($_POST["medico"]," ");  //codmedico
  $fechafac    = trim($_POST["fecha"]," ");   //fechafac
  $usuario     = trim($_POST["idusr"]," ");   //usuario
  $subtotal    = $_POST["frmsubtotal"]; //subtotal
  $descuento   = ($_POST["discamount"] == "" ? "0" : $_POST["discamount"] );  //descuento
  $alicuota    = ($_POST["discprcntg"] == "" ? "0" : $_POST["discprcntg"] );  // Alicuota
  $TotImpuesto = ($_POST["frmtax"] == "" ? "0" : $_POST["frmtax"] ) ;  //TotImpuesto
  $frmshipping = ($_POST["frmshipping"] == "" ? "0" : $_POST["frmshipping"] );   // monto_flete
  $total       = $_POST["frmtotal"];  //   total  (TIENE INCLUIDO EL shipping HAY QUE RESTARLO)
  $iva         = $_POST["taxilicuota"];  //  IVA
  $workstation = trim($_POST["workstation"]," ");
  $ipaddress   = trim($_POST["ipaddress"]," ");
  $seguro      = trim($_POST["codseguro"], " ");
  $devolucion  = trim($_POST["devolucion"], " ");
  $devolucion  = trim($_POST["devnumber"], " ");

  $invoiceNumber = saveMaster($codclien,$codmedico, $fechafac ,  $usuario ,$subtotal,$descuento , $alicuota, $TotImpuesto,$frmshipping, $total-$frmshipping, $iva, $seguro,$workstation,$ipaddress, $numfactu,$devolucion);
  calculaImpuestos($invoiceNumber,$subtotal,$descuento,$TotImpuesto);
  if ($devolucion !=="") {
     deleteRecords($invoiceNumber);
  }
  

  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["name"][$i] != ''))  
       {   
          if ($_POST["serv"][$i]!=="") {          
              
              $coditems     = trim($_POST["serv"][$i]," ");  // coditems     [serv]
              $codseguro    = $seguro;   //codseguro
              $codtipre     = trim($_POST["listaprecio"][$i]," ");  //  codtipre      [listaprecio]
              $aplicaiva    = trim($_POST['apptax'][$i]," "); //aplicaiva
              $aplicadcto   = trim($_POST['aplicadcto'][$i]," ");   //
              $aplicacommed = trim($_POST['aplicaComMed'][$i]," "); 
              $aplicacomtec = trim($_POST['aplicaComTec'][$i]," "); 
              $costo        = $_POST['costo'][$i] == "" ? "0" : $_POST['costo'][$i];
              $dosis        = $_POST['dosis'][$i] == "" ? "0" : $_POST['dosis'][$i];
              $cant_sugerida= $_POST['sugerido'][$i] == "" ? "0" : $_POST['sugerido'][$i];
              $cantidad     = $_POST['cantidad'][$i] == "" ? "0" : $_POST['cantidad'][$i];
              $precunit     = $_POST['precio'][$i] == "" ? "0" : $_POST['precio'][$i];
              $descuento    = $_POST['descuento'][$i] ==""  ? "0" : $_POST['descuento'][$i];
              $monto_imp    = $_POST['tax'][$i] == "" ? "0" : $_POST['tax'][$i];
              if ( $coditems  !=="") {
                 saveDetails($invoiceNumber,$coditems,$codseguro,$codtipre,$aplicaiva,$aplicadcto,$aplicacommed,$aplicacomtec,$costo,$dosis,$cant_sugerida,$cantidad,$precunit,$descuento,$monto_imp,$workstation,$ipaddress,$usuario,$codmedico,$fechafac,$alicuota );
             }
          }
       }  
  }  

 echo $invoiceNumber;  //print_r($_POST);  
 }  
 else  
 {  
      
 }  


function saveMaster($codclien,$codmedico,$fechafac,$usuario,$subtotal,$descuento,$alicuota,$TotImpuesto,$frmshipping,$total,$iva,$seguro,$workstation,$ipaddress,$numfactu,$devolucion ){
 
 if ($devolucion !=="") {
    $invoiceNumber = $devolucion ;
  } else{    
    $invoiceNumber=getInvoiceNumber();
   // updateLastClientPurchase($codclien,$invoiceNumber,$fechafac);    
  }

 

  $query="SELECT *  from  mnotacredito  WHERE numnotcre = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $query="UPDATE  mnotacredito  SET codclien ='$codclien',codmedico = '$codmedico', usuario = '$usuario' ,subtotal = '$subtotal' ,descuento = '$descuento',Alicuota='$alicuota',TotImpuesto = '$TotImpuesto',monto_flete = '$frmshipping',total = '$total',iva='$iva',codseguro= '$seguro',workstation='$workstation',ipaddress='$ipaddress',horareg='$hora' Where numnotcre='$numfactu' " ;
       mssqlConn::insert($query);
  }else{
     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
      $query="INSERT INTO  mnotacredito (numnotcre,fechanot,codclien,codmedico,usuario,fecreg,horareg,statnc,cancelado,monto_abonado,tipopago,codseguro,subtotal,totalnot,TotImpuesto,descuento,alicuota,iva,impuesto,workstation,ipaddress,monto,tasadesc,saldo,tipo,codtiponotcre,numfactu,ct,monto_flete)"
                            . " Values  ('$invoiceNumber','$fechafac','$codclien','$codmedico','$usuario','$fechafac','$hora','1','0','0','0','$seguro','$subtotal','$total','$TotImpuesto','$descuento','$alicuota','$iva','$impuesto',  '$workstation', '$ipaddress',0,0,0,'04','1','$numfactu',0 , $frmshipping)";
       mssqlConn::insert($query);
      }
return $invoiceNumber;
}


function saveDetails($invoiceNumber,$coditems,$codseguro,$codtipre,$aplicaiva,$aplicadcto,$aplicacommed,$aplicacomtec,$costo,$dosis,$cant_sugerida,$cantidad,$precunit,$descuento,$monto_imp,$workstation,$ipaddress,$usuario,$codmedico,$fechafac,$alicuota  ){

inventoryUpdate($coditems,$cantidad);

$hora =  date("H:i:s", time()); 

$query="INSERT INTO dnotacredito  (numnotcre        ,fechanot    ,coditems   ,cantidad    ,precunit ,codtipre      ,usuario   ,fecreg     ,horareg, aplicaiva  ,aplicadcto   ,aplicacommed  ,aplicacomtec     ,costo   ,monto_imp ,descuento ,porcentaje       ,workstation    , ipaddress,tipoitems) "
                      . " VALUES  ('$invoiceNumber','$fechafac','$coditems', '$cantidad','$precunit','$codtipre','$usuario','$fechafac','$hora','$aplicaiva','$aplicadcto' ,'$aplicacommed','$aplicacomtec','$costo','$monto_imp'     ,'$descuento','$alicuota', '$workstation', '$ipaddress','P')";
        mssqlConn::insert($query);
 }


 function inventoryUpdate($coditems,$qty){
  $query="UPDATE MInventario SET  existencia = existencia + $qty Where coditems ='$coditems' ";
  mssqlConn::insert($query);
 }

 function deleteRecords($invoiceNumber){
   $query="SELECT * from dnotacredito  WHERE numnotcre = '$invoiceNumber' ";
   $res = mssqlConn::Listados($query);
   $result = json_decode($res, true);
   $len = sizeof($result);
   if($len>0){
      for ($i=0; $i <=  $len-1 ; $i++) { 
          $qty = $result[$i]['cantidad']; 
          $coditems = $result[$i]['coditems']; 
          $qty = $qty*-1;
          inventoryUpdate($coditems,$qty);
          $query="DELETE FROM dnotacredito WHERE numnotcre = '$invoiceNumber' and coditems = '$coditems'";
          mssqlConn::insert($query);
      } 
   }

 }

 function getInvoiceNumber(){
  $invoiceNumber=1;
  $query="SELECT * from empresa where id_centro='1' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $invoiceNumber = $result[0]['UltimoCredito']; 
     $invoiceNumber++;
  }
  $invoiceNumber =  str_pad($invoiceNumber, 7, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET UltimoCredito ='$invoiceNumber' where id_centro='1' ";
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
  }

  return  $array;
}

function calculaImpuestos($invoiceNumber,$subtotal,$descuento,$TotImpuesto){
  
  $query="DELETE FROM ImpxFactDevProd WHERE numfactu = '$invoiceNumber' ";
  mssqlConn::insert($query); 
  
  $taxfac = (int)$TotImpuesto ;
  if ($taxfac>0) {
      $query="SELECT *  from  Impuestos  where Activo=1 ";
      $res = mssqlConn::Listados($query);
      $impt = json_decode($res, true);
      $lenimp = sizeof($impt);

      $baseimponible = $subtotal-$descuento;

      if($lenimp>0){
         for ($i=0; $i < $lenimp ; $i++) { 
              
              $montoimp  =($impt[$i]['Porcentaje'] * $baseimponible )/100;
              $porcentaje=$impt[$i]['Porcentaje']; 
              $codimp    =$impt[$i]['Codigo'];   
              $query="INSERT INTO ImpxFactDevProd (numfactu,codimp,base,porcentaje,montoimp) VALUES ('$invoiceNumber','$codimp','$baseimponible','$porcentaje','$montoimp')";
              mssqlConn::insert($query);

         }
      }

  }

  }

?> 