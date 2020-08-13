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
require('../controllers/MovimientoController.php');
require('../controllers/CMA_DFacturaController.php');
require('../controllers/CMA_MFacturaController.php');
require('../controllers/MediosController.php');
require('../controllers/MInventarioController.php');
require('../controllers/TipoprecioController.php');
require('../controllers/CMA_DnotacreditoController.php');
require('../controllers/KitController.php');
require('../controllers/EmpresaController.php');
require('../controllers/CMA_MnotacreditoController.php');
require('../controllers/MPreciosController.php');
require('../controllers/ExclusivosController.php');
require('../controllers/ClientesController.php');
require('../controllers/MpagosController.php');
require('../controllers/IdcardController.php'); 




if (isset($_POST['q']) && $_POST['q']=="productos" ) {
   $result =  getProductos($value='');
} elseif (isset($_POST['q']) && $_POST['q']=="lp" ) {
   $result =getListaPrecios($value='');
}elseif ( ( isset($_POST['del']) && $_POST['del']=="1" ) && ( isset($_POST['coditems'] )  && $_POST['coditems']!=""  )  ){
   $invoiceNumber=$_POST['factura'];
   $coditems=$_POST['coditems'];
   deleteRecords($invoiceNumber,false,$coditems);
   if ( trim($_POST['factura']) !="") {
      save(trim($_POST['factura']));
   }
   $result = array("true");
}elseif ( ( isset($_POST['del']) && $_POST['del']=="2" ) && ( isset($_POST['factura'] )  && $_POST['factura']!=""  )  ){
   $invoiceNumber=$_POST['factura'];
   deleteRecords($invoiceNumber,true,'');
   if ( trim($_POST['factura']) !="") {
      save(trim($_POST['factura']));
   }
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
 }



echo( json_encode($result) );

//================================================================

