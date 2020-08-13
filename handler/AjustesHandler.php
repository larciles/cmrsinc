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




require('../controllers/MInventarioController.php');

if (isset($_POST['q']) ) {

   $value=$_POST['q'];
   $result =  getProductos($value);
}elseif (isset($_POST['limit'])) {

   
   if (isset($_POST["offset"])) {
    if (isset($_POST['producto']) && $_POST['producto']==="") {
      return;
    }
     $result=pager();
   }else{
      $result=paginar();
   }
   

   
 }


echo( json_encode($result) );

//================================================================

function getProductos($value=''){
    $minventariocontroller = new MInventarioController();
    $query="SELECT fechacierre, desitems,coditems,t.ventas ventas,compra compras,devcompra AS devcompra,anulaciones AS devVentas,ne AS NE,nc AS NC,ajustespos AS Ajustes_mas,ajustesneg AS Ajustes_neg,(SELECT isnull(DC.existencia,0) From DCierreInventario DC WHERE coditems=t.coditems AND fechacierre=t.fechacierre) AS existencia FROM TEST011516 t WHERE coditems='$value' Order by fechacierre desc ";
    $invetario=$minventariocontroller->readUDF($query);
    return $invetario;
}

//------------------------------------------------------------------
 #PAGINATION
 function paginar($excm=""){

     $minventariocontroller = new MInventarioController();
     $value=$_POST['filtro'];
     $limit  = ( isset( $_POST['limit'] ) ) ? $_POST['limit'] : 25;
     $page   = ( isset( $_POST['page'] ) ) ? $_POST['page'] : 1;
     $links  = ( isset( $_POST['links'] ) ) ? $_POST['links'] : 7;

     if ($excm==="") {
         
          $qryRows  = "SELECT COUNT(*) num_rows FROM TEST011516 t WHERE coditems='$value'  " ;

          $query="SELECT CONVERT(varchar(11),fechacierre,101)  fechacierre, desitems,coditems,t.ventas ventas,compra compras,devcompra AS devcompra,anulaciones AS devVentas,ne ,nc ,ajustespos AS Ajustes_mas,ajustesneg AS Ajustes_neg,(SELECT isnull(DC.existencia,0) From DCierreInventario DC WHERE coditems=t.coditems AND fechacierre=t.fechacierre) AS existencia, InvPosible, InvActual FROM TEST011516 t WHERE coditems='$value' Order by t.fechacierre desc";

      }elseif ($excm==="excm") {
            $qryRows= "SELECT COUNT(*) num_rows FROM exos_movimientos_view t WHERE coditems='$value'  " ;     
            $query="SELECT CONVERT(VARCHAR(10),fecha,110) fecha_mov, * from exos_movimientos_view WHERE coditems='$value' Order by fecha desc,coditems,tipo";
      } 
     
   
     $minventariocontroller->paginator($query,$qryRows);
     $tp =$minventariocontroller->getTotalPages();
     if ($tp >0) {
        $lastPage=ceil( $tp/$limit );

        if($page>$lastPage){
           $page=$lastPage;
       }

       $results = $minventariocontroller->getData( $page, $limit ,'mssql');
       $lastPage=ceil( $results->total/$limit );

       if($page>ceil( $results->total/$limit )){
          if($lastPage!=0){
             $page=ceil( $results->total/$limit );
             $results    = $minventariocontroller->getData( $page, $limit,'mssql' );
          }
      }

      $totalRegistros=$results->datalen;
      $respuesta=  $results ;
      $xlinks= $minventariocontroller->createLinks($links, 'pagination pagination-sm pg');
      
      $array[] = $respuesta;
      $array[] = $xlinks;
      $res=$array;

     }else{
        $totalRegistros=0;
        $respuesta="";
        $res="";
    } 
    return $res;
 }
//------------------------------------------------------------------
 function fixArrayEXCM($mov){
     $exo_cm_mov= array();
  
  for ($i=0; $i < count($mov) ; $i++) { 

       $fecha_mov= trim($mov[$i]['fecha_mov']);
       $coditems=trim($mov[$i]['coditems']);
       $tipo=trim($mov[$i]['tipo']);

       $arrayTmpMov = array(
        'cantidad' =>trim($mov[$i]['cantidad'])
       ); 
      $exo_cm_mov[$fecha_mov][$coditems][$tipo][]=$arrayTmpMov ;

  }
  return $exo_cm_mov;
 }
