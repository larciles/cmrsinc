<?php
error_reporting(0);
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

require('../controllers/EventosController.php');




if (isset($_POST['q']) && $_POST['q']=="saveevent" ) {
   $result =  save();
}


if (isset($_POST['q']) && $_POST['q']=="loadevent" ) {
   $result =  load();
   //
}

if (isset($_POST['q']) && $_POST['q']=="del" ) {
   $result = deleteRecords();
   //
}






echo( json_encode($result) );

//================================================================

//------------------------------------------------------------------
//SAVE EVENTOS
function save(){
  
   $eventoscontroller = new EventosController();

      #INSERT MASTER  
      $set_data = array(
     
       'fecha'  =>$_POST["fechaEvent"]
      ,'usuario'=>$_POST["usuario"]     
      ,'creado' =>$_POST["creado"]     
      ,'evento' =>$_POST["evento"]
      
    );
     $eventoscontroller->create($set_data);
 
}

function load($value=''){
    $eventoscontroller = new EventosController();
    $fecha_evento=$_POST["fecha_evento"];
    $query="SELECT *, REPLACE(CONVERT(CHAR(15), creado, 101), '', '-') as fecha_creado from  eventos where fecha='$fecha_evento'  and borrado<>1 ";
    $list_events=$eventoscontroller->readUDF($query);
    return $list_events;
}

//------------------------------------------------------------------

function getListaPrecios($value=''){
    $tipopreciocontroller = new TipoprecioController();
    $query="SELECT * from  tipoprecio order by codtipre";
    $lprecios=$tipopreciocontroller->readUDF($query);
    return $lprecios;
}

//------------------------------------------------------------------

 function deleteRecords(){
   $eventoscontroller = new EventosController();
   


          $set_data = array(
              'borrado' =>1
          );

          $where_data = array(
              'id' =>  $_POST["id"]
          );

          $array_edit = array(
              'data'  => $set_data,                    
              'where' => $where_data
          );

          $eventoscontroller->update($array_edit);

    
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
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------

