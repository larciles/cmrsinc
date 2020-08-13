var field  
var htmlElement  
var phone_number;
$(function(){
  
$("#wait").hide(); 
$('.citaprevia').hide(); 
$('#xtitulo').html('<b>Control de Citas</b>');
  var lastUser
  var elementconf = undefined;
  var counter=0;
  var idCreate="";
  var objPaciente="";


  var date_input=$('input[name="fecha"]'); //our date input has the name "date"
  var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })

  $(".alpha").click(function(){
      let el=$(this);
      $('#character').val(el.attr('id'));  
      $('#submit').click();
      console.log(el.attr('id'));
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


 $('#tbl-ctrl').on('change', 'tbody tr select.medicos', function(event) {
  
        var este= $(this)
        var newMedico= $("#"+este.attr('id')).val()
        var arrDat= este.attr('id').split('-')
        var cdclien=arrDat[1]
        var datasave ={
         codmedico   : newMedico
        ,codclien : cdclien
    }

    url="../../clases/updatemedicoenclientescc.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
  
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
   
    $(document).on("dblclick", "td.edit", function(){ makeEditable(this); });
    // $(document).on("blur", "input#editbox", 
    //   function(){
    //    removeEditable(this.parent) 
    //  }); 
  
  //tipo de consultas ----------------------------------------------------------
  var codConsultas = $("#sltconsultashd").val();
  //console.log(codConsultas)
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
      //alert( "error" );
    })
    .always(function() {
    });


  //-------------------------------------
   var d = new Date();
   var mes = (d.getMonth()+1)
   var dia = d.getDate()
   if(mes>=1 && mes<10){
     mes='0'+mes
   }

   if(dia>=1 && dia<10){
     dia='0'+dia
   }


   var strDate = mes+ "/" + dia  +"/" +d.getFullYear()  ;
   if($('#fecha').val()==""){
      $('#fecha').val(strDate)
   }
  //-------------------------------------
})

//---------------------------------------------------------
function makeEditable(element) { 
   
  field=document.getElementsByClassName('observacion')[element.parentElement.rowIndex]
  htmlElement=element
  element.contentEditable=true 
  console.log(element) ;

  

  ////console.log(element)
  // $(element).html('<input id="editbox" style="color: #373A3C;" size="'+  $(element).text().length +'" type="text" value="'+ $(element).text() +'">');  
  // $('#editbox').focus();
  // $(element).addClass('current');    //
  //  var nodoTd = element.parentNode; //Nodo TD
  //  var nodoTr = nodoTd.parentNode; //Nodo TR   
  //  var nodoTd = element.parentNode; //Nodo TD
  //  var nodoTr = nodoTd.parentNode; //Nodo TR
  //  var nodosEnTr = nodoTr.getElementsByTagName('tr');
  //  var coditems = nodoTd.id
}




function focusOut() {

  // se optiene la fecha del primer elemento [0] correspondiente a la fecha
  let fecha = htmlElement.parentElement.children[0].innerText

  htmlElement.contentEditable=false  
  removeEditable(htmlElement.id.split('-')[1]
    ,htmlElement.id.split('-')[0]
    ,htmlElement.id.split('-')[2]
    ,htmlElement.innerText
    ,fecha)
  

};


   //---------------------------------------------------
   $('.confirmado').change(function(event) {
      event.stopPropagation();
      var resp= $(this).prop('checked');
      if (typeof elementconf== 'undefined') {
          elementconf = this
      }
      if (typeof  counter== 'undefined') {counter=0}
        
        var coditems=""; 
        try{
            coditems=elementconf.parentElement.parentElement.parentElement.attributes['producto'].value   
        }catch(err) {
          console.log(err.message);
        }

        var tipoConsulta=elementconf.attributes["campo"].value
        var codclien=elementconf.attributes["id"].value
      
        var fecha = $('#fecha').val()
        var usuario = $('#loggedusr').val()

        counter++
  
        setConfirmar(tipoConsulta,codclien,coditems,fecha,usuario,resp)
    })