//------------------------------------------------------------------ 
 function pager(){
// obtiene los valores para realizar la paginacion
$limit = isset($_POST["limit"]) && intval($_POST["limit"]) > 0 ? intval($_POST["limit"])  : 10;
$offset = isset($_POST["offset"]) && intval($_POST["offset"])>=0  ? intval($_POST["offset"])  : 0;

// array para devolver la informacion
$json = array();
$data = array();

//consulta que deseamos realizar a la db  
$minventariocontroller = new MInventarioController();

$query="SELECT CONVERT(VARCHAR(10),fecha,110) fecha_mov, * from exos_movimientos_view WHERE coditems='EXOS01'  Order by fecha desc,coditems,tipo OFFSET $offset ROWS FETCH NEXT $limit  ROWS ONLY";
if (isset($_POST['producto']) && $_POST['producto']!="") {

  $producto=$_POST['producto'];
  $query="SELECT CONVERT(VARCHAR(10),fecha,110) fecha_mov, * from exos_movimientos_view WHERE coditems='$producto'  Order by fecha desc,coditems,tipo OFFSET $offset ROWS FETCH NEXT $limit  ROWS ONLY";
}


$mov=$minventariocontroller->readUDF($query);


// obtener valores 

  $exo_cm_mov= array();

  $ventas=0;
  $return=0;
  $ajustep=0;
  $ajusten=0;
  
  for ($i=0; $i < count($mov) ; $i++) { 

       $fecha_mov= trim($mov[$i]['fecha_mov']);
       $coditems=trim($mov[$i]['coditems']);
       $tipo=trim($mov[$i]['tipo']);

       $ventas=0;
       $return=0;
       $ajustep=0;
       $ajusten=0;
  

       if ($tipo=="01") {
        $ventas= trim($mov[$i]['cantidad']);
       }

       if ($tipo=="04") {
        $return = trim($mov[$i]['cantidad']);
       }

       if ($tipo=="i") {
        $ajustep = trim($mov[$i]['cantidad']);
       }

       if ($tipo=="o") {
         $ajusten = trim($mov[$i]['cantidad']);
       }




       $arrayTmpMov = array(        
        'ventas'=> $ventas,
        'return'=> $return,
        'ajustep'=>$ajustep,
        'ajusten'=>$ajusten
       ); 
      $exo_cm_mov[$fecha_mov]['producto'][$coditems][]=$arrayTmpMov ;

  }


   $farray   = array( );
    foreach ($exo_cm_mov as $fecha => $producto) {   
     
    $ventas=0;
    $return=0;
    $ajustep=0;
    $ajusten=0;
    $prod="";           
 
    $prod=$k;
    foreach ($producto['producto'] as $k => $v) {
        $prod=$k;
      foreach ($v as $k1 ) {  
      $ventas= $ventas+ (int)$k1['ventas'];

      $return=$return+(int)$k1['return'];

      $ajustep=$ajustep+ (int)$k1['ajustep'];

      $ajusten=$ajusten+ (int)$k1['ajusten'];


      } 
    }

       $data_json = array(  
        'fecha'=>$fecha,      
        'ventas'=> $ventas,
        'return'=> $return,
        'ajustep'=>$ajustep,
        'ajusten'=>$ajusten
       ); 
      
       $data[]=$data_json ;     
   
      
           
    } 

// while ($query->fetch()) {
//   $data_json = array();
//   $data_json["id"] = $id_usuario;
//   $data_json["nombres"] = $nombres;
//   $data_json["apellidos"] = $apellidos;     
//   $data[]=$data_json; 
// }

// obtiene la cantidad de registros

$query="SELECT count(*) as total from exos_movimientos_view WHERE coditems='EXOS01'";
$row=$minventariocontroller->readUDF($query);

// $cantidad_consulta = $con->query("select count(*) as total from customers");
// $row = $cantidad_consulta->fetch_assoc();
$cantidad['cantidad']=$row[0]['total'];

$json["lista"] = array_values($data);
$json["cantidad"] = array_values($cantidad);

// envia la respuesta en formato json   
header("Content-type:application/json; charset = utf-8");
echo json_encode($json);
exit();
 }
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------
//------------------------------------------------------------------

