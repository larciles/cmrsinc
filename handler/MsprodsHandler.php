<?php
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
set_time_limit(0);

session_start();

if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
    $access=$_SESSION['access'];
    $codperfil=$_SESSION['codperfil'];

    $prninvoice=$_SESSION['prninvoice'];
    $autoposprn=$_SESSION['autoposprn'];
    $pathprn=$_SESSION['pathprn'];
}
$d=__DIR__;

require('../controllers/ImpxFactController.php');
require('../controllers/ImpuestosController.php');

require('../controllers/MedicosController.php');
require('../controllers/MediosController.php');
require('../controllers/MInventarioController.php');
require('../controllers/TipoprecioController.php');
require('../controllers/DFacturaController.php');
require('../controllers/KitController.php');
require('../controllers/EmpresaController.php');
require('../controllers/MFacturaController.php');
require('../controllers/MPreciosController.php');
require('../controllers/ExclusivosController.php');
require('../controllers/ClientesController.php');
require('../controllers/MpagosController.php');
require('../controllers/IdcardController.php');
require('../controllers/PrescritopController.php');



if (isset($_POST['q']) && $_POST['q']=="productos" ) {
   $result =  getProductos($value='');
} elseif (isset($_POST['q']) && $_POST['q']=="lp" ) {
   $result =getListaPrecios($value='');
}elseif ( ( isset($_POST['del']) && $_POST['del']=="1" ) && ( isset($_POST['coditems'] )  && $_POST['coditems']!=""  )  ){
   $invoiceNumber=$_POST['factura'];
   $coditems=$_POST['coditems'];
   deleteRecords($invoiceNumber,false,$coditems);
   save(trim($_POST['factura']));
   $result = array("true");
}elseif ( ( isset($_POST['del']) && $_POST['del']=="2" ) && ( isset($_POST['factura'] )  && $_POST['factura']!=""  )  ){
   $invoiceNumber=$_POST['factura'];
   deleteRecords($invoiceNumber,true,'');
   save(trim($_POST['factura']));
   $result = array("true");
}elseif ( isset($_POST['save']) ) {
   $result =  save(trim($_POST['factura']));
}elseif(isset($_POST['price'])){
    $coditems=$_POST['id'];
    $codtipre=$_POST['pl'];
    $result=getPrice($coditems,$codtipre);
 }elseif (isset($_POST['patient']) && isset($_POST['q']) ) {
    $result=getPatient($_POST['q']);
    if ($_POST['factura']!="") {
       updateClient($result[0]['codclien'],$_POST['factura']);
    }
 }elseif (isset($_POST['md']) && isset($_POST['q']) ) {
    updateMD($_POST['q'],$_POST['factura'] );
 }elseif (isset($_POST['media'])  ) {
   $result=loadMedia();
 }elseif (isset($_POST['q']) && isset($_POST['medio']) ) {
   $result=updateMedia($_POST['q'],$_POST['factura']);
 }elseif (isset($_POST['exclusive']) && isset($_POST['coditems']) ) {
   $result=getExclusivo($_POST['coditems']);
 }elseif (isset($_POST['pay_data'])) {
   $result=getPayments($_POST['pay_data']);
 }elseif(isset($_POST['cliente'])  && isset($_POST['today']) ){
   $result=checkInvoiceToday($_POST['cliente']);
 } elseif (isset($_POST['q']) && $_POST['q']=="taxes" ) {
   $result=getTaxes();
 }elseif ($_POST['patientupdate']) {
    updateClient($_POST['codclien'],$_POST['factura']);
 }elseif ( isset($_POST['gtotal'])  ) {
   $result= findGranTotalCliente($_POST['codclien']);
 }



echo( json_encode($result) );

//================================================================

function getProductos($value=''){
    $minventariocontroller = new MInventarioController();
    $query="SELECT  b.CapsulasXUni, a.* from  MInventario a left join NTPRODUCTOS b on a.coditems=b.Cod_prod where a.prod_serv='p' and a.activo = 1 order by  a.desitems ";
    $invetario=$minventariocontroller->readUDF($query);
    return $invetario;
}

//------------------------------------------------------------------