//-------------------------------------------------------
$('.citar').click(function(){
  element=$(this);
  idCreate = element.attr('id').toString()
  console.log(idCreate)
  $("#exampleModal").modal();
})

//-------------------------------------------------------
$('.editar').click(function(){
  element=this
  var nodoTd = element.parentElement.parentElement; //Nodo TD
  lastUser=nodoTd.id
  //console.log(lastUser)
})
//---------------------------------------
function setConfirmar(tipoConsulta,codclien,coditems,fecha,usuario,resp){          
      var postdata = {
          tipoConsulta : tipoConsulta,
          codclien : codclien,
          coditems : coditems,
          fecha : fecha,
          usuario : usuario,
          resp : resp
      }
      //

      var jqxhr = $.post( "../../clases/controlcconfirmado.php",postdata, function() {
      
   
      })
      .done(function() {
      })
      .fail(function() {
        //alert( "error" );
      })
      .always(function() {
      });
     
}
//----------------------------------------

function currentPaciente(element) {
  $(element).addClass('current'); 
   //
   var nodoTd = element.parentNode; //Nodo TD
   var nodoTr = nodoTd.parentNode; //Nodo TR 
   lastUser=nodoTd.id
   //console.log(lastUser)
   var tipoConsulta=element.attributes["campo"]
   var codclien=element.attributes["id"]

  // function getCodCl(element) { 
    var nodoTd = element.parentNode; //Nodo TD
    var nodoTr = nodoTd.parentNode; //Nodo TR
    var nodosEnTr = nodoTr.getElementsByTagName('tr');
    var coditems = nodoTd.id
}

 $('#myModal').on('show.bs.modal', function (event) {

    var cedula=undefined;
    var usuariocl;
    $('#closebtn').click(function(){
      idCreate=""
      $('#idpaciente').val("");     
    })

    $('#idcl').val(idCreate);
    $('#phonecl').val(idCreate);
    
    $('#crtnewpaciente').click(function(){
        $("#myModal").modal('hide');
        $("#briefModal").modal('show');       
     })

    $( '#closecl' ).click(function() {
        idCreate="";
         $('#idpaciente').val("");    
      });



    $('#savecl').confirm({
      text: "¿Seguro (a) de guardar este nuevo paciente ?",
      title: "Confirmación requerida",
      confirm: function(button) {
                nombre =  $('#name').val(); 
      apellidos =  $('#apellido').val(); 
      nombres =  $('#apellido').val(); 
      cedula =   $('#idcl').val(); 
      telefono =  $('#phonecl').val(); 
      usuariocl = $('#usuariocl').val(); 

      if(nombre=="" || apellidos=="" || cedula=="" || telefono=="" || usuariocl==""){
        return;
      }

      var  fields = {
        nombre :nombre,
        apellidos : apellidos,
        nombres : nombres,
        cedula : cedula,
        telefono : telefono,
        usuariocl : usuariocl
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
      error:function(objXMLHttpRequest){
        console.log(objXMLHttpRequest);
      }
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
      cancelButtonClass: "btn-danger",
      dialogClass: "modal-dialog modal-lg" // Bootstrap classes for large modal
    });


    //  $('#savecl').click(function(){
      
    //   nombre =  $('#name').val(); 
    //   apellidos =  $('#apellido').val(); 
    //   nombres =  $('#apellido').val(); 
    //   cedula =   $('#idcl').val(); 
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
     if (cedula!==undefined) {
       $('#idpaciente').val(cedula); 
     }
    //
 })



//** MODAL
//*********
//*********
//*********
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

    var modalf = $(this)
    $( "#idpaciente" ).focus();

    $('input:visible:enabled:first', this).focus();
    modalf.find('input:text')[0].focus();
    modalf.find("input:visible:first").focus();


    var observ= $('#messagetext').val()
    var usuario = $('#loggedusr').val()

    var fecha_input=$('input[name="fechaNewApmt"]'); //our date input has the name "date"
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

        let superUsr=$('#codperfil2').val();
        if (superUsr!='01') {
           if (fechacita<fechaact) {
              valday=false
              $("#fgfechacita").addClass("has-error");
              return;
           }else{
              if($("#fgfechacita").hasClass("has-error")){
                $("#fgfechacita").removeClass("has-error");
              }
            }

        };



//**** 


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

    $('#idpaciente').keyup(function(e){
        idCreate="";
        var tecla = undefined
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
            if (typeof data == "string"){
                var xerr=data.indexOf('Fatal error')
                if (xerr<0) {
                  items = jQuery.parseJSON(data);
                };
            }
            
            
            modal.find('.modal-title').text('Nueva cita ' )
            if (typeof items!= 'undefined' && items!==null) {      
      
              if(items.length>0){   
                    modal.find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
                    objPaciente = items[0].codclien
                    
                    if ($("#tabs .active:visible").index()==1) {
                    
                      loadMedicos($("#codmedico2").val())  
                    }else{
                       loadMedicos(items[0].codmedico)  
                    };
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
                 modal.find('.modal-title').text('Nueva cita ' )
                  
               }
               }else{
                 idCreate=$('#idpaciente').val();
                 $("#exampleModal").modal('hide');
                 $("#myModal").modal();
               }
            })
                .done(function() {
                })
                .fail(function() {
                 // alert( "error" );
                })
                .always(function() {
               
                }); 
  
        if ($('#citas').val()=="03" || $('#citas').val()=="01") {
           $('#option1').parent().addClass('active');
        }

  }

 // function findCitaPrevia(id){
 //  var ajxmed = $.post( "../../clases/controlcitaprevia.php",{ q : id }, function(data) {    
 //  var items="";  
 //  $('#citaprevia').val(""); 
 //  items= jQuery.parseJSON(data);        

 //        if (typeof items!= 'undefined' && items!==null ) {            
 //            if(items.length>0){                 
              
 //              var date = new Date(items[0].fecha_cita);
 //              var appd= (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
 //               $('#citaprevia').val(appd); 
 //            }
 //        }
               
 //          })
 //          .done(function() {
 //          })
 //          .fail(function() {
 //          })
 //          .always(function() {
 //          }); 
 //    return true;  


 // }

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


   // function loadConsultas(){
   //   var codMedOculto = $("#citashd").val();
   //   var ajxmed = $.post( "../../clases/consultas.php",{}, function(data) { 
   
   //   var items="";
   //   var options="";
   //   items= jQuery.parseJSON(data);
    
    
   //   for (var i = 0; i < items.length; i++) {
   //       if (codMedOculto==items[i].codmedico){
   //             options+="<option selected value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
   //         }else{
   //             options+="<option value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
   //         }           
   //   }
   //     $("#citas").html(options);
   //     })
   //     .done(function() {
   //     })
   //     .fail(function() {
   //      // alert( "error" );
   //     })
   //     .always(function() {
   //     }); 
   // }
    //END CONSULTAS 1-----------------------------------------------

   //save button
   //
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

        let hoy = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');
        
        let fechaact = new Date(hoy);
        let fechacita = new Date(fecha);        
        let superUsr=$('#codperfil2').val();

        if (superUsr!='01') {
            if (fechacita<fechaact) {
                valday=false
                $("#fgfechacita").addClass("has-error");
                return;
            }else{
                if($("#fgfechacita").hasClass("has-error")){
                    $("#fgfechacita").removeClass("has-error");
                  }
            }
        };

		  
		  	var mls=0;
			var hilt=0;

			if ($('#gethilt').val()!=''){
				hilt=$('#gethilt').val();
			}

			if ($('#getmls').val()!=''){
				mls=$('#getmls').val();
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
     
    //- AUTO CARGA DE LOS CONSULTAS
    loadConsultas()
    //- AUTO CARGA DE MEDICOS
    loadMedicos()

    var modal = $(this)
    modal.find('.modal-title').text('Nueva cita ' + recipient)
    modal.find('.modal-body input').val(recipient)


    //2018 11 06 nueva estructura
    try{
          $('#'+$('.tipo_citas >.active')[0].children[0].id).parent().removeClass('active') // remove  class  
    }catch(e){
          console.log(e)
    }
    
    if ($('#sltconsultas').val()=='2') {
       $('#option1').parent().addClass('active') // CONSULTA MEDICOS
       selectCitas('option1');    
    }else if ($('#sltconsultas').val()=='6') {
      $('#option2').parent().addClass('active')
      selectCitas('option2');
    }else if ($('#sltconsultas').val()=='7') {
      $('#option3').parent().addClass('active')
      selectCitas('option3');
    }else{
       $('#option1').parent().addClass('active') // CONSULTA MEDICOS
       selectCitas('option1');    
    }

  
    try {
        $('#idpaciente').val(idCreate);
        id=idCreate;
        idCreate="";
        findOutPacient(id)
        ///console.log(idCreate);
        //console.log($('#idpaciente').val()) 
    }
    catch(err) {
        console.log(err.message);
    }
   
  })

