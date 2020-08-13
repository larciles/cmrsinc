
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

//------------------------------------------------------------------------------------------------------------
$('#xtitulo').html('<b>Cintron</b>');
$('#titulografica').hide();


//------------------------------------------------------------------------------------------------------------
$('#ed').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
     fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
     if (fecha_cita!=='') {     
        report();
     };  

 });

//------------------------------------------------------------------------------------------------------------
$('#sd').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
     fecha_cita2  =$('#ed').val();
     if (fecha_cita2!=='') {     
        report();
     };    
 });


//------------------------------------------------------------------------------------------------------------
function report(){
  let usuario =$("#loggedusr").val();
  
  let fecha   =$('#sd').val();
  let fecha2  =$('#ed').val();
  let res ='';
  let titulo = $('.titulo').prop('checked');
  let autorizada =$("#autorizada").val();

  window.open('repventascelmaconsulta.php?fecha='+fecha+
                                  '&usuario='+usuario+
                                  '&fecha2='+fecha2                                  
                                  ,'','_blank');
}

//------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------//
function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}
//------------------------------------------------------------------------------------------------------------

function formatNumber (num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}

