<?php
date_default_timezone_set('America/Puerto_Rico');
require_once('Model.php');

class MInventarioModel extends Model{
    
    public function __construct()
    {
       $this->db_table='MInventario';
    }

    public function create($_data = array()) {
        $fieldarray= array();
        $vaulearray= array();
        
        foreach ($_data as $key => $value) {
			$$key = $value;
            array_push($fieldarray, $key);
            array_push($vaulearray, "'".$$key."'");
        }
        $fields= implode(",",$fieldarray);;
        $values= implode(",",$vaulearray);

        $this->query = "INSERT INTO ".$this->db_table." ( $fields ) VALUES( $values ) ";
        $this->set_query();
    }  


    public function read($idkey='') {
        $this->query=($idkey!='')?"SELECT * FROM ".$this->db_table." Where Id ='$idkey' " : "SELECT * FROM ".$this->db_table."  ";
        $this->get_query();
        $num_rows = count($this->rows);
		$data = array();	
		foreach ($this->rows as $key => $value) {
			array_push($data, $value);
		}
		return $data;
    }


    public function readfield($field,$idkey='') {
        $this->query=($idkey!='')?"SELECT * FROM ".$this->db_table." Where $field ='$idkey' " : "SELECT * FROM ".$this->db_table."  ";
        $this->get_query();
        $num_rows = count($this->rows);
        $data = array();    
        foreach ($this->rows as $key => $value) {
            array_push($data, $value);
        }
        return $data;
    }


    public function readcustonfield($_data = array(),$field,$idkey='') {
        
        $fields = join(",",$_data);
        $this->query= "SELECT $fields FROM ".$this->db_table." Where $field ='$idkey' "  ;
        $this->get_query();
        $num_rows = count($this->rows);
        $data = array();    
        foreach ($this->rows as $key => $value) {
            array_push($data, $value);
        }
        return $data;
    }

    
    public function update($_data = array()) {
       
        $fieldarray= array();        
        $condarray= array();        
        
        $array_set   = $_data['data'] ;        
        $array_where = $_data['where'];

        #SET DATA
        foreach ($array_set as $key => $value) {
			$$key = $value;
            array_push($fieldarray, $key."='".$$key."'");            
        }

        #WHERE DATA
        foreach ($array_where as $key => $value) {
            $$key = $value;
            array_push($condarray,  $key."='".$$key."'");            
        }

        $fields= implode(",",$fieldarray);;

        if (sizeof($condarray)>1) {
           $where = implode(" and ",$condarray);   
        }else{
           $where = implode(" ",$condarray);
        }
        
        $this->query = "UPDATE ".$this->db_table." SET  $fields WHERE $where  ";            
		$this->set_query();

    } 

    public function delete($user='') {
        $this->query = "DELETE FROM ".$this->db_table." WHERE  user = '$user' ";
		$this->set_query();
    } 

    public function validate_user($user,$pass){
        $this->query="SELECT * FROM ".$this->db_table." Where user ='$user' and passw='$pass' ";
        $this->get_query();       
        $data = array();       
		foreach ($this->rows as $key => $value) {
			array_push($data, $value);		
		}
		return $data;
    }

    private function updateField($field,$idkey,$idCostumer) {
        $this->query = "UPDATE ".$this->db_table." SET $field='$idkey' WHERE phone = '$idCostumer' ";
        $this->set_query();
    } 

    public function setCase($case,$idcustomer){      
      self::updateField('caso',$case,$idcustomer);
    }

    public function readUDF($query) {
        $this->query=$query;
        $this->get_query();
        $num_rows = count($this->rows);
        return $this->rows;//$data;
    }

 
    private function links($response,$links,$className,$limit){
        $res=$response[0];
        $num_rows=$response[1]; 
        $total_paginas=$response[2]; 
        $pagina=$response[3];

        if( empty($res) ) {
            return "";       
        }

        $list_class=$className;

        $last  = ceil( $num_rows / $limit );
        $start = ( ( $pagina - $links ) > 0 ) ? $pagina - $links : 1;
        $end   = ( ( $pagina + $links ) < $last ) ? $pagina + $links : $last;
       
        if ($end>1) {
            
            $html  = '<nav aria-label="Page navigation example"> <ul class="' . $list_class . '">';
            $class = ( $pagina == 1 ) ? "disabled" : "";

            //$style =  ( $pagina == $last ) ? "display: none;"  : "";
            $html .='<li class="' . $class . ' page-item"  style="'. $style.'">
                        <a class="page-link" href="?limit=' . $limit . '&pagina=' . ( $pagina - 1 ) . '">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>';
            if ( $start > 1 ) {
                $html   .= '<li><a href="?limit=' . $limit . '&pagina=1">1</a></li>';
                $html   .= '<li class="disabled"><span>...</span></li>';
            }

            for ( $i = $start ; $i <= $end; $i++ ) {
                $class  = ( $pagina == $i ) ? "active" : "";
                $html   .= '<li class="' . $class . '"><a href="?limit=' . $limit . '&pagina=' . $i . '">' . $i . '</a></li>';
            }

            if ( $end < $last ) {
                $html .= '<li class="disabled"><span>...</span></li>';
                $html .= '<li><a href="?limit=' . $limit . '&pagina=' . $last . '">' . $last . '</a></li>';
            }

            $class = ( $pagina == $last ) ? "disabled" : "";
            $style =  ( $pagina == $last ) ? "display: none;"  : "";
            $html .= '<li class="' . $class . ' page-item" style="'. $style.'">
                    <a class="page-link" href="?limit=' . $limit . '&pagina=' . ( $pagina + 1 ) . '" aria-label="Next"> 
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
            </a></li>';
            $html       .= '</ul> </nav>';
      return $html;
    }
    }

