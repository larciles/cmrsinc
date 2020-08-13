 <?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

 $number = count($_POST["serv"]);  
 
 
 if($number > 0)  
 {  

  $hora    = date("H:i:s");  
  $numfactu    = trim($_POST["numfactu"]," ");
  $codclien    = trim($_POST["idassoc"]," ");  //codclien
  $codmedico   = trim($_POST["medico"]," ");  //codmedico
  $fechafac    = trim($_POST["fecha"]," ");   //fechafac
  $usuario     = trim($_POST["idusr"]," ");   //usuario
  $observacion    = $_POST["observacion"]; //subtotal
  $descuento   = ($_POST["discamount"] == "" ? "0" : $_POST["discamount"] );  //descuento
  $alicuota    = ($_POST["discprcntg"] == "" ? "0" : $_POST["discprcntg"] );  // Alicuota
  $TotImpuesto = ($_POST["frmtax"] == "" ? "0" : $_POST["frmtax"] ) ;  //TotImpuesto
  $frmshipping = ($_POST["frmshipping"] == "" ? "0" : $_POST["frmshipping"] );   // monto_flete
  $total       = $_POST["frmtotal"];  //   total  (TIENE INCLUIDO EL shipping HAY QUE RESTARLO)
  $iva         = $_POST["taxilicuota"];  //  IVA
  $workstation = "FARMACIA01";//trim($_POST["workstation"]," ");
  $ipaddress   = trim($_POST["ipaddress"]," ");
  $seguro      = trim($_POST["codseguro"], " ");

if (isset($_POST['delallrecords']) && $_POST['delallrecords']=='1') {

                              //    fechanot  observacion statunot  fechanul  desanul usuario workstation ipaddress fecreg  horareg Id  vcod  codclien

                             
                                 
    $ultimoEntrega = saveMaster($fechafac,$observacion,  $usuario,$hora,$codclien);
    //calculaImpuestos($ultimoEntrega,$subtotal,$descuento,$TotImpuesto);
 }else{
    $ultimoEntrega = saveMaster($fechafac,$observacion,  $usuario,$hora,$codclien);

    //calculaImpuestos($ultimoEntrega,$subtotal,$descuento,$TotImpuesto);

    if ($numfactu!=="") {
       deleteRecords($ultimoEntrega);
    }  

  for($i=0; $i<$number; $i++)  
  {  
       if(trim($_POST["serv"][$i] != ''))  
       {   
          if ($_POST["cantidad"][$i]!=="") {          
              
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
              $costo        = getProductsData($coditems);

                           //numnotent   coditems   cantidad  costo  fechanot   usuario  fecreg  horareg 
              saveDetails($ultimoEntrega,$coditems,$cantidad,$costo,$fechafac, $usuario ,$hora);
          }
       }  
  }  

 }
  


 echo $ultimoEntrega;  //print_r($_POST);  
 }  
 else {  

     echo $ultimoEntrega;  //print_r($_POST);    
 }  

function getInvoiceNumber(){
  $ultimoEntrega=1;
  $query="SELECT * from empresa where id_centro='1' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $ultimoEntrega = $result[0]['ultimoEntrega']; 
     $ultimoEntrega++;
  }
  $ultimoEntrega =  str_pad($ultimoEntrega, 7, '0', STR_PAD_LEFT); 
  $query="UPDATE empresa SET ultimoEntrega ='$ultimoEntrega' where id_centro='1' ";
  mssqlConn::insert($query);
 return  $ultimoEntrega;
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
  $costo =0;
  if($len>0){ 
    $array['aplicaiva']    = $result[0]['aplicaiva'];
    $array['aplicadcto']   = $result[0]['aplicadcto'];
    $array['aplicacommed'] = $result[0]['aplicacommed'];
    $array['aplicacomtec'] = $result[0]['aplicacomtec'];
    $costo       = $result[0]['costo'];
  }

  return  $costo;
}

function saveMaster($fechafac,$observacion,$usuario,$hora,$codclien){
 
  $ultimoEntrega=getInvoiceNumber();

  $query="SELECT *  from  NotaEntrega  WHERE numnotent = '$ultimoEntrega' ";
  $res = mssqlConn::Listados($query);
  $result = json_decode($res, true);
  $len = sizeof($result);
  if($len>0){
     $query="UPDATE  NotaEntrega  SET codclien ='$codclien' Where numnotent='$numfactu' " ;
       mssqlConn::insert($query);
  }else{
    
     $hora =  date("H:i:s", time()); 
     $query="INSERT INTO  NotaEntrega (numnotent, fechanot, observacion, usuario, fecreg, horareg, codclien)"
                            . " Values ('$ultimoEntrega','$fechafac','$observacion','$usuario','$fechafac','$hora','$codclien')";
       mssqlConn::insert($query);
      }
return $ultimoEntrega;
}


function saveDetails($ultimoEntrega,$coditems,$cantidad,$costo,$fechafac, $usuario ,$hora){

    inventoryUpdate($coditems,$cantidad);

    $hora =  date("H:i:s", time()); 
    $query="INSERT INTO NotEntDetalle   (numnotent, coditems,  cantidad, costo,  fechanot,  usuario,  fecreg,  horareg ) "
                   . " VALUES  ('$ultimoEntrega','$coditems','$cantidad','$costo','$fechafac', '$usuario', '$fechafac' ,'$hora')";
    mssqlConn::insert($query);
 }


 function inventoryUpdate($coditems,$qty){
    $query="UPDATE MInventario SET  existencia = existencia - $qty Where coditems ='$coditems' ";
    mssqlConn::insert($query);
 }

 function deleteRecords($invoiceNumber){
   $query="SELECT * from NotEntDetalle  WHERE numnotent = '$invoiceNumber' ";
   $res = mssqlConn::Listados($query);
   $result = json_decode($res, true);
   $len = sizeof($result);
   if($len>0){
      for ($i=0; $i <=  $len-1 ; $i++) { 
          $qty = $result[$i]['cantidad']; 
          $coditems = $result[$i]['coditems']; 
          $qty = $qty*-1;
          inventoryUpdate($coditems,$qty);
          $query="DELETE FROM NotEntDetalle WHERE numnotent = '$invoiceNumber' and coditems = '$coditems'";
          mssqlConn::insert($query);
      } 
   }

 }

 function calculaImpuestos($invoiceNumber,$subtotal,$descuento,$TotImpuesto){
  
  $query="DELETE FROM ImpxFact WHERE numfactu = '$invoiceNumber' ";
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
              $query="INSERT INTO ImpxFact (numfactu,codimp,base,porcentaje,montoimp) VALUES ('$invoiceNumber','$codimp','$baseimponible','$porcentaje','$montoimp')";
              mssqlConn::insert($query);

         }
      }

  }

  }

?> 