function getListaPrecios($value=''){
    $tipopreciocontroller = new TipoprecioController();
    $query="SELECT * from  tipoprecio order by codtipre";
    $lprecios=$tipopreciocontroller->readUDF($query);
    return $lprecios;
}

//------------------------------------------------------------------

 function deleteRecords($invoiceNumber,$allYN,$coditems){
   $mpagosController = new MpagosController();
   $idcardController = new IdcardController();
   $dfacturacontroller = new DFacturaController();
   
   if ($allYN==true) {
        #LOS ELIMINA TODOS
        
        $detf = $dfacturacontroller->readAllW( array('numfactu' =>$invoiceNumber) );
    }else{
        
        $detf = $dfacturacontroller->readAllW( array('numfactu' =>$invoiceNumber,'coditems'=>$coditems) );
    } 
    
    $mpagosController->del($invoiceNumber,'1','01');
    $idcardController->del($invoiceNumber,'1','01');

    $len=sizeof($detf);
    if (isset($detf) && $len  >0 ){

      for ($i=0; $i <  $len ; $i++) { 
          $qty = $detf[$i]['cantidad']; 
          $coditems = $detf[$i]['coditems']; 
          $qty = $qty*-1;

          $prodInfo     = getProductsData($coditems);
          $kit          = $prodInfo['kit'];
 
          if ($kit=='1') {
             findKit($coditems,$qty);
          }else{
             inventoryUpdate($coditems,$qty);
          }
         

        $where_data = array(
            'coditems' => $coditems,
            'numfactu' => $invoiceNumber
        );

        $array_edit = array(                              
            'where' => $where_data
        );

        $dfacturacontroller->delete($array_edit);

          //
   
      } 
    }
 }

//------------------------------------------------------------------

 function getProductsData($coditems){
  $minventariocontroller = new MInventarioController();
  $array;

  $query="SELECT * from  MInventario where coditems='$coditems' ";
  $invetario=$minventariocontroller->readUDF($query);

  //  
  $len = sizeof($invetario);
  if($len>0){ 
    $array['aplicaiva']    = $invetario[0]['aplicaiva'];
    $array['aplicadcto']   = $invetario[0]['aplicadcto'];
    $array['aplicacommed'] = $invetario[0]['aplicacommed'];
    $array['aplicacomtec'] = $invetario[0]['aplicacomtec'];
    $array['costo']        = $invetario[0]['costo'];
    $array['kit']          = $invetario[0]['kit'];
    $array['cod_subgrupo'] = $invetario[0]['cod_subgrupo'];
    $array['cod_grupo']    = $invetario[0]['cod_grupo'];
  }

  return  $array;
}

//------------------------------------------------------------------

 function findKit($coditems,$qty){
    $kitcontroller = new KitController();
    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    for ($i=0; $i <$len ; $i++) { 
        $dis= $kit[$i]['disminuir'];
        $cantidad=$qty;
        if (!is_null( $dis ) ) {
            $cantidad=$cantidad*$dis;
        }
        $codikit= $kit[$i]['codikit'];
        inventoryUpdate($codikit,$cantidad);
    }
}

//------------------------------------------------------------------