        public function paginationUDF($longPage,$links,$className,$query='',$where=''){

         
          $res=  self::readUDFCount($where);        

          $num_rows = $this->rows[0][rows] ;
          if ($num_rows > 0) {
              
              $TAMANO_PAGINA = $longPage;
              $pagina = false;

              if (isset($_GET["pagina"]))
                $pagina = $_GET["pagina"];
                unset( $_GET['pagina'] );
                unset( $_GET['limit'] );
                if (!$pagina) {
                    $inicio = 0;
                    $pagina = 1;
                }else {
                    $inicio = ($pagina - 1) * $TAMANO_PAGINA;
                }

                $total_paginas = ceil($num_rows / $TAMANO_PAGINA);
                
                $consulta = $query." OFFSET ".$inicio." ROWS FETCH NEXT " . $TAMANO_PAGINA ."  ROWS ONLY";                

                $this->query=$consulta;
                $this->get_query();       
                $data = array();       
                foreach ($this->rows as $key => $value) {
                    array_push($data, $value);      
                }
                $dat = array();
                array_push($dat, $data); 
                array_push($dat,$num_rows);
                array_push($dat,$total_paginas);
                array_push($dat, $pagina );

                $_links = self::links($dat,$links,$className,$longPage);
                $dat = array();
               
                array_push($dat, $_links); 
                array_push($dat, $data); 
                return $dat;
          }
    }

    public function readUDFCount($where='') {
        
        $this->query="Select count(*) rows From ".$this->db_table ;
        if ($where!="") {
           $this->query="Select count(*) rows From ".$this->db_table." Where $where";
        }
        
        $this->get_query();
        $num_rows = $this->rows;
        return $this->rows;//$data;
    }


        public function pagination($longPage,$links,$className,$query=''){

         if ($query=='') {
            $res=  self::read();
         } else {
            $a= explode("=",$query);
            $res=  self::readfield($a[0],$a[1]);
         }
         
          
        

          $num_rows = count($this->rows);
          if ($num_rows > 0) {
              
              $TAMANO_PAGINA = $longPage;
              $pagina = false;

              if (isset($_GET["pagina"]))
                 $pagina = $_GET["pagina"];
                 unset( $_GET['pagina'] );
                 unset( $_GET['limit'] );
        
                if (!$pagina) {
                    $inicio = 0;
                    $pagina = 1;
                }else {
                    $inicio = ($pagina - 1) * $TAMANO_PAGINA;
                }

                $total_paginas = ceil($num_rows / $TAMANO_PAGINA);

                if ($query=='') {
                    $consulta = "SELECT * FROM masterinvoice ORDER BY date DESC,customerid,caso  DESC  LIMIT ".$inicio."," . $TAMANO_PAGINA;
                } else {
                    $consulta = "SELECT * FROM masterinvoice  Where ".$a[0]."='".trim($a[1])."' ORDER BY date DESC,customerid,caso  DESC  LIMIT ".$inicio."," . $TAMANO_PAGINA;
                }
                

                $this->query=$consulta;
                $this->get_query();       
                $data = array();       
                foreach ($this->rows as $key => $value) {
                    array_push($data, $value);      
                }
                $dat = array();
                array_push($dat, $data); 
                array_push($dat,$num_rows);
                array_push($dat,$total_paginas);
                array_push($dat, $pagina );

                $_links = self::links($dat,$links,$className,$longPage);
                $dat = array();
               
                array_push($dat, $_links); 
                array_push($dat, $data); 
                return $dat;
          }
    }

    public function paginator($query,$qryRows){

            $this->_query = $query;
            $this->_qryRows =$qryRows;

            $rs=self::readUDF($qryRows);

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

    }

    public function  getTotalPages(){
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
                $rs=self::readUDF($query);
                try{
                    //$results  = $rs->fetchAll(PDO::FETCH_ASSOC);
                }catch(Exception $e){
                    
                }
                
                foreach($rs as $row){
                    $results[]  = $row;
                }
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


    public function createLinks( $links, $list_class, $tipo='php' ) {
        if ( $this->_limit == 'all' ) {
            return '';
        }
     
        $last       = ceil( $this->_total / $this->_limit );
     
        $start      = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;
        $end        = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last;
     
        $html       = '<ul class="' . $list_class . '">';
        
        if ($tipo=="php") {        
     
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
    }else{

                $class      = ( $this->_page == 1 ) ? "disabled" : "";
                $html       .= '<li class="' . $class . '"><a href="#">&laquo;</a></li>';
             
                if ( $start > 1 ) {
                    $html   .= '<li><a href="#">1</a></li>';
                    $html   .= '<li class="disabled"><span>...</span></li>';
                }
             
                for ( $i = $start ; $i <= $end; $i++ ) {
                    $class  = ( $this->_page == $i ) ? "active" : "";
                    $html   .= '<li class="' . $class . '"><a href="#">' . $i . '</a></li>';
                }
             
                if ( $end < $last ) {
                    $html   .= '<li class="disabled"><span>...</span></li>';
                    $html   .= '<li><a href="#">' . $last . '</a></li>';
                }
             
                $class      = ( $this->_page == $last ) ? "disabled" : "";
                $html       .= '<li class="' . $class . '"><a href="#">&raquo;</a></li>';

    }
     
        $html       .= '</ul>';
     
        return $html;
    }  

    public function __destruct()
    {
        //unset($this);
    }
    
}