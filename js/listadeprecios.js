
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('#xtitulo').html('<b>Baremo</b>');
$('#titulografica').hide();

setbaremo();

function report(baremo){
  let usuario =$("#loggedusr").val();
  
  let fecha   =$('#sd').val();
  let fecha2  =$('#ed').val();
  let res ='';
  let titulo = $('.titulo').prop('checked');
  let autorizada =$("#autorizada").val();

  window.open('replistadeprecios.php?tipo='+baremo                                 
                                  ,'','_blank');
}

$('.titulo').change(function() {
   let titulo = $('.titulo').prop('checked');
}) 



 

$('#submit').on('click', function(){
  var baremo = $('#baremolst').val();
  if (baremo!="") {
     report(baremo);
  }
})

$('#baremolst').on('change', function(){
  $('#baremolst').val()
})



function setbaremo(){
           
    var serv= getData("../../clases/baremolst.php");
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Baremo</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].codtipre+"'>"+items[j].destipre+"</option>"; 
     }
     $("#baremolst").html(options);
}

function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

}


//--------------------------------------------------------------------
//--------------------------------------------------------------------


function formatNumber (num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}


$('#ed').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
     fecha_cita  =$('#sd').val();
    // fecha_cita2  =$('#ed').val();
     if (fecha_cita!=='') {     
        report();
     };  

 });

$('#sd').change(function(){
    var fecha_cita;
    var usuario = $('#c_usuario').val();
    // fecha_cita  =$('#sd').val();
     fecha_cita2  =$('#ed').val();
     if (fecha_cita2!=='') {     
        report();
     };    
 });



function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}

//----------------------------------------------------------------------------------------------------------
function getPacientesinfo( record ) {
 let url= "../../clases/getpacientesinfo.php";
 let data={record};
 let items="";

 $.ajax({
  type: 'POST',
  url: url,
  data: data,
  success: function(data) {                      
                      items = jQuery.parseJSON(data); 
                  },
  dataType: "html",
  async:false
});
  return items;

}
//------------------------------------------------------------------------------------------------------------


