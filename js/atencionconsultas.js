 var goid="";
 var g_id="";
 var codXmed;
 var isupdated=false;
 var lastid='';
 var lastid2='';
$(function(){

   var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');

if($("#fecha").val()==""){
   $("#fecha").val(hoy);
} 

$('[data-toggle="tooltip"]').tooltip()
// 'use strict'
$("#wait").hide(); 
$('#xtitulo').html('<b>Atención al Paciente</b>');

  var lastUser
  var elementconf = undefined;
  var counter=0;
  var idCreate="";
  var custId;
  var objPaciente;


  var date_input=$('input[name="fecha"]'); //our date input has the name "date"
  var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  $('#tbl-ctrl').on("dblclick", "td.record", function(){
    var element=$(this);

    
       var row_index = $(this).parent().index();
        

       var historia  = $("[name='record']")[row_index].innerHTML;
       var codclien  = $("[name='record']")[row_index].attributes.id.value;
 
    if($(this).hasClass("edit")){
        console.log('cool');
     }
    if (element.attr('field')=='record') {
        $('#'+element[0].id).prop('contenteditable', true );
        $('#'+element[0].id).addClass("selected");


        $('#'+element[0].id).attr('bgcolor','#7FCDCD');
        $('#'+element[0].id).prop('selected', true);
        $( '#'+element[0].id ).focus();
        $( '#'+element[0].id ).focus();
    }
    if (element.attr('field')=='nombres') {
      if($(this).hasClass("edit")){
        $('#'+element[0].id).prop('contenteditable', true );
        $('#'+element[0].id).addClass("selected");


        $('#'+element[0].id).attr('bgcolor','#7FCDCD');
        $('#'+element[0].id).prop('selected', true);
        $( '#'+element[0].id ).focus();
        $( '#'+element[0].id ).focus();  
      }

    }

    //ID DEL TR
    var trID = element.closest('tr').attr('id');
    emit(trID);

  });
 
    $(document).on("blur", ".selected", function(){ 
       $('.edit').prop('contenteditable', false );
       $(".selected").removeClass("selected");
       var element=$(this);
       if (element.attr('field')=='record') {
           $('#'+element[0].id).attr('bgcolor','#373a3c');
           var record = element[0].innerText;      
           var codclien=element.parents('tr').attr('id').toString();

           record=$.trim(record);
            
                      var datasave ={
             codclien : codclien
            ,record :record
            ,tipo : 'check'
           };

           //check que el record ya exista
              var xres = ajaxGen("../../clases/atencionupdaterecord.php",datasave)
           //
           if (xres==undefined) {
               var datasave ={
                   codclien : codclien
                  ,record :record
                  ,tipo : 'save'
               };

               var xres = ajaxGen("../../clases/atencionupdaterecord.php",datasave)
           }else{
            $('#alertrecord .test').text(' El paciente '+xres[0].nombres+' ya posee ese número de record ');
            $('#alertrecord').show()
           };
         };
         if (element.attr('field')=='nombres') {
      $('#'+element[0].id).attr('bgcolor','#373a3c');
           var nombres = element[0].innerText;      
           var codclien=element.parents('tr').attr('id').toString();

           nombres=$.trim(nombres);
            
           var datasave ={
             codclien : codclien
            ,nombres :nombres
           };
            var xres = ajaxGen("../../clases/atencionupdatenombres.php",datasave)



         }
    });


  $('.prioridad').change(function(event) {
      var element=$(this);
      var codclien=element.parents('tr').attr('id').toString();
      var codmedico = element.val().toString();
      var codconsulta = element.parents('tr').attr('campo').toString();
      var coditem = element.parents('tr').attr('producto').toString();
      var prioridad =0;
      var idfirstele= element.parent().parent().siblings(":first").attr('id')
      if($(this).prop('checked')){
         prioridad='1';      
         $("#"+idfirstele).addClass("bg-warning");
      }else{
         $("#"+idfirstele).removeClass("bg-warning");
      };


      var datasave ={
          codconsulta : codconsulta
         ,codclien : codclien
         ,coditems : coditem
         ,prioridad : prioridad
      };
      var xres = ajaxGen("../../clases/setprioridad.php",datasave)

    //ID DEL TR
    var trID = element.closest('tr').attr('id');
    emit(trID);
    

 })

 
  $(".sltmedico").change(function(){
      var element=$(this);
      var codclien=element.parents('tr').attr('id').toString();
      var codmedico = element.val().toString();
      var codconsulta = element.parents('tr').attr('campo').toString();
      var coditem ="";
      
      try{
        coditem = element.parents('tr').attr('producto').toString();
      }catch(err){
        
      }


      var fecha = $("#fecha").val();  
      if(fecha==""){
        fecha= new Date();
        fecha=formatDate(fecha);
      }

      var valtype="consultas"
      if (codconsulta=="07") {
          valtype="servicios"
      };
      var datasave ={
       codclien : codclien
      ,codmedico : codmedico
      ,valtype  : valtype
      ,fecha : fecha


    }
       var xres = ajaxGen("../../clases/atencionchangemedico.php",datasave)
   //ID DEL TR
    var trID = element.closest('tr').attr('id');
    emit(trID);
  })


 $(".sltconsulta").dblclick(function(){
   
   let id = $(this).closest('tr').attr('id')

   var row_index =$(this).closest('tr').index(); 

   var recordnumber =$('#'+id+'-'+row_index).text();

   

   if (recordnumber!=="") {

    //ID DEL TR
    var trID = $(this).closest('tr').attr('id');
    emit(trID);
     
    var data={
      fechacita :  $('#fecha').val()
     ,recordnumber : recordnumber
    }
      var res = ajaxGen('../../clases/checkpresente.php',data)
     // items= jQuery.parseJSON(res);
      if (res!==null && res!==undefined) {
         aFacturacion(recordnumber);
      };
      
   };;
 })

 $(".sltconsulta").change(function(){
      var element=$(this);
      var codclien=element.parents('tr').attr('id').toString();
      var codconsulta = element.val().toString();
      //var codconsulta = element.parents('tr').attr('campo').toString();
      var coditem ="";

      var fecha = $("#fecha").val();  
      if(fecha==""){
        fecha= new Date();
        fecha=formatDate(fecha);
      }
      
      var datasave ={
       codclien 
      ,codconsulta 
      ,fecha 
    }

    var xres = ajaxGen("../../clases/atencionchangetconsultas.php",datasave)
   //ID DEL TR
    var trID = element.closest('tr').attr('id');
    emit(trID);

  //alert(codconsulta);
  //atencionchangetconsultas.php
 })

$('.citar').click(function(){
  element=$(this);
  var idCreate = element.attr('id').toString()
  $('#idpaciente').val(idCreate); 
  trCodConsulta="";
  trCodItems="";
  trCodConsulta = element.parent().parent().attr('campo').toString();
  trCodItems = element.parent().parent().attr('producto').toString();
  obj=codesTiposConsultas(trCodConsulta,trCodItems);
  console.log(idCreate)
  $("#exampleModal").modal();
})

  $('.terapias').change(function(event) {
      var element=$(this);
      var codclien=element.parents('tr').attr('id').toString();
      var terapias = element.val().toString();
      var codconsulta = element.parents('tr').attr('campo').toString();
      var coditems = element.parents('tr').attr('producto').toString();
      var fecha_cita = getToDay();

      if ($('#fecha').val()!=="") {
         fecha_cita = $('#fecha').val();
      };
    var datasave ={

      codconsulta : codconsulta,
      codclien : codclien,
      fecha_cita : fecha_cita,
      coditems : coditems,      
      terapias : terapias
    }

    ajaxGen("../../clases/atencionupdateterapias.php",datasave);    

  })

  $('#tbl-ctrl').on('dblclick', 'tbody tr td.salio', function(event) {
    var codnumber = $(this).parent().attr('id') ;

   
    event.stopPropagation();
    element = $(this);
    var id = $( element).attr( "id" );    
    var codconsulta ="";     
    //ID DEL TR
    var trID = element.closest('tr').attr('id');    
    emit(trID);

    try {
        codconsulta = element.closest('tr').attr('campo') 
    }
      catch(err) {
    }     
    

    var asistido=0;
    

    var hora = new Date().toLocaleTimeString('en-US', { hour12: false, 
                                             hour: "numeric", 
                                             minute: "numeric", 
                                             second: "numeric"});
    
      elementx = document.getElementById($(".highlight").attr('id'));
      if($('#'+$(".highlight").attr('id')).hasClass("table-danger")){
           elementx.classList.remove("table-danger"); 
           $('#'+ $(this).parent().attr('id')).css("color","");          
            $('#name-'+ $(this).parent().attr('id')).css("color","");   
      }else{
          elementx.classList.add("table-danger");
          $('#'+ $(this).parent().attr('id')).css("color","black");
          $('#name-'+ $(this).parent().attr('id')).css("color","black");   
          asistido=1;
      }

    var codclien = $(".highlight").attr('id');//element.attr('id').toString();
    
    let codperfil =$('#codperfil').val();    
    let datesltd =$('#fecha').val();


    var datasave ={
     
        codclien,      
        asistido ,
        datesltd,
        codconsulta,
        codperfil,
        horain : hora
    }

    ajaxGen("../../clases/atencionupdatesalio.php",datasave);    

    
 })

//.php
  $('#tbl-ctrl').on('dblclick', 'tbody tr td.answered', function(event){

    //alert();

   var codnumber = $(this).parent().attr('id') ;

    let classnop=''
    event.stopPropagation();
    element = $(this);
    var id = $( element).attr( "id" );   

     if($("#"+id).hasClass("nop")){
        $("#"+id).removeClass('nop');
     // (this).addClass('highlight').siblings().removeClass('highlight');
    }else{
       $("#"+id).addClass('nop');
       classnop='n';
    }

    var codconsulta ="";     
    //ID DEL TR
    var trID = element.closest('tr').attr('id');    
    //emit(trID);

    try {
        codconsulta = element.closest('tr').attr('campo') 
    }
      catch(err) {
    }     
    

    var asistido=0;
    

    var hora = new Date().toLocaleTimeString('en-US', { hour12: false, 
                                             hour: "numeric", 
                                             minute: "numeric", 
                                             second: "numeric"});
    
      elementx = document.getElementById($(".highlight").attr('id'));
      if($('#'+$(".highlight").attr('id')).hasClass("table-info")){
          $('#'+ $(this).parent().attr('id')).css("color","");
      }else{
         $('#'+ $(this).parent().attr('id')).css("color","black");
          asistido=1;
      }

    var codclien = $(".highlight").attr('id');//element.attr('id').toString();
    
    let codperfil =$('#codperfil').val();    
    let datesltd =$('#fecha').val();

    emitnoanswered(trID);

    var datasave ={
     
        codclien,      
        asistido ,
        datesltd,
        codconsulta,
        codperfil,
        horain : hora,
        classnop
    }

       ajaxGen("../../clases/atencionupdateanswered.php",datasave);    
  });

//---------------------------
function emitnoanswered(id){
   var mensaje=id;
  try {
     socket.emit('noanswered',mensaje);
   } catch(err){
    
   }
}

  $('.asistido').change(function(event) {
       event.stopPropagation();
       event.preventDefault();

       var element = $(this);

       var row_index = $(this).parent().parent().parent().index();
       var col_index = $(this).parent().index();  

       var historia  = $("[name='record']")[row_index].innerHTML;
       var cdcl   = $("[name='record']")[row_index].attributes.id.value;
       var o=$(this).parent().parent().parent()  //AQUI ESTA EN EL TR PADRE

       var codclien = o.attr('id')  //CODIDO DEL CLIENTE

       var codconsulta = o.attr('campo'); // CODIGO DE LA CONSULTA

       var coditems    = o.attr('producto'); //CODIGO DEL PRODUCTO

       $("#exampleModal").attr("cconsulta-id",o.attr('campo')); //nuevo
       
       codXmed = $(".sltmedico")[row_index].value;
   
       goid = element.attr('oid');
       g_id = element.attr('id').toString();

       str = element.attr('id').toString().split("-");
       g_id=str[1];
  
       //var hasRecord = $.isNumeric(historia) ;
       var hasRecord = $.isNumeric(historia.substr(0,2)) ;	   
       var hasMedico = element.parent().parent().parent().children('.td_medico').children()[0].value
        if (hasMedico=='000') {
          $("#modal1_alert").modal();
        }
       if (!hasRecord || hasMedico=='000') {
          $("[oid="+goid+"]").trigger('click').prop('checked', false);  
          $("[oid="+goid+"]").trigger('click').prop('checked', false);    
          return;
       };

       var resp= $(this).prop('checked');
 
    

      //MODULO PARA COLOCAR LA HORA DE SALIDA AL MOMENTO DE ASISTIR
      try{
        if (resp) {
            var id = $( element).attr( "id" );
            id='s'+id+'-'+$( element).attr( "oid" );
            var hora = new Date().toLocaleTimeString('en-US', { hour12: false, 
                                             hour: "numeric", 
                                             minute: "numeric", 
                                             second: "numeric"});
            

            //ACTUALIZACION DE LA HORA DE SALIDA
            var datsave ={
                codconsulta : codconsulta,
                codclien : codclien,
                coditems : coditems,
                asist : 1,
                horaout : hora
            }         

            //ACTUALIZACION DE LA HORA DE SALIDA
    ajaxGen("../../clases/atencionupdatetimeout.php",datsave);    

    // var a=$('#'+id).siblings(0);
    //         var b=a.children(":nth-child(1)");
    //         b[0].innerHTML=hora;
            $('#'+$(this).parent().parent().siblings('.leave_time').children(0).children(0).attr('id')).siblings(0).children(":nth-child(1)")[0].innerHTML=hora;

            $("#"+id).trigger('click').prop('checked', true);
            $("#"+id).trigger('click').prop('checked', true); 

      }else{
        

          //ACTUALIZACION DE LA HORA DE SALIDA
          var datsave ={
            codconsulta : codconsulta,
            codclien : codclien,
            coditems : coditems,
            asist : 0,
            horaout : ''
          }
          //ACTUALIZACION DE LA HORA DE SALIDA
    ajaxGen("../../clases/atencionupdatetimeout.php",datsave);   

      var id = $( element).attr( "id" );
          id='s'+id+'-'+$( element).attr( "oid" );
          var a=$('#'+id).siblings(0);
          var b=a.children(":nth-child(1)");
          b[0].innerHTML="En Consulta"; 
    };

    


}
catch(err){
      
    }
    //FIN  MODULO

    var asistido;
    var noAsistido;
    
    var fecha_cita ='';
  
    fecha_cita = getToDay();
    let codperfil =$('#codperfil').val();
    if (codperfil=='01' || codperfil=='06') {
       fecha_cita =$('#fecha').val();
    };

    var usuario = $('#loggedusr').val();
    var idUsr = element.closest('tr').children('td:first').text();
  
   
    var record = historia;
 
    trCodConsulta="";
    trCodItems="";

    trCodConsulta=codconsulta;
    trCodItems=coditems;

    if (resp==true) {
        asistido =3;
        noAsistido = 0;
    }else{
        asistido =0;
        noAsistido = 0;
    }
    var datasave ={

      codconsulta : codconsulta,
      codclien : codclien,
      fecha_cita : fecha_cita,
      coditems : coditems,
      asistido : asistido,
      noAsistido : noAsistido,
      usuario : usuario
    }

    ajaxGen("../../clases/atencionasistencia.php",datasave);    

    console.log()
    if (typeof elementconf== 'undefined') {
        elementconf = this
  }

  if (resp) {
       //$('#idpaciente').val(idUsr);
      $('#idpaciente').val(record); 
      $("#exampleModal").modal();
  };

       //ID DEL TR
       var trID = element.closest('tr').attr('id');
       emit(trID);
})

  $("#sltconsultas").change(function(){
     $('#submit').click();
  })

    $("#fecha").change(function(){
      var validDate = new Date( $("#fecha").val()).toString();
      if (validDate!=="Invalid Date") {
        $('#submit').click();
      };
      
  })
    
  $("#confirmar").change(function(){
     $('#submit').click();
  })


$('#tbl-ctrl').on('click', 'tbody tr', function(event) {
    $(this).addClass('highlight').siblings().removeClass('highlight');
});

  $('.datepicker').datepicker();
  $( "form #cbfecha" ).change(function() {
    if ($(this).prop('checked')==true){           
          $('#fecha').removeAttr( "disabled" );
      }else{
          $('#fecha').attr( "disabled","true" );        
      }
  });

  $('#tbl-ctrl').on('dblclick', 'tbody tr td.consulta', function(event) {


   var recordnumber =  $(this).attr('cod').toString();
   if (recordnumber!=="") {

    //ID DEL TR
    var trID = $(this).closest('tr').attr('id');
    var sertype=$(this)[0].innerText
    emit(trID);
     
    var data={
      fechacita :  $('#fecha').val()
     ,recordnumber : recordnumber
    }
      var res = ajaxGen('../../clases/checkpresente.php',data)
     // items= jQuery.parseJSON(res);
      if (res!==null && res!==undefined) {
         aFacturacion(recordnumber,sertype);
      };
      
   };
   
 })
  

  $('#tbl-ctrl').on('dblclick', 'tbody tr td.cdcliente', function(event) {
    var codnumber = $(this).parent().attr('id') ;
    getCodCl(codnumber);
 })
  

})


