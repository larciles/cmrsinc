
  $('#exampleModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('whatever') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.


    if (recordnumber!='') {
       
       $('#idpaciente').val(recordnumber);
       // fecha_nueva_cita = fecha_nueva_cita.split('-');
       // fecha_nueva_cita = fecha_nueva_cita[1]+'/'+fecha_nueva_cita[2]+'/'+fecha_nueva_cita[0];
       // $('#fechaNewApmt').val(fecha_nueva_cita);

       $('#time').val(time); 
       if (mls_val!='' && mls_val!='0') {
          $("#mls").trigger('click').prop('checked', true);
          $("#mls").trigger('click').prop('checked', true);
          $('#getmls').val(mls_val);
          $("#getmls").show();
          $("#getmls").focus();
       };

       if (hilt_val!='' && hilt_val!='0') {
          $("#hilt").trigger('click').prop('checked', true);
          $("#hilt").trigger('click').prop('checked', true);
          $('#gethilt').val(hilt_val);
          $("#gethilt").show();
          $("#gethilt").focus();
       };
       

    };

    $(".dividassoc").hide();
      if($("#fgfechacita").hasClass("has-error")){
               $("#fgfechacita").removeClass("has-error");
               $("#erroralert").hide();
         }

    try {
        if ($('#idpaciente').val()!=="") {
           var xId=$('#idpaciente').val();
           findOutPacient(xId)
           findObservacion(xId,'07',fecha_nueva_cita,'1');
        }
    }
    catch(err) {
        console.log(err.message);
    }

   $("#closeapm").click(function(){    
     $('#idpaciente').val("")
    

   })

    var modalf = $(this)
    $( "#idpaciente" ).focus();

    $('input:visible:enabled:first', this).focus();
    modalf.find('input:text')[0].focus();
    modalf.find("input:visible:first").focus();

   $('.clockpicker').clockpicker();


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
			//paciente.php
            var ajxmed = $.post( "../../clases/atencioncitar.php",{ q : id }, function(data) { 
            ////console.log(data)
            var items="";
            var options="";
            items = jQuery.parseJSON(data);
            //modal.find('.modal-title').text('Nueva cita ' )
            if (typeof items!= 'undefined' && items!==null) {      
               // found = true;
              if(items.length>0){   
                 modal.find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
                 vav( items[0].codclien);
                 loadMedicos(items[0].codmedico)
                 custId = new ID($(''));
                  $(".dividassoc").hide();
				  
				  //OBSERVACIONES
                findObservacion(items[0].codclien,'07',fecha_nueva_cita, '2');

				
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
                loadLaser()  
            }else{
                LoadSueros()             
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
  

    try {
        loadLaser(); 
        loadNumeroDeTerapias();

    }catch(err) {
         
    }

    //- AUTO CARGA DE MEDICOS
    loadMedicos()

    var modal = $(this)
    modal.find('.modal-title').text('Nueva cita ' + recipient)
    //modal.find('.modal-body input').val(recipient)
 
  })
//--------------------------------------------------------------
function findOutPacient(id){
            var codMedOculto = $("#medicohd").val();
      //paciente.php
            var ajxmed = $.post( "../../clases/atencioncitar.php",{ q : id }, function(data) { 
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
          
          //OBSERVACIONES
                findObservacion(items[0].codclien,'07',fecha_nueva_cita, '2');
        
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

//--------------------------------------------------------------
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
//--------------------------------------------------------------
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
//--------------------------------------------------------------
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
//--------------------------------------------------------------
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
//--------------------------------------------------------------
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
//--------------------------------------------------------------
function vav(codclien){
   objPaciente = new codigoCliente(codclien);
}
//--------------------------------------------------------------
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
//--------------------------------------------------------------   
function loadNumeroDeTerapias(){
        var options="";
        for (var i = 0; i < 31; i++) {
             options+="<option value='"+i+"'>"+i+"</option>";  
        }
         $("#numterapias").html(options); 
}
//--------------------------------------------------------------
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
//--------------------------------------------------------------
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
//--------------------------------------------------------------