<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
ini_set('memory_limit', '1024M');
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';
require_once '../../clases/MedicosClass.php';
require_once '../../controllers/VIEW_SoloConsultaController.php';


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
  $post_fields = array( 'fecha','valueToSearch','confirmar','xconfirm','xasist','xnuevos','xcontrol','sltconsultas','sltconsultashd' ,'xarrived','page','x_atendidos','xblock','x_sinasist' );          
  $form_data = array();
          
  foreach ( $post_fields as $key ){
     if ( isset( $_GET[$key] )){
          $form_data[$key] = $_GET[$key];
       }
  }   

          
  if (!empty( $form_data ) && !isset( $_SESSION['form_data'] )){
       $_SESSION['form_data'] = serialize( $form_data );
  }else {

      if ( isset( $_GET['fecha']) || isset( $_GET['valueToSearch']) || isset( $_GET['confirmar'])  || isset( $_GET['xconfirm']) || isset( $_GET['xasist'])  || isset( $_GET['xnuevos']) || isset( $_GET['xcontrol'])  || isset( $_GET['sltconsultas'] ) || isset( $_GET['sltconsultashd'] )   || isset( $_GET['xarrived'])  || isset( $_GET['x_atendidos']) || isset( $_GET['xblock']) || isset( $_GET['x_sinasist'])){
          $_SESSION['form_data'] = serialize( $form_data );
      }
  }
          
  if ( isset( $_SESSION['form_data'] ) && !empty( $_SESSION['form_data'] ) &&  empty( $form_data ) ){
      $form_data = unserialize( $_SESSION['form_data'] );           
               
      foreach($form_data as $key => $value)
      {
          $_GET[ $key] =$value;
      }
  }

  if ( $_GET['page']) {
    $form_data = unserialize( $_SESSION['form_data'] );           
               
      foreach($form_data as $key => $value)
      {
          $_GET[ $key] =$value;
      }   
  }

  //to here