//** ENDS MODAL
//-------------------------------------
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
//--------------------------------------
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
//--------------------------------------

  $('.tipo_citas').on("change", function(){
      var ele=$(this);
      eletdc1=$('.tipo_citas >.active')[0].children[0].id ;
      console.log(eletdc1);      
      selectCitas(eletdc1);
  });
//--------------------------------------
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
//--------------------------------------

   function holidaysValidation(date){

        var lreturn=false;
        var ajxmed = $.post( "../../clases/controlholidays.php",{ q : date }, function(data) {         
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
        

        if (typeof items!= 'undefined' && items!==null ) {            
            if(items.length>0){   
               lreturn=true;   
            }
        }
                //alert( "success" ); 
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          }); 
    return lreturn;      
   }


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
 
//**********************************************
//******* STARTS SELECTS ***********************
//**********************************************
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
       //--LASER-------------------------------------------------------------------------
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

//**********************************************
//********ENDS SELECTS *************************
//**********************************************

var spcita=0;
var spconf=0;
if ($('#spncitados').text()!=="") {
  spcita = parseInt($('#spncitados').text().match(/\d+/).toString()); 
};

if ($('#spnconfirm').text()!=="") {
  spconf = parseInt($('#spnconfirm').text().match(/\d+/).toString()) ;
};

