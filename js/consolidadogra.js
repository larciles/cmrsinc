 
$( document ).ready(function() {

$('#monday').val(  getMonday(new Date($('#sd').val())).toISOString().substring(0, 10) ); 

  function getMonday(d) {
  d = new Date(d);
  var day = d.getDay(),
      diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
  return new Date(d.setDate(diff));
}

$('#sd').change(function(){
    $('#monday').val(  getMonday(new Date($('#sd').val())).toISOString().substring(0, 10) ); 
});


});

function getDataGraph(){

var Http = new XMLHttpRequest();
var url='../../clases/getconsolidadographdata.php';
 
var fi=$('#monday').val();
var ff=$('#ed').val(); 
var params = "fi="+fi+"&ff="+ff;

 
Http.open( "POST", url, true );

//Http.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
Http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

Http.onreadystatechange=(e)=>{
 
  if (Http.readyState==4 && Http.status==200) {
     
     var agt = new Array(); // array de grafica total

     //primert elemento del array     
     
     agt.push( new Array("Element", "$", { role: "style" } ) );
     var days = new Array("Lunes", "Martes", "Miércoles","Jueves","Viernes","Sábado");

     


     var res= JSON.parse( Http.responseText ) ;
    
      if (res.length>0) {
        for (var i = 0; i < res.length; i++) {
          let color=getRandomColor();
          let total=parseFloat( res[i].total);
          var atmp = new Array(days[i], total, color);
          agt.push(atmp)
        }
      }
      console.log( agt );


      google.charts.load("current", {packages:['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
      var data = google.visualization.arrayToDataTable(agt);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Ventas de esta Semana",
        width: 1200,
        height: 400,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("chart_div"));
      chart.draw(view, options);
  }





  }else{
    console.log(e)
  }
  
}
 

Http.send(params);

}


function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}


$('#submit').click(function(){
  $("#estadisticas").html("");
  $("#div-stats").hide();
  getDataGraph();
  getDataStats();
})