function aFacturacion(codclien,servicio){
   var postData = {
       codclien : codclien
      ,urlevento : 'atencion'
    } 
    if (servicio=="CEL MADRE") {
      $.redirect("../../vistas/suero/invoice.php",postData ); 
    } else {
      $.redirect("../../vistas/invoservices/invoice.php",postData );   
    }
    

  }


  //tipo de consultas ----------------------------------------------------------
  var codConsultas = $("#sltconsultashd").val();
  if (codConsultas=='') {
    codConsultas='2';
  };
  var ajxmed = $.post( "../../clases/tconsultas.php",{}, function(data) { 

  var items="";
  var options="";
  items= jQuery.parseJSON(data);

  for (var i = 0; i < items.length; i++) {
    if(codConsultas==items[i].id){
        options+="<option selected value='"+items[i].id+"'>"+items[i].Descripcion+"</option>"; 
    }else{
        options+="<option value='"+items[i].id+"'>"+items[i].Descripcion+"</option>"; 
    }
  }
  
    $("#sltconsultas").html(options);      
    })
    .done(function() {       
    })
    .fail(function() {     
    })
    .always(function() {
    });


function ajaxGen(file,data){
    var res;
    $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: file,
            data:data, 
            success:  function(respuesta){                        
                //console.log(respuesta);

                if (respuesta!=="") {
                items= jQuery.parseJSON(respuesta);

                if (typeof items!== 'undefined' && items!==null ) { 
                   if(items.length>0){
                      res=items;
                   }
                }
                };
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

    return res;
}


function getToDay(){
  var hoy = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');
  return hoy;
}

 $('#myModal').on('show.bs.modal', function (event) {

   var idCreate= custId.id
   if (idCreate.indexOf("-")>-1) {
      $('#idcl').val(idCreate);
      $('#phonecl').val(idCreate);    
   } else if($.isNumeric( idCreate )){
      $('#recordn').val(idCreate);
   }else{
      $('#apellido').val(idCreate);
   };
   
    
   $('#crtnewpaciente').click(function(){
        $("#myModal").modal('hide');
        $("#briefModal").modal('show');       
   })

  $( '#closebtn' ).click(function() {
      $('#idpaciente').val("");    
  });

  $( '#closecl' ).click(function() {
      $('#idpaciente').val("");    
  });
 
  $("#phonecl").inputmask({"mask": "999-999-9999"});
  $("#idcl").inputmask({"mask": "999-999-9999"});
  $("#idcl").on("blur",function(){
    if ($("#idcl").val()!=="") {
       $("#phonecl").val($("#idcl").val());
    };
  });


  $('#savecl').confirm({
    text: "¿Seguro de Guardar?",
    title: "Confirmación requerida",
    confirm: function(button) {
          nombre =  $('#name').val(); 
    apellidos =  $('#apellido').val(); 
    nombres =  $('#apellido').val(); 
    cedula =  $('#idcl').val(); 
    telefono =  $('#phonecl').val(); 
    recordn=  $('#recordn').val(); 

    if(nombre=="" || apellidos=="" || cedula=="" || telefono=="" || recordn==""){
      return;
    }

    var  fields={
      nombre :nombre,
      apellidos : apellidos,
      nombres : nombres,
      cedula : cedula,
      telefono,
      recordn
    }

    $.ajax({
    async:false,    
    cache:false,   
    dataType:"html",
    type: 'POST',   
    url: "../../clases/pacientesbrief.php",
    data:fields, 
    success:  function(respuesta){   
        
       $('#briefModal').modal('hide');
       $("#exampleModal").modal();
       $('#idpaciente').val(recordn); 
    },
    beforeSend:function(){},
    error:function(objXMLHttpRequest){}
  });
    $('#idpaciente').val(recordn);  
  

    },
    cancel: function(button) {
        // nothing to do
    },
    confirmButton: "Seguro!",
    cancelButton: "No",
    post: true,
    confirmButtonClass: "btn-success",
    cancelButtonClass: "btn-default",
    dialogClass: "modal-dialog modal-lg" // Bootstrap classes for large modal
});

  //  $('#savecl').click(function(){
    
  //   nombre =  $('#name').val(); 
  //   apellidos =  $('#apellido').val(); 
  //   nombres =  $('#apellido').val(); 
  //   cedula =  $('#idcl').val(); 
  //   telefono =  $('#phonecl').val(); 

  //   if(nombre=="" || apellidos=="" || cedula=="" || telefono=="" ){
  //     return;
  //   }

  //   var  fields={
  //     nombre :nombre,
  //     apellidos : apellidos,
  //     nombres : nombres,
  //     cedula : cedula,
  //     telefono
  //   }

  //   $.ajax({
  //   async:false,    
  //   cache:false,   
  //   dataType:"html",
  //   type: 'POST',   
  //   url: "../../clases/pacientesbrief.php",
  //   data:fields, 
  //   success:  function(respuesta){   
        
  //      $('#briefModal').modal('hide');
  //      $("#exampleModal").modal();
  //      $('#idpaciente').val(cedula); 
  //   },
  //   beforeSend:function(){},
  //   error:function(objXMLHttpRequest){}
  // });
  //   $('#idpaciente').val(cedula);  
  //  })

 })

//*******************
//** STARTS MODAL  **
//*******************

  $('#exampleModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('whatever') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    $(".dividassoc").hide();
      if($("#fgfechacita").hasClass("has-error")){
               $("#fgfechacita").removeClass("has-error");
               $("#erroralert").hide();
         }

    try {
        if ($('#idpaciente').val()!=="") {
           var xId=$('#idpaciente').val();
           findOutPacient(xId,'r')
           findObservacion(xId,trCodConsulta,$('#fecha').val(),'1');
        }else{
          $('#messagetext').val("");
        }
    }
    catch(err) {
        console.log(err.message);
    }

   $("#closeapm").click(function(){
     trCodConsulta="";
     trCodItems="";
     $('#idpaciente').val("")
     $('#idpaciente').val('')
     console.log(goid);
     $("[oid="+goid+"]").trigger('click').prop('checked', false);
     $("[oid="+goid+"]").trigger('click').prop('checked', false);


     
     var sid='s'+g_id+'-'+goid;
     $("#"+sid).trigger('click').prop('checked', false);
     $("#"+sid).trigger('click').prop('checked', false);

     $('#messagetext').val("");
 
     codXmed=undefined;

   })

    var modalf = $(this)
    $( "#idpaciente" ).focus();

    $('input:visible:enabled:first', this).focus();
    modalf.find('input:text')[0].focus();
    modalf.find("input:visible:first").focus();

    var objPaciente
    var observ= $('#messagetext').val()
    var usuario = $('#loggedusr').val()

    
    var fecha_input=$("#fechaNewApmt"); //our date input has the name "date"
    
    // var contai=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    // fecha_input.datepicker({
    // format: 'mm/dd/yyyy',
    // contai: contai,
    // todayHighlight: true,
    // autoclose: true,
    // })

    $( "#fechaNewApmt" ).change(function() { 

        //Fecha de la Cita
        var fecha = $('#fechaNewApmt').val()   

        //Hoy
        var hoy = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');
        
        var fechaact = new Date(hoy);
        var fechacita = new Date(fecha);
        var valday=true
        if (fechacita<fechaact) {
            valday=false;
            $("#fgfechacita").addClass("has-error");
            return;
        }else{
           if($("#fgfechacita").hasClass("has-error")){
              $("#fgfechacita").removeClass("has-error");
            }
        }

        today = new Date(fecha)
        dayIndex = today.getDay()
         
         //VALIDACION DE LOS DIAS DOMINGOS
        if (dayIndex==0 || valday==false ) {
           $("#fgfechacita").addClass("has-error");
        }else{
           if($("#fgfechacita").hasClass("has-error")){
              $("#fgfechacita").removeClass("has-error");
            }
        }

        //Valida que la cita no exceda los 36 dias
        var today = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');

        var date1 = new Date(today);
        var date2 = new Date(fecha);
        var timeDiff = Math.abs(date2.getTime() - date1.getTime());
        var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
        if(diffDays>35){
           $("#fgfechacita").addClass("has-error");
        }

       //HOLIDAY DAY VALIDATION
       var holiday=false;
       var data ={
            q : fecha
       }
       var holiday = ajaxGen("../../clases/controlholidays.php",data);
       
       if(holiday!==undefined){
          $("#fgfechacita").addClass("has-error");          
          $( "#erroralert" ).html( "<b>"+fecha+"</b> Holiday day" );
          $("#erroralert").show();
          return;
        }else{
            $("#erroralert").hide();
            if($("#fgfechacita").hasClass("has-error")){
              $("#fgfechacita").removeClass("has-error");
         }
        }

        
    });

    $('#idpaciente').keyup(function(e){
        if (e.which == 13) {
            var id=$('#idpaciente').val()
            tecla=13
            findOutPacient(id);        
        }
  })


  function findOutPacient(id,r){
            var codMedOculto = $("#medicohd").val();
            var datosajx;
            if (r!==undefined) {
                datosajx ={
                   q : id
                  ,r : r
                }
            }else{
              var isnu=$.isNumeric(id);
              if ( isnu===true ) {
                   datosajx ={
                     q : id
                    ,r : 'r'
                }
              }else{
                 datosajx ={
                     q : id                
                 } 
              };               
            };

            var ajxmed = $.post( "../../clases/atenciobuscarpaciente.php",datosajx, function(data) { 
            ////console.log(data)
            var items="";
            var options="";
            items = jQuery.parseJSON(data);
            modal.find('.modal-title').text('Nueva cita ' )
            if (typeof items!= 'undefined' && items!==null) {      
               // found = true;
              if(items.length>0){   
                 modal.find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
                 vav( items[0].codclien);
                 
                 

                 if (codXmed!==undefined) {
                    loadMedicos(codXmed)   
                 }else{
                    loadMedicos(items[0].codmedico)
                 }                 ;
                 
                 codXmed=undefined;

                 custId = new ID($(''));
                  $(".dividassoc").hide();
          
          //OBSERVACIONES
                findObservacion(items[0].codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
        
                  if(items.length>1){  
                    $(".dividassoc").show();
                    loadIdsAsociados(items)
                 }
               }else{
                 modal.find('.modal-title').text('Nueva cita ' )
                  
               }
               }else{
                  custId = new ID($('#idpaciente').val());
                  $("#exampleModal").modal('hide');
                  $("#myModal").modal();  //ALERTA
               }
            })
                .done(function() {
                
                })
                .fail(function() {
                  
                })
                .always(function() {
                
                }); 
  }

    $('#tiposervicio').change(function(event) {    
      $("#rowtipterapia").hide();
            if($('#tiposervicio').prop('checked')){
               // loadLaser()  
            }else{
               // LoadSueros()             
            }
     })


     $("#tiposervicio").prop("disabled",true);
     $('#tipocita').change(function(event) {
     $("#rowtipterapia").hide();
       var resp= $(this).prop('checked');
       if(resp){
            loadConsultas()
            $("#tiposervicio").prop("disabled",true);
            //console.log( $('#tiposervicio').prop('checked'));
       }else{
            $("#tiposervicio").prop("disabled",false);
            if($('#tiposervicio').prop('checked')){
               // loadLaser()  
            }else{
               // LoadSueros()             
            } 
       }
     
     })

    //Medicos Change----------------------------------------------------------
    $('#medico').change(function(event){
      event.preventDefault()
      var codmedico = $(this).val();     
    })
    //ends Medicos Change----------------------------------------------------------
  
   //save button   
    $('#save').click(function(){})
     
    //- AUTO CARGA DE LOS CONSULTAS
   
    loadConsultas()
 

    try {
        if(trCodConsulta!==undefined){

         if ($("#exampleModal").attr('cconsulta-id')!=='') {
            trCodConsulta = $("#exampleModal").attr('cconsulta-id');  
            $("#exampleModal").attr("cconsulta-id",''); 
         }

            $('#tipocita').prop('checked', false).change()
            if ( trCodConsulta=='07') {
                if (trCodItems!==undefined){
                      if (trCodItems.substring(0, 2)=="TD") {
                         //loadLaser(trCodConsulta+trCodItems);
                       }else if(trCodItems.substring(0, 2)=="ST"){
                         $('#tiposervicio').prop('checked', false).change()                        
                        // LoadSueros(trCodConsulta+trCodItems);
                       }
                }
            }else{
               $('#tipocita').prop('checked', true).change();           
               loadConsultas('03'); 
            }
        }


    }catch(err) {
         
    }

    //- AUTO CARGA DE MEDICOS
    loadMedicos()

    var modal = $(this)
    modal.find('.modal-title').text('Nueva cita ' + recipient)
    //modal.find('.modal-body input').val(recipient)

    //2018 11 06 nueva estructura
    $('#'+$('.tipo_citas >.active')[0].children[0].id).parent().removeClass('active') // remove  class
    if ($('#sltconsultas').val()=='2') {
       $('#option1').parent().addClass('active') // add class
       selectCitas('option1');    
    }else if ($('#sltconsultas').val()=='6') {
      $('#option2').parent().addClass('active')
      selectCitas('option2');
    }else if ($('#sltconsultas').val()=='7') {
      $('#option3').parent().addClass('active')
      selectCitas('option3');
    }
 
  })

//*******************
//** ENDS MODAL  **
//*******************

  //Medicos LOAD--------------------------------------------------------
    function loadMedicos(codMedOculto){
         
    if (typeof codMedOculto === "undefined" || codMedOculto === null) { 
          var xmed= $("#citashd").val();
          if(xmed!==null  || typeof codMedOculto === "undefined" ){
             codMedOculto = xmed;
          }else{
             codMedOculto = ""; 
          } 
      }

        var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
           
        for (var i = 0; i < items.length; i++) {
            if (codMedOculto==items[i].codmedico){
                  options+="<option selected value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
              }else{
                  options+="<option value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
              }           
        }
          $("#medico").html(options); 
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          });
        }
 ////ends Medicos LOAD
 function loadConsultas(cod3=''){
    if (cod3=='') {
       cod3='03';
    };
     var codMedOculto = $("#citashd").val();
     var ajxmed = $.post( "../../clases/consultas.php",{}, function(data) { 
     var items="";
     var options="";
     var code="";
     items= jQuery.parseJSON(data);
    
     for (var i = 0; i < items.length; i++) {
         code = items[i].codcons;
         if (cod3==code){
               options+="<option selected value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
           }else{
               options+="<option value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
           }           
     }
       $("#citas").html(options);
       })
       .done(function() {
       })
       .fail(function() {
       })
       .always(function() {
       }); 
   }
    //END CONSULTAS 1-----------------------------------------------
    //SUERO LOAD
    function LoadSueros(cod3=''){

        var codMedOculto = $("#citashd").val();
        var ajxmed = $.post( "../../clases/suero.php",{}, function(data) { 
        var items="";
        var options="";
        var code="";
        items= jQuery.parseJSON(data);
           
        for (var i = 0; i < items.length; i++) {
            code = items[i].codconsulta;
            if (cod3==code){
                  options+="<option selected value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }else{
                  options+="<option value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }           
        }
          $("#citas").html(options);
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          }); 
   }
   //-- END SUERO
   //--LASER-----------
   function loadLaser(cod3=''){
     $("#rowtipterapia").show();
        var codMedOculto = $("#citashd").val();
        var ajxmed = $.post( "../../clases/laser.php",{}, function(data) { 
        var items="";
        var options="";
        var code="";
        items= jQuery.parseJSON(data);
       
        for (var i = 0; i < items.length; i++) {
            code = items[i].codconsulta;
            if (cod3==code){
                  options+="<option selected value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }else{
                  options+="<option value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
            }           
        }
          $("#citas").html(options);
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          }); 
   }
   //--ENDS LASER--------------------------------------------------------------------

class ID{
  constructor(id){
    this.id = id;
  }
}

class codigoCliente{
  constructor(codclien){
    this.codclien = codclien;
 }
}

//11-7-2018

  // BLOQUEO
     function loadLista(arg){
     $("#rowtipterapia").show();
        var codMedOculto = $("#citashd").val();
        let optionsel = 0;
        optionsel = $("#sltconsultas").val();

        let param ={
           arg : arg
        }

        var ajxmed = $.post( "../../clases/laser.php",param, function(data) { 
        var items="";
        var options="";
        var code="";
        items= jQuery.parseJSON(data);
       
        for (var i = 0; i < items.length; i++) {
            code = items[i].codconsulta;
            // if (cod3==code){
            //       options+="<option selected value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
            //   }else{
                  options+="<option value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
            // }           
        }
          $("#citas").html(options);
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          });  
   }
  // END BLOQUOE

  $('.tipo_citas').on("change", function(){
      var ele=$(this);
      eletdc1=$('.tipo_citas >.active')[0].children[0].id ;
      console.log(eletdc1);      
      selectCitas(eletdc1);
      
      //remove class active
      //$('#'+$('.tipo_citas >.active')[0].children[0].id).parent().removeClass('active')
      // add class
      //$('#option2').parent().addClass('active')
  });

 
function selectCitas(eletdc1){
      if(eletdc1=="option1"){ // CONSULTAS
        loadConsultas();       
         $("#rowtipterapia").hide();
      }else if(eletdc1=="option2"){ //BLOQUEO
        loadLista('BL');
         $("#rowtipterapia").hide();
      }else if(eletdc1=="option3"){ //Cel madre
        loadLista('CM');
         $("#rowtipterapia").hide();
      }
}
//11-7-2018

$('#save').click(function(){

  var fecha = $('#fechaNewApmt').val()
  today = new Date(fecha)
  dayIndex = today.getDay()

  if(!$("#fgfechacita").hasClass("has-error")){
     
     if($("#fgfechacita").hasClass("has-error")){
        //$("#fgfechacita").removeClass("has-error");
        return;
     }

     var today = new Date().toLocaleDateString('en-US', {  
      month : 'numeric',
      day : 'numeric',        
      year : 'numeric'
      }).split(' ').join('/');

      var usuario = $('#loggedusr').val()
      var observ = $('#messagetext').val()           
      var medico = $( "#medico" ).val();
      var cita = $( "#citas" ).val();
      var prod = $( "#citas" ).val();
      var coditem ="" 
      if(cita.length>2){
         var cita = cita.substring(0,2);
         var coditems  = prod.substring(2);
      }         
      
      var Check2=1
      // if ( $('#tipocita').prop('checked')){
      //   Check2=0
      // }
      
      var Option2 = 0
      var Option3 = 1
      // if ( $('#tiposervicio').prop('checked')){
      //   Option2 = 1
      //   Option3 = 0
      // }

      if(fecha==""){
          $("#fgfechacita").addClass("has-error");          
          $( "#erroralert" ).html( "<b>Fecha blanco</b>" );
          $("#erroralert").show();
          return;
      }else{
            $("#erroralert").hide();
            if($("#fgfechacita").hasClass("has-error")){
               $("#fgfechacita").removeClass("has-error");
         }
      }

try   {
      if(objPaciente===undefined || objPaciente=="" ){
            $("#idpaciente").addClass("has-error");          
            $( "#erroralert" ).html( "<b>Id en blanco blanco</b>" );
            $("#erroralert").show();
            return;
      }else{
            $("#erroralert").hide();
            if($("#idpaciente").hasClass("has-error")){
               $("#idpaciente").removeClass("has-error");
            }
      }

      if(objPaciente.codclien==undefined){
          codclien = objPaciente;
      }else{
        var codclien = objPaciente.codclien;      
      }
      
      }catch(err) {
         
    }

      if(fecha=="" || codclien===undefined || codclien=="" ){
        return;
      }

        let hoy = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');
        
        let fechaact = new Date(hoy);
        let fechacita = new Date(fecha);        
        if (fechacita<fechaact) {
            valday=false
            $("#fgfechacita").addClass("has-error");
            return;
        }else{
           if($("#fgfechacita").hasClass("has-error")){
              $("#fgfechacita").removeClass("has-error");
            }
        }
    
      var mls=0;
      var hilt=0;

      if ($('#gethilt').val()!=''){
        hilt=$('#gethilt').val();
      }

      if ($('#getmls').val()!=''){
        mls=$('#getmls').val();
      }

      if (cita=='07') {
        cita='03';
      } 

      if (cita.length==0) {
        cita='03';
      };

      if (cita== null) {
        cita='03';
      } 


      if (  $('#option1').parent().hasClass('active')  ) {
          Check2=0;
          Option2 = 1;
          Option3 = 0;
          cita=$('#citas').val();
      }else  {
          cita='07';
      }

      var datasave ={
        fecha_cita : fecha
      , codclien : codclien
      , codmedico : medico
      , check2 : Check2
      , option2 : Option2 
      , option3 : Option3
      , codconsulta : cita
      , coditems : coditems
      , observ : observ
      , valtype  : "save-data"
      , usuario  : usuario
      , hoy : today
      , mls : mls
      , hilt : hilt

    }
       var xres = ajaxGen("../../clases/atencionsave.php",datasave)

       $('#exampleModal').modal('hide');
       //$('#submit').click();
  }else{
     $("#fgfechacita").addClass("has-error");         
  }
   $('#idpaciente').val("")
    trCodConsulta="";
    trCodItems="";
   //$('#submit').click();
   })

function vav(codclien){
   objPaciente = new codigoCliente(codclien);
}

var spasis =0;
var spcita =0;

if ($('#spnasistidos').text()!=="") {
   spasis = parseInt($('#spnasistidos').text().match(/\d+/).toString()) ;
};

if ($('#spncitados').text()!=="") {
  spcita = parseInt($('#spncitados').text().match(/\d+/).toString()); 
};

if (spcita!==0 || spasis!==0) {

  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['', ''],
      ['Citados',  spcita],
      ['Asis...',  spasis],
    ]);

  var options = {
    legend: 'none',
    pieSliceText: 'label',
    title: 'Citados / Asistidos',
    pieStartAngle: 100,
    slices: {
        0: { color: 'teal' },
        1: { color: '#16a085' }
      }
  };

   // var chart = new google.visualization.PieChart(document.getElementById('piechart'));
   // chart.draw(data, options);
  }

}; 


