<?php 
set_time_limit(0);
require_once "conexion.php";
function mostrarDetail(){

	$mysql = conexionMySQL();
	
	$num_rows = $query->num_rows;

	$sql = "SELECT id FROM sms_out ";
	
	if ($resultado = $mysql->query($sql)) {
		$totalRecords=mysqli_num_rows($resultado);
	
		//echo "siiii";
		if($totalRecords==0)
		{
			$respuesta = "<div class='error'>No existen registros en la base de dato</div>";
	
		}
		else
		{

		/*INICIO DE PAGINACION */
		$regXPag =10;
		$pagina=false;

		if(isset($_GET["p"])){
			$pagina=$_GET["p"];
		}

		if(!$pagina){
			$inicio=0;
			$pagina=1;
		}else{
			$inicio=($pagina - 1)*$regXPag ;	
		}

		$totalPaginas=ceil($totalRecords/$regXPag);
		
		$sql = "SELECT * FROM sms_out ORDER BY sent_dt DESC LIMIT  ".$inicio.",".$regXPag;
		$resultado = $mysql->query($sql);

		$paginacion="<div class='paginacion' id='paginacion'>";
			$paginacion.="<p>";
			$paginacion.="Numero de resultados: <b>$totalRecords</b>.";
			$paginacion.="Mostrado <b>$regXPag</b> resultados por pagina ";
			$paginacion.="pagina <b>$pagina</b> de <b>$totalPaginas</b>.";
			$paginacion.="</p>";
			if ($totalPaginas>1) {
					$paginacion.="<p>";
						$paginacion.=($pagina!=1)?"<a href='?p=".($pagina-1)."'>&laquo</a>":"";
						for ($i=1; $i <=$totalPaginas ; $i++) { 
							$actual ="<span class='actual'>$pagina</span>";
							$enlace="<a href='?p=$i'>$i</a>";
							$paginacion.=($pagina==$i)? $actual:$enlace ;
						}
						$paginacion.=($pagina!=$totalPaginas)?"<a href='?p=".($pagina+1)."'>&raquo</a>":"";
					$paginacion.="</p>";
			}	
		$paginacion.="<div>";

		/*FIN PAGINACION */
		$tabla ="<table id='tabla-sms-det' class='tabla'>";
		$tabla .="<thead>";
			$tabla .="<tr>";
				$tabla .="<th>Id</th>";
				$tabla .="<th>texto</th>";
				$tabla .="<th>Cell phone</th>";
				$tabla .="<th>Enviando</th>";
				$tabla .="<th>Id Net</th>";
				#$tabla .="<th></th>";
				#$tabla .="<th></th>";
			$tabla .="</tr>";
		$tabla .="</thead>";
		$tabla .="<tbody>";
			while ( $row = $resultado->fetch_assoc()) {
				$tabla .="<tr>";
					$tabla .="<td>".$row['id']."</td>";
					$tabla .="<td>".$row['sms_text']."</td>";
					$tabla .="<td>".$row['sender_number']."</td>";
					$tabla .="<td>".$row['sent_dt']."</td>";
					$tabla .="<td>".$row['flow_id']."</td>";
					#$tabla .="<td>Boton Servicio</td>";
					#$tabla .="<td>Boton Consulta</td>";
				$tabla .="</tr>";				
			}
		$tabla .="</tbody>";
		$tabla .="</table>";
		$respuesta = $tabla.$paginacion;
		}

	}
	else{
		//echo "noooo";
		$respuesta = "<div class='error'>Error: no ejecuto la consulta a la base de dato</div>";
	}
$mysql->close();
return printf($respuesta);
}
?>