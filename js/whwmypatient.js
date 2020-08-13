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
    var search  =$('#search').val();
    var items="";
    
    totalPage   = getData("../../clases/whwmypatientpaghandler.php",{totalPage:'S',search},'POST');
    if ( parseInt(totalPage)>1 ) {
       $('.pagina').show()
       Muestra_Paginacion(totalPage,med);  
    }else{
       $('.pagina').hide()
    };
    
    let pagedat = getData("../../clases/whwmypatientpaghandler.php",{page:1,search});
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
  $('#patient').html(data[0].nombres)

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

      
      
      //total  =parseFloat(data[i].total).toFixed(2);
      //var xx= data[i].fecha.split('/').join('_');
      var numfactu=data[i].codclien+'-'+data[i].codconsulta;
      var idmed =data[i].codclien+'-'+data[i].codmedico+'-'+data[i].codconsulta+'-'+i+'m';

      let clase='';
        if (data[i].asistido=='3') {
           clase = 'table-success';
        } 



      cont=i+1;
      $('#tbodycant').append('<tr  class="'+clase+'">'+  
      '<td align="right" >'+ data[i].fc +'</td>'+
      '<td align="center"><select name="users[]"  id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
      '<td align="center"><select name="medico[]"  id='+idmed+'      class="form-control medicos enterpass enterkey" > <option value="" selected ></option></select> </td>'+      
      '<td align="left" id="'+data[i].codconsulta+'"  >'+ data[i].descons +'</td>'+
      '<td align="center">'+ data[i].observacion +'</td>'+              
      '<td align="center">'+ data[i].codclien +'</td>'+
      
      // '<td align="center">'+ total+'</td>'+
      // '<td align="center">'+ data[i].record +'</td>'+
      // '<td align="center">'+ data[i].empresa+'</td>'+
      // '<td align="center">'+ data[i].codclien+'</td>'+   
      // '<td align="center">'+ data[i].ncitas+'</td>'+                 
      
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
        var search  =$('#search').val();

        let pagedat = getData("../../clases/whwmypatientpaghandler.php",{page:num,search});
        

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
    var _codclien=arrDat[0] //codclien
    var _codmedico=arrDat[1] //codmedico actual
    var _codconsulta=arrDat[2]
    var fecha = $(este).parent().siblings(":first").text().trim()
    //var fecha=arrDat[4].split('_').join('/'); 
    updateDatos(_codclien,_codconsulta,newMedico,fecha,'m')
 })


 $('body').on('change', 'select.users', function(e) {    
    var este= $(this)
    var newUser= $("#"+este.attr('id')).val()
    var arrDat= este.attr('id').split('-')

    var _codclien=arrDat[0]
    var _codconsulta=arrDat[1]
    var fecha = $(este).parent().siblings(":first").text().trim()
    //var fecha=arrDat[4].split('_').join('/') 
    updateDatos(_codclien,_codconsulta,newUser,fecha,'u')
 })

//updatefacmeduser.php
function updateDatos(codclien,codconsulta,fieLd,fecha_cita,typefield){
  /*
  codclien = CODIGO DEL CLEINTE
 
  fieLd = SI ES CODIGO DEL MEDICO O USUARIO
  typefield = m=MEDICO O u=USUARIO   
  */
  let data = {
    codclien,
    codconsulta,
    fieLd,
    fecha_cita,    
    typefield
  }
  getData("../../clases/updateuserconsultas.php",data,"POST")

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

.on( "keydown", function(event) {
      if(event.which == 13) 
         alert("Entered!");
    });
$('#search').on("keydown", function(event){  
   if(event.which == 13) {
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
     }
})



$('#ed').val(getToDay())
$('#sd').val(getToDay())