//***********************************************
function loadIdsAsociados(items){    
 var options=""; 
 let codmedico='000';
    for (var i = 0; i < items.length; i++) {    
        if (items[i].codmedico!=undefined && items[i].codmedico!='') {
            codmedico=items[i].codmedico;
        }else{
           codmedico='000';
        }; 
        options+="<option  cdmedico="+codmedico+" value='"+items[i].codclien+"'>"+items[i].nombres+"</option>";  
    }
      
  $("#idassoc").html(options);

  var $label = $('#nameasoc').find('.text'),
  $badge = $('#nameasoc').find('.badge'),
  $count = Number($badge.text())

  $badge.text(items.length);   
  $("#nameasoc").addClass("active"); 
  
//OBSERVACIONES
   findObservacion(items[0].codclien,$( "#citas" ).val(),$('#fecha').val(), '2');  

}


    $('#idassoc').change(function(event){
      event.preventDefault()
      var element = $(this).find('option:selected');
      var codmedico =  element.attr("cdmedico");
      var codclien =  element.val();
      var nombres = element.text();
      $("#citashd").val(codmedico);
      objPaciente = codclien;
      loadMedicos(codmedico)     
      $('#exampleModal').find('.modal-title').text('Nueva cita  a  ' + nombres)
    //OBSERVACIONES
       findObservacion(codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
   })

function codesTiposConsultas(c,p){
  var o = this;
  o.codeConsulta = c;
  o.codeItems = p;
}

   $('[data-toggle="tooltip"]').tooltip(); 

$('#confirmado').click(function(){
   $('#xconfirm').val('1');    
   $('#submit').click();
})

$('#btnasis').click(function(){
   $('#xasist').val('1');  
   $('#submit').click();
})

$('#btnnuev').click(function(){
   $('#xnuevos').val('1');  
   $('#submit').click();
})

$('#btn_atendidos').click(function(){
   $('#x_atendidos').val('1');  
   $('#submit').click();
})



$('#btnctrl').click(function(){
   $('#xcontrol').val('1');  
   $('#submit').click();
})

$('#spncitados').click(function(){
   $('#submit').click();
})

$('#btnarrived').click(function(){
  $('#xarrived').val('1');  
  $('#submit').click();
})

$('#btn_sinasistir').click(function(){
  $('#x_sinasist').val('1');  
  $('#submit').click();
})

 $("#tbl-ctrl").colResizable();

 function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}


