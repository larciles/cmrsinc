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
require('../controllers/MedicosController.php');
require('../controllers/MediosController.php');
require('../controllers/MInventarioController.php');
require('../controllers/TipoprecioController.php');
require('../controllers/CMA_DFacturaController.php');
require('../controllers/KitController.php');
require('../controllers/EmpresaController.php');
require('../controllers/CMA_MFacturaController.php');
require('../controllers/MPreciosController.php');
require('../controllers/ExclusivosController.php');
require('../controllers/ClientesController.php');
require('../controllers/MpagosController.php');
require('../controllers/IdcardController.php');
require('../controllers/PrescritoController.php');
require('../controllers/DisponibleController.php');





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
 }elseif ($_POST['patientupdate']) {
    updateClient($_POST['codclien'],$_POST['factura']);
 }elseif ( isset($_POST['gtotal'])  ) {
   $result= findGranTotalCliente($_POST['codclien']);
 }




echo( json_encode($result) );

//================================================================

function getProductos($value=''){
    $minventariocontroller = new MInventarioController();
    $query="SELECT * from MInventario where Prod_serv in('s','c','f')  and cod_grupo='004' and cod_subgrupo IN ('SUEROTERAPIA','CEL MADRE','BLOQUEO','INTRAVENOSO','TERAPIA LASER','PACKAGE') and activo=1 order by  desitems ";
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
   $cmadfacturacontroller = new CMA_DFacturaController();


   
   if ($allYN==true) {
        #LOS ELIMINA TODOS
        $query="SELECT * from cma_DFactura  WHERE numfactu = '$invoiceNumber' ";     
    }else{
        $query="SELECT * from cma_DFactura  WHERE numfactu = '$invoiceNumber' and coditems='$coditems' ";
    } 
    
    $mpagosController->del($invoiceNumber,'2','01');
    $idcardController->del($invoiceNumber,'2','01');

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
            'numfactu' => $invoiceNumber
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
  
  $array = array();

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
                  'tipo'=>'01',
                  'empresa'=>'02'
              );

               $array_edit = array(                              
                  'where' => $where_data
              );
              $movimientoController->delete($array_edit); 
           # code...
         }else{
          $movimientoController->create(array('coditems' =>$codikit ,'cantidad' =>$qty ,   'numfactu' =>$invoiceNumber ,'tipo'=>'01' ,'empresa'=>'02', 'fecha'=>$fecha    )) ;

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
  
    $cma_mfacturaController = new CMA_MFacturaController();
    $empresacontroller = new EmpresaController();
    $cmadfacturacontroller = new CMA_DFacturaController();
    $prescritocontroller = new PrescritoController();

    $disponibleController = new DisponibleController();

    //$movimientoController = new MovimientoController();

    if (trim($factura)=="") {
        $factura=$empresacontroller->getLastNumber('factura','2',6);
        $factura=trim($factura);
    }else{
        $dat=$cma_mfacturaController->read($factura);
    }
    $medico=$_POST["medico"];
    if (isset($_POST["medicohd"]) && trim($_POST["medicohd"])!="") {
          $medico=$_POST["medicohd"];
    }
    $medico_name=getMedico($medico);
    if (isset( $dat ) && sizeof($dat)>0) {
       #UPDATE MASTER
        $set_data = array(
             'codclien' =>$_POST["idassoc"]
            ,'codmedico' =>  $medico
            ,'subtotal' =>  $_POST["tlsubototal"] 
            ,'descuento' => $_POST["discamount"] 
            ,'Alicuota'=>$_POST["discprcntg"]
            ,'total' => $_POST["tltotal"] 
            ,'horareg'=>date("H:i:s", time()) 
            ,'medios'=>$_POST["medio"] 
            ,'desxmonto'=>$_POST["desxmonto"]
            ,'medico'=>$medico_name[0]['nombre']
            ,'mediconame'=>$medico_name[0]['apellido'] 
        );

        $where_data = array(
            'numfactu' =>   $factura
        );

        $array_edit = array(
            'data'  => $set_data,                    
            'where' => $where_data
        );

        $cma_mfacturaController->update($array_edit);


    } else {
      #INSERT MASTER
       $medico=$_POST["medico"];
       if (isset($_POST["medicohd"])) {
          $medico=$_POST["medicohd"];
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
      ,'total'=>$_POST["tltotal"]      
      ,'descuento'=>$_POST["discamount"] 
      ,'alicuota'=>$_POST["discprcntg"]      
      ,'medios'=>$_POST["medio"]
      ,'desxmonto'=>$_POST["desxmonto"]
      ,'cempresa'=>"s"
      ,'vencimiento'=>$_POST["fecha"]
      ,'medico'=>$medico_name[0]['nombre']
      ,'mediconame'=>$medico_name[0]['apellido'] 
      ,'company'=>'suero'
    );
      $cma_mfacturaController->create($set_data);
      

    }
    #INSERT DETAILS

    $alicuota=$_POST["discprcntg"];
    if (trim($_POST["discprcntg"]) =="") {
         $alicuota=0;
    }  
    deleteRecords($factura,true,'');

    #borra registros entabla disponible
    $where_data = array(
        'numfactu' =>$factura
    );

    $array_edit = array(                
        'where' => $where_data
    );

    $disp = $disponibleController->delete(  $array_edit   );


    $number = count($_POST["producto"]); 
    if($number > 0){
       for($i=0; $i<$number; $i++){
           if(trim($_POST["producto"][$i] !='')){

                
                $fecreg=date('Y-m-d');
                $horareg =  date("H:i:s", time()); 
                $prodInfo     = getProductsData($_POST["producto"][$i]);
                $detaialprcnt= ($_POST["detaialprcnt"][$i] == "" ? "0" : $_POST["detaialprcnt"][$i] );
                $descuento   = ($_POST["descuento"][$i] == "" ? "0" : $_POST["descuento"][$i]);
                $kit          = $prodInfo['kit'];

                if ($kit=='1') {
                    findKit($_POST["producto"][$i],$_POST["cantidad"][$i] );
                    fixKit($_POST["producto"][$i],$_POST["cantidad"][$i],'create',$factura,$_POST["fecha"] );
                }else{
                    inventoryUpdate($_POST["producto"][$i],$_POST["cantidad"][$i] );
                }

                $medico=$_POST["medico"];
                if (isset($_POST["medicohd"])) {
                    $medico=$_POST["medicohd"];
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
                   ,'monto_imp' =>0
                   ,'descuento' =>$descuento
                   ,'procentaje' => $alicuota
                   ,'percentage' => $alicuota
                   ,'desxmonto'=>$_POST["desxmonto"] 
                   ,'cod_subgrupo'=>$prodInfo['cod_subgrupo']
                   ,'Codmedico'=>$medico
                   ,'cod_grupo'=>$prodInfo['cod_grupo']

                );
                $cmadfacturacontroller->create($set_data);

                if ($kit=='1') {

                    
                }
                saveCantDisponible($_POST["producto"][$i] ,$_POST["cantidad"][$i],$_POST["idassoc"],$set_data );

                //$movimientoController->create(array('coditems' =>$_POST["producto"][$i]  ,'cantidad' =>$_POST["cantidad"][$i] ,   'numfactu' =>$factura ,'tipo'=>'01' ,'empresa'=>'02'    )) ;

                try { 

                      $where_data = array(
                          'numfactu' => $factura,
                          'coditems' => $_POST["producto"][$i],
                          'codclien'=>$_POST["idassoc"]
                      );

                      $array_edit = array(              
                          'where' => $where_data
                      );

                      $respuesta =  $prescritocontroller->readWhere($array_edit);
                      $longitud= count($respuesta);
                      if ($longitud>0) {
                         
                         $set_data = array(
                            'cantidad' =>$_POST["cantidad"][$i] 
                         );

                         $array_edit = array(
                            'data'  => $set_data,                    
                            'where' => $where_data
                          );

                          $prescritocontroller->update($array_edit);

                          
                      }

                  $set_data['codclien'] = $_POST["idassoc"];
                  $prescritocontroller->create($set_data);
                } catch (Exception $e) {
                  
                }
           }
       }
    }


        
return $factura;      
 
}

