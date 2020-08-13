 <?php
error_reporting(E_ERROR | E_PARSE);

try {
  if(!include('../../models/MPreciosModel.php'))
         include('../models/MPreciosModel.php');
} catch (Exception $e) {
  var_dump($e)  ;
}


class MPreciosController{
    private $model;

    public function __construct(){
        $this->model = new  MPreciosModel();
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

    public function paginationUDF($longPage,$links,$className,$consulta=''){
           return $this->model->paginationUDF($longPage,$links,$className,$consulta);   
    }

    private function links($res,$links,$className){
        return $this->model->links($res,$links,$className);
    }   

    public function getPrice($coditm,$codpre) {  
        return $this->model->getPrice($coditm,$codpre);
    }

    public function __destruct(){
        //unset($this);
    }
}