function checkBoxClick(){
    if($(this).is(':checked')){
      //  alert("Is checked!");
    }
}

//COLUMNAS DE HORA DE ENTRADA Y SALIDA
  $('.enconsulta').change(function(event) {
    event.stopPropagation();
    element = $(this);
    var id = $( element).attr( "id" );
    var resp= $(this).prop('checked');
     
    //ID DEL TR
    var trID = element.closest('tr').attr('id');

    var hora = new Date().toLocaleTimeString('en-US', { hour12: false, 
                                             hour: "numeric", 
                                             minute: "numeric", 
                                             second: "numeric"});
                                             
     var sid='s'+id;
    $("#"+sid).on("change", checkBoxClick);
    if(resp){
      var a=$('#'+id).siblings(0);
      var b=a.children(":nth-child(1)");
      b[0].innerHTML=hora;

      lastid=sid;
      lastid2=id;

      $("#"+sid).trigger('click').prop('checked', true);
      $("#"+sid).trigger('click').prop('checked', true); 


//
    var codclien = element.attr('id').toString();
    var codconsulta =""; 
    
    try {
    codconsulta = element.attr('campo').toString();
    }
      catch(err) {
    } 
    
    var coditems ="";

    try {
      coditems = element.attr('producto').toString();
    }
    catch(err){
      
    }
    
    var datasave ={
      codconsulta : codconsulta,
      codclien : codclien,
      coditems : coditems,
      asistido : 1,
      horain : hora
    }

    ajaxGen("../../clases/atencionupdatetimein.php",datasave);    


    }
     else{
   
      $("#"+sid).trigger('click').prop('checked', false);
      $("#"+sid).trigger('click').prop('checked', false);

      //
      var codclien = element.attr('id').toString();
      var codconsulta =""; 
    
      try {
        codconsulta = element.attr('campo').toString();
      }
       catch(err) {
      } 
    
    var coditems ="";

    try {
      coditems = element.attr('producto').toString();
    }
    catch(err){
      
    }
    
    var datasave ={
      codconsulta : codconsulta,
      codclien : codclien,
      coditems : coditems,
      asistido : 0,
      horain : hora
    }

    ajaxGen("../../clases/atencionupdatetimein.php",datasave);    

  
     }



    console.log($( element).attr( "id" ));
    console.log($( element).attr( "data-on" ));
    // document.getElementById($( element).attr( "id" )).setAttribute("data-on", "YES")
    // document.getElementById($( element).attr( "id" )).setAttribute("data-off", "NO!")
     console.log( element );


   
    var codclien = element.attr('id').toString();
    var codconsulta ="";

     emit(trID);

         //dob
    $('#dbomodal').modal('show');
    let patient = $('#'+document.querySelector('.highlight').id).children(".cdcliente").text();
    $('#nombrepaciente').val(patient) 
    let cdcdl=codclien.split('-')[0];
    //dob


   });