function mostrarCitas(){
 $dbmsql = mssqlConn::getConnection();

 $md= new MedicosClass();

 $md_list=  $md->getSelectOpts();

 $consts= new SelectConsultas();

 $lineLimit=350;
 if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
    $lineLimit=350;
 }
 $condicion="";
 $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : $lineLimit;
 $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
 $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

 if(isset($_POST['linexpage']) && $_POST['linexpage']!=""){
  $linexpage=$_POST['linexpage'];
  if($linexpage>350)  {
    $limit =350;
  }
    $limit =$linexpage;
 }

 
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
  
  $camposConsulta = "cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), '', '-') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder,codconfirm,horain,horaout,id,llegada,terapias,prioridad,pacienteleft,answered";

  if (isset($_GET['valueToSearch']) && $_GET['valueToSearch']!="") {

     $filter=$_GET['valueToSearch'];
     
    $spos= strpos($filter,"'");
    if($spos>=0 && $spos!==false){
      $filter=substr($filter,0,$spos)."'".substr($filter,$spos);  
    } 
     
     $invert = explode(' ', $filter);
     $len=sizeof($invert);
     $len--;
     for ($i=$len; $i >=0 ; $i--) { 
        if ( $i >=0) {
           $invertFilter.=$invert[$i].'%';
        }else{
           $invertFilter.=$invert[$i];
        }   
     }
  $qsinAsistir="SELECT COUNT(*) num_rows from VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and horain<>'' and ASISTIDOS IS NULL  and llegada<>'' ";  
   $presente     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and llegada <>''  " ;
   $qcitados     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' " ;
   $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and confirmado is not null" ;
   $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and ASISTIDOS ='Asistido' ";
   $qnuevos      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and llegada <>'' and  codconsulta in('01')";
   $qatendidos   = "SELECT COUNT(*) num_rows from VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%' and horain<>'' and ASISTIDOS='Asistido' and llegada<>'' ";

   $qnuevosorg   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";

   $qcontrol     = "SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
   $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%'   " ;
   

   $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' and  CONCAT( Historia,nombres) like '%$filter%'   ";
   $condicion    = " CONCAT( Historia,nombres) like '%$filter%' " ;
  }else{
   $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
   $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' "; 
     
   $qnuevos      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02   where $fielSet='$fecha' and activa='1' and llegada <>''  and  codconsulta in('01') ";
   $qatendidos    = "SELECT COUNT(*) num_rows from VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and horain<>'' and ASISTIDOS='Asistido' and llegada<>'' ";

   $qnuevosorg   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and primera_control='1' and ASISTIDOS = 'Asistido' ";
   $qsinAsistir   = "SELECT COUNT(*) num_rows from VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and horain<>'' and ASISTIDOS IS NULL  and llegada<>'' "; 
   $qcontrol     = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     
   $presente     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and llegada <>''  " ;

   $qcitados     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
   $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and confirmado is not null" ;
   $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and ASISTIDOS ='Asistido' ";  

  }

  if (isset($_GET['xconfirm']) && $_GET['xconfirm']!="" ) {
     $qryRows .="  and confirmado is not null ";
     $query .=" and confirmado is not null ";
     $condicion .=" and confirmado is not null ";
  }


  if (isset($_GET['xasist']) && $_GET['xasist']!="" ) {
     $qryRows .="   and ASISTIDOS ='Asistido'  ";
     $query .="  and ASISTIDOS ='Asistido'  ";
     $condicion .="  and ASISTIDOS ='Asistido'  ";
  }

    if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
     $qryRows .="   and llegada <>'' ";
     $query .="   and llegada <>''  ";
     $condicion .="   and llegada <>''  ";
  }

  if (isset($_GET['xnuevos']) && $_GET['xnuevos']!="" ) {
     $qryRows .=" and llegada <>'' ";
     $query .=" and llegada <>'' ";
     $condicion.=" and llegada <>'' ";
  }

 if (isset($_GET['x_sinasist']) && $_GET['x_sinasist']!="" ) {
     $qryRows .="  and activa='1' and horain<>'' and ASISTIDOS IS NULL  and llegada<>'' ";
     $query .="  and activa='1' and horain<>'' and ASISTIDOS IS NULL  and llegada<>'' ";
     $condicion.="  and activa='1' and horain<>'' and ASISTIDOS IS NULL  and llegada<>'' ";
  }


  if (isset($_GET['xcontrol']) && $_GET['xcontrol']!="" ) {
     $qryRows .="  and primera_control='0' and  ASISTIDOS='Asistido' and codconsulta<>'07' ";
     $query .="  and primera_control='0' and  ASISTIDOS='Asistido' and codconsulta<>'07' ";
     $condicion .="  and primera_control='0' and  ASISTIDOS='Asistido' and codconsulta<>'07' ";
  }

  if(isset($_GET['sltconsultas']) && $_GET['sltconsultas']!=""){
     $opcion=$_GET['sltconsultas'];
     if($opcion==2){
      $qcitados     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $qconfirmados .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $qasistidos   .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 
      //$qnuevos      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 
     $qatendidos   .=" and (    codconsulta  in ('01','03') )"; 

      $qryRows      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      if (isset($_GET['xnuevos']) && $_GET['xnuevos']!="" ) {
          $query      .=" and ( codconsulta is null or codconsulta  in ('01'))";
          $condicion  .=" and ( codconsulta is null or codconsulta  in ('01'))";
      }else if(isset($_GET['x_atendidos']) && $_GET['x_atendidos']!=""){
           $query     .=" and ASISTIDOS ='Asistido' and horain<>'' and llegada<>'' ";
           $condicion .=" and ASISTIDOS ='Asistido' and horain<>'' and llegada<>'' ";
      }else{
          $query        .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
          $condicion    .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      }
      

      $presente     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";

     }else if($opcion==3){
      $qcitados     .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
      $qconfirmados .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
      $qasistidos   .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 
      $qnuevos      .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 
      $qatendidos   .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 
      $qryRows      .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
      $query        .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
      $condicion    .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
      $presente     .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
     }else if ($opcion==4) {
      $qryRows      .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $query        .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $condicion    .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $qnuevos      .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $qatendidos   .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $qcitados     .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $qconfirmados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
      $qasistidos   .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";

      $presente     .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
     }else if ($opcion==6){
    //BLOQUEO
      $qryRows     .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $query       .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $condicion   .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $qnuevos     .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $qatendidos  .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $qcitados    .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $qconfirmados.=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $qasistidos  .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";
      $presente    .=" and ( coditems like 'BL%' and codconsulta  in ('07') )";

     }else if ($opcion==7){
    //CM
      $qryRows     .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $query       .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $condicion   .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $qnuevos     .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $qatendidos  .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $qcitados    .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $qconfirmados.=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $qasistidos  .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";
      $presente    .=" and ( descons='CEL MADRE' and codconsulta  in ('07') )";

  }else if ($opcion==5){
    //CM
      $qryRows     .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $query       .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $condicion   .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $qnuevos     .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $qatendidos  .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $qcitados    .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $qconfirmados.=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $qasistidos  .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";
      $presente    .=" and ( descons='INTRAVENOSO' and codconsulta  in ('07') )";

  }
    
  }else{
    #MUESTRA LAS CONSULTAS OR DEFAUL
      $opcion='2';
      $qcitados     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $qconfirmados .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $qasistidos   .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 
     // $qnuevos      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 

      $qryRows      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $query        .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
      $condicion    .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";

      $presente     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";

  }

 if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
     $query .=" order by llegada";
 }else{
     $query .=" order by nombres";
 }
    $citados     =displayInfo($qcitados,$dbmsql);
    $confirmados =displayInfo($qconfirmados,$dbmsql);
    $asistidos   =displayInfo($qasistidos,$dbmsql);
    $nuevos      =displayInfo($qnuevos,$dbmsql); 
   
    $atendidos   =displayInfo($qatendidos,$dbmsql);         
    $sinAsistir  =displayInfo($qsinAsistir ,$dbmsql);
    $control     =displayInfo($qcontrol,$dbmsql); 

    $arrived     =displayInfo($presente,$dbmsql); 
    
    if($page==0)
    {
        $page=1;
    }

    #Inicio
     # TEST PARA AGILIZAR LA VELOCIDAD DE CARGA DE LA PAGINA CON EL METODO DE PAGO  
      $arrPagos = getQuery($fecha);
    #Fin

    //FIIL NOTES
     $arrNotas =  get_nota($fecha) ;

    // 

     $newAppt=getNewAppoiment($fecha, $condicion)  ;  

    $Paginator  = new Paginator( $dbmsql, $query,$qryRows );
    $tp =$Paginator->getTotal();
    if ($tp >0) {
      # code...
   
      $lastPage=ceil( $tp/$limit );
       if($page>$lastPage){
           $page=$lastPage;
       }
      $results    = $Paginator->getData( $page, $limit,"mssql" );
      $lastPage=ceil( $results->total/$limit );
      if($page>ceil( $results->total/$limit )){
          if($lastPage!=0){
         $page=ceil( $results->total/$limit );
         $results    = $Paginator->getData( $page, $limit );
          }
      }
     
      $totalRegistros=$results->datalen;
  }else
  {
    $totalRegistros=0;
  } 
    //
 
  $tabla ="<table id='tbl-ctrl' class='table table-inverse table-condensed '>"; 
  $tabla .="<thead class='thead-inverse'>";
  $tabla .="<tr>";

  $tabla .="<th class='titleatn' >Pacientes</th>";                  
  $tabla .="<th class='titleatn' >Record</th>";
  $tabla .="<th class='titleatn' >Médico</th>"; 
  $tabla .="<th class='titleatn' >Consulta</th>";
  $tabla .="<th class='titleatn' >Próx Cita</th>";
  $tabla .="<th class='titleatn' >En consulta</th>";
  $tabla .="<th class='titleatn' >Hora Salida</th>";
  $tabla .="<th class='titleatn' >Asistido</th>"; 
  $tabla .="<th class='titleatn' >Presente</th>"; 
  $tabla .="<th class='titleatn' >Forma de pago</th>";
  $tabla .="<th class='titleatn' >Prioridad</th>";   
  if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
     //$tabla .="<th class='' ># Usuario</th>";
     $tabla .="<th class='' ># Observaci?</th>";
  } 


  $tabla .="</tr>";
  $tabla .="</thead>";
  $tabla .="<tbody>";
  if($totalRegistros>0){
    for ($i=0; $i <$totalRegistros; $i++) { 
      if (is_null($results->data[$i]['pacienteleft']) || empty($results->data[$i]['pacienteleft'])) {


         if (is_null($results->data[$i]['answered']) || empty($results->data[$i]['answered'])) {
             $tabla .="<tr id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems'].">"; 
         }else{
             $tabla .="<tr id=".$results->data[$i]['codclien']." style='color: rgb(0, 0, 0);'  class='table-info'"." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems']." >";  
         }

         
      }else{
          if (is_null($results->data[$i]['answered']) || empty($results->data[$i]['answered'])) {
             $tabla .="<tr id=".$results->data[$i]['codclien']."   class='table-danger'"." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems']." >";  
          }else{
             $tabla .="<tr id=".$results->data[$i]['codclien']."  style='color: rgb(0, 0, 0);' class='table-danger table-info'"." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems']." >";  
          }
          
      }   
         
      $idC=$results->data[$i]['codclien'];
    
     #PACIENTE
     if ($results->data[$i]['codconfirm']=='2') {  
         
         if ($results->data[$i]['prioridad']=='1') {
           $tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='table-warning bg-warning cdcliente' >".$results->data[$i]['nombres']."</td>";   
         } else {
           $tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='table-success bg-success cdcliente' >".$results->data[$i]['nombres']."</td>";   
         }       
         
      
       }else{
      
         if ($results->data[$i]['prioridad']=='1') {
           $tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='bg-warning cdcliente' >".$results->data[$i]['nombres']."</td>";   
         } else{
           $tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='cdcliente' >".$results->data[$i]['nombres']."</td>";   
         }  
      
       }
  
      #RECORD
      $tabla .="<td align='center' contenteditable='false' name='record' field='record' id=".$results->data[$i]['codclien'].'-'.$i." class='edit record'>".$results->data[$i]['Historia']."</td>"; 

      #MEDICO
      $tabla .="<td align='center' class='td_medico' >";
      $medi= $results->data[$i]['codmedico'];
      $xx= $md_list;

      $optico="<select id='md$i' class='form-control sltmedico'  >";
      $optico.=$xx;
      $optico.="</select></td>";
     
      // $uu=strrpos($optico, $medi);
      $oo=str_replace($medi, $medi." selected",$optico);
      $tabla .=$oo;

 
      #CONSULTAS 
    
       if($_SESSION['codperfil']=='01' && $opcion=='2'){

          $tabla .="<td align='center'>";
          $x_cc=$results->data[$i]['codconsulta'];
          $checkmd=$consts->getSelectOpts();
          $opticomd="<select id='sltconsulta'  class='form-control sltconsulta consulta'  >";
          $opticomd.=$checkmd;
          $opticomd.="</select></td>";
          $oocst=str_replace($x_cc, $x_cc." selected",$opticomd);
          $tabla .= $oocst;

       }else{
      

          $idC="-";           
          if($results->data[$i]['Historia']!=="" || $results->data[$i]['Historia']!=='Noasign'){
             $idC=$results->data[$i]['Historia'];
          }
          $tabla .="<td align='center' class='consulta' id=".uniqid()." cod=".$idC."  >".ltrim(rtrim($results->data[$i]['descons']))."</td>";      
    }
      #------------------------------

      #PROXIMA CITA
      $codclien_    = $results->data[$i]['codclien'];
      $codconsulta_ = $results->data[$i]['codconsulta'];
      $coditems_    = $results->data[$i]['coditems'];

   
       $nextApp=$newAppt[$codclien_];   

       $tabla .="<td align='center' class='fecha salio'>".ltrim(rtrim($nextApp))."</td>";
      

       //HORA ENTRADA A CONSULTA
       $horain =$results->data[$i]['horain'];
             $timeLenin= strlen($results->data[$i]['horain']);

              if($timeLenin==1 || $timeLenin==0 ){
                   $horain=""; 
              } else {
                  $appText =$horain;
              }
                             
      if ($horain!='' ) {
        $tabla .="<td align='center'><input type='checkbox' checked  data-toggle='toggle' data-size='small' data-on=".$appText." data-off='En Espera' class='enconsulta'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='enconsulta' ></td>";
      }else{
        $tabla .="<td align='center'><input type='checkbox'         data-toggle='toggle' data-size='small' data-on='En Consulta' data-off='En Espera' class='enconsulta'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='enconsulta' ></td>";
      }
      
          // HORA SALIDA DE CONSULTA
           $horaout =$results->data[$i]['horaout'];
             $timeLen= strlen($results->data[$i]['horaout']);

             if($timeLen==1 || $timeLen==0 ){
                   $horaout=""; 
            } else {
                  $appText =$horaout;
            }
                

      if ($horaout!='' ) {
        $tabla .="<td align='center' class='leave_time' ><input type='checkbox'  checked data-toggle='toggle' data-onstyle='warning' data-size='small' data-on=".$appText." data-off='En Espera' class='salida'  id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
      }else{

             if($horain!='' ){
                $tabla .="<td align='center' class='leave_time' ><input type='checkbox' checked data-toggle='toggle' data-onstyle='warning' data-size='small' data-on='En Consulta' data-off='En Espera' class='salida' id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
             }else{
              $tabla .="<td align='center' class='leave_time' ><input type='checkbox'         data-toggle='toggle' data-onstyle='warning' data-size='small' data-on='En Consulta' data-off='En Espera' class='salida' id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
             }
        
      }
      
      $fecha_cita= str_replace("-","",$results->data[$i]['fecha_cita']);
      $fecha_cita= str_replace(" ","",$fecha_cita);



      //id='confirmado'
      if ($results->data[$i]['coditems'] !==" ") {
        $xcoditems =$results->data[$i]['coditems']; 
      } else {
        $xcoditems = "N/A";
      }
      

      if ($results->data[$i]['ASISTIDOS']=='Asistido') {
        $tabla .="<td align='center'><input type='checkbox' checked  data-toggle='toggle' data-size='small' data-on='Asistido' data-off='No asistido' class='asistido'  id=a-".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='asistido' oid=".$results->data[$i]['id']."  ></td>";
      }else{
        $tabla .="<td align='center'><input type='checkbox'         data-toggle='toggle' data-size='small' data-on='Asistido' data-off='No asistido' class='asistido'  id=a-".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='asistido' oid=".$results->data[$i]['id']."  ></td>";
      }
      
      //PACIENTE LLEGO Y ESTA PRESENTE EN EL CENTRO
      $arrivedTime = $results->data[$i]['llegada'];
      $arrivedTLen = strlen($results->data[$i]['llegada']);
      $presente="P-".$results->data[$i]['llegada'];
      if($arrivedTLen==1 || $arrivedTLen==0 ){
               $tabla .="<td align='center' class='presente'><input type='checkbox'          data-toggle='toggle' data-size='small' data-on='Presente' data-off='Ausente' class='llego'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='llego' ></td>";
            } else {
               $tabla .="<td align='center' class='presente'><input type='checkbox' checked data-toggle='toggle'  data-toggle='tooltip' title='Hooray!'  data-size='small' data-on=".$presente." data-off='Ausente' class='llego'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='llego' ></td>";
      }


            
        #FORMA DE PAGO
        $pagos=null;

       $keypass= trim($results->data[$i]['codclien'].$results->data[$i]['coditems']) ;
       $fac_nota="";
       try {
           $pagos=$arrPagos[$keypass]; 

           if ($pagos!="") {
               $_fac_nota= explode(" ", $pagos)[0];    
               $fac_nota=$arrNotas[$_fac_nota ];
            }         
      
       } catch (Exception $e) { }

      $unique=uniqid();
      if (is_null($results->data[$i]['answered']) || empty($results->data[$i]['answered'])) {
         $tabla .="<td  align='center' class='answered' id=$unique>$pagos<span class='cellcomment' >$fac_nota</span></td>";  
      }else{
         $tabla .="<td  align='center' class='answered nop' id=$unique>$pagos<span class='cellcomment' >$fac_nota</span></td>";
      }
      




      #PRIORIDAD prioridad

      if($results->data[$i]['prioridad']=='1' ){               
               $tabla .="<td align='center'><input type='checkbox' checked data-toggle='toggle' data-onstyle='warning' data-toggle='tooltip' title='Hooray!'  data-size='small' data-on='Prioridad' data-off='Sin Prioridad' class='prioridad'  id=".$results->data[$i]['codclien'].'-p'." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='prioridad' ></td>";
            } else {
              $tabla .="<td align='center'><input type='checkbox'          data-toggle='toggle' data-onstyle='warning' data-size='small' data-on='Prioridad' data-off='Sin Prioridad' class='prioridad'  id=".$results->data[$i]['codclien'].'-p'." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='prioridad' ></td>";
      }

          
      if (isset($_GET['xarrived']) && $_GET['xarrived']!="" ) {
          $turno=$i+1;

          $tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='observacion' >".$turno." ".$results->data[$i]['usuario']."<span class='cellcomment' >". $results->data[$i]['observacion'] ."</span></td>"; 
       } 

          
      $tabla .="</tr>";
    }
  } 
  $tabla .="</tbody>";
  $tabla .="</table>";
  if($totalRegistros>0){
      $page  ="<div class='container' style= 'padding-left: 10px; padding-right: 10px;' >"; 
      $page  ="<div class='row'>";  
      $page .="<div style='float:left; padding-left: 10px; padding-right: 10px;'>";
      $page .=" <button type='button' id='spncitados' class='btn btn-active'>Citados <span  class='badge'>".$citados."</span></button>";
      $page .=" <button type='button' id='btnarrived' class='btn btn-active' data-tip='Pacientes presentes en el centro' >Presentes <span class='badge'>".$arrived."</span></button> ";
      $page .=" <button type='button' id='btnnuev' class='btn btn-active'>Nuevos <span class='badge'>".$nuevos."</span></button> ";
      $page .=" <button type='button' id='btn_atendidos' class='btn btn-active'>Atendidos <span class='badge'>".$atendidos."</span></button> ";
      if ($sinAsistir>0) {
         $page .=" <button type='button' id='btn_sinasistir' class='btn btn-active'>Sin Asistir <span class='badge blink_me'>".$sinAsistir."</span></button> ";
      } 

      $page .="</div>";
  
        $page .="<div style='float:right; padding-left: 10px; padding-right: 10px;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
        $page .="</div>";
        echo $page;
    }
    $respuesta =  $tabla  ;
  echo $respuesta;
}


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
  // Nueva version 2020 01 04

  $sql="SELECT  b.codclien
