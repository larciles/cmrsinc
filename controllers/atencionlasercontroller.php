<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';

if (version_compare(phpversion(), '5.4.0', '<')) {
     if(session_id() == '') {
        session_start();
     }
 }
 else
 {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();   
    }
 }



  //from here 
  $post_fields = array( 'fecha','valueToSearch','confirmar','xconfirm','xasist','xnuevos','xcontrol','sltconsultas','sltconsultashd' ,'xarrived','page' ,'lasertype');          
  $form_data = array();
          
  foreach ( $post_fields as $key ){
     if ( isset( $_GET[$key] )){
          $form_data[$key] = $_GET[$key];
       }
  }   

          
  if (!empty( $form_data ) && !isset( $_SESSION['form_data'] )){
       $_SESSION['form_data'] = serialize( $form_data );
  }else
  {

      if ( isset( $_GET['fecha']) || isset( $_GET['valueToSearch']) || isset( $_GET['confirmar'])  || isset( $_GET['xconfirm']) || isset( $_GET['xasist'])  || isset( $_GET['xnuevos']) || isset( $_GET['xcontrol'])  || isset( $_GET['sltconsultas'] ) || isset( $_GET['sltconsultashd'] )   || isset( $_GET['xarrived'])  || isset( $_GET['lasertype']) ){
          $_SESSION['form_data'] = serialize( $form_data );
      }
  }
          
  if ( isset( $_SESSION['form_data'] ) && !empty( $_SESSION['form_data'] ) &&  empty( $form_data )  ||  (isset ($_GET['lasertype']) && $_GET['lasertype']!='') ){
      $form_data = unserialize( $_SESSION['form_data'] );           
               
      foreach($form_data as $key => $value)
      {
          $_GET[ $key] =$value;
      }
  }

  if ( $_GET['page']  ) {
    $form_data = unserialize( $_SESSION['form_data'] );           
               
      foreach($form_data as $key => $value)
      {
          $_GET[ $key] =$value;
      }   
  }

  //to here