// FIN DE ENTRADA Y SALIDA
// GRAFICA DE MEDICOS

$('#piechart').click(function(){

   //Hoy
  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');
        
   var fecha = $("#fecha").val();  

   if (fecha=="") {
    fecha=hoy;
   };

   var ajxmed = $.post( "../../clases/atencionatendidosxmedico.php",{ fecha_cita : fecha }, function(data) { 
  
   var items="";
   var options="";
    items = jQuery.parseJSON(data);
 
    if (typeof items!= 'undefined' && items!==null) {      
  
       if(items.length>0){ 
          var  arr1=[],arr2=[],arrprincipal=[],arrGraph=[] ;

          arr2.push('');
          arr2.push('');
          arrGraph.push(arr2);
          arrprincipal.push(arr2);

          for (var i = 0; i < items.length; i++) {
              arr1=[];
              arr2=[];

              arr2.push(items[i].Medico);
              arr1.push(items[i].Medico);
              var amount=parseFloat(items[i].nAsistidos);

              tmp = {
                 'v': amount
              }
              arr1.push(tmp)
              arr2.push(amount);
        
              arrprincipal.push(arr1);
              arrGraph.push(arr2);
          }
   
          google.charts.load("current", {packages:["corechart"]});
          google.charts.setOnLoadCallback(drawChart);
          function drawChart() {
              var data = google.visualization.arrayToDataTable(arrGraph);

              var options = {
                  legend: 'none',
                  pieSliceText: 'label',
                  title: 'Pacientes Atendidos x Médicos',
                  pieStartAngle: 100,
                  slices: {
                     0: { color: 'teal' },
                    1: { color: '#16a085' }
                  }
              };

              var chart = new google.visualization.PieChart(document.getElementById('piechart'));
    chart.draw(data, options);
  }

                 } else{}
              }else{
                 
               }
             })
                 .done(function() {
                
                 })
                 .fail(function() {
                  
                 })
                 .always(function() {
                
                 }); 
})

