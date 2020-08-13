<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
ini_set('memory_limit', '1024M');
require_once '../../db/mssqlconn.php';
require_once '../../clases/paginator.class.php';
// require_once '../../controllers/MedicosController.php';
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
	$post_fields = array( 'fecha','valueToSearch','confirmar','xconfirm','xasist','xnuevos','xcontrol','sltconsultas','sltconsultashd' ,'xarrived','page' );          
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

	    if ( isset( $_GET['fecha']) || isset( $_GET['valueToSearch']) || isset( $_GET['confirmar'])  || isset( $_GET['xconfirm']) || isset( $_GET['xasist'])  || isset( $_GET['xnuevos']) || isset( $_GET['xcontrol'])  || isset( $_GET['sltconsultas'] ) || isset( $_GET['sltconsultashd'] )   || isset( $_GET['xarrived'])  ){
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
  
  $camposConsulta = "cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), '', '-') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder,codconfirm,horain,horaout,id,llegada,terapias";

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
	 $presente     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'    and llegada <>''  " ;
	 $qcitados     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'   " ;
	 $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%' and confirmado is not null" ;
	 $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'   and ASISTIDOS ='Asistido' ";
     $qnuevos      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";
     $qcontrol     = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'   " ;
     $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[nombres]) like '%$filter%'   ";
  }else{
     $qryRows      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
     $query        = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' "; 
     
     $qnuevos      = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and primera_control='1' and ASISTIDOS = 'Asistido' ";
     $qcontrol     = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     
     $presente     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1'  and llegada <>''  " ;

     $qcitados     = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
	 $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and confirmado is not null" ;
	 $qasistidos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and ASISTIDOS ='Asistido' ";  

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
  	 $opcion=$_GET['sltconsultas'];
  	 if($opcion==2){
  	 	$qcitados     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$qconfirmados .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$qasistidos   .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 
  	 	$qnuevos      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))"; 

  	 	$qryRows      .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";
  	 	$query        .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";

  	 	$presente     .=" and ( codconsulta is null or codconsulta  in ('01','02','03','04','05','06'))";

  	 }else if($opcion==3){
  	 	$qcitados     .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 
  	 	$qnuevos      .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 

  	 	$qryRows      .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$query        .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";

  	 	$presente     .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 }else if ($opcion==4) {
  	 	$qryRows      .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$query        .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";

  	 	$qnuevos      .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qcitados     .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";

  	 	$presente     .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 }else if ($opcion==5) {
  	 	$qryRows      .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";
  	 	$query        .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";

  	 	$qnuevos      .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";
  	 	$qcitados     .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";

  	 	$presente     .=" and (   coditems like 'LI0%' and   codconsulta  in ('07') )";
  	 }else if ($opcion==6) {
  	 	$qryRows      .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$query        .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";

  	 	$qnuevos      .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qcitados     .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 	$qasistidos   .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";

  	 	$presente     .=" and (   coditems like 'BL%' and   codconsulta  in ('07') )";
  	 }
  	
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
    $control     =displayInfo($qcontrol,$dbmsql); 

    $arrived     =displayInfo($presente,$dbmsql); 
    
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
    //
 	//$tabla ="<table id='tbl-ctrl' class='table table-bordered table-hover table-condensed table-striped'>";	
	$tabla ="<table id='tbl-ctrl' class='table table-inverse table-condensed '>";	
	$tabla .="<thead class='thead-inverse'>";
	$tabla .="<tr>";
	$tabla .="<th class='titleatn' >Id</th>";
	$tabla .="<th class='titleatn' >Pacientes</th>";									
	$tabla .="<th class='titleatn' >Confirmado</th>";
	$tabla .="<th class='titleatn' >Asistido</th>";
	if ($opcion==4 || $opcion==1) { //LASER o TODOS	
		$tabla .="<th class='titleatn' ># Terapias</th>";	
	}
	
	
		
	
	$tabla .="<th class='titleatn' >Consulta</th>";
	if ($opcion==2 || $opcion==1) {
	$tabla .="<th class='titleatn' >Próx Cita</th>";	
	$tabla .="<th class='titleatn' >Presente</th>";
	$tabla .="<th class='titleatn' >En consulta</th>";
	$tabla .="<th class='titleatn' >Hora Salida</th>";
	$tabla .="<th class='titleatn' >Forma de pago</th>";
	}
	$tabla .="<th class='titleatn' >Médico</th>";
	$tabla .="<th class='titleatn' >Record</th>";		
					
	$tabla .="</tr>";
	$tabla .="</thead>";
	$tabla .="<tbody>";
	if($totalRegistros>0){
		for ($i=0; $i <$totalRegistros; $i++) { 
			$tabla .="<tr id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems'].">";

			$idC=$results->data[$i]['codclien'];

			$tabla .="<td class='cdcliente' cod=".$idC.">".$results->data[$i]['cedula']."</td>"; 
			
			$tabla .="<td field='nombres'id="."name-".$results->data[$i]['codclien']." class='edit' >".$results->data[$i]['nombres']."</td>";   
			$fecha_cita= str_replace("-","",$results->data[$i]['fecha_cita']);
			$fecha_cita= str_replace(" ","",$fecha_cita);
			#$tabla .="<td>".$fecha_cita."</td>";
			#$tabla .="<td>".$results->data[$i]['telfhabit']."</td>";
			#$tabla .="<td>".$results->data[$i]['CITADOS']."</td>";

			if($results->data[$i]['codclien']=='83513'){
							// echo "";                                                        
                        }
                        $cxo=$results->data[$i]['codconfirm'];
			if ($results->data[$i]['codconfirm']=='2') {				                                                                                                                            
				$tabla .="<td  align='center' field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox' checked disabled  data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado'  data-onstyle='success' class='confirmado'  id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." name='confirmado' disabled></td>";
			}else{
				$tabla .="<td  align='center' field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox'  disabled data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado' class='confirmado' id=".$results->data[$i]['codclien']."  campo=".$results->data[$i]['codconsulta']." name='confirmado'  disabled></td>";
			}
			//id='confirmado'
			if ($results->data[$i]['coditems'] !==" ") {
				$xcoditems =$results->data[$i]['coditems']; 
			} else {
				$xcoditems = "N/A";
			}
			

			if ($results->data[$i]['ASISTIDOS']=='Asistido') {
				$tabla .="<td align='center'><input type='checkbox' checked  data-toggle='toggle' data-size='small' data-on='Asistido' data-off='No asistido' class='asistido'  id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='asistido' oid=".$results->data[$i]['id']."  ></td>";
			}else{
				$tabla .="<td align='center'><input type='checkbox'         data-toggle='toggle' data-size='small' data-on='Asistido' data-off='No asistido' class='asistido'  id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='asistido' oid=".$results->data[$i]['id']."  ></td>";
			}
			
   
			//NUMERO DE TERAPIAS APLICADAS
			if ($opcion==4 || $opcion=="1"){
				
			
			if ($results->data[$i]['codclien']=='93878') {
				 
			}
			if ($results->data[$i]['codconsulta']=='07') {
				$len_t=73;
        		if (substr($results->data[$i]['coditems'], 0, 2)=="TD") {
        			$tabla .="<td  width='130' align='center'><select class='form-control terapias' style='filter: invert(30%);' >";
        			$teraph=$results->data[$i]['terapias'];
                    if(is_null($teraph)){
                        $teraph=0;
                    }
        			for ($t=0; $t < $len_t ; $t++) { 
        				if ($teraph==$t) {
        					$tabla .="<option  value=$t selected>$t</option>";
        				}else{        				                                        
        				    $tabla .="<option  value=$t >$t</option>";
        				}
        			}
				$tabla .="</select></td>";
        		}else{
        			$tabla .="<td align='center' >N/A</td>";
        		}
        	}else{
        		$tabla .="<td align='center' >N/A</td>";
        	}
        	}			
			//

			$idC="-";        		//CONSULTAS
			if($results->data[$i]['Historia']!=="" || $results->data[$i]['Historia']!=='Noasign'){
				$idC=$results->data[$i]['Historia'];
	        }else{
	        	//$idC=$results->data[$i]['codclien'];
	        }   
			$tabla .="<td align='center' class='consulta' id=".uniqid()." cod=".$idC."  >".ltrim(rtrim($results->data[$i]['descons']))."</td>";
			


			//$tabla .="<td>".$results->data[$i]['ASISTIDOS']."</td>";
//$tabla .="<td align='center'>".ltrim(rtrim($results->data[$i]['descons']))."</td>";
if ($opcion==2 || $opcion==1) {
			//PROXIMA CITA
			$codclien_    = $results->data[$i]['codclien'];
			$codconsulta_ = $results->data[$i]['codconsulta'];
			$coditems_    = $results->data[$i]['coditems'];

			 $nextApp= getNextAppoiment($codclien_,$codconsulta_,$coditems_);
			 
			 
                         $tabla .="<td align='center'>".ltrim(rtrim($nextApp[0]['fecha_cita']))."</td>";
			//$tabla .="<td>".ltrim(rtrim($results->data[$i]['descons']))."</td>";


			#$tabla .="<td class='edit'>".$results->data[$i]['observacion']."</td>";
			//$tabla .="<td>".$results->data[$i]['Medico']."</td>";

			//PACIENTE LLEGO Y ESTA PRESENTE EN EL CENTRO
    		$arrivedTime = $results->data[$i]['llegada'];
    		$arrivedTLen = strlen($results->data[$i]['llegada']);

    		if($arrivedTLen==1 || $arrivedTLen==0 ){
               $tabla .="<td align='center'><input type='checkbox'          data-toggle='toggle' data-size='small' data-on='Presente' data-off='Ausente' class='llego'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='llego' ></td>";
            } else {
            	$tabla .="<td align='center'><input type='checkbox' checked data-toggle='toggle'  data-toggle='tooltip' title='Hooray!'  data-size='small' data-on='Presente' data-off='Ausente' class='llego'  id=".$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='llego' ></td>";
			}


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
				$tabla .="<td align='center'><input type='checkbox'  checked data-toggle='toggle' data-onstyle='warning' data-size='small' data-on=".$appText." data-off='En Espera' class='salida'  id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
			}else{

             if($horain!='' ){
                $tabla .="<td align='center'><input type='checkbox' checked data-toggle='toggle' data-onstyle='warning' data-size='small' data-on='En Consulta' data-off='En Espera' class='salida' id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
             }else{
             	$tabla .="<td align='center'><input type='checkbox'         data-toggle='toggle' data-onstyle='warning' data-size='small' data-on='En Consulta' data-off='En Espera' class='salida' id=".'s'.$results->data[$i]['codclien'].'-'.$results->data[$i]['id']." campo=".$results->data[$i]['codconsulta']." producto=".$xcoditems." name='salida' ></td>";
             }
				
			}
			
		    // FORMA DE PAGO
		    $pagos=null;
		    if ($results->data[$i]['codconsulta']=='07') {		    				
		    	$dispPago = getPago($results->data[$i]['codclien'],$results->data[$i]['fecha_cita'],$results->data[$i]['coditems']);		    
		    }else{
				$dispPago = getPago($results->data[$i]['codclien'],$results->data[$i]['fecha_cita'],'');
		    }	    
		    
            if($dispPago!=null){
                $pagos=$dispPago[0]['disp']; 
            }
			$tabla .="<td align='center' >$pagos</td>";

}
			// $tabla .="<td align='center'><select id='sltmedico'  class='form-control sltmedico'  >";
			
			      #MEDICO
      $tabla .="<td align='center' class='td_medico' >";
      $medi= $results->data[$i]['codmedico'];
      $xx= $md->getSelectOpts();

      $optico="<select id='md$i' class='form-control sltmedico'  >";
      $optico.=$xx;
      $optico.="</select></td>";
     
      // $uu=strrpos($optico, $medi);
      $oo=str_replace($medi, $medi." selected",$optico);
      $tabla .=$oo;

 
			// for ($j=0; $j < $lenmedi; $j++) { 
			// 	if($medicos[$j]['codmedico']==$results->data[$i]['codmedico']){
			// 		$tabla .="<option selected value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
			// 	}else{
			// 		$tabla .="<option value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
			// 	}				
			// }
			// $tabla .="</select></td>";
	        //$tabla .="<td  contenteditable='false'  id=".$results->data[$i]['codclien'].'-'.$i." class='edit'>".$results->data[$i]['Historia']."</td>"; 
			$tabla .="<td  contenteditable='false'  field='record' id=".$results->data[$i]['codclien'].'-'.$i." class='edit'>".$results->data[$i]['Historia']."</td>"; 
	        // contenteditable='true'                      
	        //$tabla .="<td display:none;>".$results->data[$i]['codclien']."</td>"; 
	        if($results->data[$i]['Historia']!==""){
				$idx=$results->data[$i]['Historia'];
	        }else{
	        	$idx=$results->data[$i]['cedula'];
	        }
	        
	        //$tabla .="<td><button type='button' id=".$idx." class='btn btn-info btn-xs btn-block citar'>Citar</button></td>";
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
			$page .=" <button type='button' id='confirmado' class='btn btn-active'>Confirmados <span class='badge'>".$confirmados."</span></button>";
			$page .=" <button type='button' id='btnasis' class='btn btn-danger'>Asistidos <span id='spnasistidos' class='badge'>".$asistidos."</span></button> ";
			$page .=" <button type='button' id='btnnuev' class='btn btn-active'>Nuevos <span class='badge'>".$nuevos."</span></button> ";
			$page .=" <button type='button' id='btnctrl' class='btn btn-active' data-tip='Solo Consultas, no inluye servicios' >Control <span class='badge'>".$control."</span></button> ";

			$page .=" <button type='button' id='btnarrived' class='btn btn-active' data-tip='Pacientes presentes en el centro' >Presentes <span class='badge'>".$arrived."</span></button> ";

			// $page .=" <div  style='opacity: 1; float:right;    width: 102px; height: 82px;'class='clearfix col-sm-2'  > ";
		    // $page .=" <div  id='piechart' style='opacity: 1; vertical-align: top; position:absolute; width: 180px; height: 100px;'></div> ";
			// $page .="</div>";
			$page .="</div>";
	
	    	$page .="<div style='float:right; padding-left: 10px; padding-right: 10px;'>". $Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
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

function getPago($codclien,$fechafac,$coditems){
	if($coditems==""){
		//CONSULTAS
		$query="select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials) disp "
		. "from cma_MFactura a "
		. "inner join cma_DFactura b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
		. "inner join loginpass c on a.usuario = c.login " 
		. "inner join Mpagos d on a.numfactu=d.numfactu and d.id_centro=2 "
		. "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
		. "where codclien='$codclien' and a.fechafac='$fechafac' ";
	}elseif ( substr($coditems,0,2) !="TD") {
		//SUERO
		$query="select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials) disp "
		. "from cma_MFactura a "
		. "inner join cma_DFactura b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
		. "inner join loginpass c on a.usuario = c.login " 
		. "inner join Mpagos d on a.numfactu=d.numfactu and d.id_centro=2 "
		. "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
		. "where codclien='$codclien' and a.fechafac='$fechafac' and coditems='$coditems' ";
	}else{
		//LASER
		$query="Select a.numfactu,a.total,c.initials usuario,e.initials metodo_pago, CONCAT(a.numfactu,' ',c.initials, ' ',e.initials) disp "
		. "from MSSMFact a "
		. "inner join MSSDFact b on  a.numfactu=b.numfactu and a.fechafac=b.fechafac "
		. "inner join loginpass c on a.usuario = c.login "
		. "inner join Mpagos d on a.numfactu=d.numfactu  "
		. "inner join MTipoTargeta e on d.codtipotargeta=e.codtipotargeta "
		. "where a.fechafac='$fechafac'  and coditems='$coditems' ";

	}
    $res =null;
    $pago = mssqlConn::Listados($query);
    $res  = json_decode($pago, true);
    return $res;
}

function getNextAppoiment($codclien,$codconsulta,$coditems){
	if ($codconsulta=='07') {

        if (substr($coditems, 0, 2)=="TD") {
        	//-- Laser
	    	$query="select convert(varchar(10), cast(fecha_cita as date), 111)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta ='07' and coditems  like '%TD' and ASISTIDOS is null	";
        }else{
        	//-- Suero
        	$query="select convert(varchar(10), cast(fecha_cita as date), 111)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta ='07' and coditems not like '%TD' and ASISTIDOS is null ";
        }	    

	}else
	//-- Consultas
	 $query="select convert(varchar(10), cast(fecha_cita as date), 111)  fecha_cita from VIEW_mconsultas_02 where codclien='$codclien'and codconsulta <>'07' and ASISTIDOS is null";
	 
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