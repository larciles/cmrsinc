<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:http://localhost/cma/vistas/login/login.php");
}else{
    $user=$_SESSION['username'];
    $controlcita=$_SESSION['controlcita'];
}

require_once '../../models/user_model.inc.php';
// require "../../controllers/atencionlasercontroller.php";
require "../../clases/firstendweekday.php";
require "../../clases/firstendweekdaywname.php";

require('../../controllers/ClientesController.php');
require('../../controllers/MedicosController.php');
require('../../controllers/MconsultaSSController.php');
require('../../controllers/DisplaserController.php');
require('../../controllers/AppmnttherapiesController.php');


//var_dump($_POST);
#fecha del calendario
if(isset($_GET['sltconsultas'])){
  $xcod=$_GET['sltconsultas'];
}

$medicosController = new MedicosController();
$clientescontroller = new ClientesController();
$firstendweekday = new Firstendweekday();

$firstendweekdaywname= new Firstendweekdaywname();

$mconsultaSSController = new MconsultaSSController();
$displaserController = new DisplaserController();
$appmnttherapiesController = new AppmnttherapiesController();

$arrHoras=['07:00','08:00','09:00','10:00','11:00','12:00','01:00','02:00','03:00','04:00','05:00'];
$daysname=["","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];

$curtime=(int)date('h');
if ($curtime>12) {
   $curtime=$curtime-12;
}

for ($i=0; $i <count($arrHoras) ; $i++) { 
  $valtime=(int)$arrHoras[$i]; 
  if ($curtime==$valtime) {
     $timeopt.="<option value=".$valtime." selected>".$arrHoras[$i]."</option>";
  }else{
    $timeopt.="<option value=".$valtime.">".$arrHoras[$i]."</option>";
  }
}



#variables
$idpaciente="";
$opts="";
$fecha=date('m/d/Y');
$idfound="";

if (isset($_POST['oksub']) && $_POST['oksub']!="" ) {
    setAppointment($_POST);
    unset($_POST);
    unset($_REQUEST);
}

if ( isset($_POST) && !empty($_POST['idpaciente']) ) {

   if ($_POST['previousid']==$_POST['idpaciente']) {
    
        $arrayPost = array(); 
        for($i=0;$i<count($_POST['td-chk']);$i++ ){

            $arrayTmp = array('cantidad' =>$_POST['terapiasn'][$i]  );
            $arrayPost[$_POST['td-chk'][$i]]=$arrayTmp;
        }

      //  var_dump($arrayPost);
    
     echo "<script> console.log('diferente') </script>";
   }
   
   //busco si hay fechas seleccionadas para citas
  if ($_POST['prog-chk']) {
     $calendarseltddate=KeepDataToAppmnt($_POST['prog-chk'], $_POST['calendarseltddate']);
  }


  // var_dump($_POST);
  // 
   $idpaciente=trim($_POST['idpaciente']);
   #paciente
    $query="Select TOP 25 Historia,nombres,Cedula,codmedico,codclien from Mclientes where  concat(Historia,nombres,Cedula) like '%$idpaciente%' ";    

   $paciente=$clientescontroller->readUDF($query);

    if (count($paciente)) {    
       for ($i=0; $i <count($paciente) ; $i++) { 
           $opts .="<option value=".$paciente[$i]['codclien']." record=".$paciente[$i]['Historia']." medico=".$paciente[$i]['codmedico'].">".$paciente[$i]['nombres']."</option>";
       }

       $subject=$opts;
       $search =$paciente[0]['codclien'];
       $replace=$paciente[0]['codclien']." selected";       
       $opts=preg_replace("/".$search."/", $replace, $subject, 1);


       //SELECCIONA EL MEDICO DEL PACIENTE
       $codmedico=$paciente[0]['codmedico'];       
       #medico   
       $medico=$medicosController->readUDF("Select Codmedico , CONCAT(Nombre,' ' , apellido)  medico from Mmedicos where activo=1");

       if (count($medico)>0) {
          for ($i=0; $i <count($medico) ; $i++) { 
            $mdopts.="<option value=".$medico[$i]['Codmedico']." >".$medico[$i]['medico']."</option>";
          }
       }
    

       $xx= $mdopts;
       $medicos=str_replace($codmedico, $codmedico." selected",$xx);
     
    }

   #fecha del calendario
   $fecha=date('m/d/Y');
   if (isset($_POST['fecha']) && !empty($_POST['fecha'])) {
      $fecha=$_POST['fecha'];
   }
   
  // $weekdays= $firstendweekday->getDates($fecha);

    $weekdays= $firstendweekdaywname->getDates($fecha);


  #busca el paciente en la semana activa
   if (count($paciente)) {    
    
    $codclient=$paciente[0]['codclien'];

    $query="Select a.* from ( select CASE WHEN  
    CONVERT(int,
     Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) >=1 and
     CONVERT(int,
     Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) <6
     THEN
     Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end +12
     ELSE
     Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end
    END timeapp
    ,codclien,Historia,Cedula,nombres,codmedico,Medico,descons,codconsulta,coditems
    ,mls,hilt,terapias,asistido,ASISTIDOS,fecha_cita,usuario
    ,CONVERT(varchar(11),fecha_cita,101)  fecha 
    from VIEW_mconsultas_02 where activa=1 and codconsulta='07' and asistido=0  ) a
    Where  codclien='$codclient' " ;

   
    $paciente_cita=$mconsultaSSController->readUDF( $query );
    if (count($paciente_cita)>0) {
      
        $apmntime=$paciente_cita[0]['timeapp'];
        if ($apmntime>12) {
           $apmntime=$apmntime-12;
        }
       $xx= $timeopt;


       #limpia el selected de la hora actual
       $subject=$timeopt;
       $search ="selected";
       $replace="  ";       
       $timeopt=preg_replace("/".$search."/", $replace, $subject, 1);
       #establece el selected de la cita
       $subject=$timeopt;
       $search =$apmntime;
       $replace=$apmntime." selected";       
       $timeopt=preg_replace("/".$search."/", $replace, $subject, 1);

       $idfound=str_replace("/","-","_".$paciente_cita[0]['fecha'])."-".$paciente_cita[0]['timeapp'] ;

       $arrayAppointment = array(); 
       $key="";

       $primera_cita=$paciente_cita[0]['fecha'];  

      // Fecha selecionada de la citas pendiente del paciente en caso de que posea mas de una cita POR SERVICIO    
       if (isset($_POST['fecha-cur-cita']) && $_POST['fecha-cur-cita']!="" ) {
           
           $esta_cita=str_replace("-","/",  $_POST['fecha-cur-cita']);

           $query="select fecha_cita from (select * from VIEW_mconsultas_02 where activa=1 and codconsulta='07' and asistido=0 ) a    Where codclien='$codclient' and fecha_cita='$esta_cita' "; 

           $esta_en_este_paciente = $mconsultaSSController->readUDF( $query );           
           if (is_array($esta_en_este_paciente) && count($esta_en_este_paciente)>0) {
               $primera_cita=$esta_cita; 
            }
       
       } 
       
       $fechacitas="";
       
       $arraServicios=["vitc","intra","laser"];
       $pemf=""; //para marche el checkbo en caso de que tenga cita

       for ($i=0; $i <count($paciente_cita) ; $i++) { 

           $estafecha=str_replace("/","-",$paciente_cita[$i]['fecha']);       
           
 
           $pos = strpos($fechacitas, $estafecha);

            // Note our use of ===.  Simply == would not work as expected
            // because the position of 'a' was the 0th (first) character.
           if ($pos === false) {
              $fechacitas.="<option value=". $estafecha .">".$paciente_cita[$i]['fecha']."</option>";
           }

           

           #filtra las citas pendientes de un mismos dia 
               
           
                 if ($primera_cita==$paciente_cita[$i]['fecha']) {
                    $key="";
                    if ( strtolower($paciente_cita[$i]['descons'])==strtolower('SUEROTERAPIA') ) {
                       $key="vitc";   
                   }else if( strtolower($paciente_cita[$i]['descons'])==strtolower('INTRAVENOSO') ){
                       $key="intra";   
                   }else if( strtolower($paciente_cita[$i]['descons'])==strtolower('Terapia Laser') ){
                       $key="laser";               
                   }
                  
                
                   if ( strtolower($paciente_cita[$i]['descons'])==strtolower('TERAPIA MAG') ) {
                     $pemf="1";
                   }

                   $res=array_search($key, $arraServicios);
                   if ($res>=0){
                      $arraytmp = array('mls'=>$paciente_cita[$i]['mls']
                                       ,'hilt'=>$paciente_cita[$i]['hilt']
                                       ,'hora'=>$apmntime  
                               );

                      $arrayAppointment[$key]=$arraytmp;                   
                     
                   }

                 }
           
       }
        

       #selecciona la fecha  de la cita
       $subject= $fechacitas;
       $search = str_replace("/","-",$primera_cita);  
       $replace=$search." selected";
       $fechacitas=preg_replace("/".$search."/", $replace, $subject, 1);

      //nueva opcion
      $query="Select CONCAT( CONVERT(varchar(11), fecha,110),'-',RTRIM( lTRIM(hora))  ) fechahora,numfactu,cantidad,coditems from appmnttherapies Where codclien='$codclient' and asistido=0" ;


      $resul=$appmnttherapiesController->readUDF( $query );

      $citas=  array();
      
      // var_dump($resul);

      for($i=0; $i<count($resul);$i++){

        $fechahora=$resul[$i]['fechahora'];
        $coditems=trim($resul[$i]['coditems']);

        $arraytmp = array('numfactu' =>$resul[$i]['numfactu'],
                          'cantidad' =>$resul[$i]['cantidad']
                           );

        $citas[$fechahora][$coditems]=$arraytmp;

      }



    }
/*
echo "<pre>";
print_r($citas);
echo "</pre>";
*/ 
// var_dump( $citas );



 
    // BUSCA DISPONIBILIDA DE TERAPIAS
    $query="Select t.* from ( select max(b.clase) as clase, max(b.tipo) as tipo, max(b.cod_subgrupo) as cod_subgrupo, max(b.desitems) as desitems, a.codclien, a.record, a.numfactu, a.cantidad, a.quedan, a.codmedico, CONVERT(varchar(11),a.fecha,101) fecha, CONCAT( max( c.nombre),' ', max(c.apellido)) medico,a.itemfac
      from displaser a 
      inner join MInventario b on a.itemfac=b.coditems
      inner join Mmedicos c on a.codmedico=c.Codmedico
      group by a.itemfac,a.codclien,a.record,a.numfactu,a.cantidad,a.quedan,a.codmedico,a.fecha 
      union all
      select b.clase,b.tipo,b.cod_subgrupo,b.desitems,a.codclien,a.record,a.numfactu,a.cantidad,a.quedan,a.codmedico, CONVERT(varchar(11),a.fecha,101) fecha, CONCAT(c.nombre,' ', c.apellido) medico,itemfac 
      from disponible a 
      inner join MInventario b on a.coditems=b.coditems
      inner join Mmedicos c on a.codmedico=c.Codmedico
      where b.cod_subgrupo not in('EXOSOMAS','CEL MADRE') ) t where t.codclien='$codclient' and t.quedan>0 order by t.fecha desc";

      $dispo=$displaserController->readUDF($query);

   //  echo("<pre>");
   //   print_r(  $dispo );
   // echo("</pre>");
  }


}else{

 if ( isset($_POST) && !empty($_POST['fecha']) ) {

    $fecha=$_POST['fecha'];
    unset($_POST);
    unset($_REQUEST);
   // $weekdays= $firstendweekday->getDates($fecha);

  $weekdays= $firstendweekdaywname->getDates($fecha);

 }else{
    $fecha=date('m/d/Y');
  //  $weekdays= $firstendweekday->getDates($fecha);

     $weekdays= $firstendweekdaywname->getDates($fecha);
       
 }
 

}

