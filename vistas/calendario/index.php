<?php
error_reporting(0);
header('Access-Control-Allow-Origin: *');  
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
ini_set('memory_limit', '1024M');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';

require('../../controllers/EventosController.php');
//var_dump($_GET);

if(isset($_GET['sltconsultas'])){
  $xcod=$_GET['sltconsultas'];
}

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">


  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link href="../../css/bootstrap-toggle.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  <link rel="stylesheet" href="../../css/blinking.css">
  <link rel="stylesheet" href="../../css/alert.css?v=072719">
  <link rel="stylesheet" href="../../css/calendario.css?v=072710a"/>
  
  <style>
    .blink_me {
      animation: blinker 2s linear infinite;
      background-color:#FF4136;
    }

    @keyframes blinker {
      50% {
        opacity: 0;
        background-color:#FF4136;
      }
  }
  </style>

 </head>
<body>
 

  <header>
		<div class="container-fluid ">
			<?php include '../layouts/header.php';?>
		</div>
	</header>
   <?php include('modal_eventos.php'); ?>  


    <div class="header">
        <div class="row header-selection"> 
    <?php 
      $arrayMnt= getMonths('es');
    ?>
    <div class="control-group col-sm-4">
         </div> 
<div class="form-inline">
    <div class="form-group col-xs-6">
    <form method="post" action="index.php" id="formmonth" >
          <select class="form-control" id="meses" name="month" onchange="onSelectChange();" >
            <?php
               $hoy = date("Y-m-d");
               $este_mes=  explode("-", $hoy )[1] ;
               $este_dia=  explode("-", $hoy )[2] ;
               
               if(substr($este_mes,0,1)=="0"){
                    $este_mes=substr($este_mes,1,1)-1;	
                }else{
                  $este_mes--;
                }
               
               for ($i=0; $i < sizeof($arrayMnt) ; $i++) { 


              if (isset($_POST['month']) && $_POST['month']==$i) {         
                print(' <option selected value='.$i.'>'.$arrayMnt[$i].'</option>');
              } else {
                  if (!isset($_POST['month']) && $i==$este_mes ) {
                       print(' <option selected value='.$i.'>'.$arrayMnt[$i].'</option>');
                  }else{
                      print(' <option value='.$i.'>'.$arrayMnt[$i].'</option>'); 
                  }
               
              }
              
              $_year=explode("-", date("Y-m-d"))[0] ;
              if (isset($_POST['year']) ) {   $_year= $_POST['year']; }
              
            } ?>
          </select>    

           <input type="text" class="display-2 mb-2 year center form-control" name="year" placeholder="" value=<?php echo($_year); ?> >

             <!-- <div class="col-sm-2"> -->
             <label class="control-label"></label> 
          <div class="form-group">
              <input type="checkbox" class="titulo" checked data-toggle="toggle" data-on="Mostrar" data-off="Ocultar Eventos" data-onstyle="success" data-offstyle="info">
          </div>
     

      <!-- </div> -->
    </form>
