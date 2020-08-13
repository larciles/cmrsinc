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

if ($type==1 && $rpt=="" ) {	
   $dbtable="VIEW_Week_Report_CompProd";
   if ($pr=="") {
     getDataProducts($sd,$ed,$dbmsql);
   } else {
     getProdSales($sd,$ed,$pr);
   }   
}

if ($type==3 && $rpt=="" ) {
	getDataLaser($sd,$ed,$dbmsql);
}

if ($type==2 && $rpt=="" ) {
	getDataSuero($sd,$ed);
}

 
 function getProdSales($sd,$ed,$pr){
  $query ="IF OBJECT_ID('tempdb.dbo.#temptable', 'U') IS NOT NULL   DROP TABLE #temptable;  CREATE TABLE #temptable (amount money, periodo nvarchar(7),nombremedico nvarchar(100), year int, mes int, fechafac datetime)   insert #temptable Select sum(cantidad*precunit)-sum(Descuento) amount,periodo,nombremedico,year,vp.mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac from  VIEW_Week_Report_ vp where fechafac between '$sd' and '$ed' and Prod_serv='p' and coditems in('150')  group by mes, periodo,nombremedico,year,month(fechafac) order by year desc,mes desc SELECT periodo,[Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio]  FROM #temptable  pivot (sum (amount) for nombremedico in ([Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio])) as pvt order by year desc,mes desc";
  //MEDICOS
   $query=" Select distinct nombremedico from  VIEW_Week_Report_ vp  where fechafac between '$sd' and '$ed' and Prod_serv='p' and coditems in('$pr') group by mes, periodo,nombremedico,year,month(fechafac) order by nombremedico";
   $resultm = mssqlConn::Listados($query);
   $objmed = json_decode($resultm, true);
   $lenmed = sizeof($objmed);
   $errmed="Month,";
   if($lenmed>0){
     for ($i=0; $i <$lenmed ; $i++) { ;
      $md=$objmed[$i]['nombremedico'];
      if ($i <$lenmed -1) {
        $errmed.=$objmed[$i]['nombremedico'].',';
      } else {
                 $errmed.=$objmed[$i]['nombremedico'];
             }      
     }
    }
   $result="";
   //$medicos   = getMedicos();
   $result[]  = explode(",",$errmed); 
   //PERIODOS
  $query="Select distinct year,mes,periodo from  VIEW_Week_Report_ vp where fechafac between '$sd' and '$ed' and Prod_serv='p' and coditems in('$pr')  group by mes, periodo,nombremedico,year,month(fechafac) order by year desc,mes desc ";
  $res = mssqlConn::Listados($query);
  $periodo = json_decode($res, true); 
  $lperiodo = sizeof($periodo);
  //VENTAS
  $query="Select sum(cantidad*precunit)-sum(Descuento) amount,periodo,nombremedico,year,vp.mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac from  VIEW_Week_Report_ vp where fechafac between  '$sd' and '$ed' and Prod_serv='p' and coditems in('$pr')  group by mes, periodo,nombremedico,year,month(fechafac) order by year desc,mes desc ";

  $res = mssqlConn::Listados($query); 
  $ventas = json_decode($res, true);
  $lventas = sizeof($ventas);


  //FILL PIVOT
  $foundOut=false;
   for ($i=0; $i < $lperiodo ; $i++) { 
        $mes=$periodo[$i]['mes'];
        $year=$periodo[$i]['year'];

        $arrtemp=$periodo[$i]['periodo'].',';
        $long=sizeof($result[0])-1;
        $pos=0;
        for ($m=1; $m <sizeof($result[0]) ; $m++) { 
          # code...
            $medico=$result[0][$m];
            $foundOut=false;

            
            for ($j=0; $j <$lventas ; $j++) { 
                $vyear=$ventas[$j]['year'];
                $vmes=$ventas[$j]['mes'];
                $vmedico=$ventas[$j]['nombremedico'];
                if ($vyear==$year && $vmes ==$mes && $medico==$vmedico) {
                   $foundOut=true;
                   $amount=$ventas[$j]['amount'];
                   break;
               }            
            }
            if (!$foundOut) {
              $amount=0;
            }
            
            if ($pos<$long -1) {
              $arrtemp.=$amount.',';
              $pos=$pos+1;
           }else{
              $arrtemp.=$amount;
              break;
           } 

        }
        $result[]  = explode(",",$arrtemp); 
   }

 echo stripslashes(json_encode($result)); 
 }


