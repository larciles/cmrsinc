  $('#appoimentModal').on('show.bs.modal', function (event) {

    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('whatever') // Extract info from data-* attributes
    
    if (button.attr('id')!==undefined) {
        var codclien =button.attr('id');
        $('#idpaciente').val(codclien)
        findOutPacient(codclien); 
    };
    loadConsultas();

    $('#idpaciente').keyup(function(e){
        idCreate="";
        var tecla = undefined
        if (e.which == 13) {
            var codclien = $('#idpaciente').val()
            tecla=13
            findOutPacient(codclien);        
        }
  })


   if($("#fgfechacita").hasClass("has-error")){
      $("#fgfechacita").removeClass("has-error");
      $("#erroralert").hide();
   }

    var modalf = $(this)
    $( "#idpaciente" ).focus();

    $('input:visible:enabled:first', this).focus();
    modalf.find('input:text')[0].focus();
    modalf.find("input:visible:first").focus();


    var observ= $('#messagetext').val()
    var usuario = $('#loggedusr').val()



    $( "#NewApmt" ).change(function() { 

        //Fecha de la Cita
        var fecha = $('#NewApmt').val()   

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

        //console.log(fecha)
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
    });



 //save button

     $('#save').click(function(){
      var fecha = $('#NewApmt').val()
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
          var holiday=false;
          var data ={
            q : fecha
          }

          var holiday = ajaxGen("../../clases/controlholidays.php",data);

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



          if(fecha=="" || objPaciente===undefined || objPaciente=="" || holiday!==undefined){

            if(holiday!==undefined){
              $( "#erroralert" ).html( "<b>"+fecha+"</b> Holiday day" );
            }
            $("#erroralert").show();
            
           // $('.className').css({propertyName: ''});

            return;
          }
		  
		  	var mls=0;
			var hilt=0;

			if ($('#gethilt').val()!=''){
				hilt=$('#gethilt').val();
			}

			if ($('#getmls').val()!=''){
				mls=$('#getmls').val();
			}

          var datasave ={
            fecha_cita : fecha
          , codclien : objPaciente
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
           var xres = ajaxGen("../../clases/controlcitassave.php",datasave)

           $('#exampleModal').modal('hide');
           $('#submit').click();
      }else{
        //
         $("#fgfechacita").addClass("has-error");         
      }

     })
  //save button
  
  })

//** ENDS MODAL



    var fecha_input=$('input[name="NewApmt"]'); //our date input has the name "date"
    var contai=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    fecha_input.datepicker({
    format: 'mm/dd/yyyy',
    contai: contai,
    todayHighlight: true,
    autoclose: true,
    })
    

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


  function findOutPacient(id){
              
            var ajxmed = $.post( "../../clases/paciente.php",{ q : id, field : 'codclien' }, function(data) { 
            ////console.log(data)
            var items="";
            var options="";
            if (typeof data == "string"){
                var xerr=data.indexOf('Fatal error')
                if (xerr<0) {
                  items = jQuery.parseJSON(data);
                };
            }
            
            
             $('#appoimentModal').find('.modal-title').text('Nueva cita ' )
            if (typeof items!= 'undefined' && items!==null) {      
      
              if(items.length>0){   
                     $('#appoimentModal').find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
                    objPaciente = items[0].codclien
                    loadMedicos(items[0].codmedico)
                    $(".dividassoc").hide();
                    //controlcitaprevia.php
                    findCitaPrevia(objPaciente);
          
          //OBSERVACIONES
                     findObservacion(items[0].codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
                 if(items.length>1){  
                    $(".dividassoc").show();
                    loadIdsAsociados(items)
                 }
               }else{
                  $('#appoimentModal').find('.modal-title').text('Nueva cita ' )
                  
               }
               }else{
               //  idCreate=$('#idpaciente').val();
               //  $("#appoimentModal").modal('hide');
                
               }
            })
                .done(function() {
                })
                .fail(function() {
                 // alert( "error" );
                })
                .always(function() {
               
                }); 
  }

function findCitaPrevia(id){
  $('.citaprevia').hide(); 
  var ajxmed = $.post( "../../clases/controlcitaprevia.php",{ q : id }, function(data) {    
  var items="";  
  $('#citaprevia').val(""); 
  items= jQuery.parseJSON(data);        

        if (typeof items!= 'undefined' && items!==null ) {            
            if(items.length>0){                 
              $('.citaprevia').show();
              var date = new Date(items[0].fecha_cita);
              var appd= (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
               $('#citaprevia').val(appd); 
            }
        }
               
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          }); 
    return true;  


 }


function loadMedicos(codMedOculto){
         
    if (typeof codMedOculto === "undefined" || codMedOculto === null) { 
          var xmed= $("#citashd").val();
          if(xmed!==null  || typeof codMedOculto === "undefined" ){
           codMedOculto = xmed;
          }else{
          
                   codMedOculto = ""; }
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
            //alert( "error" );
          })
          .always(function() {
          });
        }
       //ends Medicos LOAD

 function loadConsultas(){
     var codMedOculto = $("#citashd").val();
     var ajxmed = $.post( "../../clases/consultas.php",{}, function(data) { 
   
     var items="";
     var options="";
     items= jQuery.parseJSON(data);
    
    
     for (var i = 0; i < items.length; i++) {
         if (codMedOculto==items[i].codmedico){
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
        // alert( "error" );
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
      //console.log('el cod es '+codmedico)
   })
  //ends Medicos Change----------------------------------------------------------

   function loadLaser(){
     $("#rowtipterapia").show();
        var codMedOculto = $("#citashd").val();
        var ajxmed = $.post( "../../clases/laser.php",{}, function(data) { 
        ////console.log(data)
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
        ////console.log(items)
       
        for (var i = 0; i < items.length; i++) {
            if (codMedOculto==items[i].codmedico){
                  options+="<option selected value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }else{
                  options+="<option value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }           
        }
          $("#citas").html(options); 

            //alert( "success" ); 
          })
          .done(function() {
            //console.log();
            //alert( "second success" );
          })
          .fail(function() {
           // alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          }); 
   }
   //--ENDS LASER-

     //SUERO LOAD
    function LoadSueros(){

        var codMedOculto = $("#citashd").val();
        var ajxmed = $.post( "../../clases/suero.php",{}, function(data) { 
        ////console.log(data)
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
        ////console.log(items)
       
        for (var i = 0; i < items.length; i++) {
            if (codMedOculto==items[i].codmedico){
                  options+="<option selected value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }else{
                  options+="<option value='"+items[i].codconsulta+"'>"+items[i].descons+"</option>";  
              }           
        }
          $("#citas").html(options); 

            //alert( "success" ); 
          })
          .done(function() {
            //console.log();
            //alert( "second success" );
          })
          .fail(function() {
           // alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          }); 
   }
   //-- END SUERO

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
                if (typeof items!= 'undefined' && items!==null ) { 
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