 
$( document ).ready(function() {


$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


var hoy = new Date().toLocaleDateString('en-US', {  
              month : 'numeric',
              day : 'numeric',        
              year : 'numeric'
              }).split(' ').join('/');

$('#sd').val(hoy);


});

$('#submit').click(function(){
  getDataGraph()  
})

function getDataGraph(){

var Http = new XMLHttpRequest();
var url='../../clases/getsalesxhours.php';
 
var fi=$('#sd').val();
var ff=$('#ed').val(); 
var params = "fi="+fi+"&ff="+ff;

 
Http.open( "POST", url, true );

//Http.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
Http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

Http.onreadystatechange=(e)=>{
 
  if (Http.readyState==4 && Http.status==200) {     
        
     
     var res= JSON.parse( Http.responseText ) ;

       google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable(res);

        var options = {
          title: 'Company Performance',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        chart.draw(data, options);
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


