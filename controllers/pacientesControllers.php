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
	$post_fields = array( 'linexpage','valueToSearch' );        
	$form_data = array();
	        
	foreach ( $post_fields as $key ){
	   if ( isset( $_POST[$key] )){
	        $form_data[$key] = $_POST[$key];
	     }
	}   

	        
	if (!empty( $form_data ) && !isset( $_SESSION['form_data'] )){
	     $_SESSION['form_data'] = serialize( $form_data );
	}else
	{
	    if ( isset( $_POST['linexpage']) || isset( $_POST['valueToSearch']) ){
	        $_SESSION['form_data'] = serialize( $form_data );
	    }
	}
	        
	if ( isset( $_SESSION['form_data'] ) && !empty( $_SESSION['form_data'] ) &&  empty( $form_data ) ){
	    $form_data = unserialize( $_SESSION['form_data'] );           
	             
	    foreach($form_data as $key => $value)
	    {
	        $_POST[ $key] =$value;
	    }
	}

	//to here


function mostrarPacientes(){

{
	$dbmsql = mssqlConn::getConnection();
    //$totalRegistros=count( $results->data );


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
   
    if(isset($_GET['go']))
    {        
        $qryRows = "SELECT COUNT(*) num_rows FROM [farmacias].[dbo].[MClientes] p inner join Mmedicos m ON m.codmedico=p.codmedico" ;
        $query   = "SELECT p.[codclien],p.[Cedula],p.[nombres],p.[direccionH],p.[telfhabit],p.[email] ,p.[Historia]     
		,p.[codmedico],m.nombre+' '+m.apellido as medico
		FROM [farmacias].[dbo].[MClientes] p
		inner join Mmedicos m ON m.codmedico=p.codmedico order by nombres";
    }
    else
    { 
    	if((isset($_POST['valueToSearch']) && $_POST['valueToSearch']!="" ) || isset($_GET['idp']))
    	{
    		$filter=$_POST['valueToSearch'];
    		if ( isset($_GET['idp'])) {
    			$filter = $_GET['idp'];
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

    		$filter=str_replace(" ","%",$filter);
 			$qryRows  = "SELECT COUNT(*) num_rows FROM [farmacias].[dbo].[MClientes] p inner join Mmedicos m ON m.codmedico=p.codmedico where CONCAT( p.[Historia],p.[Cedula],p.[codclien],p.[nombres]) like '%$filter%'  " ;
       		$query="SELECT p.[codclien],p.[Cedula],p.[nombres],p.[direccionH],p.[telfhabit],p.[email] ,p.[Historia] ,p.[codmedico],m.nombre+' '+m.apellido as medico 		FROM [farmacias].[dbo].[MClientes] p left join Mmedicos m ON m.codmedico=p.codmedico   where CONCAT( p.[Historia],p.[Cedula],p.[codclien],p.[nombres]) like '%$filter%'   order by nombres ";    	
    	}
    	else
    	{
    	    $qryRows  = "SELECT COUNT(*) num_rows FROM [farmacias].[dbo].[MClientes] p inner join Mmedicos m ON m.codmedico=p.codmedico " ;
    	    $query="SELECT p.[codclien],p.[Cedula],p.[nombres],p.[direccionH],p.[telfhabit],p.[email] ,p.[Historia]     
    		,p.[codmedico],m.nombre+' '+m.apellido as medico
    		FROM [farmacias].[dbo].[MClientes] p
    		inner join Mmedicos m ON m.codmedico=p.codmedico order by nombres ";
    	}
    }
    $compras = array();
    $queryCount="SELECT codclien,count(*) compras  from mfactura where statfact<>2 group by  codclien";
    $result=$dbmsql->query($queryCount);
    foreach($result as $fila){
		$compras[$fila['codclien']]=$fila['compras'];
	}


    if($page==0)
    {
        $page=1;
    }
  //
    $Paginator  = new Paginator( $dbmsql, $query,$qryRows );
    $tp =$Paginator-> getTotal();
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
			$tabla ="<table id='tabla-smsrpl' class='table table-hover table-condensed table-striped '>";
			$tabla .="<thead>";
				$tabla .="<tr>";
					$tabla .="<th>Pacientes</th>";
					$tabla .="<th>Id</th>";								
					$tabla .="<th>Record</th>";	
					$tabla .="<th>Teléfono</th>";
					$tabla .="<th>Médico</th>";
					$tabla .="<th>Código</th>";		
					$tabla .="<th>Editar</th>";
					$tabla .="<th>Consultar</th>";		
					$tabla .="<th>Citar</th>";			
				$tabla .="</tr>";
			$tabla .="</thead>";
			$tabla .="<tbody>";
			if($totalRegistros>0){
				for ($i=0; $i <$totalRegistros; $i++) { 
					$tabla .="<tr id=".$results->data[$i]['codclien'].">";
					// $results->data[$i]
					// $results->data[$i]['sent_dt']
						$tabla .="<td>".$results->data[$i]['nombres']."</td>"; //telefono
						$tabla .="<td>".$results->data[$i]['Cedula']."</td>"; //Mensaje
						$tabla .="<td>".$results->data[$i]['Historia']."</td>"; //Fecha
                        $tabla .="<td>".$results->data[$i]['telfhabit']."</td>"; //Fecha
                        //$tabla .="<td>".$results->data[$i]['medico']."</td>"; //Fecha
                         #MEDICO
                        $idmed="med-".$results->data[$i]['codclien']."-".$i;
      					$tabla .="<td align='center'><select id=".$idmed."  class='form-control medicos'  >";
      					for ($j=0; $j < $lenmedi; $j++) { 
        					if($medicos[$j]['codmedico']==$results->data[$i]['codmedico']){
          						$tabla .="<option selected value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
        					}else{
          						$tabla .="<option value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
        					}       
      					}
      					$tabla .="</select></td>";
                        $tabla .="<td>".$results->data[$i]['codclien']."</td>"; //Fecha
                        //$tabla .="<td><button type='button' class='btn btn-info editar'>Editar</button></td><td><button type='button' id=".$results->data[$i]['codclien']." class='btn btn-warning btnclcon' data-toggle='modal' data-target='#recordConsModal'>Consultar</button></td>"; //Fecha
                        $tabla .="<td><button type='button' class='btn btn-info editar'>Editar</button></td><td><button type='button' id=".$results->data[$i]['codclien']." class='btn btn-warning btn-block btnclcon' data-toggle='modal' data-target='#recordConsModal'>Consultas <span class='badge'>".$compras[$results->data[$i]['codclien']]."</span></button></td>"; //Fecha                                                                                                          //  data-toggle='modal' data-target='#exampleModal' data-whatever="@getbootstrap">
					    $tabla .="<td><button type='button' id=".$results->data[$i]['codclien']." class='btn btn-success citar' data-toggle='modal' data-target='#appoimentModal'>Citar</button></td>"; //Fecha
					$tabla .="</tr>";
				}
			}			
			$tabla .="</tbody>";
			$tabla .="<tr>";
			  $tabla .="<thead>";
                $tabla .="<th>Pacientes</th>";
                $tabla .="<th>Id</th>";								
			    $tabla .="<th>Record</th>";	
			    $tabla .="<th>Teléfono</th>";
			    $tabla .="<th>Médico</th>";
			    $tabla .="<th>Código</th>";
			    $tabla .="<th>Editar</th>";
				$tabla .="<th>Consultar</th>";	
				$tabla .="<th>Citar</th>";	
			  $tabla .="</thead>";					
			$tabla .="</tr>";
	    $tabla .="</table>";
            if($totalRegistros>0){
	     echo "<div style='float:right;'>".$Paginator->createLinks( $links, 'pagination pagination-sm' )."</div>";
            }
	   // $paginacion = $Paginator->createLinks( $links, 'pagiantion pagination-sm' );
	    $respuesta = $tabla ;
	echo $respuesta;
}


}