<?php
 
class Paginator {
 	
   	private $_conn;
        private $_limit;
        private $_page;
        private $_query;
        private $_total;
        private $_qryRows;

public function __construct( $conn, $query, $qryRows  ) {
     
    $this->_conn = $conn;
    $this->_query = $query;
    $this->_qryRows =$qryRows;
 
  //  $rs= $this->_conn->query( $this->_query );
    $rs= $this->_conn->query( $this->_qryRows );
    $rows=0;
    try {
        foreach($rs as $row){
            $results  = $row['num_rows'];
            $rows++;
        }
        if($rows>1){
            $this->_total = $rows;
        }else{
            $this->_total = $results;
        }
        
    } catch (Exception $e) {
        
    }

    //  while ( $row = $rs->fetch_assoc() ) {
    //     $results  = $row['num_rows'];        
    // }
   // $this->_total = $results; //$rs->num_rows;
    //echo print_r($results->num_rows);
    //$this->_total = $results;
     
}

public function  getTotal()
{
    return $this->_total;
}

public function getData(  $page = 1,$limit = 10,$driver="mysql" ) {
     
    $this->_limit   = $limit;
    $this->_page    = $page;
 
    if ( $this->_limit == 'all' ) {
        $query      = $this->_query;
    } else {
        if($driver=="mysql"){
              $query      = $this->_query . " LIMIT " . ( ( $this->_page - 1 ) * $this->_limit ) . ", $this->_limit";
        }else{
              $query      = $this->_query . " OFFSET " . ( ( $this->_page - 1 ) * $this->_limit ) . " ROWS FETCH NEXT ".  $this->_limit." ROWS ONLY";
        }
      
    }
    $rs       = $this->_conn->query( $query );
    $results  = $rs->fetchAll(PDO::FETCH_ASSOC);
    // foreach($rs as $row){
    //     $results[]  = $row;
    // }
    $npo=sizeof($results);
 
    $result         = new stdClass();
    $result->page   = $this->_page;
    $result->limit  = $this->_limit;
    $result->total  = $this->_total;
    $result->data   = $results;
    $result->datalen   = $npo;
    $result->totpaginas  =ceil($this->_total/$this->_limit );
  
    return $result;
}

public function createLinks( $links, $list_class ) {
    if ( $this->_limit == 'all' ) {
        return '';
    }
 
    $last       = ceil( $this->_total / $this->_limit );
 
    $start      = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;
    $end        = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last;
 
    $html       = '<ul class="' . $list_class . '">';
 
    $class      = ( $this->_page == 1 ) ? "disabled" : "";
    $html       .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page - 1 ) . '">&laquo;</a></li>';
 
    if ( $start > 1 ) {
        $html   .= '<li><a href="?limit=' . $this->_limit . '&page=1">1</a></li>';
        $html   .= '<li class="disabled"><span>...</span></li>';
    }
 
    for ( $i = $start ; $i <= $end; $i++ ) {
        $class  = ( $this->_page == $i ) ? "active" : "";
        $html   .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . $i . '">' . $i . '</a></li>';
    }
 
    if ( $end < $last ) {
        $html   .= '<li class="disabled"><span>...</span></li>';
        $html   .= '<li><a href="?limit=' . $this->_limit . '&page=' . $last . '">' . $last . '</a></li>';
    }
 
    $class      = ( $this->_page == $last ) ? "disabled" : "";
    $html       .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page + 1 ) . '">&raquo;</a></li>';
 
    $html       .= '</ul>';
 
    return $html;
}
 
}