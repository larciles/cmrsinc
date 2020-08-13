
  var date_input=$('input[name="fecha_scit"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

var date_input2=$('input[name="fecha_scit2"]');

    date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


 $("#print_scit").click(function(){
    pdfSerReporteCitados();
 })

function pdfSerReporteCitados(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#fecha_scit').val();
    var fecha2 =$('#fecha_scit2').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repsercitados.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'SERVICIOS CITADOS'+'&fecha2='+fecha2,'Servicios Citados'+'&codperfil'+codperfil, '_blank');
  
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
function getSCitados(fecha_cita,usuario,fecha_cita2){
    let codperfil  =$('#codperfil').val();
    var items="";
    var ajxmed = $.post( "../../clases/getserrepcitados.php",{ fecha_cita ,usuario,fecha_cita2,codperfil}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableSCitados(items);
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
function fillTrTableSCitados(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].desitems     +'</td>'+ 

              '</tr>';

  }
  $("#tbody_scitados").html(html);
}


$('#fecha_scit2').change(function(){
    var fecha_scita;
    var usuario = $('#c_usuario').val();
    fecha_scita  =$('#fecha_scit').val();
    fecha_scita2  =$('#fecha_scit2').val();
    if (fecha_scita!=='') {     
       getSCitados(fecha_scita,usuario,fecha_scita2);
    };    
 });

$('#fecha_scit').change(function(){
    var fecha_scita;
    var usuario = $('#c_usuario').val();
    fecha_scita  =$('#fecha_scit').val();
    fecha_scita2  =$('#fecha_scit2').val();
    if (fecha_scita2!=='') {     
       getSCitados(fecha_scita,usuario,fecha_scita2);
    };    
 });    