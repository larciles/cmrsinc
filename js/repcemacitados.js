
  var date_input=$('input[name="fechacema"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

var date_input2=$('input[name="fechacema2"]');

    date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


 $("#print_citcema").click(function(){
    pdfReporteCitadoscema();
 })

function pdfReporteCitadoscema(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#fechacema').val();
    var fecha2 =$('#fechacema2').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repcitadoscema.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'CITADOS CELULAS MADRES'+'&fecha2='+fecha2+'&codperfil='+codperfil,'Citados', '_blank');
  
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
function getCitadoscema(fecha_cita,usuario,fecha_cita2){
   var codperfil  =$('#codperfil').val();

    var items="";
    var ajxmed = $.post( "../../clases/getrepcitadoscema.php",{ fecha_cita ,usuario,fecha_cita2,codperfil}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableCitadoscema(items);
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
function fillTrTableCitadoscema(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      var latendant=""

      if(data[i].UltimaAsistida !=null) latendant=data[i].UltimaAsistida

      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + latendant     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';

  }
  $("#tbody_cemacitados").html(html);
}


$('#fechacema2').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#fechacema').val();
    fecha_cita2  =$('#fechacema2').val();
    if (fecha_cita!=='') {     
       getCitadoscema(fecha_cita,usuario,fecha_cita2);
    };    
 });

$('#fechacema').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    fecha_cita  =$('#fechacema').val();
    fecha_cita2  =$('#fechacema2').val();
    if (fecha_cita2!=='') {     
       getCitadocema(fecha_cita,usuario,fecha_cita2);
    };    
 });    