function getDataProducts($sd,$ed,$dbmsql){
$arrCompair;
$query="SELECT periodo,[Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio] FROM VIEW_Week_Report_CompProd pivot (sum (amount) for nombremedico in ([Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio])) as pvt  where fechafac between '$sd' and '$ed'  order by year desc, mes desc";
  //$query="SELECT periodo,[319],[328],[327],[315],[004],[320],[317] FROM VIEW_Week_Report_CompProd pivot (sum (amount)  for codmedico in ([319],[328],[327],[315],[004],[320],[317] )) as pvt  where fechafac between '$sd' and '$ed' order by mes, year ";


 $medicos = getMedicos();
 $result[]  = explode(",",$medicos); 
 $res = mssqlConn::Listados($query); 
 $objUpd = json_decode($res, true);

  $ms="";
  foreach( $objUpd as $fila){
 //   $var = $fila['periodo'].','.$fila[319].','.$fila[328].','.$fila[327].','.$fila[315].','.$fila[004].','.$fila[320].','.$fila[317];   
       
   $var = $fila['periodo'].','.$fila['Edwin Perez'].','.$fila['Ellen Rivera'].','.$fila['Gilberto Caraballo'].','.$fila['Idamys Herrera'].','.$fila['Juan Pagan'].','.$fila['Leonel Guedes'].','.$fila['Victor M. Ocasio'];       
	$result[]=explode(",",$var);
  }

  echo stripslashes(json_encode($result)); 
}

function getDataLaser($sd,$ed,$dbmsql){
$arrCompair;
$query="SELECT periodo,[Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio] FROM VIEW_Week_Report_CompLaser pivot (sum (amount) for nombremedico in ([Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio])) as pvt  where fechafac between '$sd' and '$ed' order by year desc, mes desc";

 $medicos = getMedicos();
 $result[]  = explode(",",$medicos); 
 $res = mssqlConn::Listados($query); 
 $objUpd = json_decode($res, true);

  $ms="";
  foreach( $objUpd as $fila){
       
   $var = $fila['periodo'].','.$fila['Edwin Perez'].','.$fila['Ellen Rivera'].','.$fila['Gilberto Caraballo'].','.$fila['Idamys Herrera'].','.$fila['Juan Pagan'].','.$fila['Leonel Guedes'].','.$fila['Victor M. Ocasio'];       
  $result[]=explode(",",$var);
  }

  echo stripslashes(json_encode($result)); 
}


function getDataSuero($sd,$ed){
$query="SELECT periodo,[Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio] FROM VIEW_Week_Report_CompSuero pivot (sum (amount) for nombremedico in ([Edwin Perez],[Ellen Rivera],[Gilberto Caraballo],[Idamys Herrera],[Juan Pagan],[Leonel Guedes],[Victor M. Ocasio])) as pvt  where fechafac between '$sd' and '$ed' order by year desc, mes desc";

$medicos = getMedicos();
$result[]  = explode(",",$medicos); 
$res = mssqlConn::Listados($query); 
$objUpd = json_decode($res, true);

$ms="";
foreach( $objUpd as $fila){
     
 $var = $fila['periodo'].','.$fila['Edwin Perez'].','.$fila['Ellen Rivera'].','.$fila['Gilberto Caraballo'].','.$fila['Idamys Herrera'].','.$fila['Juan Pagan'].','.$fila['Leonel Guedes'].','.$fila['Victor M. Ocasio'];       
$result[]=explode(",",$var);
}

  echo stripslashes(json_encode($result)); 
}



function getMedicos(){
$query="SELECT * from viewmedicos where activo=1 and codmedico!='000' order by medico ";
$result = mssqlConn::Listados($query);
$objUpd = json_decode($result, true);
$lenupd = sizeof($objUpd);
$errmed="Month,";
    if($lenupd>0){
     for ($i=0; $i <$lenupd ; $i++) { ;
     	$md=$objUpd[$i]['medico'];
     	if ($i <$lenupd -1) {
     		$errmed.=$objUpd[$i]['medico'].',';
     	} else {
                 $errmed.=$objUpd[$i]['medico'];
             }     	
     }
    }
    // $result = explode(",",$errmed);
    return     $errmed;
//echo json_encode($result) ;
}


