<?php
date_default_timezone_set('America/Puerto_Rico');
require_once('Model.php');

class CMA_MFacturaModel extends Model{
    
    public function __construct()
    {
        $this->db_table='CMA_MFactura';
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

        $this->query = "INSERT INTO ".$this->db_table." ( $fields ) VALUES ( $values ) ";
        $this->set_query();
    }  


    public function read($idkey='') {
        $this->query=($idkey!='')?"SELECT * FROM ".$this->db_table." Where numfactu ='$idkey' " : "SELECT * FROM ".$this->db_table."  ";
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

    public function readWhere($where ) {
        $this->query= "SELECT * FROM ".$this->db_table." Where $where ";
        $this->get_query();
        $num_rows = count($this->rows);
        $data = array();    
        foreach ($this->rows as $key => $value) {
            array_push($data, $value);
        }
        return $data;
    }



    public function readAllW($_data = array()){

        $condarray= array();   
        #WHERE DATA
        foreach ($_data as $key => $value) {
            $$key = $value;
            array_push($condarray,  $key."='".$$key."'");            
        }

         if (sizeof($condarray)>1) {
           $where = implode(" and ",$condarray);   
        }else{
           $where = implode(" ",$condarray);
        }


        $this->query="SELECT * FROM ".$this->db_table."  WHERE $where  " ;
        $this->get_query();
        $num_rows = count($this->rows);
        $data = array();    
        foreach ($this->rows as $key => $value) {
            array_push($data, $value);
        }
        return $data;
    }


    public function delAllW($_data = array()){

        $condarray= array();   
        #WHERE DATA
        foreach ($_data as $key => $value) {
            $$key = $value;
            array_push($condarray,  $key."='".$$key."'");            
        }

         if (sizeof($condarray)>1) {
           $where = implode(" and ",$condarray);   
        }else{
           $where = implode(" ",$condarray);
        }


        $this->query = "DELETE FROM ".$this->db_table." WHERE  $where ";
        $this->set_query();
    }

    public function readRows($where) {
        $this->query="Select count(*) rows From ".$this->db_table." Where ".$where;
        $this->get_query();
        $num_rows = count($this->rows);
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

        public function paginationUDF($longPage,$links,$className,$query){

           $pos = strpos( strtolower ( $query) , "from");
           $str_a=substr($query, $pos+5);
           $posw = strpos( strtolower ( $str_a ) , "where");
           $str_b=substr(strtolower ( $str_a ), $posw);  # here starts Where

           #where
           $poso=strpos( strtolower ( $str_b ) , "order by");
           $where= substr($str_b,5,$poso-5);

           #table
           $dbTable= substr($query, $pos+5,$posw-1);


         
          $res=  self::readUDFCount($where,$dbTable);        

          //$num_rows = $this->rows[0][rows] ;
          $num_rows = $res[0]['rows'] ;
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

    public function readUDFCount($where,$dbTable) {
        
        if ($dbTable!="") {
            $this->query="Select count(*) rows From ".$dbTable ;
        } else {
            $this->query="Select count(*) rows From ".$this->db_table ;
        }
        
        
        
        if ($where!="") {
             if ($dbTable!="") {
                $this->query="Select count(*) rows From ".$dbTable ." Where $where";
             }else{
                $this->query="Select count(*) rows From ".$this->db_table." Where $where";
             }
           
        }
        
        $this->get_query();
        $num_rows = $this->rows;
        return $this->rows;//$data;
    }


    public function __destruct()
    {
        //unset($this);
    }
    
}