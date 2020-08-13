<?php
date_default_timezone_set('America/Puerto_Rico');
session_start();


  if(!include('../../db/config.php') )
        include('../db/config.php');

//require_once "../../db/config.php";

abstract class Model{
 private static $db_host = "localhost"; //192.130.74.33  localhost
 private static $db_user = "root";
 private static $db_pass =""; //superv14@
 protected $db_name="amasdb";
 private static $db_charset="utf8";
 private  $conn;
 protected  $query;
 protected $rows = array();


 
 abstract protected function create(); 
 abstract protected function read();
 abstract protected function update();
 abstract protected function delete();

 private function db_open(){
   try {            

		
		//$this->conn = new PDO("sqlsrv:server=".MSQL_HOST.";Database=".MSQL_DBNAME,  MSQL_USER, MSQL_PASS); 
	 	  $this->conn = new PDO("sqlsrv:server=".MSQL_HOST.";Database=".MSQL_DBNAME,  MSQL_USER, MSQL_PASS);  
       // $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	 	   $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		


	} catch (PDOException $e) {
		print "¡Error!: " . $e->getMessage() . "<br/>";
		die();
	}
 }

 private  function db_close()
 {
	$this->conn= null;
 }

 protected function set_query() {
 	try {

 	    $rc=0;
 		$user=$_SESSION['user'];
		$this->db_open();
		$stmt = $this->conn->query($this->query);
         
        $li=$this->conn->lastInsertId();
        $rc=$stmt->rowCount();  

		self::writelog($user,$this->query,"query");
		$this->db_close();
	}catch(Exception $e) {
		self::writelog($user,$this->query,"error");
		$rc=-1;
  
	}

	$result= array('rowsaffected'=>$rc
                  ,'lastInsertId'=>$li);		
        return $result;
 }

 protected function get_query() {
 	try {
 		$user=$_SESSION['user'];
		$this->db_open();
                
                self::writelog($user,$this->query,"query");
                
		$result =	$this->conn->query( $this->query );
		$this->rows = $result->fetchAll(PDO::FETCH_ASSOC);		
		$result= null;;
		//self::writelog($user,$this->query,"query");
		$this->db_close();		
		return ($this->rows);
	}catch(Exception $e) {
  
	}	
 }

private function writelog($user,$cadena,$tipo){
	$filename="/tmp/log_".date("Y-m-d").".txt";
	try {
		$handle=fopen($filename,"a");	
		fwrite($handle,  "[".date("Y-m-d H:i:s.u")."] "."[user:".$user."] [tipo:".$tipo."] [".$cadena."]". PHP_EOL);
		fclose($handle);
	}catch(Exception $e) {
  
	}	
}


}
 