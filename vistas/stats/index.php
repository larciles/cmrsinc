<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
require_once '../../models/user_model.inc.php';


require('../../controllers/MedicosController.php');
 

 

  #medicos
  $medicosController = new MedicosController();
  $medicos=$medicosController->readUDF("Select Codmedico, concat(nombre,' ' ,apellido) medico from Mmedicos Where activo=1  order by nombre "); 

  if (count($medicos)>0) {
    $doctors="<option value=''>Todos</option>";

    for ($i=0; $i <count($medicos) ; $i++) { 
       $doctors .="<option value=".$medicos[$i]['Codmedico'].">".$medicos[$i]['medico']."</option>";
    }
  }

 
  
$fecha="";
$doc="";
$tratante="";
$product="";
$hoy="";

if ( isset($_POST['sd']) && !empty($_POST['sd']) ) {
    $fecha=$_POST['sd'];
}else{
    $hoy="Hoy";
}


if (isset($_POST['ed']) && !empty($_POST['ed']) ) {
    $enddate=$_POST['ed'];
}

if ( isset($_POST['sltmed']) && !empty($_POST['sltmed']) ) {
    $doc=$_POST['sltmed'];            
    $xx=  $doctors;
    $doctors=str_replace($doc, $doc." selected",$xx);
}


$sltpac="03";
if (isset($_POST['sltpac']) && !empty($_POST['sltpac'])) {
   $sltpac= $_POST['sltpac'];
}

$today=Date('m/d/Y');
if ($fecha==$enddate && $today==$enddate ) {
   $hoy="Hoy";
}

if ( empty($fecha) || empty( $enddate)) {
  $hoy=Date('Y-m-d');
  $fecha1=$hoy;
  $enddate1=$hoy;
}else{
  $fecha1   =$fecha;
  $enddate1 =$enddate;
}


// var_dump($fecha);
$sqlnuevos="
select sum(a.seguimiento) seguimiento from  (
select  a.codmedico, COUNT(*) seguimiento, max( concat( substring( b.nombre,1,1),' ', b.apellido)) medico,max( concat( substring( b.nombre,1,1),substring( b.apellido,1,1))) initials, a.fecha_cita from view_mconsultas_02 a
inner join Mmedicos b on a.codmedico=b.Codmedico
where a.asistido=3 and a.codconsulta='01'  And a.fecha_cita between '$fecha1' and '$enddate1' group by a.codmedico,a.fecha_cita ) a  ";

$sqlSeg=" 
select sum(a.seguimiento) seguimiento from  (
select  a.codmedico, COUNT(*) seguimiento, max( concat( substring( b.nombre,1,1),' ', b.apellido)) medico,max( concat( substring( b.nombre,1,1),substring( b.apellido,1,1))) initials, a.fecha_cita from view_mconsultas_02 a
inner join Mmedicos b on a.codmedico=b.Codmedico
where a.asistido=3 and a.codconsulta='03'  And a.fecha_cita between '$fecha1' and '$enddate1' group by a.codmedico,a.fecha_cita ) a ";

$sqlatend="
select sum(a.seguimiento) seguimiento from  (
select  a.codmedico, COUNT(*) seguimiento, max( concat( substring( b.nombre,1,1),' ', b.apellido)) medico,max( concat( substring( b.nombre,1,1),substring( b.apellido,1,1))) initials, a.fecha_cita from view_mconsultas_02 a
inner join Mmedicos b on a.codmedico=b.Codmedico
where a.asistido=3 and a.codconsulta in('01','03') And a.fecha_cita between '$fecha1' and '$enddate1'    group by a.codmedico,a.fecha_cita ) a ";

 

$query="select sum(a.seguimiento) seguimiento, count(a.fecha_cita) dias,a.codmedico, max(initials) initials, max(medico) medico from  (
select  a.codmedico, COUNT(*) seguimiento, max( concat( substring( b.nombre,1,1),' ', b.apellido)) medico,max( concat( substring( b.nombre,1,1),substring( b.apellido,1,1))) initials, a.fecha_cita from view_mconsultas_02 a
inner join Mmedicos b on a.codmedico=b.Codmedico
where a.asistido=3 and a.codconsulta='$sltpac' ";


