var arrGraph=[];
var arrEfec=[];
var arrMayor=[];
var arrXXX=[];
$(function(){


//--------------------------------------------------------------------------------------
//CALCULA LA FECHA ACTUAL


var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd
	} 

if(mm<10) {
    mm='0'+mm
} 

today = mm+'/'+dd+'/'+yyyy;
var hoy = today;
$('#ed').val(today);

//CALCULA EL PRIMER DIA DEL MES ( CUANDO CARGLA PAGINA ), ESTE REPORTE SOLO FUNCIONA DESDE EL PRIMERO DE CADA MES 
var date = new Date(), y = date.getFullYear(), m = date.getMonth();
var firstDay = new Date(y, m, 1);
 dd = firstDay.getDate();
 mm = firstDay.getMonth()+1; //January is 0!
 yyyy = firstDay.getFullYear();

if(dd<10) {
    dd='0'+dd
} 

if(mm<10) {
    mm='0'+mm
} 

today = mm+'/'+dd+'/'+yyyy;
$('#sd').val(hoy);
//-------------------------------------------------------------------------------------------------------------------------------------------

$("#wait").hide(); 
$('#xtitulo').html('<b>Efectividad Médicos </b>');

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
  
});


$('#submit').click(function(){  

   datachart=getEfectData();
   
   arrGraph=[]
   arrEfec=[];
   arrMayor=[];

   arrGraph.push(datachart[0]);
   for (var i = 1; i < datachart.length; i++) {
      tmparr=[];
      tmparr.push(datachart[i][0]); // MEDICO
      tmparr.push(parseInt(datachart[i][1])); // VISTOS
      tmparr.push(parseInt(datachart[i][2])); // COMPRARON
      tmparr.push(parseFloat(datachart[i][3])); // EFECTIVIDAD
      arrGraph.push(tmparr);
   }

   let  xx = getEfectMayorData('efectividad'); //EFECTIVIDAD
   fixEfectividad(xx)

   var yy= getEfectMayorData('vistos');      //MAS VISTOS
   fixMayorVistos(yy)
 
  

  google.charts.load('current', {'packages':['bar','corechart']});
  google.charts.setOnLoadCallback(drawChartG);


  google.charts.setOnLoadCallback(drawChart);
  google.charts.setOnLoadCallback(drawChart3);


})


  function drawChartG() {

        var data = google.visualization.arrayToDataTable(arrGraph);

        var options = {
          chart: {
            title: 'CMA Performance',
            subtitle: 'Pacientes vistos, compras, y efectividad: ',
          }
        };

        var chart = new google.charts.Bar(document.getElementById('columnchart'));

        chart.draw(data, google.charts.Bar.convertOptions(options));
}


    function drawChart() {
      var data = google.visualization.arrayToDataTable(arrXXX);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Efectividad de Medicos",
        width: 600,
        height: 400,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("graphcolumnchart"));
      chart.draw(view, options);
  }

    function drawChart3() {
      var data = google.visualization.arrayToDataTable(arrMayor);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Numero de pacientes vistos por medico",
        width: 600,
        height: 400,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("graphcolumnmasv"));
      chart.draw(view, options);
  }


function fixEfectividad(datefect){
  let tmparr=[]
  arrXXX=[]
  tmp = {
        role: "style" 
        }
  tmparr.push("Element")
  tmparr.push("Efectividad")      
  tmparr.push(tmp)

  arrXXX.push(tmparr);
   for (var i = 1; i < datefect.length; i++) {
      tmparr=[];
      tmparr.push(datefect[i][0]); // MEDICO            
      tmparr.push(parseFloat(datefect[i][3])); // EFECTIVIDAD
      let color= getRandomColor();
      tmparr.push("color: "+color)
      
      arrXXX.push(tmparr);
   }

}


function fixMayorVistos(datefect){
  let tmparr=[]

  tmp = {
        role: "style" 
        }
  tmparr.push("Element")
  tmparr.push("Mas Vistos")      
  tmparr.push(tmp)

  arrMayor.push(tmparr);
   for (var i = 1; i < datefect.length; i++) {
      tmparr=[];
      tmparr.push(datefect[i][0]); // MEDICO            
      tmparr.push(parseFloat(datefect[i][1])); // VISTOS
      let color= getRandomColor();
      tmparr.push("color: "+color)
      
      arrMayor.push(tmparr);
   }

}

 
function getEfectData(){
  var sd      =  $('#sd').val();
  var ed      =  $('#ed').val();


  data = {
      sd,
      ed,
      efctype:'1'
    }
  var res = getData("../../clases/getefectividad.php",data);
  items = jQuery.parseJSON(res);
  console.log(items);
  return items;
}

function getEfectMayorData(order){
  var sd      =  $('#sd').val();
  var ed      =  $('#ed').val();



  data = {
      sd,
      ed,
      efctype:'2',
      order
    }
  var res = getData("../../clases/getefectividad.php",data);
  items = jQuery.parseJSON(res);
  console.log(items);
  return items;
}


function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}


function getData(url,data){

  return jsonData = $.ajax({
      type: "POST",
      url: url,
      data:data,
      dataType: "json",
      async: false
  }).responseText;
}

 })

