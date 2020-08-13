<?php
require_once '../../controllers/KitController.php';

class KitClass
{



  public function findKit($coditems,$qty){

    $arrayResult = array();
    $kitcontroller = new KitController();
    $query="SELECT * from kit where coditems='$coditems' ";
    $kit=$kitcontroller->readUDF($query); 
    $len = sizeof($kit);

    if (count( $kit )>0) {      
      for ($i=0; $i <$len ; $i++) { 
        $dis= $kit[$i]['disminuir'];
        $cantidad=$qty;
        if (!is_null( $dis ) ) {
            $codikit= $kit[$i]['codikit']; 
            $cantidad=$cantidad*$dis;
            $arrayTmp= array($codikit=>$cantidad);
            $arrayResult[] =$arrayTmp;
        }
        
        
    }

    }else{
      $arrayTmp= array($coditems=>$qty);
      $arrayResult[] =$arrayTmp;
    }
    return $arrayResult;
    
}




}