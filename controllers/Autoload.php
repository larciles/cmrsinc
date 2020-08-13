<?php 
class Autoload
{
	public function __construct()
	{
            
            define("ROOT", dirname(__FILE__));
            define("DS",DIRECTORY_SEPARATOR);
		//http://php.net/manual/es/function.spl-autoload-register.php
		spl_autoload_register(function ($class_name){
			$models_path = ROOT.DS. '../models/' . $class_name . '.php';
			$controllers_path   = ROOT.DS.$class_name . '.php';

			/*
			echo "
				<p>$models_path</p>
				<p>$controllers_path</p>
			";
			*/

			if( file_exists($models_path) )  require_once($models_path);

			if( file_exists($controllers_path) )  require_once($controllers_path);
		});
	}

	public function __destruct() {
		//unset();
	}
}