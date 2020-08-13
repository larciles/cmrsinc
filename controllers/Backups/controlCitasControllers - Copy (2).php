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
	$post_fields = array( 'fecha','valueToSearch','confirmar','xconfirm','xasist','xnuevos','xcontrol' );          
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

	    if ( isset( $_GET['fecha']) || isset( $_GET['valueToSearch']) || isset( $_GET['confirmar'])  || isset( $_GET['xconfirm']) || isset( $_GET['xasist'])  || isset( $_GET['xnuevos']) || isset( $_GET['xcontrol'])  ){
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
 $dbmsql = mssqlConn::getConnection();

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

  $fielSet="fecha";
  if(isset($_GET['confirmar'])){
  	$fielSet="fecha_cita";
  }
  date_default_timezone_set("America/Puerto_Rico");
  $fecha=date("Y-m-d");
  

 // $fech a=date("Y-m-d")-1;	
  if (isset($_GET['fecha']) && $_GET['fecha']!="") {
  	 $fecha=$_GET['fecha'];
  } 
  
  $camposConsulta = "cedula,nombres,REPLACE(CONVERT(CHAR(15), fecha_cita, 101), ' ', ' - ') AS fecha_cita, REPLACE(CONVERT(varchar(5), hora, 109), '', '') AS hora,telfhabit,CITADOS,CONFIRMADO,ASISTIDOS,NO_ASISTIO,descons,observacion,Medico,codclien,fecha,codmedico,codconsulta,citacontrol,activa,usuario,primera_control,nocitados,Historia,exonerado,coditems, fecha_cita as FOrder,codconfirm";



  if (isset($_GET['valueToSearch']) && $_GET['valueToSearch']!="") {

    $filter=$_GET['valueToSearch'];
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
	 $qcitados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'   " ; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
	 $qconfirmados = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%' and confirmado is not null" ; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
	 $qasistidos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  and ASISTIDOS ='Asistido' "; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%' 
     $qnuevos = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  and primera_control='1' and ASISTIDOS = 'Asistido' ";  //and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
     $qcontrol = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     $qryRows  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  " ;  //and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
     $query="select ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' and  CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$filter%'  "; // and CONCAT( [Historia],[Cedula],[codclien],[nombres],[telfhabit],descons,Medico) like '%$invertFilter%'
  }else{
     $qryRows  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
     $query = "SELECT ".$camposConsulta." from VIEW_mconsultas_02 where $fielSet='$fecha' and activa='1' "; 
     
     $qnuevos   = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' and primera_control='1' and ASISTIDOS = 'Asistido' ";
     $qcontrol = " SELECT COUNT(*) num_rows from mconsultas where primera_control='0' and fecha_cita='$fecha' and  activa='1' and asistido='3' ";
     $qcitados  = "SELECT COUNT(*) num_rows FROM VIEW_mconsultas_02  where $fielSet='$fecha' and activa='1' " ;
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
  	 	$qcitados .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$qasistidos .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 
  	 	$qnuevos .=" and (   coditems like '%ST' and   codconsulta  in ('07') )"; 

  	 	$qryRows .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 	$query .=" and (   coditems like '%ST' and   codconsulta  in ('07') )";
  	 }else if ($opcion==4) {
  	 	$qryRows .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$query .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";

  	 	$qnuevos .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qcitados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qconfirmados .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )";
  	 	$qasistidos .=" and (   coditems like 'TD%' and   codconsulta  in ('07') )"; 
  	 }
  	
  }

   $query .=" order by nombres";


    $citados=displayInfo($qcitados,$dbmsql);
    $confirmados =displayInfo($qconfirmados,$dbmsql);
    $asistidos =displayInfo($qasistidos,$dbmsql);
    $nuevos =displayInfo($qnuevos,$dbmsql); 
    $control =displayInfo($qcontrol,$dbmsql); 
    
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
	    $npo=sizeof($result->data);
	    $tr = count($result->data);
	    $totalRegistros=$results->datalen;
 	}else
 	{
 		$totalRegistros=0;
 	} 

 	$tabla ="<table id='tbl-ctrl' class='table table-bordered table-hover table-condensed table-striped'>";	
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
	$tabla .="<th class='titulos' >Médico</th>";
	$tabla .="<th class='titulos' >Record</th>";		
	$tabla .="<th class='titulos' >Acción</th>";					
	$tabla .="</tr>";
	$tabla .="</thead>";
	$tabla .="<tbody>";
	if($totalRegistros>0){
		for ($i=0; $i <$totalRegistros; $i++) { 
			$tabla .="<tr id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." producto=".$results->data[$i]['coditems'].">";
			$tabla .="<td>".$results->data[$i]['cedula']."</td>"; 
			$tabla .="<td>".$results->data[$i]['nombres']."</td>";  
			$fecha_cita= str_replace("-","",$results->data[$i]['fecha_cita']);
			$fecha_cita= str_replace(" ","",$fecha_cita);
			$tabla .="<td>".$fecha_cita."</td>";
			$tabla .="<td>".$results->data[$i]['telfhabit']."</td>";
			#$tabla .="<td>".$results->data[$i]['CITADOS']."</td>";

			if($results->data[$i]['codclien']=='83513'){
							// echo "";                                                        
                        }
                        $cxo=$results->data[$i]['codconfirm'];
			if ($results->data[$i]['codconfirm']=='2') {				                                                                                                                            
				$tabla .="<td  field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox' checked data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado' class='confirmado'  id=".$results->data[$i]['codclien']." campo=".$results->data[$i]['codconsulta']." name='confirmado'></td>";
			}else{
				$tabla .="<td  field=".$results->data[$i]['codclien']."%".$results->data[$i]['codconsulta']." campo=".$results->data[$i]['codclien']."><input type='checkbox'  data-toggle='toggle 'data-size='small' data-on='Confirmado' data-off='No confirmado' class='confirmado' id=".$results->data[$i]['codclien']."  campo=".$results->data[$i]['codconsulta']." name='confirmado'></td>";
			}
			//id='confirmado'
			if ($results->data[$i]['ASISTIDOS']=='Asistido') {				
				$tabla .="<td><button type='button' disabled class='btn btn-success btn-sm btn-block'>Asistido</button></td>";
			} else {
				$tabla .="<td><button type='button' disabled class='btn btn-warning btn-sm btn-block' >Sin asistir</button></td>";
			}
			//$tabla .="<td>".$results->data[$i]['ASISTIDOS']."</td>";
			$tabla .="<td>".$results->data[$i]['descons']."</td>";
			$tabla .="<td class='edit'>".$results->data[$i]['observacion']."</td>";
			$tabla .="<td>".$results->data[$i]['Medico']."</td>";
			// $tabla .="<td><select  class='selectpicker' data-style='btn-primary' >";
			// for ($j=0; $j < $lenmedi; $j++) { 
			// 	if($medicos[$j]['codmedico']==$results->data[$i]['codmedico']){
			// 		$tabla .="<option selected value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
			// 	}else{
			// 		$tabla .="<option value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
			// 	}				
			// }
			// $tabla .="</select></td>";
	        $tabla .="<td>".$results->data[$i]['Historia']."</td>";                        
	        //$tabla .="<td display:none;>".$results->data[$i]['codclien']."</td>"; 
	        if($results->data[$i]['Historia']!==""){
				$idx=$results->data[$i]['Historia'];
	        }else{
	        	$idx=$results->data[$i]['cedula'];
	        }
	        
	        $tabla .="<td><button type='button' id=".$idx." class='btn btn-info btn-xs btn-block citar'>Citar</button></td>";
			$tabla .="</tr>";
		}
	}	
	$tabla .="</tbody>";
	$tabla .="</table>";
	if($totalRegistros>0){
		    $page  ="<div class='container' style= 'padding-left: 10px; padding-right: 10px;' >";	
		    $page  ="<div class='row'>";	
			$page .="<div style='float:left; padding-left: 10px; padding-right: 10px;'>";
			$page .=" <button type='button' id='spncitados' class='btn btn-primary'>Citados <span class='badge'>".$citados."</span></button>";
			$page .=" <button type='button' id='spnconfirm' class='btn btn-success'>Confirmados <span class='badge'>".$confirmados."</span></button>";
			$page .=" <button type='button' id='btnasis' class='btn btn-primary'>Asistidos <span class='badge'>".$asistidos."</span></button> ";
			$page .=" <button type='button' id='btnnuev' class='btn btn-primary'>Nuevos <span class='badge'>".$nuevos."</span></button> ";
			$page .=" <button type='button' id='btnctrl' class='btn btn-primary'  data-toggle='tooltip' title='Solo consultas'  >Control <span class='badge'>".$control."</span></button> ";
			$page .=" <div id='barchart_material' style='width: 375px; height: 65px; float:right;' ></div>";
			$page .="</div>";
	//$page .="<div><button type='button' class='btn btn-primary btn-xs' data-toggle='modal' data-target='#consultasrecord' >Historial de consulas</button></div>";
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