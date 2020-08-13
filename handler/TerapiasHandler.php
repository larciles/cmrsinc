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



require('../controllers/MconsultaSSController.php');
require('../controllers/ClientesController.php');
require('../controllers/AppmnttherapiesController.php');

$mconsultaSSController = new MconsultaSSController();
// $p=$_POST ;
// $result = array($_POST); 


#terapia
if (isset($_POST['fecha']) && isset($_POST['hora']) ) {
    $fecha=trim($_POST['fecha']);
    $hora=trim($_POST['hora']);

    $query="Select a.*  from ( select CASE WHEN  
  CONVERT(int,
   Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) >=1 and
   CONVERT(int,
   Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) <6
   THEN
   Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end +12
   ELSE
   Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end
  END  timeapp
  ,codclien,Historia,Cedula,nombres,codmedico,Medico,descons,codconsulta,coditems
  ,mls,hilt,terapias,asistido,ASISTIDOS,fecha_cita,usuario
  ,CONVERT(varchar(11),fecha_cita,101)  fecha 
  from VIEW_mconsultas_02 where activa=1 and codconsulta='07' and fecha_cita ='$fecha' ) a
  Where  a.timeapp=$hora";

  $citados=$mconsultaSSController->readUDF($query);
 // json_encode($citados)
  echo json_encode($citados); 
}

/**
* Add appointment
*/

if (isset($_POST['array'])   ) {
    $arr= json_decode($_POST['array']);

    if (is_array($arr) && count($arr)>0) {
        for ($i=0;$i<count($arr); $i++){
            
            $arr1=$arr[$i];

            $codclien=$arr1->codclien;
            $codmedico=$arr1->codmedico; 
            $cantidad=$arr1->cantidad;
            $coditems=$arr1->coditems;
            $numfactu=$arr1->numfactu;
            $clase=$arr1->clase ;
            $fecha=$arr1->fecha ;
            $hora=$arr1->hora ;
            $disponible=$arr1->disponible;
            $compra=$arr1->compra;


            // para Mejorar en ss
            $mls=0;
            $hilt=0;

            #para mejorar
            $observacion="";

            $datuser=Apmntuser($codclien,$coditems);

                      //construyo el array de datos

             $set_data = array('codclien'=>$codclien
                        ,'fecha' =>date('m-d-Y h:m:s')
                        ,'hora'=>$hora
                        ,'fecha_cita' =>$fecha
                        ,'citacontrol' =>$datuser['citacontrol']
                        ,'codmedico' =>$codmedico
                        ,'citados' =>1
                        ,'confirmado' => 0
                        ,'asistido' =>0
                        ,'asistido' =>0
                        ,'primera_control'=>$datuser['primera_control']
                        ,'activa' =>1
                        ,'observacion'=> is_null($observacion) ? "":$observacion
                        ,'usuario' =>$datuser['usuario']
                        ,'fecreg' =>date('m-d-Y h:m:s')
                        ,'servicios' => 1
                        ,'codconsulta' =>'07'
                        ,'coditems'  => $coditems
                        ,'mls' =>$mls
                        ,'hilt' =>$hilt
                        ,'paciente' =>$datuser['paciente']
                        ,'record' =>$datuser['record']
                        ,'ts'=>date('m-d-Y h:m:s')
                        ,'numfactu'=>$numfactu
                        ,'cantidad'=>$cantidad
                        ,'clase'=>$clase
                        ,'disponible'=>$disponible
                        ,'compra'=>$compra
                      );

               $res= savealldetails($set_data);



        }
        
    }
}

/**
* Del appointment en el calendario
*/

if (isset($_POST['delappmnt'])   ) {

    $appmnttherapiesController = new AppmnttherapiesController();

    
    $arr= json_decode($_POST['delappmnt']);

    if (is_array($arr) && count($arr)>0) {
       
       for ($i=0;$i<count($arr); $i++){
       
            $arr1=$arr[$i];

            $codclien=$arr1->codclien;
            $codmedico=$arr1->codmedico; 
            $cantidad=$arr1->cantidad;
            $coditems=$arr1->coditems;
            $numfactu=$arr1->numfactu;
            $clase=$arr1->clase ;
            $fecha=$arr1->fecha ;
            $hora=$arr1->hora ;
            $disponible=$arr1->disponible;
            $compra=$arr1->compra;

            $where_data = array(
             'codclien' => $codclien
            ,'coditems' => $coditems
            ,'fecha'    => $fecha
            ,'asistido' => '0'
            );

           $array_del = array(                              
            'where' => $where_data
           );
  

           $res= $appmnttherapiesController->delete($array_del); 

           $where_data = array(
             'codclien' => $codclien
            ,'coditems' => $coditems
            ,'fecha_cita'  => $fecha
            ,'asistido' =>'0'
            );

           $array_del = array(                              
            'where' => $where_data
           );

           $res= $mconsultaSSController->delete($array_del); 

       }


        //chequeo que existan mas citas
        $res=$appmnttherapiesController->readUDF("select count(*) n_citas from  appmnttherapies where codclien='$codclien' and asistido=0");     

       echo json_encode($res); 

    }

}


