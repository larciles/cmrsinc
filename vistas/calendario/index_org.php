<!-- $dayofweek = date('w', strtotime("2019-03-31")); -->
<?php
error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('America/Puerto_Rico');
?>
 <!-- <link rel="stylesheet" href="bootstrap.min.css"> -->
 
 <link rel="stylesheet" href="../../css/bootstrap.min_v4.0.0-beta.css"/>

<style>
  
  select {
  font-size: 22px;
 
  -moz-appearance: none;
  -webkit-appearance: none;
    /*display: block;*/
    margin-left: auto;
    margin-right: auto;

      border:0px;
   outline:0px;
}
  @media (max-width:575px) {
    .display-4 {
        font-size: 1.5rem;
    }
    .day h5 {
        background-color: #f8f9fa;
        padding: 3px 5px 5px;
        margin: -8px -8px 8px -8px;
    }
    .date {
        padding-left: 4px;
    }
}

@media (min-width: 576px) {
    .day {
        height: 14.2857vw;
    }
}

.year{
  width:32%;
    float: right;
     font-size: 52px;
      border:0px;
   outline:0px;
}
</style>
<div class="container-fluid">

  <header>
        <div class="row"> 
    <?php 
      $arrayMnt= getMonths('es');
    ?>
    <div class="control-group col-sm-4">
         </div> 
<div class="form-inline">
    <div class="form-group col-xs-6">
    <form method="post" action="index.php" id="formmonth" >
          <select class="display-4 mb-4 t center    " name="month" onchange="onSelectChange();" >
            <?php  for ($i=0; $i < sizeof($arrayMnt) ; $i++) { 

              if (isset($_POST['month']) && $_POST['month']==$i) {         
                print(' <option selected value='.$i.'>'.$arrayMnt[$i].'</option>');
              } else {
                print(' <option value='.$i.'>'.$arrayMnt[$i].'</option>');
              }
              
              $_year=explode("-", date("Y-m-d"))[0] ;
              if (isset($_POST['year']) ) {   $_year= $_POST['year']; }
              
            } ?>
          </select>    

           <input type="text" class="display-2 mb-2 year center form-control" name="year" placeholder="" value=<?php echo($_year); ?> >
    </form>
</div>
</div>

   </div>
    <!-- <h4 class="display-4 mb-4 text-center">November 2017</h4> -->
    <div class="row d-none d-sm-flex p-1 bg-dark text-white">
      <h5 class="col-sm p-1 text-center">Sunday</h5>
      <h5 class="col-sm p-1 text-center">Monday</h5>
      <h5 class="col-sm p-1 text-center">Tuesday</h5>
      <h5 class="col-sm p-1 text-center">Wednesday</h5>
      <h5 class="col-sm p-1 text-center">Thursday</h5>
      <h5 class="col-sm p-1 text-center">Friday</h5>
      <h5 class="col-sm p-1 text-center">Saturday</h5>
    </div>
      
  </header>

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
       $array_temp=array(
       'day' => $i,
       'month' => $beforemonth,
       'year' => $beforeyear,
       'date' => $beforeyear.'-'.$beforemonth.'-'.$i
      );
      array_push($array_days,$array_temp); 
   }   
  }
  

   #CURRENT MONTH
   $currentmonth =explode("-",$start_date)[1];  
   $currentyear  = explode("-",$start_date)[0]; 
   $monthdays    = cal_days_in_month(CAL_GREGORIAN,$currentmonth,$currentyear);

   for ($i=1; $i <=$monthdays ; $i++) { 
      $array_temp=array(
       'day' => $i,
       'month' => $currentmonth,
       'year' => $currentyear,
       'date' => $currentyear.'-'.$currentmonth.'-'.$i
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
      $array_temp=array(
       'day' => $i,
       'month' => $nextmonth,
       'year' => $nextyear,
       'date' => $nextyear.'-'.$nextmonth.'-'.$i 
      );
      array_push($array_days,$array_temp); 
   } 


  }
  
    $dayscount=0;
    $calend =sizeof($array_days);
      for ($i=0; $i <  $calend  ; $i++) {  

       $fecha = $array_days[$i][day];
       $mes= $array_days[$i][month];
       $weekday = $array_days[$i][date];


       if ($currentmonth==$mes) {
           print('<div class="day col-sm p-2 border border-left-0 border-top-0 text-truncate ">  '); 
       }else{
           print('<div class="day col-sm p-2 border border-left-0 border-top-0 text-truncate d-none d-sm-inline-block bg-light text-muted">');
       }
      ?>
      <h5 class="row align-items-center">
        <span class="date col-1"><?php echo $fecha; ?></span>
        <small class="col d-sm-none text-center text-muted"><?php echo getDaysName($weekday); ;?></small>
        <span class="col-1"></span>
      </h5>
      <p class="d-sm-none">No events</p>
    </div>

  <?php 
  $dayscount++;
  $startDay++;
  if ($dayscount==7) {
      $dayscount=0;        
      print('<div class="w-100"></div>');  
  }
}

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


    
  </div>
</div>
<script type="text/javascript">
  function onSelectChange(){
 document.getElementById('formmonth').submit();
}
</script>