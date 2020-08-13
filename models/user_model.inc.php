<?php
/**
* 
*/
class UserModel {
	private $username;
	private $nombre;
	private $apellido;
	private $codperfil;
	private $permisobusqueda;
	private $controlcita;
	private $access;

	function __construct($username, $nombre,$apellido,$codperfil,$permisobusqueda,$controlcita,$access)
	{
		$this->username=$username;
		$this->nombre=$nombre;
		$this->apellido=$apellido;
		$this->codperfil=$codperfil;
		$this->permisobusqueda=$permisobusqueda;
		$this->controlcita=$controlcita;

		$this->access=$access;

	}

	function get_username(){
		return $this->username;
	}

	function set_username($username){
		$this->username=$username;
	}


	function set_nombre(){
		$this->nombre=$nombre;
	}

	function get_nombre(){
		return $this->nombre;
	}

	function set_apellido(){
		$this->apellido=$apellido;
	}

	function get_apellido(){
		return $this->apellido;
	}
	  
	function set_codperfil(){
		$this->codperfil=$codperfil;
	}

	function get_codperfil(){
		return $this->codperfil;
	}
	  
	function set_controlcita(){
		$this->controlcita=$controlcita;
	}

	function get_controlcita(){
		return $this->controlcita;
	}
	
	function set_permisobusqueda(){
		$this->permisobusqueda=$permisobusqueda;
	}

	function get_permisobusqueda(){
		return $this->permisobusqueda;
	}

	function set_access(){
		$this->access=$access;
	}

	function get_access(){
		return $this->access;
	}
  
}
?>