<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$type   = $_POST['type'];
$sd     = $_POST['sd'];
$ed     = $_POST['ed'];
$rpt    = $_POST['rpt'];
$pr     = $_POST['pro'];

$sd = str_replace('/','-',$sd);
$ed = str_replace('/','-',$ed);

#FACTURAS X MEDICO
$query ="SELECT codmedico, max(nombremedico) medico, SUM(cantidad * precunit - Descuento) AS facturas_x_medico "
    ."FROM  VIEW_Ventas_Medicos "
    ."WHERE  (fechafac  between '$sd' and '$ed') AND (statfact <> '2') AND (Prod_serv = 'P')  and codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
    ."GROUP BY codmedico ";



$result1 = mssqlConn::Listados($query);
$obj1 = json_decode($result1 , true);
$lenobj1 = sizeof($obj1);


#UNDADES  x MEDICO
$query ="SELECT  a.codmedico ,sum(b.cantidad) unidades_medicos "
    ."FROM VentasDiarias  a "
	."inner join DFactura b On  b.numfactu = a.numfactu "
	."inner join MInventario c On  b.coditems = c.coditems "
	."WHERE   (a.fechafac  between '$sd' and '$ed')  and (a.statfact <> '2') AND (a.doc = '01') and a.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) and  c.cod_subgrupo='1'	"
	."group by  a.codmedico ";



$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['unidades_medicos']=$value['unidades_medicos'];
 			unset($obj2[$key]);
 			break;
		}
	}		

}


#UNIDADES POR PACIENTES
$query ="SELECT codmedico, SUM(cantidad) AS cantUni ,  SUM(cantidad)/(SELECT COUNT(t.codclien) AS numeroPacientes "
    ."FROM  dbo.VIEW_Asistidos_0215 t "
	."WHERE  (t.fecha_cita BETWEEN '$sd' and '$ed') AND (t.asistido = '3 ') and s.codmedico=t.codmedico "
	."GROUP BY codmedico)   unidades_pacientes "
	."FROM  dbo.VIEW_Stat_Enl_0215  s "
	."WHERE   (fechafac BETWEEN '$sd' and '$ed') AND (cod_subgrupo = '1') and codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
	."GROUP BY codmedico ";

$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['unidades_pacientes']=$value['unidades_pacientes'];
 			unset($obj2[$key]);
 			break;
		}
	}

}


#FORMULAS X PACIENTES
$query ="SELECT codmedico,  SUM(cantidad)/(SELECT COUNT(t.codclien) AS formula_paciente "
 ."FROM  dbo.VIEW_Asistidos_0215 t "
."WHERE  (t.fecha_cita BETWEEN  '$sd' and '$ed') AND (t.asistido = '3') and s.codmedico=t.codmedico "
."GROUP BY codmedico)   formula_paciente "
."FROM  dbo.VIEW_Stat_Enl_0215  s "
."WHERE  (fechafac BETWEEN  '$sd' and '$ed') AND (cod_subgrupo = '2') and codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
."GROUP BY codmedico ";


$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['formula_paciente']=$value['formula_paciente'];
 			unset($obj2[$key]);
 			break;
		}
	}

}


#NUMERO DE PACIENTES VISTOS
$query ="SELECT b.codmedico, COUNT(b.codclien) AS numeroPacientes "
    ."From VIEW_Asistidos_0215  b "
    ."WHERE  (b.fecha_cita  between '$sd' and '$ed') AND (b.asistido = '3') and b.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
	."GROUP BY b.codmedico ";

$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['numeroPacientes']=$value['numeroPacientes'];
 			unset($obj2[$key]);
 			break;
		}
	}	
}


#FACTURACION POR PACIENTE
   $query ="SELECT a.codmedico, SUM(a.cantidad * a.precunit - a.Descuento) /(SELECT COUNT(b.codclien) "
    ."From VIEW_Asistidos_0215  b "
    ."WHERE  (b.fecha_cita BETWEEN '$sd' and '$ed') AND (b.asistido = '3') and b.codmedico =a.codmedico  and b.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
	."GROUP BY b.codmedico) facturacion_paciente "
    ."From VIEW_Ventas_Medicos a "
    ."WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (a.statfact <> '2') AND (a.Prod_serv = 'P') and a.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
    ."GROUP BY a.codmedico ";


