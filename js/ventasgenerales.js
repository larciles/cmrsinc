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
getSelectOpProd();
//----------------------------------------------------------------------------------------------------------
function getCitados(fechai,fechaf,med){
    var codperfil  =$('#codperfil').val();
    var items="";
    
    totalPage= getData("../../clases/ventasgeneralspaghandler.php",{totalPage:'S',fechai,fechaf,med},'POST');
    Muestra_Paginacion(totalPage,med);
    let pagedat = getData("../../clases/ventasgeneralspaghandler.php",{page:1,fechai,fechaf,med});
    items = jQuery.parseJSON(pagedat);         
    fillTrTableCitados(items);
}
//------------------------------------------------------------------------------------------------------------
function fillTrTableCitados(data){
  var html = '';  
  var cont=0;
  var estilo ="";
  var cantidadTotal =0;

     
    $("#tbodycant > tr").remove();
    
    $("#tbodycant").find("tr:gt(0)").remove(); 


  var uoptions='' ;
  var serv= getData("../../clases/getusers.php");
  let usuario = jQuery.parseJSON(serv);
  
  //-------
  var moptions=''
  var res = getData("../../clases/medicos.php",{filtro:'si' });
  var medico = jQuery.parseJSON(res);
  
  //-------

  for(var i = 0; i < data.length; i++){   

     //<USUARIOS
      for (var j = 0; j < usuario.length; j++) {             
            if(usuario[j].login.toUpperCase()==data[i].usuario.toUpperCase()){              
              uoptions+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              uoptions+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }            
      }
     //USUARIOS>
     //<MEDICOS     
     
     for (var mi =0; mi < medico.length;  mi++) {
           if(medico[mi].codmedico==data[i].codmedico){ 
              moptions+="<option value='"+medico[mi].codmedico+"' selected >"+medico[mi].medico+"</option>";   
           }else{
              moptions+="<option value='"+medico[mi].codmedico+"'>"+medico[mi].medico+"</option>";   
           }             
     };
     //MEDICOS>


 
      if (parseFloat(data[i].neto)<0) {
         estilo ="color:red;";   
      } else{
         estilo ="";
      };

      
      
      total  =parseFloat(data[i].total).toFixed(2);
      var xx= data[i].fecha.split('/').join('_');
      var numfactu=data[i].factura+'-'+data[i].usuario+'-'+data[i].codcompany+'-'+data[i].id;
      var idmed   =data[i].factura+'-'+data[i].codmedico+'-'+data[i].codcompany+'-'+data[i].id+'-'+i+'m';

      let clase='';
        if (data[i].asistido=='3') {
           clase = 'table-success';
        } 

      cont=i+1;
      $('#tbodycant').append('<tr  class="'+clase+'">'+  
      '<td align="right" >'+ data[i].factura +'</td>'+
      '<td align="center">'+ data[i].fecha +'</td>'+              
      '<td align="left"  >'+ data[i].cliente +'</td>'+
      '<td align="center">'+ total+'</td>'+
      '<td align="center">'+ data[i].record +'</td>'+
      '<td align="center">'+ data[i].empresa+'</td>'+
      '<td align="center">'+ data[i].codclien+'</td>'+   
      '<td align="center">'+ data[i].ncitas+'</td>'+                 
      '<td align="center"><select name="medico[]"  id='+idmed+'      class="form-control medicos enterpass enterkey" > <option value="" selected ></option></select> </td>'+
      '<td align="center"><select name="users[]"  id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
      '<td align="center" >' +cont+'</td>'+
      '</tr>');
      
      
      $("#"+numfactu+'-'+i).html(uoptions);
      $("#"+idmed).html(moptions);
      uoptions=''
      moptions=''

      
  }

}

function formatNumber (num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}


$('#ed').change(function(){
    // var fecha_cita;
    // var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
    // if (fecha_cita!=='') {     
    //    getCitados(fecha_cita,fecha_cita2);
    // };    
 });

