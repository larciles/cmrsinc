$(function(){
   
   $('#xtitulo').html('<b>Cuadre General</b>');

    var date_input=$('input[name="efechai"]'); 
    var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
    });


    var hoy = new Date().toLocaleDateString('en-US', {  
          month : 'numeric',
          day : 'numeric',        
          year : 'numeric'
      }).split(' ').join('/');


});

let hoy = new Date().toLocaleDateString('en-US', {  
    month : 'numeric',
    day : 'numeric',        
    year : 'numeric'
}).split(' ').join('/');


//-------------------------------------------------------------------------
  

  $(document).ready(function () {
     document.getElementsByClassName ('shadow')[0].style.display ="none"    
   })

//-------------------------------------------------------------------------