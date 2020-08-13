<?php
require_once 'paginacion.php';
$paginas = new Pagina();

$limit =25;

$dbTable="mconsultas";

$field=" a.fecha_cita, b.Historia,b.nombres,b.telfhabit ";
$where="";






if ( isset( $_POST['totalPage'] ) || isset( $_GET['totalPage'] ) ) {
   $grupo =$_POST['grupo']; 

   if (  isset($_POST['fecha']) && $_POST['fecha']!=""   ) {
      
       $fecha=$_POST['fecha'];
       $query  = "SELECT COUNT(*) total FROM mconsultas a
       inner join MClientes b ON  a.codclien= b.codclien
       inner join Mmedicos c ON  a.codmedico= c.Codmedico
       where 
       fecha_cita='$fecha' and   
       codconsulta='01'  and 
       asistido=3 and
       a.codclien not in (Select codclien from MFactura where fechafac='$fecha') and
       a.codclien not in (Select codclien from MSSMFact where fechafac='$fecha')  and
       a.codclien not in (
       select codclien  from cma_MFactura where  fechafac='$fecha' and  numfactu  in  (Select numfactu from cma_DFactura where  cod_subgrupo='SUEROTERAPIA'  ))";

   } else {
       $fecha =date("Y-m-d");
       $query  = "SELECT COUNT(*) total FROM mconsultas a
       inner join MClientes b ON  a.codclien= b.codclien
       inner join Mmedicos c ON  a.codmedico= c.Codmedico
       where 
       fecha_cita='$fecha' and   
       codconsulta='01'  and 
       asistido=3 and
       a.codclien not in (Select codclien from MFactura where fechafac='$fecha') and
       a.codclien not in (Select codclien from MSSMFact where fechafac='$fecha')  and
       a.codclien not in (
       select codclien  from cma_MFactura where  fechafac='$fecha' and  numfactu  in  (Select numfactu from cma_DFactura where  cod_subgrupo='SUEROTERAPIA'  ))";

   }

    $res=$paginas->getTotalPages($query,$limit );

	echo $res;
}

if (isset( $_POST['page'] ) || isset( $_GET['page'] )) {
    
    if (isset($_GET["page"])) { 
      	$page  = $_GET["page"]; 
    } else { 
    	  $page=1; 
    };  

    $grupo =$_GET['grupo'];

	   if (isset($_GET['fecha']) && $_GET['fecha']!="") {

       $fecha=$_GET['fecha'];
       $query = " Select  convert(varchar(10), cast(a.fecha_cita as date), 101) fecha_cita, b.Historia,b.nombres,b.telfhabit, CONCAT(c.nombre, ' ',c.apellido) medico,a.usuario  from mconsultas a
       inner join MClientes b ON  a.codclien= b.codclien
       inner join Mmedicos c ON  a.codmedico= c.Codmedico
       where 
       fecha_cita='$fecha' and   
       codconsulta='01'  and 
       asistido=3 and
       a.codclien not in (Select codclien from MFactura where fechafac='$fecha') and
       a.codclien not in (Select codclien from MSSMFact where fechafac='$fecha')  and
       a.codclien not in (
       select codclien  from cma_MFactura where  fechafac='$fecha' and  numfactu  in  (Select numfactu from cma_DFactura where  cod_subgrupo='SUEROTERAPIA'  ))
       order by b.nombres";
   }else {
       $fecha =date("Y-m-d");
       $query = " Select  convert(varchar(10), cast(a.fecha_cita as date), 101) fecha_cita, b.Historia,b.nombres,b.telfhabit, CONCAT(c.nombre, ' ',c.apellido) medico,a.usuario  from mconsultas a
       inner join MClientes b ON  a.codclien= b.codclien
       inner join Mmedicos c ON  a.codmedico= c.Codmedico
       where 
       fecha_cita='$fecha' and   
       codconsulta='01'  and 
       asistido=3 and
       a.codclien not in (Select codclien from MFactura where fechafac='$fecha') and
       a.codclien not in (Select codclien from MSSMFact where fechafac='$fecha')  and
       a.codclien not in (
       select codclien  from cma_MFactura where  fechafac='$fecha' and  numfactu  in  (Select numfactu from cma_DFactura where  cod_subgrupo='SUEROTERAPIA'  ))
       order by b.nombres";

   }

	$res=$paginas->getNewPage($query,$limit,$page);
	echo $res;	
}