$startday=$weekdays[0]['first'];
$lastday  =$weekdays[0]['last'];
//LASER HILT Y MLS

 $query="Select CONVERT(varchar(11),fecha_cita,101)  fecha , sum(mls) n_mls, sum(hilt) n_hilt, 
CASE WHEN 
CONVERT(int,
 Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) >=1 and
 CONVERT(int,
 Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) <6
 THEN
 Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end +12
 ELSE
 Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end
END  timeapp
from VIEW_mconsultas_02 
where activa=1 and codconsulta='07' and fecha_cita between '$startday' and '$lastday'  
group by fecha_cita,Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end order by fecha_cita  asc, timeapp ASC"; 
// echo "<pre>";
// print_r($query);
// echo "</pre>";
$g_citasHiltMls=$mconsultaSSController->readUDF($query);

$arrayCitasMLSHILT = array(); 
if (count($g_citasHiltMls)>0) {
    for ($i=0; $i <count($g_citasHiltMls); $i++) {
         #fecha de la citas 
         $fechaCita=$g_citasHiltMls[$i]['fecha']; 
         $timecita=$g_citasHiltMls[$i]['timeapp']; 
         
         $mls =$g_citasHiltMls[$i]['n_mls'];
         $hilt=$g_citasHiltMls[$i]['n_hilt'];

         $arrayTmp = array('mls' => $mls
                          ,'hilt' => $hilt );

         $arrayCitasMLSHILT[$fechaCita][$timecita]=$arrayTmp;

     } 
}

#SUERO E INTRAVENOSO
$arrayIntraVitC = array();
$query="Select CONVERT(varchar(11),fecha_cita,101)  fecha , count(*) cantidad, descons, 
 CASE WHEN 
 CONVERT(int,
  Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) >=1 and
  CONVERT(int,
  Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end ) <6
  THEN
  Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end +12
  ELSE
  Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end
 END  timeapp
 from VIEW_mconsultas_02 
 where activa=1 and codconsulta='07' and fecha_cita between '$startday' and '$lastday' and descons in ('INTRAVENOSO','SUEROTERAPIA')  
 group by fecha_cita,Case when LEN(hora)> 0 then RTRIM( LTRIM( SUBSTRING(hora,1, CHARINDEX(':',hora)-1 ) ) ) end, descons order by fecha_cita  asc, timeapp ASC";