if ( !empty($fecha) && !empty( $enddate) && !empty($doc) ) {
  $sql =" And a.fecha_cita between '$fecha' and '$enddate' and a.codmedico='$doc' ";
} else if (  !empty($fecha) && !empty( $enddate)  )  {
  $sql =" And a.fecha_cita between '$fecha' and '$enddate'  ";
}else{
  $hoy=Date('Y-m-d');
   $sql ="   And  a.fecha_cita  = '$hoy'  ";
}
 
$queryavg.= $sql." group by  a.fecha_cita   ";

$query.=$sql." group by a.codmedico,a.fecha_cita ) a group by a.codmedico order by seguimiento desc";

 

$estadisticas= $medicosController->readUDF( $query );

 $statsnuevos = $medicosController->readUDF( $sqlnuevos  );
 $statsSeg= $medicosController->readUDF( $sqlSeg );
 $statsatend= $medicosController->readUDF( $sqlatend );


/*  echo "<pre>";
  var_dump ($_POST);
  echo "</pre>";
 
 echo "<pre>";
  print_r ($sqlnuevos );
  echo "</pre>";
*/ 

 ?>
<!DOCTYPE html>
<html lang="en">
<head>  
  <title>CMA WEB</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <link rel="stylesheet" href="../../css/bootstrap.min.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-select.min.css"> 
  <link rel="stylesheet" href="../../css/bootstrap-datepicker3.css"/>
  <link rel="stylesheet" href="../../css/bootstrap-iso.css" />
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css">
  <link rel="stylesheet" href="../../css/bootstrap_4.0.0-alpha_css_bootstrap.min.css">
  <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="index.css" />
  <link rel="stylesheet" href="export.css" type="text/css" media="all" />
  
<!--     <script src="amcharts.js"></script> -->
<!--     <script src="serial.js"></script> -->
   <!--  <script src="export.min.js"></script> -->
<!--     <script src="light.js"></script> -->



  <style type="text/css">

        * {
  box-sizing: border-box;
}

    body{
          overflow: auto;
  }
    .buscar{
      padding-top: 6px;
    }
    .tr-head-inv{
      background: #87d6d8;
    }

    .tr-head-ent{
      background:#b3ec9c;
    }

    .tr-head-salidas{
      background: #5f6366;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        /*background-color: #3b4847;*/
      }

      .table-active, .table-active>td, .table-active>th, .table-hover tbody tr:hover {
    /*background-color: #4aa0a0;*/
    }

    .total-ent{
      background-color: #2d2a5c!important;
    }

    .titulo-prin{
      text-align: center;
    }

    .avg-md{
/*      width: 100px;
      height: 100px;
      background: lightgreen;
      color: white;
      font-size: 20px;
      float: left;
      margin-right: 0.5rem;
      border-radius: 50%;
*/
width: 100px;
    height: 100px;
    background: lightgreen;
    color: white;
    font-size: 20px;
    float: left;
    margin-right: 30px;
    border-radius: 50%;
    /*margin-left: -24px;*/
    margin-left: -6px;

 
    }

    .baged{
    position: relative;
    width: 50px;
    height: 50px;
    background: #1642e0;
    color: white;
    font-size: 20px;
    float: left;
    margin-right: 1.5rem;
    border-radius: 50%;
    top: -90px;
    }

    .avg-md >h3{
          font-size: 31px;
          position: relative;
          top: 15%;
    }

    .avg-content{
      text-align: center;
      margin-left:14%;
    }

    .avg-md >span{
    position: relative;
    text-align: center;
    margin-bottom: 29%;
    top: -11px;
    margin-right: 50px;
    color: #0fec49;
    font-size: unset;
    font-weight: bold;
    z-index: 99;
    }

        .md-info  {
   /*     position: relative;
    z-index: 9;
    background: lightgreen;
    width: 100px;
    height: 100px;
    float: left;
   
    border-radius: 0.5rem;
    margin-left: 1%;*/

    position: relative;
    z-index: 9;
    background: lightgreen;
    width: 100px;
    height: 100px;
    float: left;
    margin-right: 115PX;
    border-radius: 0.5rem;
    margin-left: -13%;
}

.md-info:before {
  content: "";
    position: absolute;
    z-index: -1;
    top: 50%;
    right: 0%;
    bottom: 0;
    left: 0;
    background: lightblue;
}

.ctrl-info{
  position: relative;
  margin-top: 15%;
}