//COLUMNAS DE PRESENTE
  $('.llego').change(function(event) {
    event.stopPropagation();
    element = $(this);
    var id = $( element).attr( "id" );
    var resp= $(this).prop('checked');

    //ID DEL TR
    var trID = element.closest('tr').attr('id');
    emit(trID);
    

    var hora = new Date().toLocaleTimeString('en-US', { hour12: false, 
                                             hour: "numeric", 
                                             minute: "numeric", 
                                             second: "numeric"});
                                             
    // var sid='s'+id;
    // $("#"+sid).on("change", checkBoxClick);
    if(resp){
      // var a=$('#'+id).siblings(0);
      // var b=a.children(":nth-child(1)");
      // b[0].innerHTML=hora;

      // $("#"+sid).trigger('click').prop('checked', true);
      // $("#"+sid).trigger('click').prop('checked', true); 


    var codclien = element.attr('id').toString();
    var codconsulta =""; 
    
    try {
    codconsulta = element.attr('campo').toString();
    }
      catch(err) {
    } 
    
    var coditems ="";

    try {
      coditems = element.attr('producto').toString();
    }
    catch(err){
      
    }
    let codperfil =$('#codperfil').val();
    let datesltd =$('#fecha').val();
    var datasave ={
      codconsulta : codconsulta,
      codclien : codclien,
      coditems : coditems,
      asistido : 1,
      horain : hora,
      codperfil,
      datesltd
    }

    ajaxGen("../../clases/atencionupdatearrived.php",datasave);    

      $(this).siblings(0).children(":nth-child(1)")[0].innerHTML='P-'+hora
    }
     else{
   
      // $("#"+sid).trigger('click').prop('checked', false);
      // $("#"+sid).trigger('click').prop('checked', false);

      //
      var codclien = element.attr('id').toString();
      var codconsulta =""; 
    
      try {
        codconsulta = element.attr('campo').toString();
      }
       catch(err) {
      } 
    
    var coditems ="";

    try {
      coditems = element.attr('producto').toString();
    }
    catch(err){
      
    }
    let codperfil =$('#codperfil').val();
    let datesltd =$('#fecha').val();
    var datasave ={
      codconsulta : codconsulta,
      codclien : codclien,
      coditems : coditems,
      asistido : 0,
      horain : hora,
      codperfil,
      datesltd
    }

    ajaxGen("../../clases/atencionupdatearrived.php",datasave);    

  
     }

    //dob
    // $('#dbomodal').modal('show');
    // let patient = $('#'+document.querySelector('.highlight').id).children(".cdcliente").text();
    // $('#nombrepaciente').val(patient) 
    // let cdcdl=codclien.split('-')[0];
    //dob


    console.log($( element).attr( "id" ));
    console.log($( element).attr( "data-on" ));
    // document.getElementById($( element).attr( "id" )).setAttribute("data-on", "YES")
    // document.getElementById($( element).attr( "id" )).setAttribute("data-off", "NO!")
     console.log( element );


   
    var codclien = element.attr('id').toString();
    var codconsulta ="";

   });