function getProductos($value=''){
    $minventariocontroller = new MInventarioController();
    $query="SELECT * from MInventario where Prod_serv in('s','c','f')  and cod_grupo='004' and cod_subgrupo IN ('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER') and activo=1 order by orden, desitems ";
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
   $cmadfacturacontroller = new CMA_DnotacreditoController();
   
   if ($allYN==true) {
        #LOS ELIMINA TODOS
        $query="SELECT * from CMA_Dnotacredito  WHERE numnotcre = '$invoiceNumber' ";     
    }else{
        $query="SELECT * from CMA_Dnotacredito  WHERE numnotcre = '$invoiceNumber' and coditems='$coditems' ";
    } 
    
    $mpagosController->del($invoiceNumber,'2','04');
    $idcardController->del($invoiceNumber,'2','04');

    $detf = $cmadfacturacontroller->readUDF( $query ); 
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
             fixKit($coditems,$qty,'del',$invoiceNumber);
          }else{
             inventoryUpdate($coditems,$qty);
          }
          //
         $existencia = $invetario[0]['existencia'];

         $existencia=$existencia-$qty;

        // $set_data = array(
        //     'existencia' =>  $existencia
        // );

        $where_data = array(
            'coditems' => $coditems,
            'numnotcre' => $invoiceNumber
        );

        $array_edit = array(                              
            'where' => $where_data
        );

        $cmadfacturacontroller->delete($array_edit);

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

 function fixKit($coditems,$qty,$option,$invoiceNumber,$fecha=""){
    $kitcontroller = new KitController();
    $movimientoController = new MovimientoController();

    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    for ($i=0; $i <$len ; $i++) { 
        $dis= $kit[$i]['disminuir'];
        if (!is_null( $dis ) ) {
            $qty=$qty*$dis;
        }
        $codikit= $kit[$i]['codikit'];
        
        if ($option=='del') {             
            
              $where_data = array(
                  'coditems' => $codikit,
                  'numfactu' => $invoiceNumber,
                  'tipo'=>'04',
                  'empresa'=>'02'
              );

               $array_edit = array(                              
                  'where' => $where_data
              );
              $movimientoController->delete($array_edit); 
           # code...
         }else{
          $qty=$qty*-1;
          $movimientoController->create(array('coditems' =>$codikit ,'cantidad' =>$qty ,   'numfactu' =>$invoiceNumber ,'tipo'=>'04' ,'empresa'=>'02', 'fecha'=>$fecha    )) ;

         } 

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

        $existencia=$existencia+$qty;

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
  
    $cma_MnotacreditoController = new CMA_MnotacreditoController();
    $empresacontroller = new EmpresaController();
    $cmadfacturacontroller = new CMA_DnotacreditoController();

    if (trim($factura)=="") {
        $factura=$empresacontroller->getLastNumber('return','2',6);
        $factura=trim($factura);
    }else{
        $dat=$cma_MnotacreditoController->read($factura);
    }

    if (isset( $dat ) && sizeof($dat)>0) {
       #UPDATE MASTER

        $set_data = array(
            'codclien' =>$_POST["idassoc"]
            ,'codmedico' => $_POST["medico"]
            ,'subtotal' =>  $_POST["tlsubototal"] 
            ,'descuento' => $_POST["discamount"] 
            ,'alicuota'=>$_POST["discprcntg"]
            ,'totalnot' => $_POST["tltotal"] 
            ,'horareg'=>date("H:i:s", time()) 
            ,'medios'=>$_POST["medio"] 
            ,'desxmonto'=>$_POST["desxmonto"] 
        );

        $where_data = array(
            'numfactu' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_MnotacreditoController->update($array_edit);
    } else {

      #INSERT MASTER
      $tasadesc=$_POST["discprcntg"];
      if ( trim( $_POST["discprcntg"])=="") {
         $tasadesc=0;
      }
     
      $set_data = array(
       'numnotcre'=>$factura
      ,'fechanot'=>$_POST["fecha"]
      ,'codclien'=>$_POST["idassoc"]
      ,'codmedico'=>$_POST["medico"]      
      ,'usuario'=>$_POST["idusr"]
      ,'fecreg'=>date('Y-m-d')
      ,'horareg'=>date("H:i:s", time())
      ,'statnc'=>"1"
      ,'cancelado'=>0
      ,'monto_abonado'=>0
      ,'tipopago'=>0            
      ,'subtotal'=>$_POST["tlsubototal"]
      ,'totalnot'=>$_POST["tltotal"]      
      ,'descuento'=>$_POST["discamount"] 
      ,'alicuota'=>$_POST["discprcntg"]      
      ,'medios'=>$_POST["medio"]
      ,'desxmonto'=>$_POST["desxmonto"]
      ,'cempresa'=>"s"
      ,'tipo'=>'04'
      ,'codtiponotcre'=>'1'
      ,'tasadesc'=> $tasadesc
      ,'numfactu'=>$_POST["numfactu"] 
      ,'saldo'=>0
    );
      $cma_MnotacreditoController->create($set_data);

      //
      if ( isset( $_POST['numfactu'] ) && trim( $_POST['numfactu'] )!="" ) {
           updateMasterInvoive($_POST['numfactu'],$factura);
      }
      //
      

    }
    #INSERT DETAILS
    deleteRecords($factura,true,'');
    $number = count($_POST["producto"]); 
    if($number > 0){
       for($i=0; $i<$number; $i++){
           if(trim($_POST["producto"][$i] !='')){

                
                $fecreg=date('Y-m-d');
                $horareg =  date("H:i:s", time()); 
                $prodInfo     = getProductsData($_POST["producto"][$i]);
                $detaialprcnt= ($_POST["discprcntg"][$i] == "" ? "0" : $_POST["discprcntg"][$i] );
                $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]);
                $kit          = $prodInfo['kit'];

                if ($kit=='1') {
                    findKit($_POST["producto"][$i],$_POST["cantidad"][$i] );
                    fixKit($_POST["producto"][$i],$_POST["cantidad"][$i],'create',$factura,$_POST["fecha"] );
                }else{
                    inventoryUpdate($_POST["producto"][$i],$_POST["cantidad"][$i] );
                }

                $procentaje=$detaialprcnt;
                $percentage =$detaialprcnt;
                #cod_subgrupo

                $set_data = array(
                   'numnotcre' => $factura     
                   ,'fechanot' =>$_POST["fecha"]    
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
                   ,'monto_imp' =>0
                   ,'descuento' =>$descuento                  
                   ,'desxmonto'=>$_POST["desxmonto"] 
                   ,'porcentaje'=>$tasadesc
                   ,'tipoitems'=>'s'
                   ,'cod_subgrupo'=>$prodInfo['cod_subgrupo']
                );
                $cmadfacturacontroller->create($set_data);

                //
                if ( isset( $_POST['numfactu'] ) && trim( $_POST['numfactu'] )!="" ) {
                    updateDetailsInvoice($_POST['numfactu'],$factura ,$_POST["cantidad"][$i]);
                }
                //
           }
       }
    }


        
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
    $query="Select TOP 25 * from Mclientes where  Historia ='$id'  ";
    $patient=$mclientescontroller->readUDF($query);
    return $patient;
}
//------------------------------------------------------------------
function updateClient($codclien ,$factura ){
    $cma_MnotacreditoController = new CMA_MnotacreditoController();
            $set_data = array(
            'codclien' =>$codclien
        );

        $where_data = array(
            'numnotcre' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_MnotacreditoController->update($array_edit);
}
//------------------------------------------------------------------
function updateMD($codmedico,$factura ){
    $cma_MnotacreditoController = new CMA_MnotacreditoController();
            $set_data = array(
            'codmedico' =>$codmedico
        );

        $where_data = array(
            'numnotcre' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_MnotacreditoController->update($array_edit);
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
  $cma_MnotacreditoController = new CMA_MnotacreditoController();
        $set_data = array(
            'medios' =>$codmedia
        );

        $where_data = array(
            'numnotcre' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_MnotacreditoController->update($array_edit);
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
function updateMasterInvoive($factura,$ret){
      $cma_mfacturaController = new CMA_MFacturaController();

        $set_data = array(
            'ret' =>  $ret
        );

        $where_data = array(
            'numfactu' => $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_mfacturaController->update($array_edit);

}
   
   
//------------------------------------------------------------------
function updateDetailsInvoice($factura,$ret,$qty){
       $cmadfacturacontroller = new CMA_DFacturaController();

       $set_data = array(
            'ret' =>  $ret
           ,'retcan' =>  $qty
        );

        $where_data = array(
            'numfactu' => $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cmadfacturacontroller->update($array_edit);
}
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------

