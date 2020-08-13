jQuery(function($){
   
   var estado

   var autho= $('#authorization').val();
   if(autho=='01' || autho=='06'){
      $('#usuariocl').prop("disabled", false); // Element(s) are now enabled.
   }

   //formato de telefono funciona con un archivo js ------------------------------------
  $("#phone").mask("(999) 999-9999");
  $("#phone2").mask("(999) 999-9999");
  //formato de datepicker funciona con un archivo js ----------------------------------
  $('#dob').datepicker({
      format: "dd/mm/yyyy"
    }).on('change', function(){
    	var dob= $('#dob').val()
        $('.datepicker').hide();
        console.log(dob);
    });

    //Check del sexo true F, false M -------------------------------------------------
   $('#sexo').change(function() {
      var resp='Sexo: ' + $(this).prop('checked');
      var res= $(this).prop('checked');
      console.log(resp);


      if(res){
        if($("#sexicon").hasClass("fa fa-male")){
           $("#sexicon").removeClass("fa fa-male");
           $("#sexicon").addClass("fa fa-female");
        }
      }else{
           $("#sexicon").removeClass("fa fa-famale");
           $("#sexicon").addClass("fa fa-male");
      }

    })

    //Check del Activo  --------------------------------------------------------------
   $('#activo').change(function() {
      var resp='activo: ' + $(this).prop('checked');
      console.log(resp);
    })

    //Check del empleado ----------------------------------------------------------- 
   $('#empleado').change(function() {
      var resp='empleado: ' + $(this).prop('checked');
      var res= $(this).prop('checked');
      console.log(resp);


        if(res){
        if($("#empleadoicon").hasClass("fa-heartbeat")){
           $("#empleadoicon").removeClass("fa-heartbeat");
           $("#empleadoicon").addClass("fa-address-card");
        }
      }else{
           $("#empleadoicon").removeClass("fa-address-card");
           $("#empleadoicon").addClass("fa-heartbeat");
      }

    })

    //Check del vigente --------------------------------------------------------------
   $('#vigente').change(function() {
      var resp='vigente: ' + $(this).prop('checked');
      console.log(resp);
    })

  //Paises ----------------------------------------------------------------------------
  var codPaisOculto = $("#paiscohd").val();
  if (codPaisOculto=="") {codPaisOculto='160'}
  var jqxhr = $.post( "../../clases/paises.php",{}, function(data) { 
	//console.log(data)
	var items="";
	var paises="";
	items= jQuery.parseJSON(data);
	//console.log(items)

	for (var i = 0; i < items.length; i++) {
	    if(items[i].COD==codPaisOculto){
			    paises+="<option selected value='"+items[i].COD+"'>"+items[i].PAIS+"</option>";
	    }else{
	        paises+="<option value='"+items[i].COD+"'>"+items[i].PAIS+"</option>";
	    }    	
	}
    $("#country").html(paises); 
      //alert( "success" ); 
    })
    .done(function() {
      console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });

  //Change Paises ---------------------------------------------------------------------

   $('#country').change(function(event){
      event.preventDefault()
      var pais = $(this).val();
      var test = $("#statecohd").val(pais);
      $("#statecohd").val(pais);
      test = $("#statecohd").val(pais);
      states(pais,pais);
   })

  //Estados ---------------------------------------------------------------------------
  states( $("#statecohd").val(),"")
  function states(codStateOculto,alter){
   // var codStateOculto = $("#statecohd").val();
    console.log(codStateOculto)
    var jqxhr = $.post( "../../clases/estados.php",{pais:codPaisOculto, alter:alter}, function(data) { 
    console.log(data)
    var items="";
    var estados="";
    $("#region").html(estados); 
    items= jQuery.parseJSON(data);
    //console.log(items)
  
    for (var i = 0; i < items.length; i++) {
        if(items[i].COD==codStateOculto){
            estados+="<option selected value='"+items[i].COD+"'>"+items[i].State+"</option>";
        }else{
            estados+="<option value='"+items[i].COD+"'>"+items[i].State+"</option>";
        }     
    }
    $("#region").html(estados); 
      //alert( "success" ); 
    })
    .done(function() {
     // console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });
  }
  //Medicos ----------------------------------------------------------
  var codMedOculto = $("#medicohd").val();
  var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
	//console.log(data)
	var items="";
	var options="";
	items= jQuery.parseJSON(data);
	//console.log(items)

	for (var i = 0; i < items.length; i++) {
      if (codMedOculto==items[i].codmedico){
            options+="<option selected value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
        }else{
            options+="<option value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
        }	        	
	}
    $("#medico").html(options); 

      //alert( "success" ); 
    })
    .done(function() {
      console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });

 //Medicos Change----------------------------------------------------------
    $('#medico').change(function(event){
      event.preventDefault()
      var codmedico = $(this).val();
      console.log('el cod es '+codmedico)
   })

  //Coloca El valor de Id en el Telefon y le da el foco ----------------------  

  $('#id').blur(function(event){
      console.log( $(this).val());
      $("#phone").val($(this).val());
      if($("#idassoc").hasClass("visble")){
        $("#idassoc").focus();
      }else{
        $("#apellidos").focus();  
      }                 
  })
  //---------------------------------------------------------------------
   $('#previa').click(function(event){
     event.preventDefault()
     javascript:history.go(-1)
   })
 //-----------------------------------------------------------------------
  $('#apellidos').blur(function(event){
    if($('#apellidos').val()!=="" && $("#divapellidos").hasClass("has-error")==true){
       $("#divapellidos").removeClass("has-error");
    }
  })
 //-----------------------------------------------------------------------
   $('#nombres').blur(function(event){
    if($('#nombres').val()!=="" && $("#divnombres").hasClass("has-error")==true){
       $("#divnombres").removeClass("has-error");
    }
  })
 //-----------------------------------------------------------------------
   $('#id').blur(function(event){
    if($('#id').val()!=="" && $("#divid").hasClass("has-error")==true){
       $("#divid").removeClass("has-error");
    }
  })
 //-----------------------------------------------------------------------
   $('#phone').blur(function(event){
    if($('#phone').val()!=="" && $("#divphone").hasClass("has-error")==true){
       $("#divphone").removeClass("has-error");
    }
  })
 //-----------------------------------------------------------------------
  //-----------------------------------------------------------------------
   $('#phone2').blur(function(event){
    if($('#phone2').val()!=="" && $("#divphone2").hasClass("has-error")==true){
       $("#divphone2").removeClass("has-error");
    }
  })
 //-----------------------------------------------------------------------
  $('#save').click('before-submit',function(){
    var error=false;
   if($('#apellidos').val()==""){
      $("#divapellidos").addClass("has-error");
      error=true;
    }

    if($('#nombres').val()==""){
      $("#divnombres").addClass("has-error");  
       error=true;     
    }

    if($('#id').val()==""){
      $("#divid").addClass("has-error");   
       error=true;    
    }

    if($('#phone').val()==""){
      $("#divphone").addClass("has-error");  
       error=true;     
    }


    if($('#phone2').val()==""){
      $("#divphone2").addClass("has-error");  
       error=true;     
    }
 
 
    var nombres = $('#nombres').val();
    var apellidos = $('#apellidos').val();
    var id =  $('#id').val();      
    var medico = $('#medico').val();
    var dob = $('#dob').val();
    var record = $('#record').val();   
    var address_line1 = $('#address-line1').val();
    var postal_code = $('#postal-code').val();
    var country = $('#country').val();
    var region = $('#region').val();
    var address_line2 = $('#address-line2').val();
    var city = $('#city').val();
    var email = $('#email').val();
    var phone = $('#phone').val();
    var phone2 = $('#phone2').val();
    var usuariocl = $('#usuariocl').val();
    var sexo = $('#sexo').val();
    var empleado = $('#empleado').val();
    var controlcita = $('#controlcita').val();

    address_line1=address_line1+' '+address_line2;

    var fields ={
       nombres : nombres
      ,apellidos : apellidos
      ,id : id        
      ,medico : medico
      ,dob : dob
      ,record : record    
      ,address_line1 : address_line1
      ,postal_code : postal_code
      ,country : country
      ,region : region      
      ,address_line2 : address_line2
      ,city : city
      ,email : email
      ,phone : phone  
      ,phone2 : phone2  
      ,usuariocl : usuariocl
      ,sexo : sexo
      ,empleado : empleado
      ,controlcita
    }

    if(error==false){

            $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../controllers/pacientesNuevos.php",
            data:fields, 
            success:  function(respuesta){                        
                clearField();
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });     
    }

  });




function clearField(){
    $('#nombres').val('');
    $('#apellidos').val('');
    $('#id').val('');      
    $('#medico').val('');
    $('#dob').val('');
    $('#record').val('');   
    $('#address-line1').val('');
    $('#postal-code').val('');
    $('#country').val('');
    $('#region').val('');
    $('#address-line2').val('');
    $('#city').val('');
    $('#email').val('');
    $('#phone').val('');
    $('#phone2').val('');
    $('#usuariocl').val('');
    $('#sexo').val('');
    $('#empleado').val('');
}


//
$('#id').keypress(function(e){
         if(e.which === 13){
            $("#idassoc").removeClass("visble");
            id=$('#id').val();
            findOutPacient(id);
         }
 });
  
 //
   function findOutPacient(id){
                
            var ajxmed = $.post( "../../clases/getpacientebyid.php",{ q : id }, function(data) { 
            ////console.log(data)
            var items="";
            var options="";
            if (typeof data == "string"){
                var xerr=data.indexOf('Fatal error')
                if (xerr<0) {
                   items = jQuery.parseJSON(data);
                };
            }
            
            if (typeof items!= 'undefined' && items!==null) {            
              if(items.length>0){   
                
                    objPaciente = items[0].codclien
                    
                    $("#divassoc").hide();
                    
                 //findCitaPrevia(objPaciente);          
                 //OBSERVACIONES
                 // findObservacion(items[0].codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
                 if(items.length>1){  
                    $("#divassoc").show();
                    loadIdsAsociados(items)
                 }
               }else{
             
                  
               }
               }else{
                 idCreate=$('#idpaciente').val();
             
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

  //
      function loadIdsAsociados(items){    
        var options=""; 
        $('#idassoc option').remove();
        $("#idassoc").addClass("visble"); 
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
     //  findObservacion(codclien,$( "#citas" ).val(),$('#fecha').val(), '2');

    }



});