</div>
</div>

   </div>
    <!-- <h4 class="display-4 mb-4 text-center">November 2017</h4> -->





    <div class="row border border-right-0 border-bottom-0">
  <?php
  
  $hoy = date("Y-m-d");
  
  $start_date =  explode("-", $hoy)[0]."-".explode("-",$hoy)[1]."-01"; //  date('2019-09-01');
  if ( isset($_POST['month'])) {
       $m=1+(int)$_POST['month'];
       $start_date =  date( $_POST['year']."-".$m."-1");
  }



  $dayofweek = date('w', strtotime($start_date));

  $array_days=array();
  
  if ($dayofweek!=0) {
     $nuevafecha = strtotime ( -$dayofweek.' day' , strtotime ( $start_date ) ) ;
     $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
  
     $beforeDate=explode("-",$nuevafecha);
  
     $beforemonth = $beforeDate[1];
     $beforeyear  = $beforeDate[0];
     $startCalendar=$beforeDate[2];
     $beforeendmonth = cal_days_in_month(CAL_GREGORIAN,$beforemonth,$beforeyear);

     for ($i=$startCalendar; $i <=$beforeendmonth ; $i++) { 
      $ii=$i;
      if (strlen($i)==1) {
         $ii='0'.$i;
      }
      if (strlen($beforemonth)==1) {
        $beforemonth='0'.$beforemonth;
      }


       $array_temp=array(
       'day' => $i,
       'month' => $beforemonth,
       'year' => $beforeyear,
       'date' => $beforeyear.'-'.$beforemonth.'-'.$ii
      );
      array_push($array_days,$array_temp); 
   }   
  }
  

   #CURRENT MONTH
   $currentmonth =explode("-",$start_date)[1];  
   $currentyear  = explode("-",$start_date)[0]; 
   $monthdays    = cal_days_in_month(CAL_GREGORIAN,$currentmonth,$currentyear);

   for ($i=1; $i <=$monthdays ; $i++) { 
       $ii=$i;
      if (strlen($i)==1) {
         $ii='0'.$i;
      }

      if (strlen($currentmonth)==1) {
        $currentmonth='0'.$currentmonth;
      }

      $array_temp=array(
       'day' => $i,
       'month' => $currentmonth,
       'year' => $currentyear,
       'date' => $currentyear.'-'.$currentmonth.'-'.$ii
      );
      array_push($array_days,$array_temp); 
   } 

  # NEXT MONTH
  $len=sizeof($array_days);
  if ($len<42) {
     $remaindays =42-$len ; 
     $monthendday = date('Y-m-t', strtotime($start_date ));


     $nuevafecha = strtotime ( +$remaindays.' day' , strtotime ( $monthendday ) ) ;
     $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
     $nextDate=explode("-",$nuevafecha);
     
     $nextyear  = $nextDate[0];
     $nextmonth = $nextDate[1];
     $endCalendar=$nextDate[2];

     for ($i=1; $i <=$endCalendar ; $i++) { 
            $ii=$i;
      if (strlen($i)==1) {
         $ii='0'.$i;
      }
            if (strlen($nextmonth)==1) {
        $nextmonth='0'.$nextmonth;
      }
      $array_temp=array(
       'day' => $i,
       'month' => $nextmonth,
       'year' => $nextyear,
       'date' => $nextyear.'-'.$nextmonth.'-'.$ii 
      );
      array_push($array_days,$array_temp); 
   } 


  }

      $eventoscontroller = new EventosController();

      $event_start_date=$array_days[0]['date'];
      $event_end_date=$array_days[41]['date'];

      
      $display_month_events=$eventoscontroller->readUDF("Select * from eventos Where fecha between '$event_start_date' and '$event_end_date' and borrado<>1 order by fecha, id ");
      $month_events=$eventoscontroller->readUDF("Select count(*) eventos, fecha  from eventos Where fecha between '$event_start_date' and '$event_end_date'  and borrado<>1  group by fecha");
      $count_events = array();

      $descrip_event= array();

      for ($i=0; $i < count($month_events); $i++) { 
          $indice=$month_events[$i]['fecha'];
          $numero_eventos=$month_events[$i]['eventos'];
          $count_events[$indice]=$numero_eventos;
      }
      
      for ($i=0; $i < count($display_month_events) ; $i++) { 

          $fecha_evento =$display_month_events[$i]['fecha'];

          $evento= array('id' =>$display_month_events[$i]['id'] 
                        ,'evento' =>$display_month_events[$i]['evento']
                        ,'creado' =>$display_month_events[$i]['creado'] 
                        ,'usuario'=>$display_month_events[$i]['usuario'] 
                      );
          $descrip_event[$fecha_evento][]=$evento;
      }



  ?>
 
    <div class="container-fluid ">
      <!-- <div class="row d-none d-sm-flex p-1 bg-dark text-white"> -->
      <table  class="table" id="calendar_table">
        <tr>
              <td><h5 class="col-sm p-1 ">Sunday</h5></td>
              <td><h5 class="col-sm p-1 ">Monday</h5></td>
              <td><h5 class="col-sm p-1 ">Tuesday</h5></td>
              <td><h5 class="col-sm p-1 ">Wednesday</h5></td>
              <td><h5 class="col-sm p-1 ">Thursday</h5></td>
              <td><h5 class="col-sm p-1 ">Friday</h5></td>
              <td><h5 class="col-sm p-1 ">Saturday</h5></td>
        </tr>
         <tbody>
            <tr>
              <?php 

                  $dayscount=0;
                  $calend =sizeof($array_days);
                  for ($i=0; $i <  $calend  ; $i++) {  

                      $fecha  = $array_days[$i]['day'];
                      $mes    = $array_days[$i]['month'];
                      $weekday= $array_days[$i]['date'];

                      $id=$mes."-".$fecha; 

                      $class_este_dia="";  
                      $class_hoy_dia="";  

                      $hoy = date("Y-m-d");
                      $este_mes=  explode("-", $hoy )[1] ;

                      if ($currentmonth==$este_mes) {
                          $este_dia=  explode("-", $hoy )[2];               
                          if(substr($este_dia,0,1)=="0"){
                             $este_dia=substr($este_dia,1,1);
                             if ( $fecha==$este_dia) {
                                 $class_este_dia="today";  
                                 $class_hoy_dia="hoy-dia";  
                              }
                          }else{
                             $este_dia=  explode("-", $hoy )[2];
                             if ( $fecha==$este_dia) {
                                $class_este_dia="today";  
                                $class_hoy_dia="hoy-dia";  
                              }

                          }  
                      }



                      if ($currentmonth==$mes) {
                            print('<td id='.$weekday.' class=""> <div class="day col-sm p-2 border border-left-0 border-top-0 text-truncate '. $class_hoy_dia.'  ">  '); 
                      }else{
                            print( '<td id='.$weekday.'> <div class="day col-sm p-2 border border-left-0 border-top-0 text-truncate d-none d-sm-inline-block bg-light text-muted">');
                            $class_este_dia="";  
                            $class_hoy_dia="";  
                      }
                      ?>

                           <h5 class="row align-items-center">
                              <span class="date col-1 text-center <?php echo($class_este_dia)?>"><?php echo $fecha; ?></span>
                              <!-- <small class="col d-sm-none text-center text-muted"><?php //echo getDaysName($weekday); ?></small> -->
                              <span class="col-1"></span>
                          </h5>

                          <?php

                              $there_are_events=$count_events[$weekday];
                              $event_details= $descrip_event[$weekday];
                              if (!is_null($there_are_events)) {
                                  $e_count=(int)$there_are_events;
                                  if ($e_count==1) {
                                      $text_events="Evento";
                                  }else{
                                      $text_events="Eventos";
                                  } 
                                  print('<p class="con-evento">'.$e_count.' '.$text_events.'</p>');

                                  print('<div class="display-evento">');
                                  for ($ie=0; $ie < count($event_details) ; $ie++) { 
                                       print('<p class="p-con-evento">'.$event_details[$ie]['evento'].'</p>');
                                    
                                  }
                                  print('</div>');

                                  

                              }else{
                                // print('<p class="d-sm-none">No events</p>');
                              }    

                          ?>

                          

                      </div></td>
   
                      <?php 
                      $dayscount++;
                      $startDay++;
                      if ($dayscount==7) {
                      $dayscount=0;
                      print('</tr>');     
                      print('<tr>');         
                      print('<div class=""></div>');  
            }
            }

            ?>


        
      </tbody>
      </table>




    </div>
      