function mostrarCitas(){
 $dbmsql = mssqlConn::getConnection();

 $lineLimit=350;
 if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
    $lineLimit=350;
 }

 $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : $lineLimit;
 $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
 $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

 if(isset($_POST['linexpage']) && $_POST['linexpage']!=""){
  $linexpage=$_POST['linexpage'];
  if($linexpage>350)
  {
    $limit =350;
  }
    $limit =$linexpage;
 }

  $query="Select codmedico,(nombre+' ' +apellido) as medico from mmedicos where activo='1'";
  $listado = mssqlConn::Listados($query);
  $medicos = json_decode($listado, true);
  $lenmedi = sizeof($medicos);

  $query="select codconsulta,descons,codcons,coditems from VIEW_ConsultaServicios ";
  $listado = mssqlConn::Listados($query);
  $tconsulta = json_decode($listado, true);
  $lenconslt = sizeof($tconsulta);

  $query = "Select 'Todos' as Descripcion, 1 as id  union  select 'Consultas' as Descripcion, 2 as id  union  select 'Sueroterapia' as Descripcion, 3 as id  union  select 'Laser' as Descripcion, 4 as id  order by id";
  $listado = mssqlConn::Listados($query);
  $sltcons = json_decode($listado, true);


  $fielSet="fecha_cita";
 
  date_default_timezone_set("America/Puerto_Rico");
  $fecha=date("Y-m-d");
  

  // $fech a=date("Y-m-d")-1;  
  if (isset($_GET['fecha']) && $_GET['fecha']!="") {
    $valDate=explode("/",$_GET['fecha']);
    $month=$valDate[0];
    $day=$valDate[1];
    $year=$valDate[2];
    if (checkdate($month,$day,$year)) {
       $fecha=$_GET['fecha'];
     } 
  
  } 
  
 $camposConsulta = "cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), '', '-') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder,codconfirm,horain,horaout,id,llegada,terapias,prioridad,mls,hilt";

 $laser_type='mls';
 if (isset($_GET['lasertype'])  && $_GET['lasertype']!="" ) {
    $laser_type=$_GET['lasertype'];
 }


  if (isset($_GET['valueToSearch']) && $_GET['valueToSearch']!="") {

     $filter=$_GET['valueToSearch'];

     $presente     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and  CONCAT( Historia,nombres) like '%$filter%'    and llegada <>''  " ;
     $qcitados     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and  CONCAT( Historia,nombres) like '%$filter%'   " ;
     $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59'  and  CONCAT( Historia,nombres) like '%$filter%' and confirmado is not null" ;
     $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and  CONCAT( Historia,nombres) like '%$filter%'   and ASISTIDOS ='Asistido' ";
     $qnuevos      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and  CONCAT( Historia,nombres) like '%$filter%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";
     $qcontrol     = "SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and asistido='3' ";
     $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02   where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and   CONCAT( Historia,nombres) like '%$filter%'   " ;
     $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59' and  CONCAT( Historia,nombres) like '%$filter%'   ";
     
     #MORNIG 
     $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '07:00' and '07:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query8to9    = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '08:00' and '08:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query9to10   = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '09:00' and '09:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query10to11  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '10:00' and '10:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query11to12  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '11:00' and '11:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 

     #EVENIG
     $query12to13  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '12:00' and '12:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query13to14  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '13:00' and '13:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query14to15  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '14:00' and '14:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query15to16  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '15:00' and '15:59'  and  CONCAT( Historia,nombres) like '%$filter%' "; 
     $query16to17  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora >= '16:00'"; 

  }else{

     $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02   where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and mls>0  and hora between '07:00' and '07:59'" ;
     #MORNIG 
     $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '07:00' and '07:59'"; 
     $query8to9    = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '08:00' and '08:59'"; 
     $query9to10   = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '09:00' and '09:59'"; 
     $query10to11  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '10:00' and '10:59'"; 
     $query11to12  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '11:00' and '11:59'"; 

     #EVENIG
     $query12to13  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '12:00' and '12:59'"; 
     $query13to14  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '13:00' and '13:59'"; 
     $query14to15  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '14:00' and '14:59'"; 
     $query15to16  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora between '15:00' and '15:59'"; 
     $query16to17  = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1'  and coditems like 'TD%' and ".$laser_type.">0  and hora >= '16:00'"; 
     
  }

  if (isset($_GET['xconfirm']) && $_GET['xconfirm']!="" ) {
     $qryRows .="  and confirmado is not null ";
     $query .=" and confirmado is not null ";
  }


  if (isset($_GET['xasist']) && $_GET['xasist']!="" ) {
     $qryRows .="   and ASISTIDOS ='Asistido'  ";
     $query .="  and ASISTIDOS ='Asistido'  ";
  }

    if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
     $qryRows .="   and llegada <>'' ";
     $query .="   and llegada <>''  ";
  }

  if (isset($_GET['xnuevos']) && $_GET['xnuevos']!="" ) {
     $qryRows .="   and primera_control='1' and ASISTIDOS = 'Asistido'   ";
     $query .="  and primera_control='1' and ASISTIDOS = 'Asistido'   ";
  }

  if (isset($_GET['xcontrol']) && $_GET['xcontrol']!="" ) {
     $qryRows .="  and primera_control='0' and  ASISTIDOS='Asistido' and codconsulta<>'07' ";
     $query .="  and primera_control='0' and  ASISTIDOS='Asistido' and codconsulta<>'07' ";
  }

  if(isset($_GET['sltconsultas']) && $_GET['sltconsultas']!=""){
     
  }else{
 

  }

 if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
   $query .=" order by llegada";
 }else{
     $query .=" order by nombres";
 }


    
    if($page==0)
    {
        $page=1;
    }

/**/
  #MORNING
  $morning=  prepareData($query, $query8to9, $query9to10, $query10to11, $query11to12);
  $tableArray=$morning[0];
  $maxLenArr =$morning[1];
  $len_endArr=$morning[2];
  
  #EVENING
                            
  $evening=  prepareData($query12to13, $query13to14, $query14to15, $query15to16, $query16to17);
  $tableArrayE=$evening[0];
  $maxLenArrE =$evening[1];
  $len_endArrE=$evening[2];



 

  $len_endTblArr=sizeof($tableArray);