//------------------------------------------------------------------

 function saveCantDisponible($coditems,$qty,$codclien,$set_data ){
    $kitcontroller = new KitController();
    $disponibleController = new DisponibleController();

    $cod_subgrupo="";
    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    if (count($kit)>0) {
	        for ($i=0; $i <$len ; $i++) { 
		        $cantidad=$qty;
		        $dis= $kit[$i]['disminuir'];
		        if (!is_null( $dis ) ) {
		            $cantidad=$cantidad*$dis;
		        }

		        $codikit= $kit[$i]['codikit'];
		        $hora=$set_data['horareg'];
		        

		        $prodInfo = getProductsData( $codikit );
		        if (count($prodInfo)>0) {
		        	 $cod_subgrupo=$prodInfo['cod_subgrupo'];
		        }
		        saveDisponible($codikit,$cantidad,$codclien,$cod_subgrupo,$set_data,$coditems);
		    }	 
    }else{
		$prodInfo = getProductsData( $coditems );
        if (count($prodInfo)>0) {
        	 $cod_subgrupo=$prodInfo['cod_subgrupo'];
        }
        saveDisponible($coditems,$qty,$codclien,$cod_subgrupo,$set_data,$coditems);
    }

}
//------------------------------------------------------------------
function saveDisponible($codikit,$cantidad,$codclien,$cod_subgrupo,$set_data,$itemfac){
 
        $disponibleController = new DisponibleController();
        $cma_mfacturaController = new CMA_MFacturaController();
        $mclientescontroller = new ClientesController();
        
        $nfactura=trim($set_data['numfactu']); 

        $facinfo= $cma_mfacturaController->readUDF("select codmedico from cma_mfactura where numfactu='$nfactura'");
        $codmedico="";
        if (count($facinfo)>0) {
          $codmedico=$facinfo[0]['codmedico'];
        }

        $clieinfo=$mclientescontroller->readUDF("select Historia from Mclientes where codclien='$codclien'");

        $record="";  
        if (count($clieinfo)>0) {
           $record=$clieinfo[0]['Historia'];  
        }



        $set_datad = array(
           'coditems'    =>$codikit
          ,'cantidad'    =>$cantidad
          ,'codclien'    =>$codclien
          ,'numfactu'    =>$set_data['numfactu']   
          ,'fecha'       =>$set_data['fechafac'] 
          ,'horar'       =>$set_data['horareg']
          ,'usuario'     =>$set_data['usuario']
          ,'fecreg'      =>Date('Y-m-d h:m:s')
          ,'cod_subgrupo'=>$cod_subgrupo
          ,'cod_grupo'   =>$set_data['cod_grupo']
          ,'codmedico'   =>$codmedico
          ,'kit'         =>$set_data['kit']
          ,'quedan'      =>$cantidad
          ,'record'      =>$record
          ,'itemfac'     =>$itemfac
        
         );


         $where_data = array(
            'numfactu' => $set_data['numfactu'],
            'coditems' => $codikit 
         );

         $array_edit = array(              
            'where' => $where_data
         );

        $respuesta =  $disponibleController->readWhere($array_edit);

        if (count($respuesta)>0) {
            $existencia = $respuesta[0]['cantidad'];
            $existencia = $existencia+$qty;

            $set_data = array(
               'cantidad' => $existencia
               ,'quedan'  => $existencia
            );

            $where_data = array(
                'coditems' =>   $codikit
               ,'numfactu'    =>$set_datad['numfactu'] 
            );

            $array_edit = array(
                'data'  => $set_data,                    
                'where' => $where_data
            );

            $disponibleController->update($array_edit);
        }else{
            $disponibleController->create($set_datad);
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
     $cma_mfacturaController = new CMA_MFacturaController();
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

          $cma_mfacturaController->update($array_edit);
  }
    
}
//------------------------------------------------------------------
function updateMD($codmedico,$factura ){
    $cma_mfacturaController = new CMA_MFacturaController();
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

        $cma_mfacturaController->update($array_edit);
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
  $cma_mfacturaController = new CMA_MFacturaController();
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

        $cma_mfacturaController->update($array_edit);
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
    $cma_mfacturaController = new CMA_MFacturaController();
    $fecha=date('Y-m-d');
    //$query="Select * from cma_MFactura where fechafac='$fecha' and codclien='$codclien' "; 
    $patient=$cma_mfacturaController->readAllW(array('fechafac'=>$fecha,'codclien'=>$codclien));
    return $patient;
}

//------------------------------------------------------------------
 function findGranTotalCliente($codclien){

   $cma_mfacturaController = new CMA_MFacturaController();
   $fecha=Date('Ymd');


   $query="SELECT numfactu,total,codclien,statfact,'Producto' servicio from MFactura  where statfact='1'  and fechafac ='$fecha'  and codclien='$codclien'
union all
           SELECT numfactu,total,codclien,statfact,'Laser'    servicio from MSSMFact  where statfact='1' and fechafac ='$fecha'  and codclien='$codclien' ";

   $totales = $cma_mfacturaController->readUDF($query);
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
//------------------------------------------------------------------
//------------------------------------------------------------------

