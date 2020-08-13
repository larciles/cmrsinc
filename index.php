<?php
require_once 'controllers/user_controller.inc.php';
require_once 'models/user_model.inc.php';

 $session_options = array(
			'use_only_cookies' => 1,
			'auto_start' => 1
		);

 if( !isset($_SESSION) )  session_start($session_options);
 if( !isset($_SESSION['ok']) ){
 	$_SESSION['ok'] = false; 
 }else{
 	 $op  ="in";
 }  

if(isset($_REQUEST["op"])){
	$op  =$_REQUEST["op"];
}elseif(isset ($_SESSION['username'])){
    $op  ="in";
}


$user_controller = new UserController();

switch ($op) {
	case 'login':
		$username = strtoupper($_POST['username']);
		$password = $_POST['password'];

		if($user_controller->login($username,$password)){
			$_SESSION['ok']=true;
			header("Location:main.php");
		}else{
            header("Location:vistas/login/login.php");
        }
		
		break;
        case 'in':
		header("Location:main.php");
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