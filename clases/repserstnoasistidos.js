
  var date_input=$('input[name="fecha_sstnoasis"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  var date_input2=$('input[name="fecha_sstnoasis2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })



//-PDF
 $("#print_sstnoasis").click(function(){
    repserstnoasistidos();
 })

function repserstnoasistidos(){
    var usuario = $('#c_usuario').val();
    var fecha  =$('#fecha_sstnoasis').val();
    var fecha2 =$('#fecha_sstnoasis2').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repserstnoasistidos.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'SERVICIOS ST NO ASISTIDOS'+'&fecha2='+fecha2,'SERVICIOS ST NO ASISTIDOS', '_blank');
  
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

// // // //----------------------------------------------------------------------------------------------------------
function getSerSTNoAsistidos(fecha_cita,usuario,fecha_cita2){

    var items="";
    var ajxmed = $.post( "../../clases/getserstrepnoasistidos.php",{ fecha_cita ,usuario,fecha_cita2}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableSerSTNOAsistidos(items);
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
// // // //------------------------------------------------------------------------------------------------------------
function fillTrTableSerSTNOAsistidos(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].fecha_cita     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';
  }
  $("#tblnoasistidossst").html(html);
}


$('#fecha_sstnoasis2').change(function(){
    var fecha_sstnoasis;
    var usuario    =$('#c_usuario').val();
    fecha_sstnoasis   =$('#fecha_sstnoasis').val();
    fecha_sstnoasis2  =$('#fecha_sstnoasis2').val();
    if (fecha_sstnoasis!=='') {     
       getSerSTNoAsistidos(fecha_sstnoasis,usuario,fecha_sstnoasis2);
    };    
 });


$('#fecha_sstnoasis').change(function(){
    var fecha_sstnoasis;
    var usuario    =$('#c_usuario').val();
    fecha_sstnoasis   =$('#fecha_sstnoasis').val();
    fecha_sstnoasis2  =$('#fecha_sstnoasis2').val();
    if (fecha_sstnoasis2!=='') {     
       getSerSTNoAsistidos(fecha_sstnoasis,usuario,fecha_sstnoasis2);
    };    
 });

// // //     