$g_intravc=$mconsultaSSController->readUDF($query);

if (count($g_intravc)>0) {
  for ($i=0; $i <count($g_intravc) ; $i++) { 
        $fechaCita=$g_intravc[$i]['fecha']; 
        $timecita=$g_intravc[$i]['timeapp']; 
        $terapia=$g_intravc[$i]['descons'];

        $arrayTmp = array( 'cantidad'=>$g_intravc[$i]['cantidad'] );
        $arrayIntraVitC[$fechaCita][$timecita][$terapia]=$arrayTmp;
  }
   
}
// echo("<pre>");
// print_r($arrayIntraVitC);
// echo("</pre>");
// echo("<pre>");
// var_dump($g_intravc);
// echo("</pre>");
$activeDay=explode("/", $fecha)[1];
//  unset($_REQUEST);
// unset($_POST);
 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  
  
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/timepicker/bootstrap-timepicker.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">  
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />  
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  <link rel="stylesheet" href="../../css/blinking.css">
  <link rel="stylesheet" href="../../css/alert.css">
  <link rel="stylesheet" href="../../css/datepicker/bootstrap-datepicker.css" />
  <link rel="stylesheet" href="./estiloscitaslaser.css" />
  <link rel="stylesheet" href="./css/estilos.css" />

 </head>
<body>
 
  <header>
    <div class="container-fluid ">
      <?php include '../layouts/header.php';?>
    </div>
  </header>
  <!-- incluyo el modal de las terapias -->
  <?php include './terapiasmodal.php'; ?>
  <div class="container-fluid">

    <div class="box">
    <div class="ribbon"><span>TERAPIAS</span></div>
    </div>
                        <form  method="POST" id="formterapias">

                        <?php if(is_array($calendarseltddate) && count($calendarseltddate)>0 ){  foreach($calendarseltddate as $value){ ?> 
                        <input type="hidden" name="calendarseltddate[]" value="<?php echo($value) ?>">
                        <?php  }   } ?>

                        <input id="oksub" type="hidden" name="oksub" >
                        <input id="previousid" type="hidden" name="previousid" value="<?php echo($_POST['idpaciente']) ?>" >


                    <div class="row row-inicial ">
                        <div class="form-group col-sm-2">
                          <label for="idpaciente">ID-Nombre-Teléfono</label>
                            <input type="text" id="idpaciente" name="idpaciente" class="form-control idpaciente" placeholder="Record" value="<?php echo( $idpaciente) ?>" autocomplete="off">
                        </div>
                          
                      <div class="form-group  col-sm-2">    
                            <label for="idassoc">Paciente</label>                          
                            <select id="idassoc" name="idassoc" class="form-control idassoc" autocomplete="off">
                               <?php echo($opts) ?>
                            </select>
                      
                        <p id='nameasoc' style='opacity: 1; float:left;' class='clearfix text pacientes-vinculados'>Pacientes viculados <span class="badge"></span></p>
                   
                      </div>

                        <div class="form-group col-sm-2 div-fecha-cita">
                            <label for="medico">Médico</label>                          
                            <select id="medico" name="medico" class="form-control medico" autocomplete="off">
                               <?php echo($medicos) ?>
                            </select>
                        </div>

                        <div class="form-group col-sm-1 citaactual" id="citaactual" style="display:<?php
                        echo( !empty($paciente_cita[0]['fecha'])? 'block' : 'none' ) 
                         ?>">
                         <label for="currentdate">Cita</label>
                         <select class="form-control fecha-cur-cita" name="fecha-cur-cita" id="fecha-cur-cita" autocomplete="off" >
                           <?php echo($fechacitas) ?>
                         </select>
                        </div>
                        
                        <div class="form-group col-sm-2" id="fgfechacita">
                            <label for="fecha">Fecha</label>
                            <div class="input-group date" data-provide="datepicker">
                                <input type="text" name="fecha" id="fecha" class="form-control fecha" autocomplete="off" value="<?php echo($fecha)?>">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                            </div>

                        </div>
                         <?php if( !empty(trim($_POST['idpaciente'])) ) { ?>

 <!--                        <div class="form-group col-sm-2">
                          <label for="submitok"></label>
                            <div class="form-group">
                                <input type="buttom" name="submitok" id="submitok" class="btn btn-info submitok" value="Citar" />  
                              </div> 
                        </div>  -->
                      <?php } ?>

                    </div>


                     <div class="row disponibilidad" style="display:<?php echo( (is_array($dispo) && count($dispo)>0)? 'block':'none' )?>; ">
                      <div class="titulo-3">
                         <h4>Terapias Disponibles</h4> 
                      </div>
                      <table class="table table-hover " id="tb-disponible" >
                        <thead>
                          <tr>
                          <th>#</th>
                          <th>Clase</th>
                          <th class="th-citar" id="th-citar">Citar</th>
                          <th>Producto</th>

                          <th># Terapias</th>

                          <th>Disponible</th>

                          <th>Compra</th>
                          
                          <th>Factura</th>
                          <th>Fecha</th>
                          <th>Médico</th>
                          
                          </tr>
                        </thead>
                        <tbody class="body-3">
                          <?php 
                          if (is_array($dispo) && count($dispo)>0) {
                             for ($i=0; $i < count($dispo) ; $i++) { 

                              $desitems=$dispo[$i]['desitems'];
                              $cantidad="";
                              $checkeddis="";


                              $keys = null;
                              if (!is_null($citas)) {
                                  $keys = searchForId($dispo[$i]['itemfac'], $citas,'key'); 
                              }

                              
                          //    var_dump($citas);
                              if (is_null($keys)) {
                                // var_dump($dispo[$i]['itemfac']); 
                                // 
                              }
                             

                              //var_dump($dispo[$i]['itemfac']);


                              if ( !is_null($keys) && $dispo[$i]['numfactu']==$keys['numfactu'] ) {

                                  $cantidad=$keys['cantidad'];
                                  $checkeddis="checked";
                                 
                              }

                              if ( is_array($arrayPost) && count($arrayPost)>0 ) {
                                 $respost=  $arrayPost[$dispo[$i]['itemfac'].'-'.$dispo[$i]['numfactu'].'-'.$dispo[$i]['tipo']];
                                  
                                 if ( is_array($respost)  && count( $respost )>0  ) {
                                  if ($cantidad=="") {
                                        $cantidad=$respost['cantidad'];
                                  }
                                   
                                      $checkeddis="checked";
                                 }
                             
                                 

                              }

                          ?>
                          <tr id="<?php echo($dispo[$i]['itemfac']) ?> ">
                            
                            <th class=""><?php echo ($i+1)  ?></th>
                            <td class="clase"><?php echo($dispo[$i]['tipo'])?></td> 


                            <td class="td-disp-chk"> 
                              <label class="lb-td-chk"> 
                                <input type="checkbox" class="td-chk" name="td-chk[]" value="<?php echo($dispo[$i]['itemfac'].'-'.$dispo[$i]['numfactu'].'-'.$dispo[$i]['tipo']) ?>"  <?php echo $checkeddis; ?>  >
                                <span class="label-text"></span>  
                              </label>   
                            </td> 
                            <td class=""><?php echo($desitems)  ?></td> 
                            <td class="laser-cant">
                            
                                     <input name="terapiasn[]" class="form-control terapiasn" min="0"  max="<?php echo($dispo[$i]['quedan']) ?>"  placeholder="# terapias" type="number" value="<?php echo($cantidad); ?>" /> 
                            </td> 
                             

                            <td class="td-disponi"><?php echo($dispo[$i]['quedan'])  ?></td> 
                            <td class="td-compra"><?php echo($dispo[$i]['cantidad'])  ?></td>                             
                            <td class=""><?php echo($dispo[$i]['numfactu'])  ?></td> 
                            <td class=""><?php echo($dispo[$i]['fecha'])  ?></td> 
                            <td class=""><?php echo($dispo[$i]['medico'])  ?></td> 
                            
                          </tr>
                          <?php 
                            }
                           }
                           ?>

                          
                        </tbody>
                      </table>
                    </div>
     
 
 
              <!--   </form> -->
  </div>

  <div class="container-fluid">
    <table class="table table-hover app" id="app" >
      <caption>Citas de Laser MLS / HILT / Intravenoso / Vitamina C </caption>
      <thead>
         <!--  -->
        <tr>
          <th>Hora</th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['first'])[1]==$activeDay ? 'activo':'' )?>">
              <?php echo  $daysname[date('N', strtotime($weekdays[0]['first']))] ; ?>     
              <?php echo( explode('/', $weekdays[0]['first'])[1]  )  ?>
              
            </th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['second'])[1]==$activeDay? 'activo':'' )?>">
            <?php echo  $daysname[date('N', strtotime($weekdays[0]['second']))] ; ?>
            <?php echo( explode('/', $weekdays[0]['second'])[1] )  ?>
              
            </th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['third'])[1]==$activeDay ? 'activo':'' )?>">
            <?php echo  $daysname[date('N', strtotime($weekdays[0]['third']))] ; ?>
            <?php echo( explode('/', $weekdays[0]['third'])[1]  )  ?>
              
            </th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['fourth'])[1]==$activeDay? 'activo':'' )?>">
            <?php echo  $daysname[date('N', strtotime($weekdays[0]['fourth']))] ; ?>
            <?php echo( explode('/', $weekdays[0]['fourth'])[1] )  ?>
              
            </th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['fifth'])[1]==$activeDay ? 'activo':'') ?>">
            <?php echo  $daysname[date('N', strtotime($weekdays[0]['fifth']))] ; ?>
            <?php echo( explode('/', $weekdays[0]['fifth'])[1]  )  ?>
              
            </th>
          <th  class="th-titulos <?php echo(explode('/', $weekdays[0]['sixth'])[1]==$activeDay ? 'activo':'') ?>">
            <?php echo  $daysname[date('N', strtotime($weekdays[0]['sixth']))] ; ?>
            <?php echo( explode('/', $weekdays[0]['sixth'])[1]  )  ?>
              
            </th>
        </tr>

      </thead>
      <tbody>
        <?php 
        $time=7;
        for ($i=0; $i <count($arrHoras) ; $i++) { 

            $curTime=(int)$arrHoras[$i];
          
            if ($curTime>=1 && $curTime<6) {
               $curTime=$curTime+12;
            }
         ?>
        <tr class="row-data altitud-tr">
          <th class="t-time"><?php echo($arrHoras[$i]) ?></th>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['first'])[1]==$activeDay ? 'activo':'' )?>
             <?php 
              $mls =$arrayCitasMLSHILT[$weekdays[0]['first']][$curTime]['mls'];
              $hilt=$arrayCitasMLSHILT[$weekdays[0]['first']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' );             

              ?>"  id="<?php echo( str_replace('/','-', "_".$weekdays[0]['first'] ) .'-'.$curTime)  ?>"><div class="wrap-main"> <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            
          
            <?php
            $mls =$arrayCitasMLSHILT[$weekdays[0]['first']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['first']][$curTime]['hilt']; 

            $intra=trim($arrayIntraVitC[$weekdays[0]['first']][$curTime]['INTRAVENOSO']['cantidad']);
            $vitac=$arrayIntraVitC[$weekdays[0]['first']][$curTime]['SUEROTERAPIA']['cantidad'];
            
            echo("<div class='wrap-laser'>");

            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera"> Total: '.($mls+$hilt). '</span>');
            }

             echo("</div>");

            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra  label label-success verde'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
            echo("</div>");
              ;?>