$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 


	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['facturacion_paciente']=$value['facturacion_paciente'];
 			unset($obj2[$key]);
 			break;
		}
	}
	
}  


#FORMULA POR MEDICO
$query ="SELECT a.codmedico,sum(a.cantidad) formula_medicos "
    ."FROM VIEW_Stat_FINAL_0215 a "
    ."where (a.fechafac  between '$sd' and '$ed') and a.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' )  AND cod_subgrupo=2 "
    ."group by a.codmedico,a.numeroPacientes ";


$query ="SELECT a.codmedico,sum(b.cantidad) formula_medicos "
    ."FROM VentasDiarias  a "
	."inner join DFactura b On  b.numfactu = a.numfactu "
	."inner join MInventario c On  b.coditems = c.coditems "
	."WHERE   (a.fechafac  between  '$sd' and '$ed')  and (a.statfact <> '2') AND (a.doc = '01') and a.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) and  c.cod_subgrupo='2' "
	."group by  a.codmedico ";


$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['formula_medicos']=$value['formula_medicos'];
 			unset($obj2[$key]);
 			break;
		}
	}

}


#% FORMULAS & PACIENTES //  FORMAT(, 'N', 'en-us')

 $query ="SELECT f.codmedico,sum(f.cantidad)/ (SELECT sum(b.cantidad) formulas_unidades "
    ."FROM VentasDiarias  a "
	."inner join DFactura b On  b.numfactu = a.numfactu "
	."inner join MInventario c On  b.coditems = c.coditems "
	."WHERE   (a.fechafac  between  '$sd' and '$ed')  and (a.statfact <> '2') AND (a.doc = '01') and a.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) and  c.cod_subgrupo='1' and a.codmedico =f.codmedico  "
	."group by  a.codmedico  )  formulas_unidades "
    ."FROM VIEW_Stat_FINAL_0215 f "
    ."where (f.fechafac  between '$sd' and '$ed') and f.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' )  AND cod_subgrupo=2 "
    ."group by f.codmedico,f.numeroPacientes ";

    $query ="SELECT  f.codmedico,sum(b.cantidad) / (SELECT sum(b.cantidad) "
    ."FROM VentasDiarias  a "
	."inner join DFactura b On  b.numfactu = a.numfactu "
	."inner join MInventario c On  b.coditems = c.coditems "
	."WHERE   (a.fechafac  between   '$sd' and '$ed')  and (a.statfact <> '2') AND (a.doc = '01') and  c.cod_subgrupo='1'  and a.codmedico =  f.codmedico "
	."group by  a.codmedico ) formulas_unidades "
    ."FROM VentasDiarias  f "
	."inner join DFactura b On  b.numfactu = f.numfactu "
	."inner join MInventario c On  b.coditems = c.coditems "
	."WHERE   (f.fechafac  between   '$sd' and '$ed')  and (f.statfact <> '2') AND (f.doc = '01') and f.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) and  c.cod_subgrupo='2' "
	."group by  f.codmedico ";

$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);

for ($i=0; $i <$lenobj1 ; $i++) { 

	foreach ($obj2  as $key => $value) {
		if ($value['codmedico']===$obj1[$i]['codmedico']) {
			$obj1[$i]['formulas_unidades']=$value['formulas_unidades'];
 			unset($obj2[$key]);
 			break;
		}
	}

}