#------------------------------------------------------------------------
  if ($maxLenArr>0) {
    $tabla  .="<div class='col-sm-12'  style='padding: 0;'  >";
     $tabla .="<table id='table7to12' class='table table-inverse table-condensed '>";             
        $tabla .="<thead class='thead-inverse'>";
            $tabla .="<tr>";  
                 $tabla .="<th class='titleatn'  >#</th>";
                 $tabla .="<th class='titleatn'  >07:00 am - 08:00 am</th>";
                 $tabla .="<th class='titleatn'  >08:00 am - 09:00 am</th>";
                 $tabla .="<th class='titleatn'  >09:00 am - 10:00 am</th>";
                 $tabla .="<th class='titleatn'  >10:00 am - 11:00 am</th>";
                 $tabla .="<th class='titleatn'  >11:00 am - 12:00  m</th>";                                     
            $tabla .="</tr>";
        $tabla .="</thead>";

        $tabla .=table_X($tableArray,$maxLenArr,$len_endArr);

      $tabla .="</table>";
    $tabla .="</div>";   
  }
  

  if ($maxLenArrE>0) {
      $tabla  .="<div class='col-sm-12'  style='padding: 0;'  >";
       $tabla .="<table id='table12to5' class='table table-inverse table-condensed '>";             
          $tabla .="<thead class='thead-inverse'>";
              $tabla .="<tr>";  
                   $tabla .="<th class='titleatn'  >#</th>";
                   $tabla .="<th class='titleatn'  >12:00  m - 01:00 pm</th>";
                   $tabla .="<th class='titleatn'  >01:00 pm - 02:00 pm</th>";
                   $tabla .="<th class='titleatn'  >02:00 pm - 03:00 pm</th>";
                   $tabla .="<th class='titleatn'  >03:00 pm - 04:00 pm</th>";
                   $tabla .="<th class='titleatn'  >02:00 pm - 05:00 pm</th>";                                     
              $tabla .="</tr>";
          $tabla .="</thead>";

          $tabla .=table_X($tableArrayE,$maxLenArrE,$len_endArrE);

        $tabla .="</table>";
    $tabla .="</div>";   
  }
  

  unset($tableArray);  
  unset($tableArrayE);   
  #------------------------------------------------------------------------    

 
  if($totalRegistros>0){
      $page  ="<div class='container' style= 'padding-left: 10px; padding-right: 10px;' >"; 
      $page  ="<div class='row'>";  
      $page .="<div style='float:left; padding-left: 10px; padding-right: 10px;'>";
      $page .=" <button type='button' id='spncitados' class='btn btn-active'>Citados <span  class='badge'>".$citados."</span></button>";
  
      $page .=" <button type='button' id='btnarrived' class='btn btn-active' data-tip='Pacientes presentes en el centro' >Presentes <span class='badge'>".$arrived."</span></button> ";

      $page .="</div>";
  
      $page .="<div style='float:right; padding-left: 10px; padding-right: 10px;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
      $page .="</div>";
          //echo "<div style='float:right;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
        echo $page;
    }
    $respuesta =  $tabla  ;
  echo $respuesta;
}

//--------------------------------
function prepareData($query1,$query2,$query3,$query4,$query5){
  $arr_nElements= array(); 
  $result1 = mssqlConn::Listados($query1); 
  $result1 = json_decode($result1);

  $result2 = mssqlConn::Listados($query2); 
  $result2 = json_decode($result2);

  $result3 = mssqlConn::Listados($query3);
  $result3 = json_decode($result3);

  $result4 = mssqlConn::Listados($query4);
  $result4 = json_decode($result4);

  $result5 = mssqlConn::Listados($query5);
  $result5 = json_decode($result5);

           
  $endArray = array();
  $tableArray = array();

  $result1 = fillArray($result1);
  array_push($arr_nElements,sizeof($result1 ));  
  array_push($endArray,$result1 );  

  $result2 = fillArray($result2);
  array_push($arr_nElements,sizeof($result2 )); 
  array_push($endArray,$result2 ); 


  $result3 = fillArray($result3);
  array_push($arr_nElements,sizeof($result3 )); 
  array_push($endArray,$result3 ); 

  $result4 = fillArray($result4); 
  array_push($arr_nElements,sizeof($result4 ));
  array_push($endArray,$result4);

  $result5 = fillArray($result5); 
  array_push($arr_nElements,sizeof($result5 ));
  array_push($endArray,$result5);

  rsort($arr_nElements);
  
  $maxLenArr=$arr_nElements[0];

  $len_endArr=sizeof($endArray);

  for ($i=0; $i <$len_endArr ; $i++) { 
       $currenArr=$endArray[$i];
       if (sizeof($endArray[$i])<$maxLenArr) {
          $n_fix = $maxLenArr-sizeof($currenArr) ;

          $fixedArray = fixLenArray($currenArr, $n_fix );

          array_push($tableArray ,$fixedArray );

       }else{

           array_push($tableArray ,$currenArr );
       }
  }

  unset($endArray);

  $endresult=  array();

  array_push($endresult, $tableArray);
  array_push($endresult, $maxLenArr);
  array_push($endresult, $len_endArr);
  
  return  $endresult;
}

