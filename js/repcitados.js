
  var date_input=$('input[name="fecha_cit"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

var date_input2=$('input[name="fecha_cit2"]');

    date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


 $("#print_cit").click(function(){
    pdfReporteCitados();
 })

function pdfReporteCitados(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#fecha_cit').val();
    var fecha2 =$('#fecha_cit2').val();
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
function getCitados(fecha_cita,usuario,fecha_cita2){
   var codperfil  =$('#codperfil').val();

    var items="";
    var ajxmed = $.post( "../../clases/getrepcitados.php",{ fecha_cita ,usuario,fecha_cita2,codperfil}, function(data) { 

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
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].UltimaAsistida     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';

  }
  $("#tbody_citados").html(html);
}


$('#fecha_cit2').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#fecha_cit').val();
    fecha_cita2  =$('#fecha_cit2').val();
    if (fecha_cita!=='') {     
       getCitados(fecha_cita,usuario,fecha_cita2);
    };    
 });

$('#fecha_cit').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#fecha_cit').val();
    fecha_cita2  =$('#fecha_cit2').val();
    if (fecha_cita2!=='') {     
       getCitados(fecha_cita,usuario,fecha_cita2);
    };    
 });    