.baged .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;

  /* Position the tooltip */
  position: absolute;
  z-index: 1;
}

.baged:hover .tooltiptext {
  visibility: visible;
}


.md-info{}
.md-inf-value{}
.md-info-segui{
  position: relative;
}
.md-info-segui-title{
    position: relative;
    top: 40px;
    font-weight: bold;
    font-size: 15px;
}
.md-info-segui-value{
  position: relative;
    top: 58px;
    font-size: xx-large;
}
.md-info-value{
   font-size: xx-large;
   margin-left: 22%; 
}

.md-info-segui-titulo{
  position: relative;
    top: 18%;
    font-weight: bold;
}

/**/

    .box-top{
      position: relative;
        width: 100%;
        height: 60px;
        background: #74b9ff;
        border-radius: 5px 5px 0px 0px;
    }
    .box-bottom{
       width: 100%;
       height: 60px;
             background: #0984e3;
             border-radius:  0px 0px 5px 5px;
    }
    
    .box-main{
        /*clear: both;*/
        width: 120px;
        height: 120px;
        float: left;
        padding-right: 8px;
        margin-right: 0.4%;
    }

    .top-text{
      /*clear: both;*/
      position: relative;
      margin-left: 7%;
      margin-bottom: 25px;
      top: 8%;
      font-size: 3rem;
      font-weight: bold;
      color:#ff6600;
    }
    .bottom-text{
        /*clear: both;*/
        position: relative;
        margin-left: 7%;
        margin-bottom: 25px;
        top: 8%;
        font-size: 3rem;
        font-weight: bold;
        color: white;
    }
 .top-text-titulo{
    /*clear: both;*/
    position: relative;
    margin-left: 0%;
    margin-bottom: 25px;
    top: 8%;
    font-size: 2rem;
    font-weight: bold;
    color: #ff6600;
}
 .bottom-text-titulo{
   /*clear: both;*/
    position: relative;
    margin-left: 2%;
    margin-bottom: 25px;
    top: 23%;
    font-size: 1rem;
    font-weight: bold;
    color: white;
 }

 .linea{
  clear: both;
  margin-left: -13%;
  padding-top: 1%;
  margin-bottom: 1%;
 }

.wrap-table{
      padding: 25px;
}

.titulos-table{
  text-align: center;
}

.tr-stats-data{
  text-align: center;
  font-size: 3rem;
  font-weight: bold;


}

.tr-titulo-table{
    background: #74b9ff;
    color:#c75509;
    font-size: 1.5rem;
    font-weight: bold;
}

.stats-table{
  background: #0984e3;
  color: #fff;
}

  </style> 
 </head>
<body>
<header>
  <div class="container-fluid">
    <?php include '../layouts/header.php';?>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
    <div class="filtro">
      <form  name="list-display" id="list-display"  method="POST">

        <div class="col-sm-2"> 
        </div>

          <div class="col-sm-2">    
            <label class="control-label">Desde</label> 
              <div class="form-group">  
                <input class="datepicker form-control" id="sd" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy" name="sd" autocomplete="off" value="<?php echo($fecha) ?>">
              </div>
          </div>

          <div class="col-sm-2">    
            <label class="control-label">Hasta</label> 
              <div class="form-group">  
                <input class="datepicker form-control" id="ed" placeholder="Fecha final"  data-date-format="mm/dd/yyyy" name="ed" autocomplete="off" value="<?php echo($enddate) ?>">
              </div>
          </div>

          <div class="col-sm-2">    
            <label class="control-label">Médico</label> 
              <div class="form-group">  
                <select class="form-control" id="sltmed" class="sltmed" name="sltmed">
                    <?php echo($doctors) ?>
                </select>
              </div>
          </div>


          <div class="col-sm-2">    
            <label class="control-label">Pacientes</label> 
              <div class="form-group">  
                <select class="form-control" id="sltpac" class="sltpac" name="sltpac">
                     <option value="03">Seguimientos</option>
                     <option value="01">Nuevos</option>
                </select>
              </div>
          </div>


          <div class="control-group col-sm-2 buscar">
            <label class="control-label"></label> 
            <div class="form-group">
              <input type="submit" name="submitok" id="submitok" class="btn btn-info submitok" value="Busca" />  
            </div> 
          </div> 

      </form>
    </div>
  </div>
 
