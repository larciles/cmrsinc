var totalPage=300;     
var reporte=false;

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


$('#titulografica').hide();

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
    
    totalPage= getData("../../clases/paginacionhandler.php",{totalPage:'S',fechai,fechaf},'POST');
    Muestra_Paginacion(totalPage);
    let pagedat = getData("../../clases/paginacionhandler.php",{page:1,fechai,fechaf});
    items = jQuery.parseJSON(pagedat);         
    fillTrTableCitados(items);
}
//------------------------------------------------------------------------------------------------------------
function fillTrTableCitados(data){
  var html = '';  
  var cont=0;
  var estilo ="";
  var cantidadTotal =0;
  

  for(var i = 0; i < data.length; i++){   

      if (parseFloat(data[i].neto)<0) {
         estilo ="color:red;";   
      } else{
         estilo ="";
      };

      let start = formatNumber (data[i].existencia);
      let end  = formatNumber (data[i].InvPosible);

      cont=i+1;
      html += '<tr>'+                  
              '<td align="left" >' + data[i].nombre_alterno +'</td>'+
              '<td align="center" >' + data[i].fechacierre +'</td>'+
              '<td align="center" >' + start +'</td>'+
              '<td align="center" >' + data[i].compras +'</td>'+
              '<td align="center" >' + data[i].DevCompras +'</td>'+
              '<td align="center" >' + data[i].ventas+'</td>'+
              '<td align="center" >' + data[i].anulaciones+'</td>'+
              '<td align="center" >' + data[i].ajustes+'</td>'+
              '<td align="center" >' + data[i].NotasCreditos+'</td>'+
              '<td align="center" >' + data[i].NotasEntregas+'</td>'+
              '<td align="center" >' + end +'</td>'+
              
              '</tr>';
             // cantidadTotal=cantidadTotal+parseFloat(data[i].neto);

  }
  //align="left"

  $("#tbodycant").html(html);
 // $("#total").html('Total Ventas $ '+ cantidadTotal.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,') );

  //grafica20Items(data);

}

function formatNumber (num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
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

function getData(url,data,type){
  if (data==undefined) {
    data = {    }
  };
  if (type==undefined) {
    type = "GET"
  };
    return $.ajax({
        type: type,
        url: url,
        data : data,
        async: false
    }).responseText;

}

function Muestra_Paginacion (totalPage) {
 
 $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
                
        $("#dynamic_field > tr").remove();    
        $("#dynamic_field").find("tr:gt(0)").remove(); 
        let fechai  =$('#sd').val();
        let fechaf  =$('#ed').val();

        let pagedat = getData("../../clases/paginacionhandler.php",{page:num,fechai,fechaf});
        

        let xx = jQuery.parseJSON(pagedat);        
        fillTrTableCitados(xx);


        $(".content2").html("Page " + num); // or some ajax content loading...
    });

}

