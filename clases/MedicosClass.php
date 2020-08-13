<?php
require_once '../../controllers/MedicosController.php';
class MedicosClass
{
  private $options="";

  function __construct()
  {
    $md = new MedicosController();

    $query="Select codmedico,(nombre+' ' +apellido) as medico from mmedicos where activo='1'";

    $medicos= $md->readUDF($query);

      $opts="";   
      for ($j=0; $j < count($medicos); $j++) { 
        
          $opts .="<option value=".$medicos[$j]['codmedico'].">".$medicos[$j]['medico']."</option>";
        
      }
      
     $this->options=$opts;

  }

  public function getSelectOpts(){
    return $this->options;
  }


}