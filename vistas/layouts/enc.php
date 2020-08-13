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

session_start();
if(!isset($_SESSION['username'])){
    header("Location:vistas/login/login.php");
}else{
    $user=$_SESSION['username'];
    $ip=$_SERVER['HTTP_CLIENT_IP'];
}
?>
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="../../main.php">CMA v2.0</a>                
            </div>

            <ul class="nav navbar-nav">    
                <li class="dropdown">
                   <a href="#" style="font-size: 150%;"  id="xtitulo"></a> 
                </li>
            </ul>
          
            <ul class="nav navbar-top-links navbar-right">

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <?php echo $user ;?> <i class="fa fa-caret-down"></i>
                    </a>
                        <input  id="loggedusr" type="hidden"  name="loggedusr" value=<?php echo $user ; ?>>
                    <ul class="dropdown-menu dropdown-user">
                        <!-- <li ><a href="#" ><i class="fa fa-user fa-fw"></i> <?php echo $user ;?></a></li> -->
                        <li class="divider"></li>
                        <li><a href="../../index.php?op=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>

                </li>
            </ul>

        </nav>



    </div>