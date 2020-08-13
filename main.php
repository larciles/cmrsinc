<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once('models/user_model.inc.php');
session_start();
if(!isset($_SESSION['username'])){
	header("Location:vistas/login/login.php");
}else{
	$user=$_SESSION['username'];

    if (!empty($_SERVER["HTTP_CLIENT_IP"]))
{
 //check for ip from share internet
 $ip = $_SERVER["HTTP_CLIENT_IP"];
}
elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
{
 // Check for the Proxy User
 $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
}
else
{
 $ip = $_SERVER["REMOTE_ADDR"];
}
}
?>
<!DOCTYPE html>
<html>
<head>
   

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <link rel='icon' href='favicon.ico' type='image/x-icon'/ >
    <title>CMA WEB</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="css/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/dist/css/sb-admin-2.css" rel="stylesheet">
    <!-- Morris Charts CSS -->
    <link href="css/vendor/morrisjs/morris.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="css/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  
    <!-- <link rel="shortcut icon" href="../favicon.ico">  -->
    <link rel="stylesheet" type="text/css" href="css/demo.css" />
    <link rel="stylesheet" type="text/css" href="css/style1.css" />
    <style type="text/css">
        body{
            overflow-y: hidden;
        }
    </style>
   

</head>
<body>

    <?php include ("vistas/layouts/header.php") ?>
    <!-- start body -->
    <!--  e·qua·nim·i·ty -->
    
            <ul class="cb-slideshow">
             <li><span>Image 01</span><div><h3>re·lax·a·tion</h3></div></li>
            <li><span>Image 02</span><div><h3>qui·e·tude</h3></div></li>
            <li><span>Image 03</span><div><h3>bal·ance</h3></div></li>
            <li><span>Image 04</span><div><h3>qua.li.ty of life</h3></div></li>
            <li><span>Image 05</span><div><h3>tech.no.lo.gic</h3></div></li>
            <li><span>Image 06</span><div><h3>se·ren·i·ty</h3></div></li>  
        </ul>
        
        <div class="container">
            <!-- Codrops top bar -->

      
        </div>
    <!-- ends body -->
    <!-- footer -->
    
    <script src="css/vendor/jquery/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="css/vendor/bootstrap/js/bootstrap.min.js"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="css/vendor/metisMenu/metisMenu.min.js"></script>
    <!-- Morris Charts JavaScript -->
    <script src="css/vendor/raphael/raphael.min.js"></script>
    <script src="css/vendor/morrisjs/morris.min.js"></script>
    <script src="js/data/morris-data.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="css/dist/js/sb-admin-2.js"></script>

    <script type="text/javascript" src="js/modernizr.custom.86080.js"></script>
</body>
</html>