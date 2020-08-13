<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
ini_set('memory_limit', '1024M');
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';
require_once '../../db/mysqlconn.php';
require_once '../../db/mysqlcmaconn.php';
require_once '../../clases/MedicosClass.php';

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
	$post_fields = array( 'fecha','valueToSearch','confirmar','xconfirm','xasist','xnuevos','xcontrol','sltconsultas','sltconsultashd','character','fecha_noasis_1','fecha_noasis_2' );          
	$form_data = array();
	        
	foreach ( $post_fields as $key ){
	   if ( isset( $_GET[$key] )){
	        $form_data[$key] = $_GET[$key];
	     }
	}   
    
    $m1= $_GET['fecha_noasis_2'];
	        
	if (!empty( $form_data ) && !isset( $_SESSION['form_data'] )){
	     $_SESSION['form_data'] = serialize( $form_data );
	}else
	{

	  if ( isset( $_GET['fecha']) || isset( $_GET['valueToSearch']) || isset( $_GET['confirmar'])  || isset( $_GET['xconfirm']) || isset( $_GET['xasist'])  || isset( $_GET['xnuevos']) || isset( $_GET['xcontrol'])  || isset( $_GET['sltconsultas'] ) || isset( $_GET['sltconsultashd'] ) || isset( $_GET['character'] ) || isset( $_GET['fecha_noasis_1'] ) || isset( $_GET['fecha_noasis_2'] )  ){
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

	//to here

function mostrarCitas(){
#PARA HABLILITAR O NO LA CONEXION MYSQL EN CASO DE PROBLEMAS CON EL WWWW SERVER
# 0= DESHABILITADO 1= HABILITADO
 $use_MYSQL_con =ENDI_MYSQL_CONN;
 $class_btn_disabled="";
 #
 #



 $dbmsql = mssqlConn::getConnection();
 $md= new MedicosClass();
 $md_list=  $md->getSelectOpts();

 $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 25;
 $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
 $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

 if(isset($_POST['linexpage']) && $_POST['linexpage']!=""){
	$linexpage=$_POST['linexpage'];
	if($linexpage>200)
	{
		$limit =200;
	}
	$limit =$linexpage;
 }

  $query="select codconsulta,descons,codcons,coditems from VIEW_ConsultaServicios ";
  $listado = mssqlConn::Listados($query);
  $tconsulta = json_decode($listado, true);
  $lenconslt = sizeof($tconsulta);

  $query = "Select 'Todos' as Descripcion, 1 as id  union  select 'Consultas' as Descripcion, 2 as id  union  select 'Sueroterapia' as Descripcion, 3 as id  union  select 'Laser' as Descripcion, 4 as id  order by id";
  $listado = mssqlConn::Listados($query);
  $sltcons = json_decode($listado, true);

  $fielSet="fecha";
  if(isset($_GET['confirmar'])){
  	$fielSet="fecha_cita";
  }
  date_default_timezone_set("America/Puerto_Rico");
  $fecha=date("Y-m-d");
  

 // $fech a=date("Y-m-d")-1;	
  if (isset($_GET['fecha']) && $_GET['fecha']!="") {
  	 $valDate=explode("/",$_GET['fecha']);
     $month=$valDate[0];
     $day=$valDate[1];
     $year=$valDate[2];
     if (strlen($year)==4) {
      	$fecha=$_GET['fecha'];
     }   
  } 
  
  $camposConsulta = "cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), ' ', ' - ') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder,codconfirm,mls,hilt";



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
	 $qcitados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'   " ; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
	 $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%' and confirmado is not null" ; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
	 $qasistidos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  and ASISTIDOS ='Asistido' "; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%' 
     $qnuevos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";  //and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
     $qcontrol = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     $qryRows  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  " ;  //and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
     $query="SELECT ".$camposConsulta." from VIEW_mconsultas_02 where inactivo<>'1' and  $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  "; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'

  }elseif (isset($_GET['character']) && $_GET['character']!="") {
  	    $character =$_GET['character'];
  		$qcitados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  nombres like '$character%'   " ;
	 	$qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  nombres like '$character%' and confirmado is not null" ; 
	 	$qasistidos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  nombres like '$character%'  and ASISTIDOS ='Asistido' ";
     	$qnuevos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  nombres like '$character%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";
     	$qcontrol = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     	$qryRows  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and  nombres like '$character%'  " ; 
     	$query="SELECT ".$camposConsulta." from VIEW_mconsultas_02 where inactivo<>'1' and  $fielSet='$fecha' and activa='1' and  nombres like '$character%'  ";
  }else{
     $qryRows  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' " ;
     $query = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where inactivo<>'1' and  $fielSet='$fecha' and activa='1' "; 
     
     $qnuevos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and primera_control='1' and ASISTIDOS = 'Asistido' ";
     $qcontrol = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     $qcitados  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' " ;
	 $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and confirmado is not null" ;
	 $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where inactivo<>'1' and $fielSet='$fecha' and activa='1' and ASISTIDOS ='Asistido' ";  

  }

 if (isset($_GET['xconfirm']) && $_GET['xconfirm']!="" ) {
  	 $qryRows .="  and confirmado is not null ";
  	 $query .=" and confirmado is not null ";
  }


  if (isset($_GET['xasist']) && $_GET['xasist']!="" ) {
  	 $qryRows .="   and ASISTIDOS ='Asistido'  ";
  	 $query .="  and ASISTIDOS ='Asistido'  ";
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
  	 $opcion=$_GET['sltconsultas'];
  	 if($opcion==2){
  	 	$qcitados.=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$qconfirmados .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$qasistidos .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 
  	 	$qnuevos .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 

  	 	$qryRows .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$query .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 }else if($opcion==3){
  	 	$qcitados .=" and (   coditems like '%ST' and codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like '%ST' and codconsulta  in ('07') )";
  	 	$qasistidos .=" and (   coditems like '%ST' and codconsulta  in ('07') )"; 
  	 	$qnuevos .=" and (   coditems like '%ST' and codconsulta  in ('07') )"; 

  	 	$qryRows .=" and (   coditems like '%ST' and codconsulta  in ('07') )";
  	 	$query .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 }else if ($opcion==4) {
  	 	$qryRows .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$query .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";

  	 	$qnuevos .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qcitados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qasistidos .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )"; 
  	 }else if ($opcion==5) {
  	 	$qryRows      .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$query        .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$qnuevos      .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$qcitados     .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$qasistidos   .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 	$presente     .=" and (  descons='INTRAVENOSO' and  codconsulta  in ('07') )";
  	 }else if ($opcion==6) {
  	 	$qryRows      .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$query        .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";

  	 	$qnuevos      .=" and ( coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qcitados     .=" and ( coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and ( coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and ( coditems like 'BL%' and   codconsulta  in ('07') )";

  	 	$presente     .=" and ( coditems like 'BL%' and   codconsulta  in ('07') )";
  	 }else if ($opcion==7) {
  	 	$qryRows      .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";
  	 	$query        .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";

  	 	$qnuevos      .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";
  	 	$qcitados     .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";

  	 	$presente     .=" and ( descons='CEL MADRE' and   codconsulta  in ('07') )";
  	 }
  	
  }

   $query .=" order by nombres";


    $user=$_SESSION['username'];
    $citados=displayInfo($qcitados,$dbmsql); //GENERAL
    $citadosXusr = displayInfo($qcitados.' and usuario='."'".$user."'",$dbmsql); // USUARIO

    $citados=$citados.'/'.$citadosXusr;

    $confirmados =displayInfo($qconfirmados,$dbmsql); //GENERAL  // USUARIO
    $confirmadosXusr =displayInfo($qconfirmados.' and usuario='."'".$user."'",$dbmsql); // USUARIO

    $confirmados=$confirmados.'/'.$confirmadosXusr;

    $asistidos =displayInfo($qasistidos,$dbmsql); //GENERAL  // USUARIO
    $asistidosXusr =displayInfo($qasistidos.' and usuario='."'".$user."'",$dbmsql); // USUARIO

    $asistidos=$asistidos.'/'.$asistidosXusr;


    $nuevos =displayInfo($qnuevos,$dbmsql);  //GENERAL  // USUARIO
    $nuevosXusr =displayInfo($qnuevos.' and usuario='."'".$user."'",$dbmsql);  // USUARIO

    $nuevos=$nuevos.'/'.$nuevosXusr;


    $control =displayInfo($qcontrol,$dbmsql);  //GENERAL  // USUARIO
    $controlXusr =displayInfo($qcontrol.' and usuario='."'".$user."'",$dbmsql);  // USUARIO

    $control = $control.'/'.$controlXusr;
    
    if($page==0)
    {
        $page=1;
    }
  //
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
	    // $npo=sizeof($result->data);
	    // $tr = count($result->data);
	    $totalRegistros=$results->datalen;
 	}else
 	{
 		$totalRegistros=0;
 	} 
  if ($use_MYSQL_con==1) {
      $dbconns = MysqlCmaConn::getConnection_s();
      $dbconn  = MysqlConn::getConnection_my();
  }else{
     $class_btn_disabled="btn-disabled";
  }
  
  $display_btn_sms="display: none;";
  if (sms_ON_OFF=="1") {
      $display_btn_sms="display: block;";
  
    
  
  $sms_deatails=0;
try {
		$simplecomma='"';
		$doublecomma="'";
		$rpl_qry= str_replace("COUNT(*) num_rows","distinct concat( '$simplecomma', '1', replace( Cedula,'-','') , '$simplecomma') tel ",$qryRows);


		$tel_result=$dbmsql->query($rpl_qry);
		foreach($tel_result as $row){
		    $tel_lista[] = $row[0];
		}    

    if (is_array($tel_lista)) {
        $qry_tel_list=implode(",",$tel_lista); 
        $qry_tel_list_f=str_replace('"',"'",$qry_tel_list);
        $sms_array=array();    
    }
		
} catch (Exception $e) {
	
}


 if ( $sms_deatails==0) {


  if ($use_MYSQL_con==0) {
      $qry_tel_list_f="";
  }
         
  try {
	  	if ($qry_tel_list_f!="") {


            #BUSCA LOS MENSAJES ENVIADOS  
           $sms_array=getSentMessages($qry_tel_list_f,$dbconns);


            #BUSCA LOS MENSAJES RECIBIDOS

      			$sms_query = "SELECT sms_from, count(*) n_sms from fr_replies WHERE sms_from in ($qry_tel_list_f) group by sms_from ";

      			$sms_res = MysqlConn::Listados($sms_query);
      			$_obj = json_decode($sms_res, true);
      			if (is_array($_obj)) {
      				for ($i=0; $i < count($_obj); $i++) { 
      					$sms_from=$_obj[$i]['sms_from'];
      					$n_sms=$_obj[$i]['n_sms'];

                $tmp=$sms_array;
                $sms_pos=$tmp[$sms_from];
                if (is_array($sms_pos) ) {
                    if (count($sms_pos)>0) {                 
                        # SUMA LAS CANTIDADE DE SMS ENVIADOS Y RECIBIDOS
                        $n_sms= (int) $tmp[$sms_from]['cantidad']['cantidad'] + (int) $_obj[$i]['n_sms'];                        
                    }
                }

      					$tmp_array = array(          
      					     'cantidad' => $n_sms
      					);

      				 $sms_array[$sms_from]['cantidad']=$tmp_array;
      				}
      			}
	  	}
  } catch (Exception $e) {
 
  }


 
 }elseif( $sms_deatails==1){

  $sms_query = "SELECT *, date(sms_received) as received from fr_replies WHERE sms_from in ($qry_tel_list_f) order by sms_received desc ";

   $sms_res = MysqlConn::Listados($sms_query);
   $_obj = json_decode($sms_res, true);

   if (is_array($_obj)) {
      
       for ($i=0; $i < count($_obj); $i++) { 
         $sms_from=$_obj[$i]['sms_from'];
         $sms_received=$_obj[$i]['sms_received'];
         $sms_read=$_obj[$i]['sms_read'];
         $sms_body=$_obj[$i]['sms_body'];
         $sms_to=$_obj[$i]['sms_to'];
         $id=$_obj[$i]['id'];
         $received=$_obj[$i]['received'];
        
         $tmp_array = array(
          'sms_body' => $sms_body , 
          'sms_received' => $sms_received,
          'sms_read' => $sms_read,
          'id' => $id
         );
         $sms_array[$sms_from][$received]=$tmp_array;

       }
   }
   

 }
}
  // echo "<pre>";
  // print_r( $sms_array);
  // echo "</pre>";

 	//$tabla ="<table id='tbl-ctrl' class='table table-bordered table-hover table-condensed table-striped'>";	
 	$tabla ="<table id='tbl-ctrl' class='table table-inverse '>";	
	$tabla .="<thead>";
	$tabla .="<tr class='headtr'>";
	$tabla .="<th class='titulos' >Id</th>";
	$tabla .="<th class='titulos' >Pacientes</th>";								
	$tabla .="<th class='titulos' >Fecha</th>";	
	$tabla .="<th class='titulos' >Teléfono</th>";
	#$tabla .="<th class='titulos' >Citado</th>";
	$tabla .="<th class='titulos' >Confirmado</th>";
	$tabla .="<th class='titulos' >Asistido</th>";
	$tabla .="<th class='titulos' >Consulta</th>";
	$tabla .="<th class='titulos' >Observaciones</th>";
	$tabla .="<th class='titulos' >Terapias</th>";
	$tabla .="<th class='titulos' >Médico</th>";
	$tabla .="<th class='titulos' >Record</th>";		
	$tabla .="<th class='titulos' colspan='2' >Acción</th>";					
	$tabla .="</tr>";
	$tabla .="</thead>";
	$tabla .="<tbody>";
	if($totalRegistros>0){
		for ($i=0; $i <$totalRegistros; $i++) { 


			$tabla .="<tr id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems'].">";

      try {
         $to=str_replace("-", "", $results->data[$i]['cedula'] ); 
         $to="1".$to;
         $sms_cant="";
          $sms_pos=$sms_array[$to];
          $display="none;";
          if (is_array($sms_pos) ) {
             if (count($sms_pos)>0) {
                $display="inline;";
                $sms_cant=$sms_pos['cantidad']['cantidad'] ;
               
             } 
          }
    
      } catch (Exception $e) {
               
      }
     


    

			$tabla .="<td>".$results->data[$i]['cedula']."</td>"; 
			$tabla .="<td class'pac-name'>".$results->data[$i]['nombres']."</td>";  
			$fecha_cita= str_replace("-","",$results->data[$i]['fecha_cita']);
			$fecha_cita= str_replace(" ","",$fecha_cita);
			$tabla .="<td align='center' >".$fecha_cita."</td>";
			$tabla .="<td>".$results->data[$i]['telfhabit']."</td>";

      $cxo=$results->data[$i]['codconfirm'];
			if ($results->data[$i]['codconfirm']=='2') {				                                                                                                                            
				$tabla .="<td  align='center' field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox' checked data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado' class='confirmado'  id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." name='confirmado'></td>";
			}else{
				$tabla .="<td align='center' field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox'  data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado' class='confirmado' id=".$results->data[$i]['codclien']."  campo=".$results->data[$i]['codconsulta']." name='confirmado'></td>";
			}
			

			//ASISTIDO
			if ($results->data[$i]['ASISTIDOS']=='Asistido') {				
				$tabla .="<td align='center'><button type='button' align='center' disabled class='btn btn-success btn-sm btn-block'>Asistido</button></td>";
			} else {
				$tabla .="<td align='center' ><button type='button' disabled class='btn btn-warning btn-sm btn-block' >Sin asistir</button></td>";
			}
			

			//TIPO DE CONSULTA
			$tabla .="<td align='center' >".$results->data[$i]['descons']."</td>";

			//OBSERVACIONES
			$tabla .="<td class='edit'>".$results->data[$i]['observacion']."</td>";

			//TERAPIAS
			if ($results->data[$i]['codconsulta']=='07') {
				if (substr($results->data[$i]['coditems'], 0, 2)=="TD") {
					$tabla .="<td class='edit'> MLS : ".$results->data[$i]['mls']."  /  HILT : ".$results->data[$i]['hilt']."</td>";
				}else{
					$tabla .="<td align='center' >N/A</td>";	
				}
			}else{
				$tabla .="<td align='center' >N/A</td>";
			}

			//MEDICO
			

			$idmed="med-".$results->data[$i]['codclien']."-".$i;

      $tabla .="<td align='center' class='td_medico' >";
      $medi= $results->data[$i]['codmedico'];
      $xx= $md_list;//$md->getSelectOpts();

      $optico="<select id='$idmed' class='medicos form-control'  >";
      $optico.=$xx;
      $optico.="</select></td>";
     
      // $uu=strrpos($optico, $medi);
      $oo=str_replace($medi, $medi." selected",$optico);
      $tabla .=$oo;


			//RECORD
	        $tabla .="<td style='text-align: center;'>".$results->data[$i]['Historia']."</td>";                        
	        //$tabla .="<td display:none;>".$results->data[$i]['codclien']."</td>"; 
	        if($results->data[$i]['Historia']!==""){
				$idx=$results->data[$i]['Historia'];
	        }else{
	        	$idx=$results->data[$i]['cedula'];
	        }
	        
	        $tabla .="
              <td align='center' colspan='2'>
              <div class='acciones' style='display:inline'>
                <button type='button' style='width: 33%; float: left;' id=".$idx." class='btn btn-info btn-xs btn-block citar tool-tip'><i class='fas fa-calendar-check fa-2x ' title='Citar'></i>
                	<span class='tool-tip-text'>Citar</span>
                </button>

                <button type='button' style='width: 33%;margin-left: 1px; margin-top: 0px;float: left;' id="."pin".$idx." class='btn btn-success btn-xs btn-block pin  $class_btn_disabled'>                 
                  <span class='mapa'><i class='fas fa-map-marked-alt fa-2x'></i></span> 
                </button>

                <button type='button' data-toggle='modal' data-target='.bd-sms-modal' style='width: 33%;margin-left: 1px; margin-top: 0px; float: left; $display_btn_sms' id="."msg".$idx." class='btn btn-default btn-xs btn-block sms  $class_btn_disabled'>
                
                  <span class='sms-msg'>
                  <i class='fas fa-sms fa-2x'></i>
                   <span class='badge badge-danger navbar-badge' style='display:$display' >$sms_cant</span>
                  </span> 
                </button>


               </div> 
              </td>";
			$tabla .="</tr>";
		}
	}	
	$tabla .="</tbody>";
	$tabla .="</table>";
	if($totalRegistros>0){
		  $page  ="<div class='container' style= 'padding-left: 10px; padding-right: 10px;' >";	
		  $page  ="<div class='row'>";	
			$page .="<div style='float:left; padding-left: 10px; padding-right: 10px;'>";
			$page .=" <button type='button' id='spncitados' class='btn btn-primary'>Citados<span class='badge'>".$citados."</span></button>";
			$page .=" <button type='button' id='spnconfirm' class='btn btn-success'>Confir...<span class='badge'>".$confirmados."</span></button>";
			$page .=" <button type='button' id='btnasis' class='btn btn-primary'>Asis...<span class='badge'>".$asistidos."</span></button> ";
			$page .=" <button type='button' id='btnnuev' class='btn btn-primary'>Nuevos<span class='badge'>".$nuevos."</span></button> ";
			$page .=" <button type='button' id='btnctrl' class='btn btn-default'  data-toggle='tooltip' title='Solo consultas'  >Control<span class='badge'>".$control."</span></button> ";
			$page .=" <div id='barchart_material' style='width: 375px; height: 65px; float:right;' ></div>";
			$page .="</div>";
	//$page .="<div><button type='button' class='btn btn-primary btn-xs' data-toggle='modal' data-target='#consultasrecord' >Historial de consulas</button></div>";
	    	$page .="<div style='float:right; padding-left: 10px; padding-right: 10px;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
               //http://www.webslesson.info/2016/10/how-to-make-alphabetic-pagination-in-php-with-mysql.html
	    	    $character = range('A', 'Z');  
                $page .=  "<ul class='pagination ccpager'>";  
                foreach($character as $alphabet)  
                       {  
                         //$page .=  " <li><a href=' control.php?character=".$alphabet." '>$alphabet</a></li>";  
                         $page .=  " <li class='alpha' id=".$alphabet."><a>$alphabet</a></li>";  
                       }  
                $page .= "</ul>";

	    	$page .="</div>";
	     //echo "<div style='float:right;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
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


function getSentMessages($qry_tel_list_f,$dbmsql){
      // $sms_query = "SELECT sender_number, count(*) n_sms from sms_out WHERE sms_from in ($qry_tel_list_f) group by sms_from ";
      $sms_query = " SELECT telefono AS sms_from  , count(*) n_sms from (
                    SELECT  
                    CASE  substring(sender_number,1,1) WHEN  '1' THEN     
                      sender_number ELSE 
                      concat('1',sender_number)
                    END as telefono
                    , ts
                    , sender_number
                    , length(sender_number) lon                    
                    from sms_out
                    order by sender_number
                  ) as t1 Where telefono  in($qry_tel_list_f)  group by  telefono";



      $sms_res = MysqlCmaConn::Listados($sms_query);
      $_obj = json_decode($sms_res, true);
      if (is_array($_obj)) {
        for ($i=0; $i < count($_obj); $i++) { 
          $sms_from=$_obj[$i]['sms_from'];
          $n_sms=$_obj[$i]['n_sms'];

          $tmp_array = array(          
          'cantidad' => $n_sms
          );
            $sms_array[$sms_from]['cantidad']=$tmp_array;
        }
      }

 return $sms_array;     
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
/*
select  
  CASE  substring(sender_number,1,1) WHEN  '1' THEN     
    sender_number ELSE 
    concat('1',sender_number)
  END as telefono
  , ts
  , sender_number
  , length(sender_number) lon  
  
  from sms_out 

  order by sender_number



) as t1 Where telefono  in('13058739609') 
*/