/**
* Elimina cita desmarcando el checkbos del item
*/

if (isset($_POST['delitemappmnt'])   ) {

   $appmnttherapiesController = new AppmnttherapiesController();

   $arr= json_decode($_POST['delitemappmnt']);
   $arr1=$arr[0];
   $codclien=$arr1->codclien;
   $coditems=$arr1->coditems;


   $where_data = array(
    'codclien' => $codclien
   ,'coditems' => $coditems   
   ,'asistido' => '0'
   );

  $array_del = array(                              
   'where' => $where_data
  );

  $res= $appmnttherapiesController->delete($array_del); 
  $res= $mconsultaSSController->delete($array_del); 
  
  //chequeo que existan mas citas
   $res=$appmnttherapiesController->readUDF("select count(*) n_citas from  appmnttherapies where codclien='$codclien' and asistido=0");     

  echo json_encode($res); 

}




/*** Funciones ***/
 
/**
*
*/

  function savealldetails($set_data){

     $appmnttherapiesController = new AppmnttherapiesController();
     $mconsultaSSController = new MconsultaSSController();   

     $codclien   = trim($set_data['codclien']);
     $coditems   = trim($set_data['coditems']);
     $disponible = trim($set_data['disponible']);
     $fecha      = $set_data['fecha_cita'];

     
     //Si se esta solo cambiando la hora en la misma fecha hay que elimar la que existe y agregar la nueva
     // $query="Select sum(cantidad) qty from appmnttherapies Where codclien='$codclien' and coditems ='$coditems' and asistido=0  and fecha='$fecha' ";

     

        $where_data = array(
         'codclien' => $set_data['codclien']
        ,'coditems' => $set_data['coditems']
        ,'fecha'    => $fecha
        );

        $array_del = array(                              
         'where' => $where_data
        );
  
         $res= $appmnttherapiesController->delete($array_del); 

        $where_data = array(
         'codclien' => $set_data['codclien']
        ,'coditems' => $set_data['coditems']
        ,'fecha_cita'    => $fecha
        );

        $array_del = array(                              
         'where' => $where_data
        );

        $res= $mconsultaSSController->delete($array_del); 
     

     // verifico que tenga disponibilidad
     $query="Select sum(cantidad) qty from appmnttherapies Where codclien='$codclien' and coditems ='$coditems' and asistido=0  ";

     $hay=$appmnttherapiesController->readUDF($query);

     $thereare= (int)$hay[0]['qty'];

     if (  $thereare<$disponible  ) {

         $arraySave = array('fecha'     =>$set_data['fecha_cita']                       
                           ,'codclien'  =>$set_data['codclien']
                           ,'record'    =>$set_data['record']
                           ,'coditems'  =>$set_data['coditems']
                           ,'hora'      =>$set_data['hora']
                           ,'ts'        =>date('m-d-Y h:m:s')
                           ,'cantidad'  =>$set_data['cantidad']
                           ,'numfactu'  =>$set_data['numfactu']
                           ,'clase'     =>$set_data['clase']
                           ,'usuario'   =>$set_data['usuario']
                           ,'controlador'=>$set_data['usuario']
                         );

        $res=$appmnttherapiesController->create($arraySave);

        $set_data['hora']=$set_data['hora'].":00";

        $res=$mconsultaSSController->create($set_data);
        return $res;

     }

    


  }


/**
*
*/


  function Apmntuser($codclien,$coditems){

     $mconsultaSSController = new MconsultaSSController();   
     $clientescontroller = new ClientesController();

     $primera_control=1;
     $citacontrol=0;

     $query="Select b.tipo, a.* from MconsultaSS a inner join MInventario b on b.coditems=a.coditems where a.codconsulta='07' and a.activa='1' and a.codclien='$codclien' order by  fecha_cita desc";  
     
     $citascount=$mconsultaSSController->readUDF($query); 
     //obtengo el usuario si ha tenido al menos una cita y ademas se determina si es la primera o es seguimiento   
     if (count($citascount)>0) {
        $primera_control=0; 
        $citacontrol=1;
        $usuario=$citascount[0]['usuario'];
     }

     //obtengo el nombre, el recors y el usuario en caso de que sea su primera cita
     $pacienterec=$clientescontroller->readUDF("Select nombres,Historia,usuario from MClientes where codclien='$codclien' ");
 
     $paciente="";
     $record="";

     if (count($pacienterec)>0) {
        $paciente=$pacienterec[0]['nombres'];
        $record=$pacienterec[0]['Historia'];
     }

     if (count($citascount)<=0) {
         $usuario=$pacienterec[0]['usuario'];
     }

     $arrayRes = array('primera_control'=>$primera_control
                      ,'citacontrol'=>$citacontrol
                      ,'usuario'=>$usuario
                      ,'paciente'=>$paciente
                      ,'record'=>$record
                    );

     return $arrayRes; 
  }




// 
 
// 
#fin terapias
#borrar