<!-- 
                    <pre>
            <?php  #var_dump($mls)  ?>
            <?php  #var_dump($hilt)  ?>
            <?php  #var_dump($intra)  ?>
            <?php  #var_dump($vitac)  ?>
          </pre> -->
    
                <?php if ( !empty(trim($_POST['idpaciente'])) ) {
                     $response=  appmntChkSettings( $weekdays[0]['first'] ,$vitac,$intra,$mls,$hilt); 
                     echo  $response['clase'];
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['first'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                   $checkeddt="checked";
                 }                 

                 ?>

                <label class="lbl-prog"> <input type="checkbox" class="prog-chk mon-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['first'] ) .'-'.$curTime);  ?> "   <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?>  ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>
                
              
          </div>   
          </td>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['second'])[1]==$activeDay ? 'activo':'' )?>
          <?php 
               $mls =$arrayCitasMLSHILT[$weekdays[0]['second']][$curTime]['mls'];
               $hilt=$arrayCitasMLSHILT[$weekdays[0]['second']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' ); 
              ?>"  id="<?php echo( str_replace('/','-',"_".$weekdays[0]['second']).'-'.$curTime)  ?>"><div class="wrap-main"> <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            <?php 
            $mls =$arrayCitasMLSHILT[$weekdays[0]['second']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['second']][$curTime]['hilt'];

            $intra=$arrayIntraVitC[$weekdays[0]['second']][$curTime]['INTRAVENOSO']['cantidad'];
            $vitac=$arrayIntraVitC[$weekdays[0]['second']][$curTime]['SUEROTERAPIA']['cantidad'];

            echo("<div class='wrap-laser'>");
            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera "> Total: '.($mls+$hilt). '</span>');
            }
            echo("</div>");
            
            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra label label-success'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
            echo("</div>");
          ?>  
    

              <?php if (!empty(trim($_POST['idpaciente'])) ) {
                  $response=  appmntChkSettings( $weekdays[0]['second'] ,$vitac,$intra,$mls,$hilt); 
                  echo  $response['clase'];
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['second'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                    $checkeddt="checked";
                 }                 

                 ?>
               
                <label class="lbl-prog"> <input type="checkbox" class="prog-chk tue-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['second'] ) .'-'.$curTime)  ?> "  <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?> ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>

           </div> 
          </td>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['third'])[1]==$activeDay ? 'activo':'' )?>
             <?php 
               $mls =$arrayCitasMLSHILT[$weekdays[0]['third']][$curTime]['mls'];
               $hilt=$arrayCitasMLSHILT[$weekdays[0]['third']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' ); 
              ?>"  id="<?php echo( str_replace('/','-',"_".$weekdays[0]['third']).'-'.$curTime)  ?>"><div class="wrap-main"> <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            <?php
            $mls =$arrayCitasMLSHILT[$weekdays[0]['third']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['third']][$curTime]['hilt'];

            $intra=$arrayIntraVitC[$weekdays[0]['third']][$curTime]['INTRAVENOSO']['cantidad'];
            $vitac=$arrayIntraVitC[$weekdays[0]['third']][$curTime]['SUEROTERAPIA']['cantidad'];

            echo("<div class='wrap-laser'>");

            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera "> Total: '.($mls+$hilt). '</span>');
            }

             echo("</div>");

            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra label label-success'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
            echo("</div>");
          ?>
          
              <?php if (!empty(trim($_POST['idpaciente'])) ) {
                   $response=  appmntChkSettings( $weekdays[0]['third'] ,$vitac,$intra,$mls,$hilt); 
                   echo  $response['clase'];
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['third'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                   $checkeddt="checked";
                 }                 

                 ?>
              
                <label class="lbl-prog"> <input type="checkbox" class="prog-chk wed-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['third'] ) .'-'.$curTime)  ?> "  <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?> ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>



            </div>  
          </td>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['fourth'])[1]==$activeDay ? 'activo':'' )?> <?php 
               $mls =$arrayCitasMLSHILT[$weekdays[0]['fourth']][$curTime]['mls'];
               $hilt=$arrayCitasMLSHILT[$weekdays[0]['fourth']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' ); 
              ?>" id="<?php echo( str_replace('/','-',"_".$weekdays[0]['fourth']).'-'.$curTime)  ?>"><div class="wrap-main"> <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            <?php
            $mls =$arrayCitasMLSHILT[$weekdays[0]['fourth']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['fourth']][$curTime]['hilt'];

            $intra=$arrayIntraVitC[$weekdays[0]['fourth']][$curTime]['INTRAVENOSO']['cantidad'];
            $vitac=$arrayIntraVitC[$weekdays[0]['fourth']][$curTime]['SUEROTERAPIA']['cantidad'];

            echo("<div class='wrap-laser'>");

            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera "> Total: '.($mls+$hilt). '</span>');
            }

            echo("</div>");

            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra label label-success'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
            echo("</div>");
          ?>

              <?php if (!empty(trim($_POST['idpaciente'])) ) {
                   $response=  appmntChkSettings( $weekdays[0]['fourth'] ,$vitac,$intra,$mls,$hilt); 
                   echo  $response['clase'];
                    
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['fourth'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                   $checkeddt="checked";
                 }                 

                 ?>
              
                <label class="lbl-prog"> <input type="checkbox" class="prog-chk thu-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['fourth'] ) .'-'.$curTime)  ?>"  <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?> ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>

             </div> 
          </td>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['fifth'])[1]==$activeDay ? 'activo':'' )?>
          <?php 
               $mls =$arrayCitasMLSHILT[$weekdays[0]['fifth']][$curTime]['mls'];
               $hilt=$arrayCitasMLSHILT[$weekdays[0]['fifth']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' ); 
              ?>" id="<?php echo( str_replace('/','-',"_".$weekdays[0]['fifth']).'-'.$curTime)  ?>"><div class="wrap-main"> <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            <?php
            $mls =$arrayCitasMLSHILT[$weekdays[0]['fifth']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['fifth']][$curTime]['hilt'];

            $intra=$arrayIntraVitC[$weekdays[0]['fifth']][$curTime]['INTRAVENOSO']['cantidad'];
            $vitac=$arrayIntraVitC[$weekdays[0]['fifth']][$curTime]['SUEROTERAPIA']['cantidad'];

            echo("<div class='wrap-laser'>");

            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera "> Total: '.($mls+$hilt). '</span>');
            }

            echo("</div>");

            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra label label-success'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
             echo("</div>");
          ?>


              <?php if ( !empty(trim($_POST['idpaciente'])) ) {
                   $response=  appmntChkSettings( $weekdays[0]['fifth'] ,$vitac,$intra,$mls,$hilt); 
                   echo  $response['clase']; 
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['fifth'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                   $checkeddt="checked";
                 }                 

                 ?>
              
                <label class="lbl-prog"> <input type="checkbox" class="prog-chk fri-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['fifth'] ) .'-'.$curTime)  ?> " <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?> ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>
          
             </div> 
          </td>
          <td class="t-data <?php echo(explode('/', $weekdays[0]['sixth'])[1]==$activeDay ? 'activo':'' )?>
          <?php 
               $mls =$arrayCitasMLSHILT[$weekdays[0]['sixth']][$curTime]['mls'];
               $hilt=$arrayCitasMLSHILT[$weekdays[0]['sixth']][$curTime]['hilt'];
              echo( ($mls+$hilt)>=25? ' al-limite' : '' ); 
              ?>" id="<?php echo( str_replace('/','-',"_".$weekdays[0]['sixth']).'-'.$curTime)  ?>"><div class="wrap-main">  <div class="backbox">  <p id="bg-text"><?php echo($mls+$hilt) ?></p></div>
            <?php
            $mls =$arrayCitasMLSHILT[$weekdays[0]['sixth']][$curTime]['mls'];
            $hilt=$arrayCitasMLSHILT[$weekdays[0]['sixth']][$curTime]['hilt'];

            $intra=$arrayIntraVitC[$weekdays[0]['sixth']][$curTime]['INTRAVENOSO']['cantidad'];
            $vitac=$arrayIntraVitC[$weekdays[0]['sixth']][$curTime]['SUEROTERAPIA']['cantidad'];

             echo("<div class='wrap-laser'>");

            if (!empty($mls)) {
               echo( '<span class="tbldatos label label-primary tbl-mls azul"> MLS: '.$mls.'</span>' );
            }
            if (!empty($hilt)) {
              echo('<span class="tbldatos label label-danger tbl-hilt rojo"> HILT: '.$hilt. '</span>');
            }
            if (  !empty(($mls+$hilt)) ) {
               echo('<span class="tbldatos total-thera "> Total: '.($mls+$hilt). '</span>');
            }

            echo("</div>");

            echo("<div class='intravc'>");
               if (!empty($intra)) {
                  echo("<span class='intra label label-success'>Intra: ".$intra."</span>");
                }
                if (!empty($vitac)) {
                  echo("<span class='vitc label label-info suero'>Suero VC: ".$vitac."</span>");
                }
            echo("</div>");
            
          ?>

              <?php if( !empty(trim($_POST['idpaciente'])) ) {
                   $response=  appmntChkSettings( $weekdays[0]['sixth'] ,$vitac,$intra,$mls,$hilt); 
                   echo  $response['clase'];
               ?>

               <?php 
                 $datetime= str_replace('/','-',$weekdays[0]['sixth'] ) .'-'.$curTime;
                 $checkeddt="";
                 if ( !is_null($citas[$datetime]) ) {
                   $checkeddt="checked";
                 }                 

                 ?>
        
                <label class="lbl-prog"> <input type="checkbox" class="prog-chk sat-chk" name="prog-chk[]" value="<?php echo( str_replace('/','-',$weekdays[0]['sixth'] ) .'-'.$curTime)  ?> " <?php  echo($response['habilitado']); ?> <?php echo $checkeddt; ?> ><span class="label-text"></span>  </label>
              </div>
            <?php } ?>

             </div> 
          </td>
        </tr>
        <?php 
        $time++;
            }            
         ?>
        <tr>
          <th></th>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</form>