, a.numfactu
, max(a.coditems) coditems
, 'consulta' tipo
, a.fechafac
, max(b.total) total
, max(c.initials) usuario
, max(e.initials) metodo_pago
, concat(b.codclien, ' ') keypass
, CONCAT(a.numfactu, ' '
, max(c.initials), ' '
, CASE WHEN max(e.initials) IS NULL THEN 'EXO' ELSE max(e.initials) END, ' '
, max(b.total))           disp
, max(b.statfact) statfact
     FROM  cma_DFactura a 
LEFT JOIN  cma_MFactura b ON a.numfactu = b.numfactu 
INNER JOIN loginpass c ON b.usuario = c.login 
LEFT JOIN  Mpagos d ON a.numfactu = d .numfactu AND d .id_centro = 2 
LEFT JOIN  MTipoTargeta e ON d .codtipotargeta = e.codtipotargeta
WHERE        a.coditems NOT LIKE '%ST%' and a.fechafac='$fechafac' and b.statfact=3
GROUP BY a.numfactu, b.codclien, a.fechafac";   
  $pago   = mssqlConn::Listados($sql);
  $result = json_decode($pago, true);
  $pagos  = array();

  $lenO= sizeof($result);
  if ($lenO>0) {
      foreach($result as $fila){
          $status= $fila['statfact'];
          if ($status=='2') {
              $pagos[trim($fila['keypass'])]="ANULADA";
          }else{
              $pagos[trim($fila['keypass'])]=$fila['disp'];
          }
      }
  }
  
 return $pagos;
} 