//--------------------------------
function fixLenArray($tmpArr, $n_times ){
  for ($i=0; $i < $n_times ; $i++) { 
       array_push($tmpArr,array('codclien' => '', 'record'=> '','nombre'=>''));
  }
  //var_dump($tmpArr );
  return $tmpArr;
}
//--------------------------------
function fillArray($data){
    
    $len_a=sizeof($data);
    
    $arrayX = array();

    for ($i=0; $i <$len_a ; $i++) { 

      $n_mls =(int)$data[$i]->mls; 
      if ($n_mls>1) {
          for ($j=0; $j < $n_mls; $j++) {
            array_push($arrayX,array('codclien' => $data[$i]->codclien, 'record'=> $data[$i]->Historia,'nombre'=>$data[$i]->nombres));
          }
      }else{
            array_push($arrayX,array('codclien' => $data[$i]->codclien, 'record'=> $data[$i]->Historia,'nombre'=>$data[$i]->nombres));
          }
    }
    
    
    
    return $arrayX;
}
//--------------------------------
function table_X($tableArray,$n_MaxRec,$n_Col){
$tabl ="<tbody>";


for ($i=0; $i <$n_MaxRec; $i++) { 

      $tabl .="<tr>";
       $n_row = $i+1;
       $tabl .="<th scope='row'>".$n_row."</th>";
          for ($j=0; $j <$n_Col ; $j++) { 
              
              $id=$tableArray[$j][$i]['codclien'];
              $nombre_Record =$tableArray[$j][$i]['nombre'].' '.$tableArray[$j][$i]['record'];
              $record = $tableArray[$j][$i]['record'];

              $tabl .="<td id=".$id." class='paciente' record=".$record.">".$nombre_Record."</td >";
           } 
    
      $tabl .="</tr>";
 
}

$tabl .="</tbody>";            
return $tabl;
           

}

 


//------------------------------------
function displayInfo($query,$dbmsql){

  $result=$dbmsql->query($query);
  foreach($result as $row)
  {
      $lista[] = $row;
  }
  $npo=sizeof($lista);
        $res=0;
        if ($npo>0) {
            $res=$lista[0]['num_rows'];
        }
  // $listado = mssqlConn::Listados($query);
  // $inventario = json_decode($listado, true);
  // $nin=sizeof($inventario);
  // $totalRegistros=sizeof($inventario);
return $res;
}

function getPago($codclien,$fechafac,$coditems){
  if($coditems==""){
    //CONSULTAS
    $query="select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials,' ',a.total) disp "
    . "from cma_MFactura a "
    . "inner join cma_DFactura b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
    . "inner join loginpass c on a.usuario = c.login " 
    . "inner join Mpagos d on a.numfactu=d.numfactu and d.id_centro=2 "
    . "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
    . "where codclien='$codclien' and a.fechafac='$fechafac' ";
  }elseif ( substr($coditems,0,2) !="TD") {
    //SUERO
    $query="select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials,' ',a.total) disp "
    . "from cma_MFactura a "
    . "inner join cma_DFactura b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
    . "inner join loginpass c on a.usuario = c.login " 
    . "inner join Mpagos d on a.numfactu=d.numfactu and d.id_centro=2 "
    . "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
    . "where codclien='$codclien' and a.fechafac='$fechafac' and coditems='$coditems' ";
  }else{
    //LASER
    $query="Select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials,' ',a.total) disp "
    . "from MSSMFact a "
    . "inner join MSSDFact b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
    . "inner join loginpass c on a.usuario = c.login "
    . "inner join Mpagos d on a.numfactu=d.numfactu  "
    . "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
    . "where a.fechafac='$fechafac'  and coditems='$coditems' ";

  }
    $res =null;
   
    $pago  = mssqlConn::Listados($query);
    $res   = json_decode($pago, true);
    return $res;
}

function getQuery($fechafac){    
  $sql = "Select  * from view_displaypagos where fechafac='$fechafac'";      
  $pago   = mssqlConn::Listados($sql);
  $result = json_decode($pago, true);
  $pagos  = array();

  $lenO= sizeof($result);
  if ($lenO>0) {
      foreach($result as $fila){
         $pagos[$fila['keypass']]=$fila['disp'];
      }
  }
  
 return $pagos;
} 

function getNextAppoiment($codclien,$codconsulta,$coditems){
  if ($codconsulta=='07') {

        if (substr($coditems, 0, 2)=="TD") {
          //-- Laser
        $query="select convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta ='07' and coditems  like '%TD' and ASISTIDOS is null ";
        }else{
          //-- Suero
          $query="select convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta ='07' and coditems not like '%TD' and ASISTIDOS is null ";
        }     

  }else
  //-- Consultas
   $query="select convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta <>'07' and ASISTIDOS is null";
   
     $resultSet = mssqlConn::Listados($query);
     $res  = json_decode($resultSet, true);
     return $res;
}