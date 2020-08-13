<?php
// header("Content-Type: text/html;charset=utf-8");
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
// set_time_limit(0);

// session_start();

// if(!isset($_SESSION['username'])){
//     header("Location:../login/login.php");
//     return;
// }else{
//     $user=$_SESSION['username'];
//     $workstation=$_SESSION['workstation'];
//     $ipaddress=$_SESSION['ipaddress'];
//     $access=$_SESSION['access'];
//     $codperfil=$_SESSION['codperfil'];

//     $prninvoice=$_SESSION['prninvoice'];
//     $autoposprn=$_SESSION['autoposprn'];
//     $pathprn=$_SESSION['pathprn'];
// }
// $d=__DIR__;

// require('../controllers/MPreciosController.php');


// if (isset($_POST["id"])) {
    
//          $coditems=$_POST["id"];
//          $listaprecio=$_POST["pl"];    
//          $result=getPrecios("coditems='$coditems' AND codtipre='$listaprecio' "); 
// }

// echo( json_encode($result) );



// function getPrecios($value=''){
//     $mprecioscontroller = new MPreciosController();
//     $query="SELECT * from MPrecios Where $value";
//     $precio=$mprecioscontroller->readUDF($query);
//     return $precio;
// }