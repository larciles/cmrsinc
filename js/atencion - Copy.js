$(function(){
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

  $('#tbl-ctrl').on("dblclick", "td", function(){
    var element=$(this);
 
    if($(this).hasClass("edit")){
        console.log('cool');
     }

    $('#'+element[0].id).prop('contenteditable', true );
    $('#'+element[0].id).addClass("selected");

  });
 
    $(document).on("blur", ".selected", function(){ 
       $('.edit').prop('contenteditable', false );
       $(".selected").removeClass("selected");
       var element=$(this);
       var record = element[0].innerText;      
       var codclien=element.parents('tr').attr('id').toString();

       var datasave ={
         codclien : codclien
        ,record :record
       };

       var xres = ajaxGen("../../clases/atencionupdaterecord.php",datasave)
    });


 
  $(".sltmedico").change(function(){
      var element=$(this);
      var codclien=element.parents('tr').attr('id').toString();
      var codmedico = element.val().toString();
      var codconsulta = element.parents('tr').attr('campo').toString();
      var coditem = element.parents('tr').attr('producto').toString();
      var valtype="consultas"
      if (codconsulta=="07") {
          valtype="servicios"
      };
      var datasave ={
       codclien : codclien
      ,codmedico : codmedico
      ,valtype  : valtype

    }
       var xres = ajaxGen("../../clases/atencionchangemedico.php",datasave)

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


  $('.asistido').change(function(event) {
    event.stopPropagation();
    element = $(this);
    var resp= $(this).prop('checked');
    var codclien = element.attr('id').toString();
    var codconsulta = element.attr('campo').toString();
    var coditems = element.attr('producto').toString();
    var asistido;
    var noAsistido;
    var fecha_cita = getToDay();
    var usuario = $('#loggedusr').val();
    var idUsr = element.closest('tr').children('td:first').text();
    
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
      $('#idpaciente').val(idUsr);
      $("#exampleModal").modal();
  };
})

  $("#sltconsultas").change(function(){
     $('#submit').click();
  })

    $("#fecha").change(function(){
     $('#submit').click();
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
})
   
  //tipo de consultas ----------------------------------------------------------
  var codConsultas = $("#sltconsultashd").val();

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
                items= jQuery.parseJSON(respuesta);

                if (typeof items!== 'undefined' && items!==null ) { 
                   if(items.length>0){
                      res=items;
                   }
                }
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
   $('#idcl').val(idCreate);
   $('#phonecl').val(idCreate);
    
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


  $('#savecl').confirm({
    text: "¿Seguro de Guardar?",
    title: "Confirmación requerida",
    confirm: function(button) {
          nombre =  $('#name').val(); 
    apellidos =  $('#apellido').val(); 
    nombres =  $('#apellido').val(); 
    cedula =  $('#idcl').val(); 
    telefono =  $('#phonecl').val(); 

    if(nombre=="" || apellidos=="" || cedula=="" || telefono=="" ){
      return;
    }

    var  fields={
      nombre :nombre,
      apellidos : apellidos,
      nombres : nombres,
      cedula : cedula,
      telefono
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
       $('#idpaciente').val(cedula); 
    },
    beforeSend:function(){},
    error:function(objXMLHttpRequest){}
  });
    $('#idpaciente').val(cedula);  
  

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
           findOutPacient(xId)
        }
    }
    catch(err) {
        console.log(err.message);
    }

   $("#closeapm").click(function(){
     trCodConsulta="";
     trCodItems="";
     $('#idpaciente').val("")
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
    var contai=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    fecha_input.datepicker({
    format: 'mm/dd/yyyy',
    contai: contai,
    todayHighlight: true,
    autoclose: true,
    })

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
            valday=false
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

  
  function findOutPacient(id){
            var codMedOculto = $("#medicohd").val();
            var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 
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
                 loadMedicos(items[0].codmedico)
                 custId = new ID($(''));
                  $(".dividassoc").hide();
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
     
            if($('#tiposervicio').prop('checked')){
                loadLaser()  
            }else{
                LoadSueros()             
            }
     })


     $("#tiposervicio").prop("disabled",true);
     $('#tipocita').change(function(event) {
       var resp= $(this).prop('checked');
       if(resp){
            loadConsultas()
            $("#tiposervicio").prop("disabled",true);
            //console.log( $('#tiposervicio').prop('checked'));
       }else{
            $("#tiposervicio").prop("disabled",false);
            if($('#tiposervicio').prop('checked')){
                loadLaser()  
            }else{
                LoadSueros()             
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

            $('#tipocita').prop('checked', false).change()
            if ( trCodConsulta=='07') {
                if (trCodItems!==undefined){
                      if (trCodItems.substring(0, 2)=="TD") {
                         loadLaser(trCodConsulta+trCodItems);
                       }else if(trCodItems.substring(0, 2)=="ST"){
                         $('#tiposervicio').prop('checked', false).change()                        
                         LoadSueros(trCodConsulta+trCodItems);
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

$('#save').click(function(){

  var fecha = $('#fechaNewApmt').val()
  today = new Date(fecha)
  dayIndex = today.getDay()

  if(!$("#fgfechacita").hasClass("has-error")){
     
     if($("#fgfechacita").hasClass("has-error")){
        $("#fgfechacita").removeClass("has-error");
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
      if ( $('#tipocita').prop('checked')){
        Check2=0
      }
      
      var Option2 = 0
      var Option3 = 1
      if ( $('#tiposervicio').prop('checked')){
        Option2 = 1
        Option3 = 0
      }

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


      var codclien="";
      
	  if(objPaciente.codclien==undefined){
          codclien = objPaciente;
      }else{
          codclien = objPaciente.codclien;      
      }
	  
      if(fecha=="" || codclien===undefined || codclien=="" ){
        return;
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

    }
       var xres = ajaxGen("../../clases/atencionsave.php",datasave)

       $('#exampleModal').modal('hide');
       $('#submit').click();
  }else{
     $("#fgfechacita").addClass("has-error");         
  }
   $('#idpaciente').val("")
    trCodConsulta="";
    trCodItems="";
   $('#submit').click();
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
    title: '',
    pieStartAngle: 100,
    slices: {
        0: { color: 'teal' },
        1: { color: '#16a085' }
      }
  };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
    chart.draw(data, options);
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

$('#btnctrl').click(function(){
   $('#xcontrol').val('1');  
   $('#submit').click();
})

$('#spncitados').click(function(){
   $('#submit').click();
})

 $("#tbl-ctrl").colResizable();