if (spcita!==0 || spconf!==0) {

google.charts.load('current', {'packages':['bar']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  var data = google.visualization.arrayToDataTable([
    ['Pacie...', 'Citados', 'Confirmados'],        
    ['-', spcita , spconf]
  ]);

  var options = {
    chart: {
      title: '',
      subtitle: '',
    },
    bars: 'horizontal', // Required for Material Bar Charts.
    colors: ['#2c3e50','#16a085']
  };

  var chart = new google.charts.Bar(document.getElementById('barchart_material'));

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
        
	 //OBSERVACIONES
       findObservacion(codclien,$( "#citas" ).val(),$('#fecha').val(), '2');

    }


    $('#idassoc').change(function(event){
      event.preventDefault()
      var element = $(this).find('option:selected');
      var codmedico =  element.attr("cdmedico");
      var codclien =  element.val();
      var nombres = element.text();
      $("#citashd").val(codmedico);
      objPaciente = codclien;
       findCitaPrevia(codclien);
      loadMedicos(codmedico)     
     
      $('#exampleModal').find('.modal-title').text('Nueva cita  a  ' + nombres)
	   //OBSERVACIONES
       findObservacion(codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
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
  
  //TIPO TERAPIA
   
      $('#mls').change(function(event) {
       var resp= $(this).prop('checked');
       if(resp){
          $("#getmls").show();
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
       }else{
          $("#gethilt").val("");
          $("#gethilt").hide();
       }
        console.log(resp);
     })
//\TIPO TERAPIA
  
   
   $('[data-toggle="tooltip"]').tooltip(); 


$('#spnconfirm').click(function(){
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

 function removeEditable(v_codconsulta,v_codclien,v_producto,observacion,fecha) { 
  
 // $('#indicator').show();
    // var v_codconsulta ="";
    // try{
    //   v_codconsulta = element.parentNode.parentNode.getAttribute('campo');
    // } catch(err){

    // }
    // var v_codclien    = element.parentNode.parentNode.getAttribute('id');

    // var v_producto    ="";
    // try{
    //   var v_producto    = element.parentNode.parentNode.getAttribute('producto');
    // }catch(err){

    // }


    //var observacion = $('#editbox').val();

    //var fecha = $('#fecha').val();
    //fecha = element.parentElement.parentElement.childNodes[2].innerText;
    if(fecha==''){
            var fecha = new Date().toLocaleDateString('en-US', {  
                month : 'numeric',
                day : 'numeric',        
                year : 'numeric'
        }).split(' ').join('/');
        
     }
     fecha=fecha.trim(); 
     var datasave ={
          tipoConsulta : v_codconsulta,
          codclien     : v_codclien,
          fecha_cita   : fecha,
          observacion  : observacion,
          coditems     : v_producto
      }
      var xres = ajaxGen("../../clases/atencionupdateobservacion.php",datasave)

    // $('td.current').html($(element).val());
    $('.current').removeClass('current'); 

    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));


 

}


$('.pin').on("click", function(e){
  e.preventDefault();
  console.log(e.target);
  var telNumber= this.closest('tr').firstElementChild.textContent.replace(/-/gi, '')

  if (telNumber!="") {
        var body="https://www.google.com/maps/place/Centro+M%C3%A9dico+Adapt%C3%B3geno/@18.399487,-66.1574777,17z/data=!3m1!4b1!4m5!3m4!1s0x8c036a3076a835ff:0x4d7f5bce3afd9b8a!8m2!3d18.399487!4d-66.155289";
        var usuario = document.getElementById('loggedusr').value;
        send_sms(telNumber,body,usuario,'Localización enviada!');
  }

})

function send_sms(telNumber,body,usuario, confirmacion){
      var ajxmed = $.post( "http://192.130.74.2:8080/cma/clases/sendmessage.php",{ to:telNumber,body:body,usuario:usuario }, function(data) {    
      var items="";  
      try{
          items= jQuery.parseJSON(data); 
          console.log(items); 
      } catch(e){
          console.log(e);
      }     
           
            if (typeof items!='undefined' && items!=null ) {            
                Swal.fire({
                type: 'success',
                title: confirmacion ,
                showConfirmButton: true
              })
            }
                   
              })
              .done(function() {
              })
              .fail(function() {
              })
              .always(function() {
              }); 
}

$('.sms').on("click", function(e){

 // clean previuos conversation
 document.getElementById('conversation').innerHTML = "";

// get tel number
 var telefono= this.closest('tr').firstElementChild.textContent.replace(/-/gi, '')
 var paciente_name = this.closest('tr').firstElementChild.nextElementSibling.innerText;
  phone_number=telefono;
  // Get the modal
var modal = document.getElementById("sms-modal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("sms-modal-close")[0];

// When the user clicks the button, open the modal 
 modal.style.display = "block";

 try{
    var user_pacient= document.getElementsByClassName("usuario-paciente")[0];
    user_pacient.innerHTML=document.getElementById('loggedusr').value+' / <span id="sms_paciente">'+paciente_name+'</span>';
 }catch(e){

 }

//search message

loadMessage('1'+telefono);

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
})

 
function loadMessage(telefono){
      $('.loading').show();
      var ajxmed = $.post( "../../clases/getmessages.php",{ telefono }, function(data) {    
      var items="";        
      items= jQuery.parseJSON(data);        
      console.log(items);
      if (items!==null) {   
         displaySMS(items);
      }
              })
              .done(function() {
              })
              .fail(function() {
              })
              .always(function() {
                $('.loading').hide();
              }); 
}


function displaySMS(xres){
   if(xres.length>0){
          var objDiv
          for (var i = 0; i < xres.length; i++) {

              
            if (xres[i].modo=='1') {
                $('#conversation').append(inCommingMesseage(xres[i].sms_body, xres[i].sms_received));
            }else{
                $('#conversation').append( sentMessage( xres[i].sms_body, xres[i].sms_received ) );   
            }
              
            objDiv = document.getElementById("conversation"); 
            objDiv.scrollTop = objDiv.scrollHeight
          };      
 }
}


//Crea un nuevo elemento de llegada
function inCommingMesseage(message,fecha) {
  var head = '<div class="row message-body"> <div class="col-sm-12 message-main-receiver"> <div class="receiver"> <div class="message-text"> ' + message + '</div> ' +
                '<span class="message-time pull-right"> ' +
                  fecha +
                '</span> ' +
              '</div> ' +
            '</div> ' +
          '</div>';
  return head;
}


//Crea un nuevo elemento de salida (Mensaje Enviado)
function sentMessage(message,fecha){
 var head = ' <div class="row message-body"> ' +
            '<div class="col-sm-12 message-main-sender"> '+
              '<div class="sender">'+
                '<div class="message-text">'+message+
                '</div>'+
                '<span class="message-time pull-right">'+
                  fecha+
                '</span>'+
              '</div>'+
            '</div>'+
          '</div>';
   return head;         
}

//CLICK PARA ENVIAR EL MENSAJE
$('.reply-send').click(function(){
  send_message()  
});


function send_message(){
  //OBJETO CON LOS DATOS PARA ENVIAR
  var dateTime=dateTimeUnix()
  let _mensaje ={
              to : phone_number,
              message :  $('#comment').val(),
              type :'send',
              fecha : dateTime
  }
   
  //socket.emit('message',_mensaje);  
  $('#conversation').append( sentMessageToDisplay(_mensaje.message,_mensaje.fecha) );   
  console.log(_mensaje)

  var body=_mensaje.message;
  var usuario = document.getElementById('loggedusr').value;
  send_sms(phone_number,body,usuario," SMS Enviado! ");

  //var time = new Date();
  //let hora = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true })
  //saveData( _mensaje.to,_mensaje.message,hora,2,'i',_mensaje.from )
  //LIMPIA EL INPUT TEXT DE LOS MENSAJES
  $('#comment').val('')

  objDiv = document.getElementById("conversation"); 
  objDiv.scrollTop = objDiv.scrollHeight




}

function sentMessageToDisplay(message,fecha){
 var head = ' <div class="row message-body"> ' +
            '<div class="col-sm-12 message-main-sender"> '+
              '<div class="sender">'+
                '<div class="message-text">'+message+
                '</div>'+
                '<span class="message-time pull-right">'+
                  fecha+
                '</span>'+
              '</div>'+
            '</div>'+
          '</div>';
   return head;         
}


function dateTimeUnix(){
var unix_timestamp = Math.round((new Date()).getTime() / 1000);   
var d = new Date(unix_timestamp*1000);

var dtime =  d.getFullYear() + "-" + 
    ("00" + (d.getMonth() + 1)).slice(-2) + "-" + 
    ("00" + d.getDate()).slice(-2) + " " + 
    ("00" + d.getHours()).slice(-2) + ":" + 
    ("00" + d.getMinutes()).slice(-2) + ":" + 
    ("00" + d.getSeconds()).slice(-2);
return dtime;
}


function time_Unix(){
// Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
var date = new Date(unix_timestamp*1000);
// Hours part from the timestamp
var hours = date.getHours();
// Minutes part from the timestamp
var minutes = "0" + date.getMinutes();
// Seconds part from the timestamp
var seconds = "0" + date.getSeconds();

// Will display time in 10:30:23 format
var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
}
