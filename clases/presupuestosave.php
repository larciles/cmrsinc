 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
date_default_timezone_set("America/Puerto_Rico");
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
  
  if ($_POST["discamount"] == "" || $_POST["discamount"] == "$0") {
      $descuento   = 0;  //descuento
  }else{
     $descuento   =   $_POST["discamount"]   ;  //descuento 
  }
  
    
  $alicuota    = ($_POST["discprcntg"] == "" ? "0" : $_POST["discprcntg"] );  // Alicuota
  $TotImpuesto = ($_POST["frmtax"] == "" ? "0" : $_POST["frmtax"] ) ;  //TotImpuesto
  $frmshipping = ($_POST["frmshipping"] == "" ? "0" : $_POST["frmshipping"] );   // monto_flete
  $total       = $_POST["frmtotal"];  //   total  (TIENE INCLUIDO EL shipping HAY QUE RESTARLO)
  $iva         = $_POST["taxilicuota"];  //  IVA
  $workstation = "FARMACIA01";//trim($_POST["workstation"]," ");
  $ipaddress   = trim($_POST["ipaddress"]," ");
  $seguro      = trim($_POST["codseguro"], " ");
  $medio      = trim($_POST["medio"], " ");
 
  
 
if (isset($_POST['delallrecords']) && $_POST['delallrecords']=='1') {

    $invoiceNumber = saveMaster($codclien,$codmedico, $fechafac ,  $usuario ,0,0 , $alicuota, 0,0, 0, 0, $seguro,$workstation,$ipaddress, $numfactu,$medio);
    
 }else{
    $invoiceNumber = saveMaster($codclien,$codmedico, $fechafac ,  $usuario ,$subtotal,$descuento , $alicuota, $TotImpuesto,$frmshipping, $total-$frmshipping, $iva, $seguro,$workstation,$ipaddress, $numfactu,$medio);

    

    if ($numfactu!=="") {
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
              


              $aply = getProductsData($coditems);              
              

              $aplicadcto   = trim($_POST['aplicadcto'][$i]," ");   //
              if ($aplicadcto=='undefined') $aplicadcto=0 ;

              $aplicacommed = trim($_POST['aplicaComMed'][$i]," "); 
              if ($aplicacommed=='undefined') $aplicacommed=0 ;

              $aplicacomtec = trim($_POST['aplicaComTec'][$i]," "); 
              if ($aplicacomtec=='undefined') $aplicacomtec=0 ;

              $costo        = $_POST['costo'][$i] == "" ? "0" : $_POST['costo'][$i];
              if ($costo=='undefined') $costo=0 ;
              
              $dosis        = $_POST['dosis'][$i] == "" ? "0" : $_POST['dosis'][$i];
              $cant_sugerida= $_POST['sugerido'][$i] == "" ? "0" : $_POST['sugerido'][$i];
              $cantidad     = $_POST['cantidad'][$i] == "" ? "0" : $_POST['cantidad'][$i];
              $precunit     = $_POST['precio'][$i] == "" ? "0" : $_POST['precio'][$i];
              $descuento    = $_POST['descuento'][$i] ==""  ? "0" : $_POST['descuento'][$i];
              $descuento    = $_POST['descuento'][$i] =="$0"  ? "0" : $_POST['descuento'][$i];
              
              $monto_imp    = $_POST['tax'][$i] == "" ? "0" : $_POST['tax'][$i];
              saveDetails($invoiceNumber,$coditems,$codseguro,$codtipre,$aplicaiva,$aplicadcto,$aplicacommed,$aplicacomtec,$costo,$dosis,$cant_sugerida,$cantidad,$precunit,$descuento,$monto_imp,$workstation,$ipaddress,$usuario,$codmedico,$fechafac);
          }
       }  
  }  

 }
  


 echo $invoiceNumber;  //print_r($_POST);  
 }  
 else {  

     echo $invoiceNumber;  //print_r($_POST);    
 }  

