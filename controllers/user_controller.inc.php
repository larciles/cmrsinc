<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once 'db/mssqlconn.php';
require_once ('clases/log.php');
/**
* 
*/
class UserController
{
	
	function __construct()
	{
		
	
	}

	function create($username,$password,$email){

	}

  function getEstacion($u){
		$dbmsql = mssqlConn::getConnection();
		$query="Select * from Vestaciones WHERE usuario = '$u' ";
		
		$result = mssqlConn::Listados($query);
	    $resp = json_decode($result, true);
	    $nin=sizeof($resp);
        if ($nin>0) {
        	 $authentic=true; 
        	 //LOG
        //	 $log = new Log();
        	  // set path and name of log file
	//	     $log->lfile('/log/cmalog.txt');   
//        	 $log->lwrite(' ok '.$u); 
        	
	//	     $log->lclose();   	 
        }
        return  $resp ;
 	}

	function login($username, $password){
       //if ($this->authenticate($username,$password)) {
       	$usr = $this->authenticate($username,$password);
       	$usrLen=sizeof($usr);
		if ($usrLen>0) {

			$estacioninfo=self::getEstacion($username);

			$workstation = $estacioninfo[0]['Workstation'];
			$ipaddress   = $estacioninfo[0]['ipaddress'];

			$nombre=$usr[0]['Nombre'];
			$apellido=$usr[0]['apellido'];
			$codperfil=$usr[0]['codperfil'];
			$permisobusqueda=$usr[0]['permisobusqueda'];
			$controlcita=$usr[0]['controlcita'];
			$access=$usr[0]['access'];

			$prninvoice=$usr[0]['prninvoice'];
			$autoposprn=$usr[0]['autoposprn'];
			$pathprn=$usr[0]['pathprn'];
			$clase=$usr[0]['clase'];
			$windows1=$usr[0]['windows1'];
			$bgc=$usr[0]['backc'];


			//session_start();
			session_start([
				'use_only_cookies' =>1,
				'auto_start' =>1,
				'read_and_close'=>true
			]);
			$user= new UserModel($username, $nombre,$apellido,$codperfil,$permisobusqueda,$controlcita, $access,$prninvoice,$autoposprn,$pathprn,$clase);
			$usuario=$user->get_username();
			$_SESSION['username']=$usuario;
			$_SESSION['codperfil']=$codperfil;
			$_SESSION['permisobusqueda']=$permisobusqueda;
			$_SESSION['controlcita']=$controlcita;
			$_SESSION['workstation']=$workstation;
			$_SESSION['ipaddress']=$ipaddress;
			$_SESSION['access']=$access;
			$_SESSION['clase']=$clase;

			$_SESSION['prninvoice']=$prninvoice;
			$_SESSION['autoposprn']=$autoposprn;
			$_SESSION['pathprn']=$pathprn;
			$_SESSION['windows']=$windows1;
			$_SESSION['bgc']=$bgc;

			return true;			
		}
		else{
			return false;
		}
	}


	static function authenticate($u,$p){
		$authentic = false;
		$dbmsql = mssqlConn::getConnection();
		$query="Select * from loginpass WHERE login = '$u' and passwork ='$p' and activo=1 ";
		
		$result = mssqlConn::Listados($query);
	    $usuario = json_decode($result, true);
	    $nin=sizeof($usuario);
        if ($nin>0) {
        	 $authentic=true;        	 
        }

         $log = new Log();
        	  
         $autenciacion='Failed ';
         if ($authentic) {
         	$autenciacion='OK ';
         }
		// $log->lfile('/log/cmalog.txt');   
        // $log->lwrite($autenciacion.$u); 
        	
		 // $log->lclose();   

		//if($u== 'admin' && $p=="admin") $authentic=true;
		return $usuario;//$authentic;
	}
 


	function logout(){
		session_start();
		session_destroy();
	}
}
?>