function inventoryUpdate($coditems,$qty){
    $minventariocontroller = new MInventarioController();
    $query="SELECT existencia From MInventario Where coditems ='$coditems' ";
    $invetario=$minventariocontroller->readUDF($query);

    $len = sizeof($invetario);
    if($len>0){ 
        $existencia = $invetario[0]['existencia'];

        $existencia=$existencia-$qty;

        $set_data = array(
            'existencia' =>  $existencia
        );

        $where_data = array(
            'coditems' =>   $coditems
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $minventariocontroller->update($array_edit);

    }

}

//------------------------------------------------------------------

function save($factura){
  
    $mfacturaController = new MFacturaController();
    $empresacontroller = new EmpresaController();
    $dfacturacontroller = new DFacturaController();
    $prescritoPController = new PrescritopController();
    
    if (trim($factura)=="") {
        
        $chk_codclie=$_POST["idassoc"];
        $chk_fechafa=$_POST["fecha"];

        $isfound=$mfacturaController->readUDF("Select numfactu from mfactura where fechafac='$chk_fechafa' and codclien='$chk_codclie' and statfact='1' ");
        if (count($isfound)>0 && is_array($isfound)) {
           $factura=trim($isfound[0]['numfactu']);
        }else{
           $factura=$empresacontroller->getLastNumber('factura','1',7);
           $factura=trim($factura);  
        } 


        
    }else{
        $dat=$mfacturaController->read($factura);
    }
    $medico=$_POST["medico"];
    if (isset($_POST["medicohd"]) && trim($_POST["medicohd"])!="") {
          $medico=$_POST["medicohd"];
    }
    $medico_name=getMedico($medico);



    $total=(float)$_POST["tltotal"];
    $envio=(float)$_POST["shipping"];
    if (trim($_POST["shipping"])!="" && $envio>0 ) {
        $total=(float)$_POST["tltotal"]-(float)$_POST["shipping"];
    } 
    



    if (isset( $dat ) && sizeof($dat)>0) {
       #UPDATE MASTER
        $set_data = array(
             'codclien' =>$_POST["idassoc"]
            ,'codmedico' =>  $medico
            ,'subtotal' =>  $_POST["tlsubototal"] 
            ,'descuento' => $_POST["discamount"] 
            ,'Alicuota'=>$_POST["discprcntg"]
            ,'total' => $total
            ,'horareg'=>date("H:i:s", time()) 
            ,'medios'=>$_POST["medio"] 
            ,'desxmonto'=>$_POST["desxmonto"]
            ,'medico'=>$medico_name[0]['nombre']
            ,'mediconame'=>$medico_name[0]['apellido'] 
            ,'monto_flete'=>$_POST["shipping"] 
            ,'TotImpuesto'=>$_POST["tlimpuesto"] 
            ,'iva'=>$_POST["tax_p"] 
            ,'cempresa'=>"p"
            ,'statfact'=>"1"
            ,'monto_abonado'=>0
        );

        $where_data = array(
            'numfactu' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $mfacturaController->update($array_edit);


    } else {
      #INSERT MASTER  
       $medico=$_POST["medico"];
       if (isset($_POST["medicohd"]) && trim($_POST["medicohd"])!="") {
          $medico=$_POST["medicohd"];
       }
       $alicuota=$_POST["discprcntg"];
       if ($_POST["discprcntg"] =="") {
           $alicuota=0;
       }
      $recipe = ($_POST["idassoc"] == "000" ? false : true); 
      $set_data = array(
       'numfactu'=>$factura
      ,'fechafac'=>$_POST["fecha"]
      ,'codclien'=>$_POST["idassoc"]
      ,'codmedico'=>$medico
      ,'recipe'=>$recipe
      ,'usuario'=>$_POST["idusr"]
      ,'fecreg'=>date('Y-m-d')
      ,'horareg'=>date("H:i:s", time())
      ,'statfact'=>"1"
      ,'cancelado'=>0
      ,'monto_abonado'=>0
      ,'tipopago'=>0            
      ,'subtotal'=>$_POST["tlsubototal"]
      ,'total'=>$total     
      ,'descuento'=>$_POST["discamount"] 
      ,'Alicuota'=>$alicuota      
      ,'medios'=>$_POST["medio"]
      ,'desxmonto'=>$_POST["desxmonto"]
      ,'cempresa'=>"p"
      ,'vencimiento'=>$_POST["fecha"]
      ,'medico'=>$medico_name[0]['nombre']
      ,'mediconame'=>$medico_name[0]['apellido'] 
      ,'monto_flete'=>$_POST["shipping"] 
      ,'TotImpuesto'=>$_POST["tlimpuesto"] 
      ,'iva'=>$_POST["tax_p"] 
    );
      $mfacturaController->create($set_data);
      

    }
    #INSERT DETAILS
    deleteRecords($factura,true,'');
    $number = count($_POST["producto"]); 
    if($number > 0){
       for($i=0; $i<$number; $i++){
           if(trim($_POST["producto"][$i] !='')){

                
                $fecreg =date('Y-m-d');
                $horareg=  date("H:i:s", time()); 
                $prodInfo    = getProductsData($_POST["producto"][$i]);
                $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );
                $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]);
                $kit         = $prodInfo['kit'];

                if ($kit=='1') {
                    findKit($_POST["producto"][$i],$_POST["cantidad"][$i] );
                }else{
                    inventoryUpdate($_POST["producto"][$i],$_POST["cantidad"][$i] );
                }
                
                
                $dosis=(int)$_POST['dosis'][$i];

                $porcent=0;
                if (!is_null($_POST["discprcntg"][$i] ) && !empty($_POST["discprcntg"][$i] ) ) {
                  $porcent= $_POST["discprcntg"][$i] ;
                }


      

                $set_data = array(
                    'numfactu' =>$factura     
                   ,'fechafac' =>$_POST["fecha"]    
                   ,'coditems' =>$_POST["producto"][$i]   
                   ,'cantidad' =>$_POST["cantidad"][$i]    
                   ,'precunit' =>$_POST["precio"][$i] 
                   ,'subtotal' =>$_POST["precio"][$i] * $_POST["cantidad"][$i]
                   ,'codtipre' =>$_POST["listaprecio"][$i]      
                   ,'usuario' =>$_POST["idusr"]   
                   ,'fecreg' =>$fecreg    
                   ,'horareg' =>$horareg 
                   ,'aplicaiva' =>$prodInfo['aplicaiva']  
                   ,'aplicadcto' =>$prodInfo['aplicadcto']  
                   ,'aplicacommed' =>$prodInfo['aplicacommed']  
                   ,'aplicacomtec' =>$prodInfo['aplicacomtec']      
                   ,'costo' =>$prodInfo['costo'] 
                   ,'kit' =>$kit  
                   ,'monto_imp' =>$_POST["tax"][$i]
                   ,'descuento' =>$descuento
                   ,'procentaje' => $_POST["discprcntg"] 
                   ,'percentage' => $_POST["discprcntg"] 
                   ,'desxmonto'=>$_POST["desxmonto"] 
                   ,'cod_subgrupo'=>$prodInfo['cod_subgrupo']
                   ,'Codmedico'=>$medico
                   ,'cod_grupo'=>$prodInfo['cod_grupo']
                   ,'dosis' =>$dosis
                   ,'cant_sugerida'=>$_POST['sugerido'][$i]

                );
                $dfacturacontroller->create($set_data);


                try {   


                      $where_data = array(
                          'numfactu' => $factura,
                          'coditems' => $_POST["producto"][$i],
                          'codclien'=>$_POST["idassoc"]
                      );

                      $array_edit = array(              
                          'where' => $where_data
                      );

                      $respuesta =  $prescritoPController->readWhere($array_edit);
                      $longitud= count($respuesta);
                      if ($longitud>0) {
                         
                         $set_data = array(
                            'cantidad' =>$_POST["cantidad"][$i] 
                         );

                         $array_edit = array(
                            'data'  => $set_data,                    
                            'where' => $where_data
                          );

                          $prescritoPController->update($array_edit);

                          
                      }
             
                          

                      $set_data['codclien'] = $_POST["idassoc"];
                      $prescritoPController->create($set_data);

                } catch (Exception $e) {
                  
                }
           }
       }
    }

    taxesByInvoices($factura,$_POST['stmdct']);
        
