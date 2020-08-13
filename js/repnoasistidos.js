
  var date_input=$('input[name="fecha_noasis"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  var date_input2=$('input[name="fecha_noasis2"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


    $('#fecha_noasis').val($('#fecha_noasis_1').val()) 
    $('#fecha_noasis2').val($('#fecha_noasis_2').val()) 

    if ($('#fecha_noasis2').val() !=='' && $('#fecha_noasis2').val()!=='') {
          var fecha_noasis;
    var usuario    =$('#c_usuario').val();
    fecha_noasis   =$('#fecha_noasis').val();
    fecha_noasis2  =$('#fecha_noasis2').val();

    $('#fecha_noasis_1').val($('#fecha_noasis').val()) 
    $('#fecha_noasis_2').val($('#fecha_noasis2').val()) 

    if (fecha_noasis2!=='') {     
       getNoAsistidos(fecha_noasis,usuario,fecha_noasis2);
    };    
    };

//-PDF
 $("#print_noasis").click(function(){

    repnoasistidos();
 })

function repnoasistidos(){
    var usuario = $('#c_usuario').val();
    var fecha  =$('#fecha_noasis').val();
    var fecha2 =$('#fecha_noasis2').val();
    var codperfil  =$('#codperfil').val();

    if (fecha=='') {
       fecha=getToDay();
    } 
    window.open('repnoasistidos.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'C'+'&titulo_reporte='+'NO ASISTIDOS'+'&fecha2='+fecha2+'&codperfil='+codperfil,'NO ASISTIDOS', '_blank');
  
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
function getNoAsistidos(fechai,usuario,fechaf){

    var codperfil  =$('#codperfil').val();
    var items="";
    
    totalPage= getData("../../clases/paginationhandlernoasist.php",{totalPage:'S',fechai,fechaf,codperfil,usuario},'POST');
    Muestra_Paginacion(totalPage);
    let pagedat = getData("../../clases/paginationhandlernoasist.php",{page:1,fechai,fechaf,codperfil,usuario});
    items = jQuery.parseJSON(pagedat);         
    fillTrTableDoNotsAsist(items);


}
// // // //------------------------------------------------------------------------------------------------------------
function fillTrTableDoNotsAsist(data){
  var html = '';  
  var cont=0;
  var estilo ="";
  var cantidadTotal =0;
  

  for(var i = 0; i < data.length; i++){   

      cont=i+1;
       html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].UltimaAsistida     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';
  }

  $("#tblnoasistidos").html(html);
}
//------------------------------------------------------------------------------------------------------------
function Muestra_Paginacion (totalPage) {
 
 $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
                
        $("#dynamic_field > tr").remove();    
        $("#dynamic_field").find("tr:gt(0)").remove(); 
        let fechai  =$('#fecha_noasis').val();
        let fechaf  =$('#fecha_noasis2').val();

        var usuario = $('#loggedusr').val();  
        var codperfil  =$('#codperfil').val();

        let pagedat = getData("../../clases/paginationhandlernoasist.php",{page:num,fechai,fechaf,codperfil,usuario});        

        let xx = jQuery.parseJSON(pagedat);        
        fillTrTableDoNotsAsist(xx);


        $(".content2").html("Page " + num); // or some ajax content loading...
    });

}

//---------------------------------------------------------------------------------------------------------
function fillTrTableNOAsistidos(data){
  var html = '';  
  var cont=0;
  for(var i = 0; i < data.length; i++){   
      cont=i+1;
      html += '<tr><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + data[i].nombres     +'</td>'+
              '<td align="right" >' + data[i].telfhabit   +'</td>'+ 
              '<td align="center" >' + data[i].Medicos      +'</td>'+                
              '<td align="center" >' + data[i].Historia    +'</td>'+
              '<td align="center" >' + data[i].UltimaAsistida     +'</td>'+ 
              '<td align="center" >' + data[i].observacion +'</td>'+ 
              '</tr>';
  }
  $("#tblnoasistidos").html(html);
}


$('#fecha_noasis2').change(function(){
    var fecha_noasis;
    var usuario    =$('#c_usuario').val();
    fecha_noasis   =$('#fecha_noasis').val();
    fecha_noasis2  =$('#fecha_noasis2').val();

    $('#fecha_noasis_1').val($('#fecha_noasis').val()) 
    $('#fecha_noasis_2').val($('#fecha_noasis2').val()) 

    $('#fecha_noasis_1').text($('#fecha_noasis').val()) 
    $('#fecha_noasis_2').text($('#fecha_noasis2').val()) 
    

    if (fecha_noasis!=='') {     
       getNoAsistidos(fecha_noasis,usuario,fecha_noasis2);
    };    
 });


$('#fecha_noasis').change(function(){
    var fecha_noasis;
    var usuario    =$('#c_usuario').val();
    fecha_noasis   =$('#fecha_noasis').val();
    fecha_noasis2  =$('#fecha_noasis2').val();

    $('#fecha_noasis_1').val($('#fecha_noasis').val()) 
    $('#fecha_noasis_2').val($('#fecha_noasis2').val()) 

    if (fecha_noasis2!=='') {     
       getNoAsistidos(fecha_noasis,usuario,fecha_noasis2);
    };    
 });


function getData(url,data,type){
  if (data==undefined) {
    data = {    }
  };
  if (type==undefined) {
    type = "GET"
  };
    return $.ajax({
        type: type,
        url: url,
        data : data,
        async: false
    }).responseText;

}
// // //     