</body>
</html>
<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script> 
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/atencionlaser.js?V=202200802-0"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/bootstrap-select.min.js"></script>
<script src="../../js/jquery.redirect.js"></script>
<script src="../../js/jquery.inputmask.bundle.js"></script>
<script src="../../js/timepicker/bootstrap-timepicker.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    $( "#idpaciente" ).keypress(function(evt) {
       var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
       if(keyCode == 13)
        {
     // $('#submitok').click(); 
      document.getElementById("formterapias").submit();      
    }
 });
 $("#fecha").change(function(){
      var validDate = new Date( $("#fecha").val()).toString();
      console.log(validDate);
      var selectedDate=$("#fecha").val();
      var dosubmit=true;
      if (validDate!=="Invalid Date") {
        <?php   foreach ($weekdays[0]   as $key => $element) { ?>
          if (selectedDate==<?php echo('"'.$element).'"' ?>) {
             dosubmit=false;            
          }
        
        <?php } ?>
        if (dosubmit) {
        removeNameAttribute();    
        document.getElementById("formterapias").submit();       
          // $('#submitok').click();
        }
      };
      
 });

//*******************************************************************
 //Elimina el atributo nama antes del submit
 function removeNameAttribute(){
     //para eliminar el atributo name si no esta seleccionado

   let x = document.getElementById("tb-disponible").rows.length;
   let chk=document.getElementsByClassName('td-disp-chk');
   for (var i = 1; i < x; i++) {
      if (!chk[i-1].children[0].children[0].checked) {
        chk[i-1].parentNode.getElementsByClassName('laser-cant')[0].children[0].removeAttribute('name');
      }
        
   }
   //</>  

 }

 //***************************************************************

 $('#submitok').click(function(e) {

   //para eliminar el atributo name si no esta seleccionado

   let x = document.getElementById("tb-disponible").rows.length;
   let chk=document.getElementsByClassName('td-disp-chk');
   for (var i = 1; i < x; i++) {
      if (!chk[i-1].children[0].children[0].checked) {
        chk[i-1].parentNode.getElementsByClassName('laser-cant')[0].children[0].removeAttribute('name');
      }
        
   }
   //</>  


    document.getElementById('oksub').value="ok";
    document.getElementById("formterapias").submit();     
   
    return true; // return false to cancel form action
});

  try{
    var element = document.getElementById("<?php echo($idfound) ?>");
    element.classList.add("find-out");
  }catch(err){
    
  }


 
  var x = document.getElementById("tb-disponible").rows.length;
  var chk=document.getElementsByClassName('td-disp-chk');
  if (x>0) {
       for (var i = 1; i < x; i++) {

         // para hilt
         let tipo=chk[i-1].previousElementSibling.textContent;

         if (chk[i-1].previousElementSibling.textContent=="HILT"){
            //  var hilt = document.getElementById('gethilt')
            //  if (hilt.value!="0" && hilt.value.trim()!="" ) {
            //     chk[i-1].children[0].children[0].checked=true;
            // }
         }

         // mls
         if (chk[i-1].previousElementSibling.textContent=="MLS"){
             var mls = document.getElementById('getmls')
             // if (mls.value!="0" && mls.value.trim()!="" ) {
             //     chk[i-1].children[0].children[0].checked=true;
             // }
         }

         if (chk[i-1].previousElementSibling.textContent=="VITC"){
               // var wi_gevitc = document.querySelector('#gevitc').checked
               // chk[i-1].children[0].children[0].checked=wi_gevitc;
         }  

         if (chk[i-1].previousElementSibling.textContent=="INTRA"){
               // let wi_getintra = document.querySelector('#getintra').checked;
               // chk[i-1].children[0].children[0].checked=wi_getintra;
         }

         if (tipo=="MAG"){       
             <?php if ($pemf=="1") {
                 ?>
                 chk[i-1].children[0].children[0].checked=true;
                 <?php  
             } ?>      
             
         }
         

       }

  }

  // FINAL
 })




