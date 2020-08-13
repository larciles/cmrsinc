<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $workstation=$_SESSION['workstation'];
    $ipaddress=$_SESSION['ipaddress'];
    $access=$_SESSION['access'];
    $codperfil=$_SESSION['codperfil'];

    $prninvoice=$_SESSION['prninvoice'];
    $autoposprn=$_SESSION['autoposprn'];
    $pathprn=$_SESSION['pathprn'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];

require_once '../../models/user_model.inc.php';
require('../../controllers/MedicosController.php');


$medcontroller = new MedicosController();
 
    if ($_POST['activo']=="1") {
        $activo="0";
    }else{
        $activo="1";
    }

    $set_data = array(
        'activo' => $activo
    );

    $where_data = array(
          'Id' =>   $_POST['Id']
    );

    $array_edit = array(
        'data'  => $set_data,                    
        'where' => $where_data

    );

    $medcontroller->update($array_edit);

    $template = '
        <div class="container">
            <p class="item  add">Actualizando ... <b>%s</b> salvado</p>
        </div>
        <script>
            window.onload = function () {
                window.location.href = "index.php";
            }
        </script>
    ';

    printf($template, $_POST['last_name']);
