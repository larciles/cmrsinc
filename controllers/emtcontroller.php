<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$testdir =  __DIR__ ;
if ($testdir  == "http://localhost/cma/controllers/") {
	require_once '../db/mssqlconn.php';
}else{
   require_once '../db/mssqlconn.php';	
}



$dbmsql = mssqlConn::getConnection();
$type   = $_POST['type'];
$sd     = $_POST['sd'];
$ed     = $_POST['ed'];
$rpt    = $_POST['rpt'];
$pr     = $_POST['pro'];

$sd = str_replace('/','-',$sd);
$ed = str_replace('/','-',$ed);
getProdSales($sd,$ed,$pr);

// if ($type==1 && $rpt=="" ) {	
//    $dbtable="VIEW_Week_Report_CompProd";
//    if ($pr=="") {
//      getDataProducts($sd,$ed,$dbmsql);
//    } else {
     
//    }   
// }

// if ($type==3 && $rpt=="" ) {
// 	getDataLaser($sd,$ed,$dbmsql);
// }

// if ($type==2 && $rpt=="" ) {
// 	getDataSuero($sd,$ed);
// }


 function getProdSales($sd,$ed,$pr){

  //MEDICOS
   //$query="Select distinct nombremedico from  VIEW_Week_Report_W_Ret  vp  where fechafac between '$sd' and '$ed' and Prod_serv='p' and coditems in('$pr') group by mes, periodo,nombremedico,year,month(fechafac) order by nombremedico";

   $query="Select distinct medico from emt_view vp  where fechafac between '$sd' and '$ed' ";
   $resultm = mssqlConn::Listados($query);
   $objmed = json_decode($resultm, true);
   $lenmed = sizeof($objmed);
   $errmed="qProductos,";
   $errTabla="qProductos,";
   if($lenmed>0){
     for ($i=0; $i <$lenmed ; $i++) { ;
      $md=$objmed[$i]['medico'];
      if ($i <$lenmed -1) {
         $errmed.=$objmed[$i]['medico'].',';
         $errTabla.=$objmed[$i]['medico'].',';
      } else {
                 $errmed.=$objmed[$i]['medico'];
                 $errTabla.=$objmed[$i]['medico'];
             }      
     }
      $errTabla.=",Total";
    }
  // $result="";
   
   $result[]  = explode(",",$errmed); 
   $resultTabla[]  = explode(",",$errTabla); 

   //PRODUCTOS ------------------------------
    

   $query="Select distinct UPPER(Dventa) Dventa from emt_view  vp  where fechafac  between '$sd' and '$ed' ";

   $res = mssqlConn::Listados($query);
   $resultSetProd = json_decode($res, true); 
   $lenPro = sizeof($resultSetProd);
   

   //VENTAS ---------------------------------
   

    //version original
    $query="Select sum(subtotal) total,medico,UPPER(Dventa) Dventa from emt_view vp where fechafac between '$sd' and '$ed' group by medico,Dventa";
    //version nueva 2019-08-12
    $query="Select sum(total) total,medico,UPPER(Dventa) Dventa from emt_view vp where fechafac between '$sd' and '$ed' group by medico,Dventa";
    

   $res = mssqlConn::Listados($query); 
   $ventas = json_decode($res, true);
   $lventas = sizeof($ventas);

   //TOTAL DEL CUADRO -----------------------

    //version original 
   $query="Select sum(subtotal) total,medico from emt_view vp where fechafac between '$sd' and '$ed' group by medico";
   //version nueva 2019-08-12
   $query="Select sum(total) total,medico from emt_view vp where fechafac between '$sd' and '$ed' group by medico";
   
   $resTotal = mssqlConn::Listados($query); 
   $totalcuadro = json_decode($resTotal, true);
   $ltcuadro = sizeof($totalcuadro);

           


 //  //FILL PIVOT
   $foundOut=false;
   $i=0;
   $total_l=0;
   $total_t=0;
   for ($i=0; $i < $lenPro ; $i++) { 

        $producto=$resultSetProd[$i]['Dventa'];
        $arrtemp=$resultSetProd[$i]['Dventa'].',';

        $long=sizeof($result[0])-1;
        $pos=0;
        for ($m=1; $m <sizeof($result[0]) ; $m++) { 
          # code...
            $medico=$result[0][$m];
            $foundOut=false;

            
            for ($j=0; $j <$lventas ; $j++) { 
                $dventa=$ventas[$j]['Dventa'];                
                $vmedico=$ventas[$j]['medico'];
                if ($dventa==$producto && $medico==$vmedico) {
                   $foundOut=true;
                   $amount=$ventas[$j]['total'];
                   $total_l+=$ventas[$j]['total'];
               
                   break;
               }      
                   if ($vmedico=="Total") {
                       $amount=$total_l;
                   }
            }
            if (!$foundOut) {
               $amount="0";
            }
            
            if ($pos<$long -1) {
              $arrtemp.=$amount.',';
              $pos=$pos+1;
           }else{
              $arrtemp.=$amount;
              break;
           } 

        }
        // $arrtemp=$arrtemp=substr($arrtemp,0,-1);
        // $arrtemp.=$total_l;
        $result[]  = explode(",",$arrtemp);
        $total_t+=$total_l; 
        $total_l=0;
   }

   //agregado el Total del cuadro
   $arrtemp='Total'.',';
   $pos=0;
    for ($m=1; $m <sizeof($result[0]) ; $m++) { 
          
            $medico=$result[0][$m];  // toma el nombre del medico
            $foundOut=false;         // variable  para salir con break si encuentra al medico

            
            //Desplazamiento atravez del array que contiene el total para el cuadro
            for ($j=0; $j <$ltcuadro ; $j++) {                      
                $vmedico=$totalcuadro[$j]['medico']; // Nombre del medico
                if ($medico==$vmedico) {
                   $foundOut=true;
                   $amount=$totalcuadro[$j]['total'];
                   break;
               }            
            }
            if (!$foundOut) {
               $amount="0";
            }
            
            if ($pos<$long -1) {
              $arrtemp.=$amount.',';
              $pos=$pos+1;
           }else{
              $arrtemp.=$amount;
              break;
           } 

    }
      //  $arrtemp=$arrtemp=substr($arrtemp,0,-1);
       // $arrtemp.=$total_t;
        $result[]  = explode(",",$arrtemp);
 //--------------------------------------------------------------
        $foundOut=false;
   $i=0;
   $total_l=0;
   $total_t=0;
   for ($i=0; $i < $lenPro ; $i++) { 

        $producto=$resultSetProd[$i]['Dventa'];
        $arrtemp=$resultSetProd[$i]['Dventa'].',';

        $long=sizeof($resultTabla[0])-1;
        $pos=0;
        for ($m=1; $m <sizeof($resultTabla[0]) ; $m++) { 
          # code...
            $medico=$resultTabla[0][$m];
            $foundOut=false;

            
            for ($j=0; $j <$lventas ; $j++) { 
                $dventa=$ventas[$j]['Dventa'];                
                $vmedico=$ventas[$j]['medico'];
                if ($dventa==$producto && $medico==$vmedico) {
                   $foundOut=true;
                   $amount=$ventas[$j]['total'];
                   $total_l+=$ventas[$j]['total'];
               
                   break;
               }      
                   if ($vmedico=="Total") {
                       $amount=$total_l;
                   }
            }
            if (!$foundOut) {
               $amount="0";
            }
            
            if ($pos<$long -1) {
              $arrtemp.=$amount.',';
              $pos=$pos+1;
           }else{
              $arrtemp.=$amount;
              break;
           } 

        }
        $arrtemp=$arrtemp=substr($arrtemp,0,-1);
        $arrtemp.=$total_l;
        $resultTabla[]  = explode(",",$arrtemp);
        $total_t+=$total_l; 
        $total_l=0;
   }

   //agregado el Total del cuadro
   $arrtemp='Total'.',';
   $pos=0;
    for ($m=1; $m <sizeof($resultTabla[0]) ; $m++) { 
          
            $medico=$resultTabla[0][$m];  // toma el nombre del medico
            $foundOut=false;         // variable  para salir con break si encuentra al medico

            
            //Desplazamiento atravez del array que contiene el total para el cuadro
            for ($j=0; $j <$ltcuadro ; $j++) {                      
                $vmedico=$totalcuadro[$j]['medico']; // Nombre del medico
                if ($medico==$vmedico) {
                   $foundOut=true;
                   $amount=$totalcuadro[$j]['total'];
                   break;
               }            
            }
            if (!$foundOut) {
               $amount="0";
            }
            
            if ($pos<$long -1) {
              $arrtemp.=$amount.',';
              $pos=$pos+1;
           }else{
              $arrtemp.=$amount;
              break;
           } 

    }
        $arrtemp=$arrtemp=substr($arrtemp,0,-1);
        $arrtemp.=$total_t;
        $resultTabla[]  = explode(",",$arrtemp);
 //--------------------------------------------------------------       

       $g_c_Array = array();

       array_push($g_c_Array, $result);
       array_push($g_c_Array, $resultTabla);



 echo stripslashes(json_encode($g_c_Array)); 
 }

  function sortMedicos($aMedicos,$aMayor,$division){
  
  $newArray = array();
  $longitud= sizeof($aMayor);
  
  $len= count($aMedicos);

  for ($i=0; $i <$longitud ; $i++) { 
    if ($aMayor[$i][Dventa] ==$division) {
      $elmedico= $aMayor[$i][medico];
      for ($j=0; $j <$len ; $j++) { 
          $elotromedico = $aMedicos[$j];   
          if($aMayor[$i][medico]==$aMedicos[$j]){
          
             array_push($newArray,$aMayor[$i][medico]);
             //unset( $aMedicos[$j] );
              break;
          }
         
            
      }
       $len= count($aMedicos); 
    }

   
  }
 $dffArr = array_diff($aMedicos,$newArray);
 if (count($dffArr)>0) {
  for ($i=0; $i <=count($dffArr) ; $i++) { 
       $textmed= $dffArr[$i];
    if (!is_null( $dffArr[$i]     )) {
      
       array_push($newArray,$dffArr[$i]);
    }
    
  }
     
 }
  return $newArray;
}