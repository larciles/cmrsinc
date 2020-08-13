<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
ini_set('memory_limit', '1024M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");    
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['controlcita'];
    $codperfil2=$_SESSION['codperfil'];
}

$colour =  $_SESSION['bgc'];  
?>
body {
    background:<?php echo $colour ?>;
}

.personalizado{
	 background:<?php echo $colour ?>;
}