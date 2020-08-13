
  var date_input=$('input[name="fecha_sasis"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


  var date_input2=$('input[name="fecha_sasis2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


//-PDF
 $("#print_sasis").click(function(){
    repserasistidos();
 })

function repserasistidos(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#fecha_sasis').val();
    var fecha2 =$('#fecha_sasis2').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repserasistidos.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'SERVICIOS ASISTIDOS'+'&fecha2='+fecha2,'SERVICIO ASISTIDOS', '_blank');
  
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
function getSerAsistidos(fecha_cita,usuario,fecha_cita2){

    var items="";
    var ajxmed = $.post( "../../clases/getserrepasistidos.php",{ fecha_cita ,usuario,fecha_cita2}, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableSerAsistidos(items);
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
function fillTrTableSerAsistidos(data){
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
  $("#tblsasistidos").html(html);
}


$('#fecha_sasis2').change(function(){
    var fecha_sasis;
    var usuario  =$('#c_usuario').val();
    fecha_sasis   =$('#fecha_sasis').val();
    fecha_sasis2  =$('#fecha_sasis2').val();
    if (fecha_sasis!=='') {     
       getSerAsistidos(fecha_sasis,usuario,fecha_sasis2);
    };    
 });

$('#fecha_sasis').change(function(){
    var fecha_sasis;
    var usuario  =$('#c_usuario').val();
    fecha_sasis   =$('#fecha_sasis').val();
    fecha_sasis2  =$('#fecha_sasis2').val();
    if (fecha_sasis2!=='') {     
       getSerAsistidos(fecha_sasis,usuario,fecha_sasis2);
    };    
 });

// // //     