return $factura;      
 
}
//------------------------------------------------------------------
function getPrice($coditems,$codtipre){
    $mprecioscontroller = new MPreciosController();   
    $price=$mprecioscontroller->getPrice($coditems,$codtipre);
    return $price[0]['precunit'];
}
//------------------------------------------------------------------
function getPatient($id){
    $mclientescontroller = new ClientesController();
   
    $r= strpos($id,"-");

    if(is_numeric($id)) {
        $query="Select * from Mclientes where Historia='$id'  ";  
    }else if($r!=false){
        $query="Select * from Mclientes where Cedula='$id'  ";
    }else{
        $query="Select TOP 55 * from Mclientes where nombres like '%$id%'  ";
    }

    $patient=$mclientescontroller->readUDF($query);
    return $patient;
}
//------------------------------------------------------------------
function updateClient($codclien ,$factura ){
  if ($codclien!=null) {
      $mfacturaController = new MFacturaController();
              $set_data = array(
              'codclien' =>$codclien
          );

          $where_data = array(
              'numfactu' =>   $factura
          );

          $array_edit = array(
              'data'  => $set_data,                    
              'where' => $where_data
          );

          $mfacturaController->update($array_edit);
  }
    
}
//------------------------------------------------------------------
function updateMD($codmedico,$factura ){
    $mfacturaController = new MFacturaController();
            $set_data = array(
            'codmedico' =>$codmedico
        );

        $where_data = array(
            'numfactu' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $mfacturaController->update($array_edit);
}
//------------------------------------------------------------------
function loadMedia(){
    $medioscontroller = new MediosController();   
    $query="SELECT * from  Medios where del=0 order by codigo";
    $media=$medioscontroller->readUDF($query);
    return $media;
}
//------------------------------------------------------------------
function updateMedia($codmedia,$factura){
  $mfacturaController = new MFacturaController();
        $set_data = array(
            'medios' =>$codmedia
        );

        $where_data = array(
            'numfactu' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $mfacturaController->update($array_edit);
}
//------------------------------------------------------------------
function getExclusivo($coditems){
    $exclusivosController = new ExclusivosController();
    $query="SELECT * from exclusivos where coditems='$coditems'"; 
    $exclusive = $exclusivosController->readUDF($query);
    return $exclusive;
}
//------------------------------------------------------------------
function getPayments($obj){
  $mpagosController = new MpagosController();
  $datatosearch=json_decode($obj);  
  $numfactu  = $datatosearch->numfactu;
  $id_centro = $datatosearch->id_centro;
  $tipo_doc  = $datatosearch->tipo_doc;
  $query="Select monto *-1 pago, * FROM mpagos where id_centro='$id_centro' and tipo_doc='$tipo_doc' and numfactu = '$numfactu' and monto !=0 ";
  $payments=$mpagosController->readUDF($query);
  return $payments;
}
//------------------------------------------------------------------
function getMedico($codmedico){
  $medicosController = new MedicosController();
  $medico=$medicosController->read($codmedico);
  return $medico;
}
//------------------------------------------------------------------
function checkInvoiceToday($codclien){
    $mfacturaController = new MFacturaController();
    $fecha=date('Y-m-d'); 
    $patient=$mfacturaController->readAllW(array('fechafac'=>$fecha,'codclien'=>$codclien));
    return $patient;
}
//------------------------------------------------------------------
function getTaxes(){
    $impuestosController = new ImpuestosController();
    $array_read = array(
            'Activo'  => 1 
     );     
    $taxes=$impuestosController->readAllW($array_read);
    return $taxes;
 }
//------------------------------------------------------------------
 function taxesByInvoices($factura,$subtotal){
 
  $impxFactController = new ImpxFactController();
  $impxFactController->delete($factura);
  
  $taxes_= getTaxes();

  $long=sizeof($taxes_);  

  for ($i=0; $i <$long ; $i++) { 
    $set_data = array(
        'numfactu' =>$factura     
       ,'codimp' => $taxes_[$i]['Codigo']   
       ,'base' =>$subtotal
       ,'porcentaje' =>$taxes_[$i]['Porcentaje']   
       ,'montoimp' =>($taxes_[$i]['Porcentaje']*$subtotal)/100
    );
    $impxFactController->create($set_data);
  }

     
     
    

 }
//------------------------------------------------------------------
 function findGranTotalCliente($codclien){

   $mfacturaController = new MFacturaController();
   $fecha=Date('Ymd');


   $query="SELECT numfactu,total,codclien,statfact, 'Suero-Amino' servicio from CMA_MFactura  where statfact='1' and  company='suero' and fechafac ='$fecha'  and codclien='$codclien'
union all
SELECT numfactu, total ,codclien ,statfact, 'Laser' servicio from MSSMFact  where statfact='1' and fechafac ='$fecha'  and codclien='$codclien'
";

   $totales = $mfacturaController->readUDF($query);
   return $totales;
   /*
SELECT numfactu,total,codclien,statfact from MFactura where statfact='1' and fechafac ='$fecha' and codclien='$codclien'
union all 
   */ 
 }
 //------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------

