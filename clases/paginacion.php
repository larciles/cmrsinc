<?php
/**
* 
*/

require_once '../db/mssqlconn.php';


class Pagina
{

public	$dbc;
	
	function __construct()
	{
		 $this->dbc = mssqlConn::getConnection();		 
	}


	function getTotalPages($select,$limit){	

		
		$rs_result = mssqlConn::Listados($select);
		$rs_result =json_decode($rs_result);
		$total_records = $rs_result[0]->total;
		$total_pages = ceil($total_records / $limit); 

		return $total_pages;	

	}


	function getNewPage($select , $limit, $page ){
		$numargs = func_num_args();

		if ( $page==null) { $page=1; };  
		$start_from = ($page-1) * $limit; 
		  
		$sql = $select." OFFSET $start_from ROWS FETCH NEXT $limit  ROWS ONLY";  

		$rs_result =  mssqlConn::Listados($sql); 

		return $rs_result;
	}
}