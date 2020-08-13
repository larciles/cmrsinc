
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


$('#titulografica').hide();

 $("#print_cit").click(function(){
    pdfReporteCitados();
 })

function pdfReporteCitados(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#sd').val();
    var fecha2 =$('#ed').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repcitados.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'CITADOS'+'&fecha2='+fecha2+'&codperfil='+codperfil,'Citados', '_blank');
  
}

function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}

//----------------------------------------------------------------------------------------------------------
function getCitados(fechai,fechaf){
   var codperfil  =$('#codperfil').val();

    var items="";
    var ajxmed = $.post( "../../clases/repventasporcajeroexos.php",{ fechai,fechaf}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableCitados(items);
       }
    }
    })
        .done(function() {
        
        })
        .fail(function() {
          
        })
        .always(function() {
        
        }); 

        if(items.length>0){
          alert();
        }
}
//------------------------------------------------------------------------------------------------------------
function fillTrTableCitados(data){
  var html = '';  
  var cont=0;
  var estilo ="";
  var cantidadTotal =0;
  var unidades=0;
  

  for(var i = 0; i < data.length; i++){   

      if (parseFloat(data[i].neto)<0) {
         estilo ="color:red;";   
      } else{
         estilo ="";
      };

      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+            
                '<td align="center" >' + data[i].usuario     +'</td>'+           
                '<td align="right" style='+estilo+' >'  +'$ '+ parseFloat(data[i].neto).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')   +'</td>'+
                '<td align="center" style='+estilo+' >'  + data[i].cantidad   +'</td>'+
               '</tr>';
              cantidadTotal=cantidadTotal+parseFloat(data[i].neto);
              unidades=unidades+parseInt(data[i].cantidad);

  }
  

  html += '<tr><td align="center" ></td>'+            
                '<td align="center" ></td>'+           
                '<td align="right"  >'  +'$ '+ parseFloat(cantidadTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')   +'</td>'+
                '<td align="center" >'  + unidades   +'</td>'+
               '</tr>';

  $("#tbodycant").html(html);
  $("#total").html('Total Ventas $ '+ cantidadTotal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,') );

  //grafica20Items(data);

}


$('#ed').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#sd').val();
    fecha_cita2  =$('#ed').val();
    if (fecha_cita!=='') {     
       getCitados(fecha_cita,fecha_cita2);
    };    
 });

$('#sd').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#sd').val();
    fecha_cita2  =$('#ed').val();
    if (fecha_cita2!=='') {     
       getCitados(fecha_cita,fecha_cita2);
    };    
 });


 function grafica20Items(data){
  var arrMain=[];
  var arrSecd=[];
  arrSecd.push('','');
  arrMain.push(arrSecd);

 
  var datos=[];
  var longitudArr=20;

  if (data.length>20) {
      datos = data.splice((data.length-20)*-1);
  }else{
    longitudArr=data.length;    
  };

  datos=data;
  datos = shuffle(datos);

  $('#titulografica').show();
  for (var i = 0; i < longitudArr; i++) {
    arrSecd=[];
    arrSecd.push(data[i].desitems,parseInt(data[i].cantidad));
    arrMain.push(arrSecd);
  };

 


  var slice1=randomIntFromInterval(0,7); 
  var slice2=randomIntFromInterval(7,19); 
  var slice3=randomIntFromInterval(0,19); 
  var slice4=randomIntFromInterval(11,19);
  var slice5=randomIntFromInterval(0,18);
  var slice6=randomIntFromInterval(0,18);
  var slice7=randomIntFromInterval(0,18);
   


 slices ={};
 slices[slice1]={offset: 0.2};
 slices[slice2]={offset: 0.2};
 slices[slice3]={offset: 0.2};
 slices[slice4]={offset: 0.2};
 slices[slice5]={offset: 0.2};
 slices[slice6]={offset: 0.2};
 slices[slice7]={offset: 0.2};



       google.charts.load("current", {packages:["corechart"]});
       google.charts.setOnLoadCallback(drawChart);
       function drawChart() {
         var data = google.visualization.arrayToDataTable(arrMain);

         var options = {
          title: '',
          legend: 'none',
          pieSliceText: 'label',
          slices,
        };

         var chart = new google.visualization.PieChart(document.getElementById('piechart'));
         chart.draw(data, options);
       }

 }    


function shuffle(array) {
    let counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
        // Pick a random index
        let index = Math.floor(Math.random() * counter);

        // Decrease counter by 1
        counter--;

        // And swap the last element with it
        let temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }

    return array;
}


function randomIntFromInterval(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}