<?php 
function getDaysName($date){
  
  $day = date('w', strtotime($date));

  $dayNames = array(
    'Sunday',
    'Monday', 
    'Tuesday', 
    'Wednesday', 
    'Thursday', 
    'Friday', 
    'Saturday', 
 );
 return $dayNames[$day];
}

function getMonths($lang){

  $Meses = array(
 'es'=> 'Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre',
 'en' =>'January ,February,March,April,May,June,July,August,September,October,November,December'
);
 
return explode(",", $Meses[$lang]) ;

}

?>


<script src="../../js/jquery-3.1.1.min.js"></script>

<script src="../../js/bootstrap.min.js"></script> 

<script type="text/javascript" src="../../js/loader.js"></script>

<script src="../../js/colResizable-1.6.min.js"></script>

<script src="../../js/pacientesrecord.js"></script>

<script src="../../js/scriptpdf.js"></script>

<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>

<script src="../../js/bootstrap-toggle.min.js"></script>

<script src="../../js/jquery-ui.js"></script>

<script src="../../js/jquery.confirm.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.3/js/bootstrap-select.min.js"></script>
<script src="../../js/jquery.redirect.js"></script>

<!-- <script src="../../js/atencion.js"></script>
 -->

<script src="../../js/jquery.inputmask.bundle.js"></script>
<script src="../../js/annyang.min.js"></script>
<script src="../../js/controlassistant.js"></script>
<script src="../../js/latinise.js"></script>
<script src="../../js/calendario.js?v=002"></script>
<script src="../../js/sweetalert2.js"></script>

<script type="text/javascript">
  function onSelectChange(){
  document.getElementById('formmonth').submit();




}
</script>
</body>
</html>