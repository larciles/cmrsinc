
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('#xtitulo').html('<b>Productos por medico orden descendente</b>');
$('#titulografica').hide();

$('#submit').click(function(){
  
  let usuario =$("#loggedusr").val();
  
  let fecha   =$('#sd').val();
  let fecha2  =$('#ed').val();
  let res ='';
  let titulo = $('.titulo').prop('checked');
  let autorizada =$("#autorizada").val();

//repreturnslaserintra.php
//repreturns.php
//repreturnsuero.php
//repreturnsconsulta.php
//repreturnsproducto.php
// let seleccion=selected();

 reportes('id','titulo')

  // window.open('repreatedprod.php?fecha='+fecha+
  //                                 '&usuario='+usuario+
  //                                 '&fecha2='+fecha2                                  
  //                                 ,'','_blank');
});


getSelectOpProd();
//$('#sltprod').hide();

$('.titulo').change(function() {
   let titulo = $('.titulo').prop('checked');
}) 

 $("#print_cit").click(function(){
    pdfReporteCitados();
 })

function pdfReporteCitados(){
   
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
function getPacientesinfo( record ) {
 let url= "../../clases/getpacientesinfo.php";
 let data={record};
 let items="";

 $.ajax({
  type: 'POST',
  url: url,
  data: data,
  success: function(data) {                      
                      items = jQuery.parseJSON(data); 
                  },
  dataType: "html",
  async:false
});
  return items;

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
    // fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
    // if (fecha_cita!=='') {     
    //    getCitados(fecha_cita,fecha_cita2);
    // };    
   
 });

$('#sd').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
    // if (fecha_cita2!=='') {     
    //    getCitados(fecha_cita,fecha_cita2);
    // };    
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



$('#tbodyreport').on('click', 'button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("reporte")>-1) {
      let titulo=elemt.text();
      let id=elemt.attr('id');
      reportes(id,titulo); 

   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          window.location.href = 'invoicedit.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
      var result = numfactu.split('-');
      let user = $('#idusr').val();
       if(result.length==1){
         resulset = isConsulta(numfactu);
         invoicePrinting(numfactu,resulset[0]['cod_subgrupo'],user); //numfactu;
       }
   }else if(elemt.attr('class').indexOf("anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
        voidinvoice=numfactu;
         $("#void").modal();
         alert();
         //resulset = isConsulta(numfactu);
         //invoicePrinting(numfactu,resulset[0]['cod_subgrupo']); //numfactu;
       }
   }
   
})

function reportes(id,titulo){

  let usuario =$("#loggedusr").val();  
  let fecha   =$('#sd').val();
  let fecha2  =$('#ed').val();
  let res ='';
 // id= $('.titulo').prop('checked');
  let codmed = $('#sltmed').val();
  let medico = $("#sltmed>option:selected").html()
  // if (id) {
     titulo = "Productos por medico Orden Descendente";
  // } else{
  //   titulo = $("#sltmed>option:selected").html()
  // };
   
//repreturnslaserintra.php
//repreturns.php
//repreturnsuero.php
//repreturnsconsulta.php
//repreturnsproducto.php
  window.open('repprodmed.php?fecha='+fecha+
                                  '&usuario='+usuario+
                                  '&titulo='+titulo+  
                                  '&codmed='+codmed+
                                  '&medico='+medico+
                                  '&fecha2='+fecha2                                  
                                  ,'','_blank');
}



// function getSelectOpProd(){
//     data = {
//     prod_serv : "p",
//     orderby   : "desitems"
//   }
//     var res = getData("../../clases/getprodtoinvoice.php",data);
//     items = jQuery.parseJSON(res);
//     var options;
//     for (var j = 0; j < items.length; j++) { 
//         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems.toUpperCase()+"</option>"; 
//     }
//     $("#sltprod").html(options);
// }

function getData(url,data){

  return jsonData = $.ajax({
      type: "POST",
      url: url,
      data:data,
      dataType: "json",
      async: false
  }).responseText;
}

function selected(){
  var selectedArray = new Array();
  var selObj = $('#sltprod');
  var i;
  var count = 0;
  var srtSlect="";
  for (i=0; i<selObj[0].options.length; i++) {
    if (selObj[0].options[i].selected) {
      selectedArray[count] = selObj[0].options[i].value;
      count++;
    }
  }
if (selectedArray.length==1) {
    srtSlect=selectedArray[0];
}
if (selectedArray.length>1) {
    for (var i = 0; i < selectedArray.length; i++) {

      if (  i==selectedArray.length-1) {
         srtSlect = srtSlect + selectedArray[i];
      }else{
         srtSlect = srtSlect + selectedArray[i]+"'"+','+"'";
      }
    }
}
return srtSlect;
}

$('.titulo').change(function() {
   let titulo = $('.titulo').prop('checked');
   if (titulo) {
      $('#sltprod').hide();
   }else{        
      $('#sltprod').show();
   };  
}) 


function getSelectOpProd(){
    data = {filtro:'si' }
    var res = getData("../../clases/medicos.php",data);
    items = jQuery.parseJSON(res);
    var options;
    for (var j = 0; j < items.length; j++) { 
        options+="<option value='"+items[j].codmedico+"'>"+items[j].medico.toUpperCase()+"</option>"; 
    }
    $("#sltmed").html(options);
}


