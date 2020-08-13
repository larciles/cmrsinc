<?php require "vistas.php"; ?>
<!DOCTYPE>
<html lang="es">
<html>
<head>
	<meta charset="utf-8"/>
	<title></title>
	<meta name="description" content="crud"/>
	<link rel="stylesheet" type="text/css" href="css/estilos.css"/>
</head>
<body>
<header id="cabecera">
	<h1>Net</h1>
	<div><img src="img/images.png" alt="Net"></div>
	<a href="?p=1" id="insertar">Insertar</a>
</header>
<section id="contenido">
	<div id="respuesta"></div>
	<div id="precargar"></div>
	<?php mostrarDetail(); ?>
</section>	
<script src="js/script.js"></script>
</body>
</html>