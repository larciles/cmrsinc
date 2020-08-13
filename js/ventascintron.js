
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
       // report();
     };  

 });

//------------------------------------------------------------------------------------------------------------
$('#sd').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
     fecha_cita2  =$('#ed').val();
     if (fecha_cita2!=='') {     
      //  report();
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
  let sserv=1;
  if ( $('.solo-servicios').prop('checked')  ) {
    sserv=0;
  }

  window.open('repventascintron.php?fecha='+fecha+
                                  '&usuario='+usuario+
                                  '&fecha2='+fecha2+
                                  '&tot_par='+$('.titulo').prop('checked')+
                                  '&with_serv='+sserv
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

//---------------------------------------------------------------------------------------------------------------
$('.titulo').change(function() {
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
     fecha_cita2  =$('#ed').val();
     if (fecha_cita2!=='') {     
        //report();
     };    
}) 

//---------------------------------------------------------------------------------------------------------------
$('.solo-servicios').change(function() {
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
     fecha_cita2  =$('#ed').val();
     if (fecha_cita2!=='') {     
       
     };    
}) 


//--------------------

$('#submit').click(function(){
  report();
})