<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
setlocale(LC_ALL, 'es_ES');
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
}
$ip=$_SERVER['HTTP_CLIENT_IP'];
$usuario=$_SESSION['username'];
require_once '../../models/user_model.inc.php';

//var_dump($_GET);

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
  <!-- <link rel="stylesheet" href="../../css/font-awesome-4.7.0/css/font-awesome.min.css"> -->
  <link rel="stylesheet" href="../../css/bootstrap-toggle.min.css" >
  <link rel="stylesheet" href="../../css/fontawesome5/css/all.css" > <!--load all styles -->
  <link rel="stylesheet" href="../../css/estiloscontrolc.css" />
  <link rel="stylesheet" href="../../css/jquery-ui.css"> 
  <link rel="stylesheet" href="../../css/styles.css?v=20191004" >
  <link rel="stylesheet" href="../../css/animate.css">
  <link rel="stylesheet" href="../../css/main.0668a5eee7f938f0ceee40829087c868.css">



<meta http-equiv="Expires" content="0">
 
<meta http-equiv="Last-Modified" content="0">
 
<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
 
<meta http-equiv="Pragma" content="no-cache">

<style>
.democlass {
  background: #00ffbf;
  font-weight: bold;
  font-size: 18px;

  animation-duration: 3s;
  animation-delay: 1s;
  /*animation-iteration-count: infinite;*/
}

.triste {
  background: #00ffbf;
  font-weight: bold;
  
}
</style>

 </head>
<body>
<header>
	<div class="container-fluid ">
		<?php include '../layouts/header.php';?>
	</div>
</header>
<div id="loading-screen" style="display:none" >
    <!-- <img src="../../img/lg.comet-spinner.gif"> -->
</div>
<div class="row">
    <div class="container">

       <div class="col-sm-1">
      </div>
      <div class="col-sm-2">
      </div>
    	<div class="col-sm-2">		
    		<label class="control-label"></label>	
    		<div class="form-group">	
            	<!-- <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" value=""> -->
                <input class="datepicker form-control" id="sd" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy" name="sd">
            </div>
    	</div>
      <input id="monday" type="hidden"  name="monday">
    	<div class="col-sm-2">		
    		<label class="control-label"></label>	
    		<div class="form-group">	
            	<input class="datepicker form-control" id="ed" placeholder="Fecha final"  name="ed" value="">
            </div>
    	</div>




    	<div class="form-group col-sm-2">
            <label class="control-label"></label> 
            <div class="form-group">  
                  <button type="button"  class="btn btn-success form-control" id="submit" value="Submit">OK</button>
            </div>
        </div>  

        <div class="form-group col-sm-2">
          <label class="control-label"></label> 
         <button type="button" class="btn btn-primary form-control"  style="display: none;" id="btn-excel">Excel</button>
 </div>  

    </div>
</div>
 
<div class="container " id="charts">
 
    <div class="row">
      <div class="container">
         <div id="table_div" ></div>
      </div>
     
    </div>

    <div id="div-stats" class="row" style="display: none; font-size: 1.2em;    margin-top: 32px;">
      <div class="form-group col-sm-2"> 
      </div> 
      <div class="form-group col-sm-3">    
          <div class="start-border circulo-guage "></div> 
          <div class="dolarporhora">
              <span class="start-ring"></span>       
              <canvas id="dollarperhours" class=""></canvas>          
          </div> 
      </div>
       <div class="form-group col-sm-1"> 
      </div>   
         <div class="form-group col-sm-4 comparativas">  
          <?php if ($usuario=='JOLALDE' || $usuario=='LA' ||  $usuario=='FABIOLA' ||  $usuario=='EDGARDO' ) {
            
           ?>
           <table class="table">
  <thead>
    <tr>
     
      <th scope="col">Hoy <?php      
          //echo ucfirst( strftime("%A",  (strtotime(date('Y-m-d'))) ) ) ; 
       ?> 
     </th>
     <th scope="col">Pasado <?php       
          //echo ucfirst( strftime("%A",  (strtotime(date('Y-m-d'))) ) ) ; 
         ?>
            
     </th>
      <th scope="col">Variación</th>    
      <th scope="col"> X&#x0304; Semana</th>

    </tr>

  </thead>
  <tbody id="estadisticas">
    
  </tbody>
</table> 
<?php }  ?>
         </div>
    </div>

    <div class="row">
          <div class="container grafica-semanal">
        

      <div  style="margin-left: auto; margin-right: auto;" >
        <div id="chart_div" style="margin-left: auto; margin-right: auto;"></div>
      </div>
       
          </div>
    </div>

         <div class="row">
            <div class="container">
        <div class="form-group col-sm-3">
             <button type="button" id="l" class="btn btn-primary">Lasers Facturados <span class="badge">7</span></button>
        </div> 

        <div class="form-group col-sm-3">
             <button type="button" id="i" class="btn btn-info">Lasers Intra. Facturados <span class="badge">7</span></button>
        </div> 
        
        
        <div class="form-group col-sm-3">
             <button type="button" id="c" class="btn btn-success">Consultas <span class="badge">7</span></button>
        </div> 
        
        <div class="form-group col-sm-3">
             <button type="button" id="s" class="btn btn-warning">Sueros <span class="badge">7</span></button>
        </div> 
            </div>
    </div>


