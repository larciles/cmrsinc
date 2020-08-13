<?php
error_reporting(E_ERROR | E_PARSE);

try {

  if(!include('../../models/MovimientoModel.php') )
        include('../models/MovimientoModel.php');

} catch (Exception $e) {
  var_dump($e)  ;
}

class MovimientoController{
    private $model;

    public function __construct(){
        $this->model = new  MovimientoModel();
    }

    public function create( $users_data = array() ) {
        return $this->model->create($users_data);
    } 

    public function read( $user='') {
        return $this->model->read($user);
    }

    public function readfield($field,$idkey=''){
        return $this->model->readfield($field,$idkey);
    }
    public function readcustonfield($_data = array(),$field,$idkey='') {
         return $this->model->readcustonfield($_data,$field,$idkey);
    }

    public function update( $users_data = array() ) {
        return $this->model->update($users_data);
    } 

    public function delete($user='') {
        return $this->model->delete($user);
    } 

    public function setCase($case,$idcustomer){
         return $this->model->setCase($case,$idcustomer);
    }

    public function readUDF($query) {       
        return $this->model->readUDF($query);
    }

    public function pagination($longPage,$links,$className,$consulta=''){
        return $this->model->pagination($longPage,$links,$className,$consulta);
    }

    public function paginationUDF($longPage,$links,$className,$consulta='',$where=''){
           return $this->model->paginationUDF($longPage,$links,$className,$consulta,$where);   
    }

    private function links($res,$links,$className){
        return $this->model->links($res,$links,$className);
    }


    public function paginator($query,$qryRows){
       return $this->model->paginator($query,$qryRows);      
    }

    public function  getTotalPages(){
        return $this->model->getTotalPages();
    }

    public function getData(  $page = 1,$limit = 10,$driver="mysql" ) {     
         return $this->model->getData( $page,$limit,$driver ) ;
    }
    
    public function createLinks( $links, $list_class,$tipo="php" ) {
         return $this->model->createLinks( $links, $list_class,$tipo );
    }          

    public function __destruct(){
        //unset($this);
    }
}