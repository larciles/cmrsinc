<?php
require_once 'controllers/user_controller.inc.php';
require_once 'models/user_model.inc.php';

if(isset($_REQUEST["op"])){
	$op  =$_REQUEST["op"];
}


$user_controller = new UserController();

switch ($op) {
	case 'login':
		$username = strtoupper($_POST['username']);
		$password = $_POST['password'];

		if($user_controller->login($username,$password)){
			header("Location:main.php");
		}else{
            header("Location:vistas/login/login.php");
        }
		
		break;
	case 'logout':
		$user_controller->logout();
		header("Location:vistas/login/login.php");
		break;
	default:
		header("Location:vistas/login/login.php");
		break;
}

?>