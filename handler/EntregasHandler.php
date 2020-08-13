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



    require('../controllers/CMA_MFacturaController.php');
    require('../controllers/KitController.php');
    require('../controllers/DisponibleController.php');
    require('../controllers/EntregasController.php');
    require('../controllers/MInventarioController.php');

$p=$_POST ;
$result = array($p); 

#borrar
if ( isset( $_POST['del'] ) ) {
    $disponibibleController =new DisponibleController();
    $entregasController = new EntregasController();
    $minventarioController = new MInventarioController();

    $coditems=trim($_POST['coditems']);
    $id=trim($_POST['id']);
    $cantidad=trim($_POST['cantidad']);
    $factura=trim($_POST['factura']);



    #Disponible


                $dispo=$disponibibleController->readUDF("Select quedan From disponible Where coditems='$coditems' And numfactu='$factura' ");

                if ( count($dispo)>0 ) {
                    $quedan=$dispo[0]['quedan'];
                    $quedan=$quedan+$cantidad;

                    $set_data = array(
                    'quedan' =>  $quedan
                    );

                    $where_data = array(
                    'coditems' => $coditems
                    ,'numfactu'=> $factura
                    );

                    $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data
                    );
                    $di=$disponibibleController->update($array_edit);
                }

                
    #Entregas
                $ent=$entregasController->readUDF("Select id From entregas Where id='$id' ");

                if (count($ent)) {
                    $set_data = array(
                    'borrado' =>  '1'
                    );

                    $where_data = array(
                     'id' => $id
                    );

                    $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data
                    );
                    $ue=$entregasController->update($array_edit); 
                }


    
    #Inventario 

                $inv=$minventarioController->readUDF("Select entrega as quedan From MInventario Where coditems='$coditems' ");

                if (count($inv)>0) {
                    $quedan=$inv[0]['quedan'];

                    $quedan=$quedan+$cantidad;

                    $set_data = array(
                    'entrega' =>  $quedan
                    );

                    $where_data = array(
                    'coditems' => $coditems
                    );

                    $array_edit = array(
                    'data'  => $set_data,                    
                    'where' => $where_data
                    );
                    $ue=$minventarioController->update($array_edit);  
                }

        
        $p=$_POST ;
        $result = array($p); 

} 
// enfermera
if ($_POST['enfermera'] && !empty($_POST['enfermera']) ) {
        
        $entregasController = new EntregasController();
        
        $enfermera=$_POST['enfermera'];
        $id=$_POST['id'];
        $idnurse=$_POST['idnurse'];
      
  
        $set_data = array(
         'enfermera' => $enfermera
        ,'idnurse' => $idnurse
        );

        $where_data = array(
         'id' => $id
        );

        $array_edit = array(
        'data'  => $set_data,                    
        'where' => $where_data
        );
        $ue=$entregasController->update($array_edit); 
      
        $result = $array_edit; 

}

//nota
if (isset($_POST['nota']) ) {

        $entregasController = new EntregasController();
        
        $nota=$_POST['nota'];
        $id=$_POST['id'];
      
      
  
        $set_data = array(
         'nota' => $nota
        );

        $where_data = array(
         'id' => $id
        );

        $array_edit = array(
        'data'  => $set_data,                    
        'where' => $where_data
        );
        $ue=$entregasController->update($array_edit); 
      
        $result = $array_edit; 
    
}


//Fecha
if (isset($_POST['fecha']) ) {

        $entregasController = new EntregasController();
        
        $fecha=$_POST['fecha'];
        $id=$_POST['id'];
      
      
  
        $set_data = array(
         'fecha' => $fecha
        );

        $where_data = array(
         'id' => $id
        );

        $array_edit = array(
        'data'  => $set_data,                    
        'where' => $where_data
        );
        $ue=$entregasController->update($array_edit); 
      
        $result = $array_edit; 
    
}

// numfactu
if (isset($_POST['numfactu']) ) {
      $numfactu=trim($_POST['numfactu']);

      $cmaMFController   = new CMA_MFacturaController();
      $query="select CONVERT(varchar(11),m.fechafac,101) fecha,pa.codclien,pa.nombres,pa.Historia,mi.clase,mi.desitems,mi.kit,df.coditems, df.cantidad,m.* from cma_MFactura m inner join cma_DFactura df on df.numfactu=m.numfactu inner join MInventario mi on df.coditems=mi.coditems inner join MClientes pa on pa.codclien=m.codclien where m.numfactu in(select d.numfactu from cma_DFactura d where coditems in(select i.coditems from MInventario i where i.clase in('cm','xsomas') and i.activo=1)) and m.statfact=3 and mi.clase in('cm','xsomas') and m.numfactu='$numfactu'";
     
        $facturasM=$cmaMFController->readUDF($query);
        if (is_array($facturasM) && count($facturasM)>0) {
          $codmedico="000";
          if ($facturasM[$i]['Codmedico']!="") {
             $codmedico=$facturasM[0]['Codmedico'];
          }
          for ($i=0; $i <count($facturasM) ; $i++) { 
               $set_data = array(
                'coditems' =>$facturasM[$i]['coditems']
               ,'cantidad' =>$facturasM[$i]["cantidad"]
               ,'codclien' =>$facturasM[$i]["codclien"]
               ,'numfactu' =>$facturasM[$i]["numfactu"]    
               ,'fechafac' =>$facturasM[$i]["fecha"]
               ,'horareg'  =>$facturasM[$i]["horareg"]
               ,'usuario'  =>$facturasM[$i]["usuario"] 
               ,'cod_grupo'=>$facturasM[$i]['cod_grupo']
               ,'Codmedico'=>$codmedico
               ,'kit'      =>$facturasM[$i]['kit']
              );
              addSaveCantDisponible( $set_data );
          }     
        }
        $result = $set_data; 

}
 
echo( json_encode($result) );



//------------------------------------------------------------------

 function addSaveCantDisponible($set_data){
    $kitcontroller = new KitController();
    $disponibleController = new DisponibleController();
    $minventarioController = new MInventarioController();
   
    $qty     = $set_data['cantidad']; 
    $coditems= $set_data['coditems']; 

    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);
    for ($i=0; $i <$len ; $i++) { 
        $cantidad=$qty;
        $dis= $kit[$i]['disminuir'];
        if (!is_null( $dis ) ) {
            $cantidad=$cantidad*$dis;
        }
        $codikit= $kit[$i]['codikit'];
        $hora=$set_data['horareg'];

        $cod_subgrupo="";

        $prodInfo     =$minventarioController->readUDF( "select * from Minventario Where coditems='$codikit' " );
        if (count($prodInfo)>0) {
         $cod_subgrupo=$prodInfo[0]['cod_subgrupo'];
         $cod_grupo=$prodInfo[0]['cod_grupo'];
        }

        $set_datad = array(
           'coditems'    =>$codikit
          ,'cantidad'    =>$cantidad
          ,'codclien'    =>$set_data['codclien']
          ,'numfactu'    =>$set_data['numfactu']   
          ,'fecha'       =>$set_data['fechafac'] 
          ,'horar'       =>$set_data['horareg']
          ,'usuario'     =>$set_data['usuario']
          ,'fecreg'      =>Date('Y-m-d h:m:s')
          ,'cod_subgrupo'=>$cod_subgrupo
          ,'cod_grupo'   =>$cod_grupo
          ,'codmedico'   =>$set_data['Codmedico']
          ,'kit'         =>$set_data['kit']
          ,'quedan'      =>$cantidad
        
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
}
