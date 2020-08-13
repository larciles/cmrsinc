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
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';
require('../../controllers/LoginpassController.php');

$loginpassController = new LoginpassController();

$currentuser = $loginpassController->readUDF("Select * from loginpass Where login='$user'");


$bgcolor= is_null($currentuser[0]['backc'])? "#fff":$currentuser[0]['backc'] ;

if (isset($_POST) && !empty($_POST)) {

   $backgcolor=trim($_POST['backgcolor']);
   $nombre=trim($_POST['nombre']);
   $apellido=trim($_POST['apellido']);
   $password=trim($_POST['password']);
   
   if (empty($backgcolor)) {
      $backgcolor="#fff";
   }   

   $set_data = array(
      'backc' =>  $backgcolor
     ,'Nombre'=>$nombre
     ,'apellido'=>$apellido
     ,'passwork'=>$password
   );

   $where_data = array(
   'login' => $user

   );

  $array_edit = array(
  'data'  => $set_data,                    
  'where' => $where_data
  );
  $di=$loginpassController->update($array_edit);

  $_SESSION['bgc']=$backgcolor;  
  $bgcolor=$backgcolor;  
}

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA Web</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css"> 
  <link rel="stylesheet" href="../../libs/bootstrap-colorpicker.min.css">
 </head>
<body>
<header>
  <div class="container-fluid ">
    <?php include '../layouts/header.php';?>
  </div>
</header>

<div class="row" ></div>

<form method="POST" id="usuariosfrm">
<div class="usuario">
  <table id="t-usuario" class="table table-dark collapsed">
    <thead>
      <thead>
        <tr>
        <th>Usuario</th>  
        <th>Nombre</th>
        <th>Apellido</th>        
        <th>Password</th>
        <th>Color de fondo</th>
        <th>Guardar</th>
        </tr>
      </thead>
      <tbody>
        <tr>
         <?php 
         if (count($currentuser)>0) {
            for ($i=0; $i <count($currentuser); $i++) { 
                $nombre=$currentuser[0]['Nombre'];
                $apellido=$currentuser[0]['apellido'];
                $usuario=$currentuser[0]['login'];
                $password=trim($currentuser[0]['passwork']);
                //var_dump($currentuser[0]['backc']);
                

              ?>
              <td class="usuario" > 
                <input type="text"  class="form-control" name="usuario" readonly="true" value="<?php echo($usuario) ?>">
              </td>
              <td class="nombre" >
                <input type="text" class="form-control" name="nombre" value="<?php echo($nombre) ?>" required> 
              </td>
              <td class="apellido" >
                <input type="text" class="form-control" name="apellido" value="<?php echo($apellido) ?>" required>
              </td>
              
              <td class="password"  >
                <input type="password" class="form-control" name="password" value="<?php echo($password) ?>" required>  

              </td>
              <td>
                <input type="button" name="bgc" id="bgc" class="form-control elcolorpicker" style="background-color:<?php echo($bgcolor) ?>" value="" >
               <input type="hidden" name="backgcolor" id="backgcolor" value="<?php echo($bgcolor) ?>"> 

                                      
                  
              </td>
              <td><input type="button" id="ok" class="btn btn-success" name="ok" value="Ok"></td>

              <?php                 
             } 
         }

          ?>
        </tr>
        
      </tbody>
    </thead>
  </table>
</div>
</form>

</body>
</html>

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/loader.js" type="text/javascript" ></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/jquery.bootpag.min.js"></script>
<script src="../../libs/bootstrap-colorpicker.min.js"></script>

<script type="text/javascript">
  
  $('.elcolorpicker').colorpicker()


   $('.elcolorpicker').on('change', function(event) {
     var el= $(this)
     console.log(el)
     el.parent().parent().parent().parent()[0].style.backgroundColor=el[0].value
     var id=el.attr('id');
     var color=el[0].value;
      $('#bgc').css('background-color', color);
     let bgc=document.getElementById('bgc');
     bgc.value=color;
     let backgcolor=document.getElementById('backgcolor');
     backgcolor.value=color; 
   }) 
   

   $('.elcolorpicker').on('blur', function(event) {
     // var el= $(this)
     // console.log(el)
     // var id=document.getElementById('loggedusr').value 
     // var color=el[0].value;
     // el[0].value='';
     // $('#bgc').css('background-color', color);
     // if (color!='') {
     //    //setBackColor(id,color)  
     // }
     

   }) 


            function setBackColor(id,color){

           let url ;
           let user = $('#user').val();
           let params = 'id='+id+'&color='+color;
           //let params = 'id='+id+'&color='+color+'&user='+user;
                    
           url="<?php echo($curserver)?>"+'../../handler/BackColorUpdateHandler.php';
           var api = new  XMLHttpRequest();
           api.open('POST',url,true);
           api.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
           api.send(params);
           api.onreadystatechange = function(){
               if(this.readyState === 4 && this.status === 200){
                 // closeModal();
                 // let dat;
                 // dat= this.responseText;
                 // dat= JSON.parse(dat); 
               }   
         }
        }

var ok = document.getElementById('ok')
ok.addEventListener("click", clickOk)
function clickOk(e){

  var nombre = document.forms["usuariosfrm"]["nombre"].value;
  var apellido = document.forms["usuariosfrm"]["apellido"].value;
  var password = document.forms["usuariosfrm"]["password"].value;

  


  if (nombre.trim()== "") {
    alert("Debe tener Nombre");
    return false;
  }
  if (apellido.trim()== "") {
    alert("Debe tener Apellido");
    return false;
  }
  if (password.trim()== "") {
    alert("Debe tener Password");
    return false;
  }

    document.getElementById("usuariosfrm").submit();
}

//
  document.body.style.backgroundColor="<?php echo( !is_null($_SESSION['bgc'])? $_SESSION['bgc']:'#fff' )  ?>";
</script>
