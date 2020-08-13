
  var date_input=$('input[name="fecha_slanoasis"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  var date_input2=$('input[name="fecha_slanoasis2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })



//-PDF
 $("#print_slanoasis").click(function(){
    repserlanoasistidos();
 })

function repserlanoasistidos(){
    var usuario = $('#c_usuario').val();
    var fecha  =$('#fecha_slanoasis').val();
    var fecha2 =$('#fecha_slanoasis2').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repserlanoasistidos.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'SERVICIOS LASER NO ASISTIDOS'+'&fecha2='+fecha2,'SERVICIOS LASER NO ASISTIDOS', '_blank');
  
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
function getSerLANoAsistidos(fecha_cita,usuario,fecha_cita2){

    var items="";
    var ajxmed = $.post( "../../clases/getserlarepnoasistidos.php",{ fecha_cita ,usuario,fecha_cita2}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableSerLANOAsistidos(items);
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
function fillTrTableSerLANOAsistidos(data){
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
  $("#tblnoasistidossla").html(html);
}


$('#fecha_slanoasis2').change(function(){
    var fecha_slanoasis;
    var usuario    =$('#c_usuario').val();
    fecha_slanoasis   =$('#fecha_slanoasis').val();
    fecha_slanoasis2  =$('#fecha_slanoasis2').val();
    if (fecha_slanoasis!=='') {     
       getSerLANoAsistidos(fecha_slanoasis,usuario,fecha_slanoasis2);
    };    
 });


$('#fecha_slanoasis').change(function(){
    var fecha_slanoasis;
    var usuario    =$('#c_usuario').val();
    fecha_slanoasis   =$('#fecha_slanoasis').val();
    fecha_slanoasis2  =$('#fecha_slanoasis2').val();
    if (fecha_slanoasis2!=='') {     
       getSerLANoAsistidos(fecha_slanoasis,usuario,fecha_slanoasis2);
    };    
 });

// // //     