</div>


<div class="container">
    <div class="row">
<!-- https://www.youtube.com/watch?v=BMXrj4cnBfI -->
    <div class="titulo-prin"><h3> Estadísticas de seguimientos </h3></div>
    <div id="chartdiv" style="width: 100%; height: 400px;"></div>
</div>
<div class="row">
<div class="avg-content">
  <?php 
      if (is_array($estadisticas) && count($estadisticas)>0) {
        for ($i=0; $i <count($estadisticas) ; $i++) {
          ?>

          <div class="avg-md"><span id="<?phpecho $estadisticas[$i]['codmedico'] ?>">
            <?php echo round( ( $estadisticas[$i]['seguimiento']/ $estadisticas[$i]['dias'] )  , 2) ; ?>
          </span>  <h3><?php echo $estadisticas[$i]['initials']; ?></h3>
          <div class="baged"><span class="tooltiptext">Promedio</span></div>
          </div>
          
          <?php

        }



        ?>

        <div class="row linea">
            <div class="box-main">
                  <div class="box-top"> <span class="top-text-titulo"> Días</span>  </div>
                  <div class="box-bottom"> <span class="bottom-text-titulo">Seguimientos</span>  </div>
            </div>
        <?php
         for ($i=0; $i <count($estadisticas) ; $i++) {
          ?>

          <div class="box-main">
            <div class="box-top"> <span class="top-text"><?php echo ( $hoy!=""? 'Hoy' : $estadisticas[$i]["dias"] ) ;  ?> </span>  </div>
            <div class="box-bottom"> <span class="bottom-text"><?php echo($estadisticas[$i]['seguimiento']) ?></span>  </div>
          </div>


          <?php
         }
    
         print_r('</div>');
      }
  
   ?>
</div>
</div>
</div>
<div class="row">

  <?php 

if (is_array($statsatend) && count($statsatend)>0) {
    
    //TOTAL ATENDIDOS
    $atendido=$statsatend[0]['seguimiento'] ;

   $seguimiento=0;
   $pacnuevos=0;

    // TOTAL SEGUIMIENTOS
   if (is_array($statsSeg) && count($statsSeg)>0) {

    if (!is_null($statsSeg[0]['seguimiento'])) {
      $seguimiento=$statsSeg[0]['seguimiento'];
    }
      
   }
 
 
   // TOTAL ATENDIDOS
   if (is_array($statsnuevos) && count($statsnuevos)>0) {
      if (!is_null($statsnuevos[0]['seguimiento'])) {
        $pacnuevos=$statsnuevos[0]['seguimiento'] ;
      }   
   }



   ?>

  <div class="container-fluid wrap-table">
    <table class="table table-hover table-striped ">
      <thead>
        <tr class="tr-titulo-table">
          <th class="titulos-table">Seguimiento</th>
          <th class="titulos-table">Nuevos</th>
          <th class="titulos-table">Atendidos</th>
        </tr>
        <tbody>
          <tr class="tr-stats-data">
            <td class="stats-table"><?php echo($seguimiento) ?></td>
            <td class="stats-table"><?php echo($pacnuevos) ?></td>
            <td class="stats-table"><?php echo($atendido) ?></td>
          </tr>
          <tr class="tr-stats-data">
          <?php if ( !is_null($atendido)  ) {
            # code...
           ?>

            <td class="stats-table"><?php echo(round(($seguimiento/$atendido)*100))."%" ?></td>
            <td class="stats-table"><?php echo(round(($pacnuevos/$atendido)*100))."%" ?></td>
            <td class="stats-table"><?php echo('100%') ?></td>
          <?php } ?>
          </tr>
        </tbody>
      </thead>
    </table>
  </div>

  <?php } ?>

</div>
    <div class="row">
<!-- https://www.youtube.com/watch?v=BMXrj4cnBfI -->
    <div class="titulo-prin"><h3> Estadísticas de seguimientos </h3></div>
    <div id="chartdivpie" style="width: 100%; height: 400px;"></div>
</div>

</div>

    

 
    <script src="core.js"></script>
    <script src="charts.js"></script>
    <script src="animated.js"></script>
    <!-- <script src="index.js"></script> -->
    <script>