$('#sd').change(function(){
    // var fecha_cita;
    // var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
    // if (fecha_cita2!=='') {     
    //    getCitados(fecha_cita,fecha_cita2);
    // };    
 });


 // function grafica20Items(data){
 //  var arrMain=[];
 //  var arrSecd=[];
 //  arrSecd.push('','');
 //  arrMain.push(arrSecd);

 
 //  var datos=[];
 //  var longitudArr=20;

 //  if (data.length>20) {
 //      datos = data.splice((data.length-20)*-1);
 //  }else{
 //    longitudArr=data.length;    
 //  };

 //  datos=data;
 //  datos = shuffle(datos);

 //  $('#titulografica').show();
 //  for (var i = 0; i < longitudArr; i++) {
 //    arrSecd=[];
 //    arrSecd.push(data[i].desitems,parseInt(data[i].cantidad));
 //    arrMain.push(arrSecd);
 //  };

 


 //  var slice1=randomIntFromInterval(0,7); 
 //  var slice2=randomIntFromInterval(7,19); 
 //  var slice3=randomIntFromInterval(0,19); 
 //  var slice4=randomIntFromInterval(11,19);
 //  var slice5=randomIntFromInterval(0,18);
 //  var slice6=randomIntFromInterval(0,18);
 //  var slice7=randomIntFromInterval(0,18);
   


 // slices ={};
 // slices[slice1]={offset: 0.2};
 // slices[slice2]={offset: 0.2};
 // slices[slice3]={offset: 0.2};
 // slices[slice4]={offset: 0.2};
 // slices[slice5]={offset: 0.2};
 // slices[slice6]={offset: 0.2};
 // slices[slice7]={offset: 0.2};



 //       google.charts.load("current", {packages:["corechart"]});
 //       google.charts.setOnLoadCallback(drawChart);
 //       function drawChart() {
 //         var data = google.visualization.arrayToDataTable(arrMain);

 //         var options = {
 //          title: '',
 //          legend: 'none',
 //          pieSliceText: 'label',
 //          slices,
 //        };

 //         var chart = new google.visualization.PieChart(document.getElementById('piechart'));
 //         chart.draw(data, options);
 //       }

 // }    


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

function Muestra_Paginacion (totalPage,med) {
 
 $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
                
        $("#dynamic_field > tr").remove();    
        $("#dynamic_field").find("tr:gt(0)").remove(); 
        let fechai  =$('#sd').val();
        let fechaf  =$('#ed').val();

        let pagedat = getData("../../clases/ventasgeneralspaghandler.php",{page:num,fechai,fechaf,med});
        

        let xx = jQuery.parseJSON(pagedat);        
        fillTrTableCitados(xx);


        $(".content2").html("Page " + num); // or some ajax content loading...
    });

}

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

$('.titulo').change(function() {
   let titulo = $('.titulo').prop('checked');
   if (titulo) {
      $('#divmedicos').hide();
   }else{        
      $('#divmedicos').show();
   };  
}) 


 

  $('body').on('change', 'select.medicos', function(e) {    
    var este= $(this)
    var newMedico= $("#"+este.attr('id')).val()
    var arrDat= este.attr('id').split('-')
    var factnum=arrDat[0]
    var idcompny=arrDat[2]
    var id=arrDat[3]
    //var fecha=arrDat[4].split('_').join('/'); 
    updateDatos(factnum,idcompny,newMedico,'m',id)
 })


 $('body').on('change', 'select.users', function(e) {    
    var este= $(this)
    var newUser= $("#"+este.attr('id')).val()
    var arrDat= este.attr('id').split('-')
    var factnum=arrDat[0]
    var idcompny=arrDat[2] 
    var id=arrDat[3]
    //var fecha=arrDat[4].split('_').join('/') 
    updateDatos(factnum,idcompny,newUser,'u',id)
 })

//updatefacmeduser.php
function updateDatos(factura,idempresa,fieLd,typefield,id){
  /*
  factura = NUMERO DE FACTURA
  idempresa = ID DE LA EMPRESA
  fieLd = SI ES CODIGO DEL MEDICO O USUARIO
  typefield = m=MEDICO O u=USUARIO   
  */
  let data = {
    factura,
    idempresa,
    fieLd,
    typefield,
    id
  }
  getData("../../clases/updatefacmeduser.php",data,"POST")

}

$('.medicos').change(function(){
  
});

$('#submit').click(function(){  
    $("#tbodycant > tr").remove();    
    $("#tbodycant").find("tr:gt(0)").remove(); 
 
    var med='' ;
    var fecha_cita;
    var usuario =$('#c_usuario').val();
    fecha_cita  =$('#sd').val();
    fecha_cita2 =$('#ed').val();
    let titulo = $('.titulo').prop('checked');
    if (!titulo) {
      med =$("#sltmed").val();
    }
   
    if (fecha_cita!=='') {     
       getCitados(fecha_cita,fecha_cita2,med);
    };    
})


$('#ed').val(getToDay())
$('#sd').val(getToDay())