function getInvoiceNumber(){
  $invoiceNumber=1;
  $query="SELECT * from empresa where id_centro='1' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $invoiceNumber = $result[0]['PRESUPUESTO']; 
     $invoiceNumber++;
  }
  $invoiceNumber =  str_pad($invoiceNumber, 7, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET PRESUPUESTO ='$invoiceNumber' where id_centro='1' ";
  mssqlConn::insert($query);
 return  $invoiceNumber;
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

function saveMaster($codclien,$codmedico,$fechafac,$usuario,$subtotal,$descuento,$alicuota,$TotImpuesto,$frmshipping,$total,$iva,$seguro,$workstation,$ipaddress,$numfactu,$medio ){

     
    if ($medio=='') {
        $medio="0";
    }   
 $date = DateTime::createFromFormat('m/d/Y H:i:s', $fechafac.' '.date("H:i:s", time()));
 $fecreg= $date->format('Y-m-d H:i:s');

 if ($numfactu!=="") {
    $invoiceNumber = $numfactu;
  } else{    
    $invoiceNumber=getInvoiceNumber();
    
  }

  $query="SELECT *  from  presupuesto_m  WHERE numfactu = '$invoiceNumber' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
    //, usuario = '$usuario' 
     $hora =  date("H:i:s", time()); 
     $query="UPDATE  presupuesto_m  SET codclien ='$codclien',codmedico = '$codmedico',subtotal = '$subtotal' ,descuento = '$descuento',Alicuota='$alicuota',TotImpuesto = '$TotImpuesto',monto_flete = '$frmshipping',total = '$total',iva='$iva',codseguro= '$seguro',workstation='$workstation',ipaddress='$ipaddress',horareg='$hora',medios='$medio' Where numfactu='$numfactu' " ;
       mssqlConn::insert($query);
  }else{
     $recipe = ($codclien == "000" ? false : true); 
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  presupuesto_m (numfactu,codclien,codmedico,fechafac,usuario,subtotal,descuento,Alicuota,TotImpuesto,monto_flete,total,iva,codseguro,workstation,ipaddress,vencimiento,fecreg,horareg,recipe,cancelado,medios)"
                            . " Values ('$invoiceNumber','$codclien','$codmedico','$fechafac','$usuario','$subtotal','$descuento','$alicuota','$TotImpuesto','$frmshipping','$total','$iva','$seguro','$workstation','$ipaddress','$fechafac','$fecreg','$hora','$recipe','0','$medio' )";
       mssqlConn::insert($query);
      }
return $invoiceNumber;
}


function saveDetails($invoiceNumber,$coditems,$codseguro,$codtipre,$aplicaiva,$aplicadcto,$aplicacommed,$aplicacomtec,$costo,$dosis,$cant_sugerida,$cantidad,$precunit,$descuento,$monto_imp,$workstation,$ipaddress,$usuario,$codmedico,$fechafac ){

$date = DateTime::createFromFormat('m/d/Y H:i:s', $fechafac.' '.date("H:i:s", time()));
$fecreg= $date->format('Y-m-d H:i:s');


 

$hora =  date("H:i:s", time()); 
$query="INSERT INTO presupuesto_d   (numfactu,fechafac,fecreg,horareg,coditems,codseguro,codtipre,aplicaiva,aplicadcto,aplicacommed,aplicacomtec,costo,dosis,cant_sugerida,cantidad,precunit,descuento,monto_imp,workstation,ipaddress,usuario,Codmedico ) "
                   . " VALUES  ('$invoiceNumber','$fechafac','$fecreg','$hora','$coditems','$codseguro','$codtipre','$aplicaiva','$aplicadcto','$aplicacommed','$aplicacomtec','$costo','$dosis','$cant_sugerida','$cantidad','$precunit','$descuento','$monto_imp','$workstation','$ipaddress','$usuario','$codmedico' )";
        mssqlConn::insert($query);
 }


 
  function findKit($coditems,$qty,$numfactu){
        $len=0;
        $query="SELECT * from kit where coditems='$coditems' ";
        $res = mssqlConn::Listados($query);
        $result = json_decode($res, true);
        $len = sizeof($result);
        if ($len>0) {
            for ($i=0; $i <$len ; $i++) { 
                $codikit= $result[$i]['codikit'];
                
            }
        } 

        return $len;
    } 

 function deleteRecords($invoiceNumber){
   $query="SELECT * from presupuesto_d  WHERE numfactu = '$invoiceNumber' ";
   $res = mssqlConn::Listados($query);
   $result = json_decode($res, true);
   $len = sizeof($result);
   if($len>0){
      for ($i=0; $i <=  $len-1 ; $i++) { 
          $qty = $result[$i]['cantidad']; 
          $coditems = $result[$i]['coditems']; 
          $qty = $qty*-1;
          
          $query="DELETE FROM presupuesto_d WHERE numfactu = '$invoiceNumber' and coditems = '$coditems'";
          mssqlConn::insert($query);
      } 
   }

 }


  

?> 