function get_nota($fechafac){
  $sql="SELECT  a.codclien, a.numfactu, a.nota FROM cma_MFactura a where fechafac='$fechafac' ";
  $nota   = mssqlConn::Listados($sql);
  $result = json_decode($nota, true);
  $notas  = array();
  $lenO= sizeof($result); 
  if ($lenO>0) {
      foreach($result as $fila){
          $status= $fila['statfact'];
          if ($status=='2') {
              $notas[trim($fila['numfactu'])]=$fila['nota'];
          }else{
              $notas[trim($fila['numfactu'])]=$fila['nota'];
          }
      }
  }
 return $notas;
}

function getNewAppoiment($fecha,$condicion){

  $query="Select codclien,Historia, convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita 
  from VIEW_mconsultas_02 
  where  codconsulta <>'07' and 
  ASISTIDO =0   and 
  codclien in (SELECT codclien from VIEW_mconsultas_02 where fecha_cita='$fecha' and  activa='1') /*and Historia='71304' */";
  if ($condicion!=="") {
      $query="Select codclien,Historia, convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita 
        from VIEW_mconsultas_02 
        where codconsulta <>'07' and 
        ASISTIDO =0 and 
        $condicion and
        codclien in (SELECT codclien from VIEW_mconsultas_02 where fecha_cita='$fecha' and  activa='1') /*and Historia='71304' */";
  }
  
  $query="Select codclien,Historia, convert(varchar(10), cast(fecha_cita as date), 101)  fecha_cita 
  from VIEW_mconsultas_02 
  where  codconsulta <>'07' and 
  ASISTIDO =0   and 
  codclien in (SELECT codclien from VIEW_mconsultas_02 where fecha_cita='$fecha' and  activa='1') /*and Historia='71304' */";

  $appmntNext   = mssqlConn::Listados($query);
  $result = json_decode($appmntNext, true);
  $appoinment  = array();
  $lenO= sizeof($result);
   if ($lenO>0) {
      foreach($result as $row){
        $appoinment[$row['codclien']]=$row['fecha_cita'];  
      }

      
   }
 return $appoinment; 
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

class SelectOp 
{
  private $options="";

  function __construct()
  {
    $md = new MedicosController();

    $query="Select codmedico,(nombre+' ' +apellido) as medico from mmedicos where activo='1'";

    $medicos= $md->readUDF($query);

      $opts="";   
      for ($j=0; $j < count($medicos); $j++) { 
        
          $opts .="<option value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
        
      }
      
     $this->options=$opts;

  }

  public function getSelectOpts(){
    return $this->options;
  }


}


class SelectConsultas
{
  private $options="";

  function __construct()
  {
    $cs = new VIEW_SoloConsultaController();

    $consultas= $cs->readUDF("Select codcons,descons from VIEW_SoloConsulta where codcons in ('01','03') ");

      $opts="";   
      for ($j=0; $j < count($consultas); $j++) { 
        
          $opts .="<option value=".$consultas[$j]['codcons'].">".$consultas[$j]['descons']."</option>";
        
      }
      
     $this->options=$opts;

  }

  public function getSelectOpts(){
    return $this->options;
  }


}

/*
codcons
descons

  $query="Select codcons,descons from VIEW_SoloConsulta where codcons in ('01','03') ";
  $listado = mssqlConn::Listados($query);
  $tconsulta = json_decode($listado, true);
  $lenconslt = sizeof($tconsulta);
*/

/*
$se="<select id='md0' class='form-control sltmedico'  ><option value=000> Sin Medico Asociado -  .</option><option value=332>Narciso  Reyes</option><option value=333>Kenneth Cintron</option><option value=334>Ezequiel Lafontaine</option><option value=315>Idamys Herrera</option><option value=317>Victor M. Ocasio</option><option value=319>Edwin Perez</option><option value=320>Leonel Guedes</option><option value=327>Gilberto Caraballo</option><option value=329>Daphne Figueroa</option></select>";
$codmed="317";
echo $se;
      print_r('<script>alert('+$codmed+' )</script>');
echo strpos('option',$se);

*/