// FIN PRESENTE
//dob
//--------------------------

$('#savepd').click(function(e){
    e.stopPropagation();
    let clientcode = document.querySelector('.highlight').id;
    let zipcode = $('#zipcode').val();
    let municipio = $('#municipio').val();
    let dob =$('#fechanacimiento').val();
    let sex = $('#sex').val()
     
    var datasave ={
      clientcode : clientcode,
      zipcode : zipcode,
      municipio : municipio,
      dob : dob,
      sex : sex
    }

    ajaxGen("../../clases/clientupdate.php",datasave);  

    // if (clientcode!='' && zipcode!='' && municipio!='' && dob!='' && sex!='') {
    //    isupdated=true;
    // }

    $('#dbomodal').modal('hide');
})

$("#dbomodal").on("hidden.bs.modal", function () {
   
   // if (isupdated) {
      
   // }else{
   //    $("#"+lastid).trigger('click').prop('checked', false);
   //    $("#"+lastid).trigger('click').prop('checked', false);

   //    $("#"+lastid2).trigger('click').prop('checked', false);
   //    $("#"+lastid2).trigger('click').prop('checked', false);      
     
   // }
   // isupdated=false;
   // lastid=''
   // lastid2=''
});

//dob
  function findObservacion(id,codconsulta,fechacita, tipo){
   
                  var ajxmed = $.post( "../../clases/getobservaciones.php",{ id : id, codconsulta : codconsulta,fechacita: fechacita,tipo:tipo }, function(data) { 
          
              var items="";
              items = jQuery.parseJSON(data);
             
              if (typeof items!= 'undefined' && items!==null) {
                 // found = true;
                  if(items.length>0){
                       var observacion=items[0].observacion;
                       $('#messagetext').val(observacion);
                   }
                }
              })
                  .done(function() {
                  
                  })
                  .fail(function() {
                    
                  })
                  .always(function() {
                  
                  });          

     
  }

  //TIPO TERAPIA
   
      $('#mls').change(function(event) {
       var resp= $(this).prop('checked');
       if(resp){
          $("#getmls").show();
          $("#getmls").focus();
       }else{
          $("#getmls").val("");
          $("#getmls").hide();
       }
        console.log(resp); //true / false
     })

       $('#hilt').change(function(event) {
       var resp= $(this).prop('checked');
       if(resp){
          $("#gethilt").show();
          $("#gethilt").focus();
       }else{
          $("#gethilt").val("");
          $("#gethilt").hide();
       }
        console.log(resp);
     })
