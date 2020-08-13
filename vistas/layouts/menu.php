<?php
  error_reporting(E_ERROR | E_WARNING | E_PARSE); 
  $actualDir=getcwd();

  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url2 = ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
  $url3 = $_SERVER["REQUEST_URI"];

  if("/vav/main.php"==$url3){
     require_once('models/user_model.inc.php');
  }else if("/vav/vistas/pedidos/"==$url3){
     require_once('../../models/user_model.inc.php');
  }

 require_once dirname(__FILE__)."/../../db/config.php";
 $curserver=SERVER_HOST; 

 session_start();
 if(!isset($_SESSION['username'])){
    header("Location:vistas/login/login.php");
 }else{
    $user=$_SESSION['username'];
    $ip=$_SERVER['HTTP_CLIENT_IP'];
 }
?>

<ul>

  <li><a href="#home">Home</a></li>
  <li><a href="#news">News</a></li>
  <li><a href="#contact">Contact</a></li>
  <li  id="user" style="float:right"><a class="active" href="#about"><?php echo $user;?></a></li>  

</ul>