</script>

<?php 
  
  function setAppointment($saveappmnt){


  /*
    $apmmnt=$saveappmnt['calendarseltddate'];
    $arrtotalcitas=array();
    if (is_array($apmmnt) && count($apmmnt)>0) {
        foreach ($$apmmnt as $value) {
          $arrtotalcitas[]=$value;
        }      
    }

   $apmmnt=$saveappmnt['prog-chk'];    
   if (is_array($apmmnt) && count($apmmnt)>0) {
        foreach ($apmmnt as $value) {
          $arrtotalcitas[]=$value;         
        }      
    }

        
    $codclien=trim($saveappmnt['idassoc']); 

    // CASO # 1 CITA SIN  ESTAR VINCULADA A UNA FACTURA
    if ( !isset( $saveappmnt['td-chk']) ) {      

        // cita para intra   
        if ( isset($saveappmnt['getintra']) ) {
            // INTRA LI001
            $coditems="LI001";
            $datuser=Apmntuser($codclien,$coditems);
            $anote=getNoteAndRemoveApmnt($codclien,$coditems);
            setArraytoSave($saveappmnt,$coditems,'','','',$datuser,$anote);
        }
         
        //cita para suero  
        if (isset($saveappmnt['gevitc'])) {
           $coditems="25GST";
           $datuser=Apmntuser($codclien,$coditems);
           $anote=getNoteAndRemoveApmnt($codclien,$coditems);
           setArraytoSave($saveappmnt,$coditems,'','','',$datuser,$anote);
        }

        //cita para hilt || mls

        if (( !empty($saveappmnt['hilt']) || !empty($saveappmnt['mls']) ) && count($arrtotalcitas)==0 ) {          
           $coditems="TD01";
           $datuser=Apmntuser($codclien,$coditems);
           $anote=getNoteAndRemoveApmnt($codclien,$coditems,'','','',$datuser,$anote);
           setArraytoSave($saveappmnt,$coditems);
        }elseif(( !empty($saveappmnt['hilt']) || !empty($saveappmnt['mls'])  ) && count($arrtotalcitas)>0 ){
           $coditems="TD01";
           $datuser=Apmntuser($codclien,$coditems);
           $anote=getNoteAndRemoveApmnt($codclien,$coditems);
           setArraytoSave($saveappmnt,$coditems);
           foreach ($arrtotalcitas as $value) {
              $value= explode("-",$value) ;                        
              $hora= trim(array_pop($value));
              $fecha=trim(join("-",$value));
              setArraytoSave($saveappmnt,$coditems,'',$fecha,$hora,$datuser,$anote);
           }           
        }
       
     } 

      //Segundo caso
      $arrayProd = array();
      if ( isset( $saveappmnt['td-chk']) ) {  

     
           for ($i=0; $i <count($saveappmnt['td-chk']) ; $i++) { 

                $arry_attr=explode("-",$saveappmnt['td-chk'][$i]);
                $coditems=$arry_attr[0];
                $factura=$arry_attr[1];
                $tipo=$arry_attr[2];

                // cita para intra   
                if ( $tipo=="INTRA"  ) {            
                    $coditems="LI001";
                    $arrayProd[$tipo]=$tipo;
                    $datuser=Apmntuser($codclien,$coditems);
                    $anote=getNoteAndRemoveApmnt($codclien,$coditems);
                    setArraytoSave($saveappmnt,$coditems,$tipo,'','',$datuser,$anote);  
                }   

                //cita para suero  
                if ( $tipo=="VITC" ) {
                    $coditems="25GST";
                    $arrayProd[$tipo]=$tipo;
                    $datuser=Apmntuser($codclien,$coditems);
                    $anote=getNoteAndRemoveApmnt($codclien,$coditems);
                    setArraytoSave($saveappmnt,$coditems,$tipo,'','',$datuser,$anote);  
                }

                //cita para hilt || mls  
                if ( ($tipo=="HILT" || $tipo=="MLS")  && count($arrtotalcitas)==0) {          
                    $coditems="TD01";
                    $arrayProd[$tipo]=$tipo;
                    $datuser=Apmntuser($codclien,$coditems);
                    $anote=getNoteAndRemoveApmnt($codclien,$coditems);
                    setArraytoSave($saveappmnt,$coditems,$tipo,'','',$datuser,$anote);  
                }

                //cita para hilt || mls  
                if ( ( $tipo=="HILT" || $tipo=="MLS" ) && count($arrtotalcitas)>0) {          
                    $coditems="TD01";
                    $datuser=Apmntuser($codclien,$coditems);
                    $anote=getNoteAndRemoveApmnt($codclien,$coditems);
                    $arrayProd[$tipo]=$tipo;
                    foreach ($arrtotalcitas as $value) {
                     
                        $value= explode("-",$value) ;                        
                        $hora= trim(array_pop($value));                        
                        $fecha=trim(join("-",$value));                         
                        
                        setArraytoSave($saveappmnt,$coditems,$tipo,$fecha,$hora,$datuser,$anote);    
                    }
                }
           }           

      }




      //Tercer caso
      if ( isset( $saveappmnt['td-chk']) ) {  
                // cita para intra   
        if ( isset($saveappmnt['getintra']) ) {
            // INTRA LI001
            $pos= $arrayProd['INTRA']; 
            if ( is_null($pos) ){
               $coditems="LI001";
               $datuser=Apmntuser($codclien,$coditems);
               $anote=getNoteAndRemoveApmnt($codclien,$coditems);
               setArraytoSave($saveappmnt,$coditems,'','','',$datuser,$anote);  
            }            
        }
         
        //cita para suero  
        if (isset($saveappmnt['gevitc'])) {
           $pos= $arrayProd['VITC']; 
           if ( is_null($pos) ){
              $coditems="25GST";
              $datuser=Apmntuser($codclien,$coditems);
              $anote=getNoteAndRemoveApmnt($codclien,$coditems);
              setArraytoSave($saveappmnt,$coditems,'','','',$datuser,$anote);
            }
        }

        //cita para hilt || mls
        if (( !empty($saveappmnt['hilt']) || !empty($saveappmnt['mls']) ) && count($arrtotalcitas)==0 ) {                     
           $pos= $arrayProd['HILT'];
           $pos1= $arrayProd['MLS'];  
           if ( is_null($pos) &&  is_null($pos1)) {
              $coditems="TD01";
              $datuser=Apmntuser($codclien,$coditems);
              $anote=getNoteAndRemoveApmnt($codclien,$coditems);
              setArraytoSave($saveappmnt,$coditems,'','','',$datuser,$anote);             
           }          
           
        }

                //cita para hilt || mls
        if ( (!empty($saveappmnt['hilt']) || !empty($saveappmnt['mls']) ) && count($arrtotalcitas)>0 ) {                     
           $pos= $arrayProd['HILT'];
           $pos1= $arrayProd['MLS'];  
           if ( is_null($pos) &&  is_null($pos1)) {
              $coditems="TD01";
              $datuser=Apmntuser($codclien,$coditems);
              $anote=getNoteAndRemoveApmnt($codclien,$coditems);
           foreach ($arrtotalcitas as $value) {
              $value= explode("-",$value) ;                        
              $hora= trim(array_pop($value));
              $fecha=trim(join("-",$value));
              setArraytoSave($saveappmnt,$coditems,'',$fecha,$hora,$datuser,$anote);
           } 

             
           }          
           
        }


      }

*/
    //*<

    /*
    tipo  coditems
    VITC  25GST
    INTRA LI001
    MLS TD01
    */

    $afechacitas=$saveappmnt['prog-chk'];
    $aitemscitas=$saveappmnt['td-chk'];
    $terapiasn=$saveappmnt['terapiasn'];
    
 


     for ($i=0; $i <count($afechacitas)-1 ; $i++) { 
         
        $codclien=trim($saveappmnt['idassoc']); 

        $arr_hora =$afechacitas=explode("-", $saveappmnt['prog-chk'][$i]);
        $hora=(int)trim($arr_hora[3]);
        $fecha=$arr_hora[0]."-".$arr_hora[1]."-".$arr_hora[2];

        for ($j=0; $j <count($aitemscitas) ; $j++) {
          
         

          $arr_itemsdata=explode("-",$aitemscitas[$j]);

          $coditems=$arr_itemsdata[0];
          $numfactu=$arr_itemsdata[1];
          $clase=$arr_itemsdata[2];

          $datuser=Apmntuser($codclien,$coditems);

          //obtego el numeo de terapias
          $cantidad= $terapiasn[$j];

          //construyo el array de datos

             $set_data = array('codclien'=>$saveappmnt['idassoc']
                        ,'fecha' =>date('m-d-Y h:m:s')
                        ,'hora'=>$hora
                        ,'fecha_cita' =>$fecha
                        ,'citacontrol' =>$datuser['citacontrol']
                        ,'codmedico' =>$saveappmnt['medico']
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
                      );
           //guardo la cita y los detalles
           savealldetails($set_data);


         }
         
     }





     
  }
  // *****************************************************
  function savealldetails($set_data){

    $appmnttherapiesController = new AppmnttherapiesController();

     $codclien = trim($set_data['codclien']);
     $coditems = trim($set_data['coditems']);

     $query="Select * from appmnttherapies Where codclien='$codclien' and coditems ='$coditems'  ";

     $hay=  $appmnttherapiesController->readUDF($query);

     if (count($hay)>0) {

        $where_data = array(
         'codclien' => $set_data['codclien']
        ,'coditems' => $set_data['coditems']
        ,'fecha'    => $set_data['fecha_cita']     
        );

        $array_del = array(                              
         'where' => $where_data
        );
  
         $res= $appmnttherapiesController->delete($array_del); 
     }

    

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
  }

