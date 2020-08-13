
  var date_input=$('input[name="fecha_asis"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


  var date_input2=$('input[name="fecha_asis2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


//-PDF
 $("#print_asis").click(function(){
    repasistidos();
 })

function repasistidos(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#fecha_asis').val();
    var fecha2 =$('#fecha_asis2').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repasistidos.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'ASISTIDOS'+'&fecha2='+fecha2+'&codperfil='+codperfil,'ASISTIDOS', '_blank');
  
}

function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}
//-Fin PDF

// // //----------------------------------------------------------------------------------------------------------
function getAsistidos(fecha_cita,usuario,fecha_cita2){
    var codperfil  =$('#codperfil').val();
    var items="";
    var ajxmed = $.post( "../../clases/getrepasistidos.php",{ fecha_cita ,usuario,fecha_cita2,codperfil }, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableAsistidos(items);
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
// // //------------------------------------------------------------------------------------------------------------
function fillTrTableAsistidos(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].ProximaCita     +'</td>'+ 
              '<td align="center" >' + data[i].NumeroCitas     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';
  }
  $("#tblasistidos").html(html);
}


$('#fecha_asis2').change(function(){
    var fecha_asis;
    var usuario  =$('#c_usuario').val();
    fecha_asis   =$('#fecha_asis').val();
    fecha_asis2  =$('#fecha_asis2').val();
    if (fecha_asis!=='') {     
       getAsistidos(fecha_asis,usuario,fecha_asis2);
    };    
 });

$('#fecha_asis').change(function(){
    var fecha_asis;
    var usuario  =$('#c_usuario').val();
    fecha_asis   =$('#fecha_asis').val();
    fecha_asis2  =$('#fecha_asis2').val();
    if (fecha_asis2!=='') {     
       getAsistidos(fecha_asis,usuario,fecha_asis2);
    };    
 });

// //     