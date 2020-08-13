<?php 
// inclued('config.php');
 require_once "config.php";



 function conexionMySQL(){
 	$conexion = new mysqli(MYSQL_HOST,MYSQL_USER,MYSQL_PASS,MYSQL_DBNAME);

 	if($conexion->connect_error){
 		// $error="<div class='error'>";
 		// 	$error .="Erros de conexion # <b>".$conexion->connect_errno."</b> Mensaje del errror: <mark>".$conexion->connect_error."</mark>";
 		// $error .="</div>";
 		// die(error);


 		$error="<div class='error'> Error de conexion # <b>%s</b> Mensaje del errror: <mark>%s</mark></div>";
 		printf($error,$conexion->connect_errno,$conexion->connect_error);

 	}
 	else{
 		//$formato="<div class='mensaje'>conexion exitosa : <b>".$conexion->host_info."</div>";
 		//$formato="<div class='mensaje'>conexion exitosa : <b>%s</b></div>";
 		//printf($formato,$conexion->host_info);

 	}
 	$conexion->query("SET CHARACTER SET UTF8");
 	return $conexion;

 }
 //conexionMySQL();
?>