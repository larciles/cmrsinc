$(function(){
	
$("#wait").hide(); 
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
   
   	$(document).on("dblclick", "td.edit", function(){ makeEditable(this); });
    $(document).on("blur", "input#editbox", function(){ removeEditable(this) }); 
  
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
	////console.log(element)
	$(element).html('<input id="editbox" size="'+  $(element).text().length +'" type="text" value="'+ $(element).text() +'">');  
	$('#editbox').focus();
	$(element).addClass('current');    //
   var nodoTd = element.parentNode; //Nodo TD
   var nodoTr = nodoTd.parentNode; //Nodo TR   
   var nodoTd = element.parentNode; //Nodo TD
   var nodoTr = nodoTd.parentNode; //Nodo TR
   var nodosEnTr = nodoTr.getElementsByTagName('tr');
   var coditems = nodoTd.id
}
   //---------------------------------------------------
   $('.confirmado').change(function(event) {
      event.stopPropagation();
      var resp= $(this).prop('checked');
      if (typeof elementconf== 'undefined') {
          elementconf = this
      }
      if (typeof  counter== 'undefined') {counter=0}
      var coditems=elementconf.parentElement.parentElement.parentElement.attributes['producto'].value 
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

      if(nombre=="" || apellidos=="" || cedula=="" || telefono=="" ){
        return;
      }

      var  fields = {
        nombre :nombre,
        apellidos : apellidos,
        nombres : nombres,
        cedula : cedula,
        telefono : telefono
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
                    loadMedicos(items[0].codmedico)
                    $(".dividassoc").hide();
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
      //console.log('el cod es '+codmedico)
   })
  //ends Medicos Change----------------------------------------------------------

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
    //END CONSULTAS 1-----------------------------------------------

   //save button
   //
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

  
    try {
        $('#idpaciente').val(idCreate);
        id=idCreate;
        idCreate="";
        findOutPacient(id)
        console.log(idCreate);
        console.log($('#idpaciente').val()) 
    }
    catch(err) {
        console.log(err.message);
    }
   
  })

//** ENDS MODAL
//-------------------------------------


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