//\TIPO TERAPIA
//---------------------------
function getCodCl(coditems) { 
 /* var nodoTd = element.parentNode; //Nodo TD
  var nodoTr = nodoTd.parentNode; //Nodo TR
  var nodosEnTr = nodoTr.getElementsByTagName('tr');
  var coditems = element.parentElement.parentElement.id.toString();*/
    //console.log(nodoTd.id)
  var currentURL = window.location.href;
    //console.log(currentURL)

  var ajxmed = $.post( "../../clases/pacienteslist.php",{c:coditems}, function(data) { 
  //console.log(data)
  var items="";
  var options="";
  items= jQuery.parseJSON(data);
  console.log(items)
  console.log("listo")

  var strNombres = items[0].nombres.trim().split(" ");
   if (items[0].apellido.trim() !="" && items[0].nombre.trim() ) {
      apellidos = items[0].apellido.trim()
      nombre = items[0].nombre.trim()      
   }else{

      if (strNombres.length==2) {
           apellidos = strNombres[0]
           nombre = strNombres[1]
      }else if(strNombres.length==3){
           apellidos = strNombres[0]+ ' '+strNombres[1]
           nombre = strNombres[2]
      }else{
           apellidos = strNombres[0]+ ' '+strNombres[1]
           nombre = strNombres[2]+ ' '+strNombres[3]
      }
   }
    var str = items[0].telfhabit.split("-");
    var telefono=str[0]+str[1]+str[2]
    
    var telefono2="";
    if(items[0].telfofic!==null){
        str = items[0].telfofic.split("-");
        telefono2=str[0]+str[1]+str[2];    
    }
    
    var sexo
    if (items[0].sexo==null  || items[0].sexo=="0" ) {
       sexo=0;
    }else{
         sexo=1;
    }

    var state
    if (items[0].ESTADO!=null) {
       state=items[0].ESTADO;
    }

    var pais 
    if (items[0].Pais!=null) {
      pais = items[0].Pais
    }

    var dob
    if (items[0].NACIMIENTO!=null) {
      dob = items[0].NACIMIENTO
    }

    var desde
    if (items[0].CliDesde!=null) {
      desde = items[0].CliDesde
    }
     
   var record
   if (items[0].Historia!=null) {
      record = items[0].Historia
    }

   var codclien
   if (items[0].codclien!=null) {
      codclien = items[0].codclien
    }

   var dirl1
   if (items[0].direccionH!=null) {
      dirl1 = items[0].direccionH
    }

   var codpostal
   if (items[0].codpostal!=null) {
      codpostal = items[0].codpostal
    }

   var activo
   if (items[0].inactivo==null || items[0].inactivo=="0" ) {
      activo = 0
    }else{
      activo = 1
    }

   var vigente
   if (items[0].fallecido==null || items[0].fallecido=="0" ) {
      vigente = 0
    }else{
      vigente = 1
    }

    var dirl2
    if (items[0].Eaddress!=null) {
      dirl2 = items[0].Eaddress
    }
    
    var ciudad
    if (items[0].hCiudad!=null) {
      ciudad = items[0].hCiudad
    }    

    var email
    if (items[0].email!=null) {
      email = items[0].email
    }    
  
   var id=items[0].Cedula
   var medico=items[0].codmedico
   
   var celular
   if (items[0].celular!=null) {
      celular = items[0].celular
    }

   var empleado
   if (items[0].exonerado==null || items[0].exonerado=="0" ) {
      empleado = 0
    }else{
      empleado = 1
    }


   var postData = {
       apellidos : apellidos,
       nombre : nombre,
       cedula : id,
       telefono : telefono,
       telefono2 : telefono2,
       sexo : sexo,
       medico : medico,
       dob : dob,
       desde : desde,
       record : record,
       codclien : codclien,
       usuariocl : celular,
       dirl1 : dirl1,
       codpostal : codpostal,
       pais : pais,
       state : state,
       activo : activo,
       empleado : empleado,
       vigente : vigente,
       dirl2 : dirl2,
       ciudad : ciudad,
       email : email,
       urlorigen : '../../vistas/atencion/atencion.php'
    } 
  
    $.redirect("../../vistas/pacientes/edit.php",postData ); 

      //alert( "success" ); 
    })
    .done(function() {
    //  console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
     // console.log();
    });  
}


//---------------------------
function emit(id){
   var mensaje=id;
  try {
     socket.emit('message',mensaje);
   } catch(err){
    
   }
}
//---------------------------
(function alertFacturaAbiertas(){
  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');

  var fecha;
  if ($('#fecha').val()=='') {
    fecha = hoy;
  } else{
    fecha = $('#fecha').val();
  };
     
  var ajxmed = $.post( "../../clases/getopeninvoice.php",{fecha}, function(data) { 
  var items="";
  var options="";
  var code="";
  items= jQuery.parseJSON(data);

   console.log(items);
   if (items!==null ) {
      var fa = items[0].abiertas;
      if (items[0].abiertas!=='0') {
          if (items[0].abiertas=='1') {
              $('#alert .test').text(' Hay una factura sin emitir ');
          } else{
              $('#alert .test').text(' Hay '+items[0].abiertas+' facturas sin emitir ');
          };
          
          $('#alert').show()
      };
   }

   })
   .done(function() {
   })
   .fail(function() {
   })
   .always(function() {
   }); 

})()
//---------------------------
//---------------------------
alertFacturasDobles();
function alertFacturasDobles(){
  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');

  var fecha;
  if ($('#fecha').val()=='') {
    fecha = hoy;
  } else{
    fecha = $('#fecha').val();
  };
     
  var ajxmed = $.post( "../../clases/getdoubleinvoice.php",{fecha}, function(data) { 
  var items="";
  var options="";
  var code="";
  items= jQuery.parseJSON(data);
    
   var texto=''; 
   console.log(items);
   if (items!==null ) {
             try{
         for (var i = 0; i < items.length; i++) {
              texto=texto+items[i].nombres+' posee '+items[i].facturas+' facturas / ';
         };
         if (texto!=='') {
            $('#alerta .test').text(texto);
            $('#alerta').show();
         };
         
       }catch(err){

       }
   }

   })
   .done(function() {
   })
   .fail(function() {
   })
   .always(function() {
   }); 

}
//---------------------------
//--------------------------
function addDays(theDate, days) {
    return new Date(theDate.getTime() + days*24*60*60*1000);
}

function formatDate(fecha){
var newDate = new Date(fecha).toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');
  return newDate;
}
//--------------------------
$('#atnopen').click(function(){

  var newDate = addDays(new Date(), 30);
  var nuevacita =formatDate(newDate);  

  var fecha = new Date(nuevacita)
  if(fecha.getDay()==0){
    newDate = addDays(fecha, 1);
    nuevacita = formatDate(newDate);
  };   
  var usuario = $('#loggedusr').val()
  var observ = $('#messagetext').val()           
  var medico = $( "#medico" ).val();
  var cita = $( "#citas" ).val();
  var prod = $( "#citas" ).val();
  var coditem ="" 
  if(cita.length>2){
     var cita = cita.substring(0,2);
  } 
  

 if(objPaciente.codclien==undefined){
    var  codclien = objPaciente;
  }else{
    var codclien = objPaciente.codclien;      
  }

  var mls=0;
  var hilt=0;

  var today = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');


  var datasave ={
    fecha_cita : nuevacita
  , codclien : codclien
  , codmedico : medico
  , check2 : '0'
  , option2 : '1' 
  , option3 : '0'
  , codconsulta : '03'
  , coditems : ''
  , observ : observ
  , valtype  : "save-data"
  , usuario  : usuario
  , hoy : today
  , mls : mls
  , hilt : hilt
}
console.log('')

var xres = ajaxGen("../../clases/atencionsave.php",datasave)
});