#VENTAS PRODUCTOS MAS SERVICOS

	 $query ="SELECT c.codmedico,( SUM(c.cantidad * c.precunit - c.Descuento)  + "	
		."(SELECT  SUM(l.cantidad * l.precunit - l.Descuento) total_laser From VIEW_Ventas_Medicos_Laser l "
    	."WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (l.statfact <> '2') AND (l.Prod_serv = 'M') and l.codmedico =c.codmedico and   l.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
    	."GROUP BY l.codmedico )  + "
		."(SELECT  SUM(b.cantidad * b.precunit - b.Descuento) total_suero  From VIEW_Ventas_Medicos_Suero b "
    	."WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (b.statfact <> '2') AND (b.Prod_serv = 'S') and b.codmedico = c.codmedico  and b.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
    	."GROUP BY b.codmedico )  ) "
		."/ (SELECT COUNT(d.codclien) "
    	."From VIEW_Asistidos_0215  d "
    	."WHERE  (d.fecha_cita BETWEEN '$sd' and '$ed') AND (d.asistido = '3') and d.codmedico =c.codmedico  and d.codmedico in( select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
		."GROUP BY d.codmedico) total_ventas "	 	 
		."From VIEW_Ventas_Medicos c "
    	."WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (c.statfact <> '2') AND (c.Prod_serv = 'P') and c.codmedico in( select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) "
    	."GROUP BY c.codmedico ";



    	$query ="SELECT c.codmedico,( ISNULL(SUM(c.cantidad * c.precunit - c.Descuento),0)  + 
		ISNULL((SELECT  SUM(l.cantidad * l.precunit - l.Descuento) total_laser From VIEW_Ventas_Medicos_Laser l 
    	WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (l.statfact <> '2') AND (l.Prod_serv = 'M') and l.codmedico =c.codmedico and   l.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
    	GROUP BY l.codmedico ),0)  + 
		ISNULL((SELECT  SUM(b.cantidad * b.precunit - b.Descuento) total_suero  From VIEW_Ventas_Medicos_Suero b 
    	WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (b.statfact <> '2') AND (b.Prod_serv = 'S') and b.codmedico = c.codmedico  and b.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
    	GROUP BY b.codmedico ),0)  ) 
		/ ISNULL((SELECT COUNT(d.codclien) 
    	From VIEW_Asistidos_0215  d 
    	WHERE  (d.fecha_cita BETWEEN '$sd' and '$ed') AND (d.asistido = '3') and d.codmedico =c.codmedico  and d.codmedico in(   select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
		GROUP BY d.codmedico),( ISNULL(SUM(c.cantidad * c.precunit - c.Descuento),0)  + 
		ISNULL((SELECT  SUM(l.cantidad * l.precunit - l.Descuento) total_laser From VIEW_Ventas_Medicos_Laser l 
    	WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (l.statfact <> '2') AND (l.Prod_serv = 'M') and l.codmedico =c.codmedico and   l.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
    	GROUP BY l.codmedico ),0)  + 
		ISNULL((SELECT  SUM(b.cantidad * b.precunit - b.Descuento) total_suero  From VIEW_Ventas_Medicos_Suero b 
    	WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (b.statfact <> '2') AND (b.Prod_serv = 'S') and b.codmedico = c.codmedico  and b.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
    	GROUP BY b.codmedico ),0)  ) ) total_ventas 
		From VIEW_Ventas_Medicos c 
    	WHERE  (fechafac BETWEEN '$sd' and '$ed') AND (c.statfact <> '2') AND (c.Prod_serv = 'P') and c.codmedico in(  select Codmedico from Mmedicos where activo=1  and Codmedico<>'000' ) 
    	GROUP BY c.codmedico ";


$result2 = mssqlConn::Listados($query);
$obj2 = json_decode($result2 , true);
$lenobj2 = sizeof($obj2);
for ($i=0; $i <$lenobj1 ; $i++) { 
	foreach ($obj2  as $key => $value) {
			if ($value['codmedico']===$obj1[$i]['codmedico']) {
				$obj1[$i]['ventas_prodserv']=$value['total_ventas'];
	 			unset($obj2[$key]);
	 			break;
			}
		}
}

echo stripslashes(json_encode($obj1)); 


function isChar($val){
  if (is_numeric($val)) {
    return $val + 0;
  }
  return 0;
} 