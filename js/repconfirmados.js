
  var date_input=$('input[name="fecha_con"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  var date_input2=$('input[name="fecha_con2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

//-PDF
 $("#print_con").click(function(){
    repconfirmados();
 })

function repconfirmados(){
    var usuario = $('#c_usuario').val();
    var fecha  =$('#fecha_con').val();
    var fecha2 =$('#fecha_con2').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repconfirmados.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'CONFIRMADOS'+'&fecha2='+fecha2+'&codperfil='+codperfil,'CONFIRMADOS', '_blank');
  
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

// //----------------------------------------------------------------------------------------------------------
function getConfirmados(fecha_cita,usuario,fecha_cita2){
    let codperfil  =$('#codperfil').val();
    var items="";
    var ajxmed = $.post( "../../clases/getrepconfirmados.php",{ fecha_cita ,usuario,fecha_cita2,codperfil}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableConfirmados(items);
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
// //------------------------------------------------------------------------------------------------------------
function fillTrTableConfirmados(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              // '<td align="center" >' + data[i].UltimaAsistida     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';
  }
  $("#tbody_confirmado").html(html);
}


$('#fecha_con2').change(function(){
    var fecha_con;
    var usuario = $('#c_usuario').val();
    fecha_con  =$('#fecha_con').val();
    fecha_con2  =$('#fecha_con2').val();
    if (fecha_con!=='') {     
       getConfirmados(fecha_con,usuario,fecha_con2);
    };    
 });


$('#fecha_con').change(function(){
    var fecha_con;
    var usuario = $('#c_usuario').val();
    fecha_con  =$('#fecha_con').val();
    fecha_con2  =$('#fecha_con2').val();
    if (fecha_con2!=='') {     
       getConfirmados(fecha_con,usuario,fecha_con2);
    };    
 });
//     