</div>
</body>
<script src="../../js/jquery-3.1.1.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../js/loader.js"></script>
<script src="../../js/colResizable-1.6.min.js"></script>
<script src="../../js/scriptpdf.js"></script>
<script src="../../js/jquery-table2excel/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript" src="../../js/formden.js"></script>
<script type="text/javascript" src="../../js/bootstrap-datepicker.min.js"></script>
<script src="../../js/bootstrap-toggle.min.js"></script>
<script src="../../js/jquery-ui.js"></script>
<script src="../../js/jquery.confirm.min.js"></script>
<script src="../../js/consolidado.js?v=20191011"></script>
<script src="../../js/consolidadogra.js?v=1725"></script>
<script src="../../js/consolidadostats.js?v=20191003"></script>
<script async src="../../js/gauge.min.js"></script>

<script>
   
  
function initScriptedGauges(usdxh) {
  var guageAniEfects=["linear","quad","quint","cycle","bounce","elastic","dequad","dequint","decycle","debounce","delastic"];
  console.log(usdxh)
    var my_escla= gauge_dynamic_scale()
    console.log( my_escla);
    new RadialGauge({
        renderTo: 'dollarperhours',
        width: 300,
        height: 300,
        units: 'US $ / h',
        title: false,
        value: 0,
        minValue: 0,
        maxValue: my_escla[11],
        majorTicks:my_escla,
        minorTicks: 2,
        strokeTicks: false,
        highlights: [
            { from: 0, to: my_escla[2]+(my_escla[1]/2), color: 'rgba(192, 57, 43,1.0)' },
            { from: my_escla[2]+(my_escla[1]/2), to: my_escla[5], color: 'rgba(230, 126, 34,1.0)' },
            { from: my_escla[5], to: my_escla[7]+(my_escla[1]/2), color: 'rgba(241, 196, 15,1.0)' },
            { from: my_escla[7]+(my_escla[1]/2), to: my_escla[10], color: '#2ecc71' },
            { from: my_escla[10], to: my_escla[11], color: '#3498db'  }
        ],
        colorPlate: '#222',
        colorMajorTicks: '#f5f5f5',
        colorMinorTicks: '#ddd',
        colorTitle: '#fff',
        colorUnits: '#ccc',
        colorNumbers: '#eee',
        colorNeedle: 'rgba(240, 128, 128, 1)',
        colorNeedleEnd: 'rgba(255, 160, 122, .9)',
        valueBox: true,
        animationRule: guageAniEfects[0],
        animationDuration: 600
    }).draw();
    animateGauges(usdxh);
}

if (!Array.prototype.forEach) {
    Array.prototype.forEach = function(cb) {
        var i = 0, s = this.length;
        for (; i < s; i++) {
            cb && cb(this[i], i, this);
        }
    }
}

document.fonts && document.fonts.forEach(function(font) {
    font.loaded.then(function() {
        if (font.family.match(/Led/)) {
            document.gauges.forEach(function(gauge) {
                gauge.update();
            });
        }
    });
});

var timers = [];

function animateGauges(venatsxHoras) {
    let max = venatsxHoras;
    let min = max*.90;

    var dt = new Date(); 
    if (dt.getHours()>=17) {
       min=venatsxHoras;
    }
    
    document.gauges.forEach(function(gauge) {
        timers.push(setInterval(function() {
            gauge.value =  randomIntFromInterval(min, max) ;
        }, gauge.animation.duration + 50));
    });

}



function stopGaugesAnimation() {
    timers.forEach(function(timer) {
        clearInterval(timer);
    });
}


function gauge_dynamic_scale(){
        var scale_array = new Array();
        scale_array.push(0);

        var gauge_dynamic_scale 
        var kscale=600;
        var countscale=600;

        for (var si=0; si < 11; si++) {                 
                   scale_array.push(countscale)
                   countscale=countscale+kscale;
        }

        return scale_array;

    }


    function randomIntFromInterval(min, max) { // min and max included 
  return Math.floor(Math.random() * (max - min + 1) + min);
}

</script>

</html>