//*******************************************************
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

  function getNoteAndRemoveApmnt($codclien,$coditems){
    
     $mconsultaSSController = new MconsultaSSController();  

     $query="Select b.tipo, a.* from MconsultaSS a inner join MInventario b on b.coditems=a.coditems where a.codconsulta='07' and a.activa='1' and a.asistido=0 and a.codclien='$codclien' and a.coditems='$coditems'";

     $busca_cita=$mconsultaSSController->readUDF($query); 

     if (count( $busca_cita)>0) {
         $observacion = $busca_cita[0]['observacion'];
      }      
      
      $arrayRes = array('observacion' =>$observacion  );

       $where_data = array(
               'codclien'=> $codclien
               ,'coditems'=>$coditems
             );
      

        $array_del = array(
             'where' => $where_data
        );

        $res=$mconsultaSSController->delete($array_del); 
   
      return $arrayRes;  
  }


  function setArraytoSave($saveappmnt,$coditems,$tipo='',$fecha='',$hora='',$datuser=array(),$anote=array()){
    
    $mconsultaSSController = new MconsultaSSController();   
    $clientescontroller = new ClientesController();

    $observacion=$anote['observacion'];   
 
    is_numeric($saveappmnt['mls']) ?$mls=$saveappmnt['mls']:$mls=0;
    is_numeric($saveappmnt['hilt']) ?$hilt=$saveappmnt['hilt']:$hilt=0;
       
    if ($tipo!="MLS" && $tipo!="HILT") {
         $mls=0;
         $hilt=0;
     }
        
       $date=$saveappmnt['fecha']; 
       $time=$saveappmnt['timetera'].":00";
       if ( !empty($fecha) && !empty($hora) ) {
           $date=$fecha;
           $time=$hora.":00";
       }

       $set_data = array('codclien'=>$saveappmnt['idassoc']
                        ,'fecha' =>date('m-d-Y h:m:s')
                        ,'hora'=>$time
                        ,'fecha_cita' =>$date
                        ,'citacontrol' =>$datuser['citacontrol']
                        ,'codmedico' =>$saveappmnt['medico']
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
                        ,'ts'=>date('m-d-Y h:m:s'));
   
    //agrega una nueva cita
    $res=$mconsultaSSController->create($set_data);
    $id=$res['lastInsertId' ];
    savealldetails($set_data,$id,$fecha,$hora);

  }


  //*******************************************************
  // funcion para el control dechk de citas dentro del calendario
  // determina que clase usar dependiendo de las condiciones
  // y habilita o no seun la fecha

  function appmntChkSettings($calendarDate,$vitac,$intra,$mls,$hilt){

    //deshabilita el chk si la fecha actual es mayor 

     $deshabilitado="";
     $nohabiltado="";
     $hoy=date("m/d/Y");
     if($hoy>$calendarDate){
        $deshabilitado="disabled";
        $nohabiltado="deshabilitado";
     }                       
     if(  ($mls+$hilt)==0 && (is_null($intra) && is_null($vitac) ) ){
          $divClass='<div class="programar  doble-cero '.$nohabiltado.'">';                
     }elseif(is_null($mls) && is_null($hilt) && (is_null($intra) || is_null($vitac) ) ){ 
          $divClass='<div class="programar  doble-cero '.$nohabiltado.'">';
     }elseif(( (is_null($mls) || $mls=='0' )&& ( is_null($hilt) || $hilt=='0'  ) )&& (!is_null($intra) && is_null($vitac )) ){       
          $divClass='<div class="programar  solo-intra '.$nohabiltado.'">';

     }elseif(( (is_null($mls) || $mls=='0' )&& ( is_null($hilt) || $hilt=='0'  ) )&& (!is_null($intra) && !is_null($vitac )) ){       
          $divClass='<div class="programar  solo-intra-suero '.$nohabiltado.'">';

     }elseif(( (is_null($mls) || $mls=='0' ) && (!is_null($hilt) || $hilt!='0'  ) )&& (!is_null($intra) && is_null($vitac )) ){       
          $divClass='<div class="programar  solo-intra-hilt '.$nohabiltado.'">';                                     
     

     }elseif(($mls+$hilt)>=1 && (is_null($intra) || is_null($vitac) ) ){                     
          $divClass='<div class="programar mas-de-cero '.$nohabiltado.'">';
      }elseif(($mls+$hilt)>=1 && (!is_null($intra) || is_null($vitac) ) ){                    
          $divClass='<div class="programar '.$nohabiltado.'">'; 
      }elseif(($mls+$hilt)>=1 && (is_null($intra) || !is_null($vitac) ) ){ 
          $divClass='<div class="programar solo-intra" '.$nohabiltado.'>';
      }elseif(($mls+$hilt)>=1 && (!is_null($intra) || !is_null($vitac) ) ){   
          $divClass='<div class="programar '.$nohabiltado.'">';
      }elseif(($mls+$hilt)==0 && (!is_null($intra) || !is_null($vitac) ) ){     
          $divClass='<div class="programar '.$nohabiltado.'">';
      }else if (($mls+$hilt)==0 ) {                  
          $divClass='<div class="programar en-cero '.$nohabiltado.'">'; 
      }else{                
          $divClass='<div class="programar '.$nohabiltado.'">';                
      }
      
      $arrayRes = array('habilitado' => $deshabilitado 
                        ,'clase'=>$divClass );         
      return  $arrayRes;

}
//******************************************************************
function KeepDataToAppmnt($progchk,$arrhidden){

  if (count($progchk)>0) {
     $arraDatAp; 
     for ($i=0; $i <count($progchk) ; $i++) { 
         $arraDatAp[]=$progchk[$i];
     }    
  }

  if (is_array($arrhidden) &&  count($arrhidden)>0 ) {
     for ($i=0; $i <count($arrhidden) ; $i++) { 
          $arraDatAp[]=$arrhidden[$i];
     }
  }
  return $arraDatAp;
}
//*************************************************************
function searchForId($id, $array,$type="val",$value="") {

   foreach ($array as $key => $val) {
    if ($type=="val") {
      if ($val[$value] === $id) {
           return $val;
       }
    }else{
         if( is_array( $val ) ){
          
          $newarray=$val;

           foreach ($newarray as $newkey => $newval) {
                    if ($newkey === $id) {                  
                      return $newval;
        }
        }
          
      }

    }


       
   }
   return null;
}
?>