document.addEventListener('DOMContentLoaded', function(){ 
    // your code goes here


 
     
     // paciente  

     //cambia la selecion de pacientes

     document.querySelectorAll('#sltpac')[0].value='<?php echo($sltpac) ?>';

    // toma el texto del option seleccionado
     var texto =  document.querySelectorAll('#sltpac')[0].options[document.querySelectorAll('#sltpac')[0].selectedIndex].text;
    // toma el valor del option selecionado
     var valor = document.querySelectorAll('#sltpac')[0].value;

     // cambia el titulo del reporte 1er grafico
     document.querySelectorAll('.titulo-prin h3')[0].textContent =" Estadísticas "+texto ;

     // cambia el titulo del reporte 2do grafico
     document.querySelectorAll('.titulo-prin h3')[1].textContent =" Estadísticas "+texto ;


    




  // primer grafico


      am4core.useTheme(am4themes_animated);
      var chart = am4core.create("chartdiv", am4charts.XYChart);

chart.data = [

<?php 
 if (is_array($estadisticas) && count($estadisticas)>0) {
        for ($i=0; $i <count($estadisticas) ; $i++) {
 ?>
{
  "medico": "<?php echo($estadisticas[$i]['medico']) ?>",
  "visits": <?php echo($estadisticas[$i]['seguimiento']) ?>
}, 
<?php 
}
?>

 
<?php
}
 ?>

];

chart.padding(40, 40, 40, 40);

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.dataFields.category = "medico";
categoryAxis.renderer.minGridDistance = 60;
categoryAxis.title.text = "Médicos";



var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

valueAxis.title.text = "# "+texto;
valueAxis.title.fontWeight = "bold";

var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.categoryX = "medico";
series.dataFields.valueY = "visits";
series.tooltipText = "{valueY.value}"
series.columns.template.strokeOpacity = 0;

chart.cursor = new am4charts.XYCursor();

// as by default columns of the same series are of the same color, we add adapter which takes colors from chart.colors color set
series.columns.template.adapter.add("fill", function (fill, target) {
  return getRandomColor(); //chart.colors.getIndex(target.dataItem.index);
});

//oculta el link de la marca del software
document.querySelectorAll('[aria-labelledby="id-66-title"]')[0].style.display='none'

//segunda grafica

// am4core.useTheme(am4themes_animated);

var chartpie = am4core.create("chartdivpie", am4charts.PieChart3D);

var tipopacien;

if (valor=="03") {

   tipopacien=<?php echo($seguimiento) ?>
}else{
   tipopacien=<?php echo($pacnuevos) ?>
}
console.log(tipopacien)
chartpie.data = [

<?php 
   if (is_array($estadisticas) && count($estadisticas)>0) {
        for ($i=0; $i <count($estadisticas) ; $i++) {
  
 ?>
{
  "country":  "<?php echo($estadisticas[$i]['medico']) ?>",
  "litres": <?php echo($estadisticas[$i]['seguimiento']) ?>
}, 

<?php 
 }
}
 ?>
 ];

chartpie.innerRadius = am4core.percent(40);
chartpie.depth = 90;

chartpie.legend = new am4charts.Legend();
chartpie.legend.position = "right";

var series = chartpie.series.push(new am4charts.PieSeries3D());
series.dataFields.value = "litres";
series.dataFields.depthValue = "litres";
series.dataFields.category = "country";

series.colors.list = [
<?php   for ($i=0; $i <count($estadisticas) ; $i++) { ?>
  am4core.color(getRandomColor() ),
 
<?php } ?>
];
// 

 // cambia el nombre del info de estadisticas inferior 
     document.querySelectorAll('.bottom-text-titulo')[0].innerHTML=texto ;
     
// colors
function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}


//ajusta el marginleft par que todo ocupe una linea si hay mas de 8
<?php 
if (count($estadisticas)>8) {  ?>
      document.getElementsByClassName('avg-content')[0].style.marginLeft = "1%";
<?php } ?>

}, false);

 </script>

<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script src="../../js/formden.js" type="text/javascript" ></script>

<script src="../../js/bootstrap-datepicker.min.js" type="text/javascript" ></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>



</body>
</html>
<script type="text/javascript">
  $('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


var  grafica= document.getElementById('chartdiv');
grafica.addEventListener("click", function(e){
 console.log(e.target)
}
  , false);
</script>