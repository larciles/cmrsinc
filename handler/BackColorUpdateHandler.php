<?php
date_default_timezone_set('America/Puerto_Rico');
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
try {
    require('../controllers/LoginpassController.php');
} catch (Exception $ex) {
   require('./controllers/LoginpassController.php'); 
}

$id=$_POST['id'];
$bgcolor=$_POST['color'];

$S=$_SESSION;
$recieved_date = date("Y-m-d H:i:s");

$loginpassController = new LoginpassController(); //read

	$set_data = array(
	'bgc' =>  $bgcolor
	);

	$where_data = array(
	'login' => $id

	);

	$array_edit = array(
	'data'  => $set_data,                    
	'where' => $where_data
	);
	$di=$loginpassController->update($array_edit);
	$_SESSION['bgc']=$bgcolor;

?>