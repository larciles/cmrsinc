$(function(){
	
  var lastUser
  var elementconf = undefined;
  var counter=0;

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
	// $("form #cbfecha").attr('checked', true)
	// alert( "Handler for .change() called." );
	$( "form #cbfecha" ).change(function() {
 		if ($(this).prop('checked')==true){       		
      		$('#fecha').removeAttr( "disabled" );
   		}else{
   			$('#fecha').attr( "disabled","true" );   			
   		}
	});
   
   	$(document).on("dblclick", "td.edit", function(){ makeEditable(this); });
    $(document).on("blur", "input#editbox", function(){ removeEditable(this) });

   //   $(document).on("click", "td", function(){ currentPaciente(this); });
  
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

      //alert( "success" ); 
    })
    .done(function() {
      //console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      //console.log();
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
  // function getCodCl(element) { 
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
      //console.log(tipoConsulta);
      //console.log(codclien);
      //console.log(coditems);
      //console.log(fecha)
      //console.log(usuario)
      //console.log(resp)
      //console.log(counter)
      counter++
      // $(elementconf).find('*').attr('disabled', true);

    //   var hr = new XMLHttpRequest();
    //   hr.open("POST", "../../clases/controlcconfirmado.php", true);
    //   hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //   hr.onreadystatechange = function() {
    //   if(hr.readyState == 4 && hr.status == 200) {
    //    // var data = JSON.parse(hr.responseText);
    //     //console.log("ok")    
    //   }
    // }
    // hr.send("tipoConsulta="+tipoConsulta+"&codclien="+codclien+"&coditems="+coditems+"&fecha="+fecha+"&usuario="+usuario+"&resp="+resp);
   
   
      setConfirmar(tipoConsulta,codclien,coditems,fecha,usuario,resp)
    })

//-------------------------------------------------------
$('.citar').click(function(){
  element=this
  var nodoTd = element.parentElement.parentElement; //Nodo TD
  lastUser=nodoTd.id
  //console.log(lastUser)
  //alert(lastUser)
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
      //alert( "success" );
     //console.log('cool');
   
      })
      .done(function() {
       //console.log();
        //alert( "second success" );
      })
      .fail(function() {
        alert( "error" );
      })
      .always(function() {
        //alert( "finished" );
        //console.log();
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


//** MODAL
//*********
//*********
//*********
  $('#exampleModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('whatever') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    
    var objPaciente
    var observ= $('#message-text').val()

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
        var fecha = $('#fechaNewApmt').val()
        //console.log(fecha)
        today = new Date(fecha)
        dayIndex = today.getDay()
         
         //VALIDACION DE LOS DIAS DOMINGOS
        if (dayIndex==0) {
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
        
       
       // $('#fecha').removeAttr( "disabled" );    
       // $('#fecha').attr( "disabled","true" );        
    
    });

  
    // var xfec = $("fechaNewApmt").val()



    $('#idpaciente').keyup(function(e){

      
        if (e.which == 13) {
            var id=$('#idpaciente').val()
            ////console.log(id)
            var codMedOculto = $("#medicohd").val();
            var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 
            ////console.log(data)
            var items="";
            var options="";
            items = jQuery.parseJSON(data);
            modal.find('.modal-title').text('Nueva cita ' )
            if (typeof items!= 'undefined' && items!==null) {      
      
              if(items.length>0){   
                 modal.find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
                 objPaciente = items[0].codclien
                 loadMedicos(items[0].codmedico)
               }else{
                 modal.find('.modal-title').text('Nueva cita ' )
               }
               }
            })
                .done(function() {
                  //console.log();
                  //alert( "second success" );
                })
                .fail(function() {
                  alert( "error" );
                })
                .always(function() {
                  //alert( "finished" );
                  //console.log();
                }); 
        }
  
  })

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
      ////console.log(jQuery.type( resp ) )
  
     })


  //Medicos LOAD--------------------------------------------------------
    function loadMedicos(codMedOculto){
         
    if (typeof codMedOculto === "undefined" || codMedOculto === null) { 
         codMedOculto = ""; 
      }

        var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
        ////console.log(data)
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
        ////console.log(items)
    
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
            //console.log();
            //alert( "second success" );
          })
          .fail(function() {
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          });
        }
 ////ends Medicos LOAD
 //Medicos Change----------------------------------------------------------
    $('#medico').change(function(event){
      event.preventDefault()
      var codmedico = $(this).val();
      //console.log('el cod es '+codmedico)
   })
  //ends Medicos Change----------------------------------------------------------
   //LOAD CONSULTAS 1------------------------------------------------------------
   function loadConsultas(){
     var codMedOculto = $("#citashd").val();
     var ajxmed = $.post( "../../clases/consultas.php",{}, function(data) { 
     ////console.log(data)
     var items="";
     var options="";
     items= jQuery.parseJSON(data);
     ////console.log(items)
    
     for (var i = 0; i < items.length; i++) {
         if (codMedOculto==items[i].codmedico){
               options+="<option selected value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
           }else{
               options+="<option value='"+items[i].codcons+"'>"+items[i].descons+"</option>";  
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
         alert( "error" );
       })
       .always(function() {
         //alert( "finished" );
         //console.log();
       }); 
   }
    //END CONSULTAS 1-----------------------------------------------
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
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          }); 
   }
   //-- END SUERO
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
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          }); 
   }
   //--ENDS LASER--------------------------------------------------------------------


   //save button
   //
     $('#save').click(function(){
      var fecha = $('#fechaNewApmt').val()
      today = new Date(fecha)
      dayIndex = today.getDay()
      //console.log(dayIndex)
      if (dayIndex>0) {
         
         if($("#fgfechacita").hasClass("has-error")){
            $("#fgfechacita").removeClass("has-error");
         }

         var today = new Date().toLocaleDateString('en-US', {  
          month : 'numeric',
          day : 'numeric',        
          year : 'numeric'
          }).split(' ').join('/');


          var observ = $('#message-text').val()           
          var medico = $( "#medico" ).val();
          var cita = $( "#citas" ).val();
          var coditem ="" 
          if(cita.length>2){
             var cita = cita.substring(0,2);
             var coditems  = cita.substring(2);
          }         

          //console.log(observ)
          //console.log(fecha)
          //console.log(medico)
          //console.log(cita) 

          if(!holidaysValidation(fecha)){

            //valida si posee otra consulta
            //var result = citasAbiertasValidation(objPaciente)
             var resp= $('#tipocita').prop('checked');  // TRUE  CITA MEDICA NORMAL
              if(resp){
                
                var tipocita =0 
                var xres = ajaxGen("../../clases/controlvalidations.php",{ q : objPaciente , valtype : 'citas-abiertas' })
                var openApmnt = controlValidations(objPaciente,'citas-abiertas')
                var numeroDeCitas =  controlValNumCitas(objPaciente)
                console.log(numeroDeCitas)
                var objUser = getUser(usuario)
                user = objUser[0].controlcita
                //var user = controlValidations(usuario,'get-user')

                var vCitados = 1
                var vNoCitados = 0
                if(!openApmnt){
                   
                    if(user=='1'){
                      //cita por control de citas
                      var zuser=usuario
                      var xAsistido = "0"
                      var primera_control
                      var vcitacontrol 

                       if( numeroDeCitas>0){
                          primera_control = "0"                        
                       }else{
                          primera_control = "1"   
                       }

                       if( numeroDeCitas==1){
                          vcitacontrol = 1
                       }else{
                          vcitacontrol = 0
                       }

                       var chkCitas = checkCitas(usuario,fecha)
                       if(!chkCitas){
                          var Nconfirmado = 0
                       }else{

                       }
                       checkInsertCitas(objPaciente,today,vCitados,fecha,medico,zuser,today,observ,primera_control,vNoCitados,Nconfirmado,vcitacontrol,tipocita,xAsistido,cita)
                    }

                }



            }
           
          

          }
         //en el archivo de validaciones 
          /*
            
            select * from mconsultas where codclien='13769' and asistido=0
            verifica que el paciente no tenga otras citas, si tiene la cambia sino agrega una nueva
            
             ...

             insert into mconsultas  (codclien,fecha,hora,citados,fecha_cita,codmedico,ipaddress,workstation,usuario,fecreg,observacion,primera_control,nocitados,confirmado,citacontrol,servicios,asistido,codconsulta,HoraRegistro) values ('13769','20170210','5:11:38 PM',1,'20170210','315','192.168.56.1','DESKTOP-5MS92AS','CCBAY1','20170210','prueba de sistemas','1','0',0,0,'0','0','01','5:24:41 PM') 

          */
         //
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
            //console.log();
            //alert( "second success" );
          })
          .fail(function() {
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
          //  //console.log();
          }); 
    return lreturn;      
   }


   function controlValidations(codclien,valtype){

        var lreturn=false;
        var ajxmed = $.post( "../../clases/controlvalidations.php",{ q : codclien , valtype : valtype }, function(data) {         
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
        

        if (typeof items!= 'undefined' && items!==null ) {            
            if(items.length>0){   

              if(valtype=='citas-abiertas'){
                 lreturn=true;   
              }else if(valtype=='numero-citas'){
                  lreturn = items[0].cant
                  ////console.log(lreturn)
              }

            }
        }
                //alert( "success" ); 
          })
          .done(function() {
           // //console.log();
            //alert( "second success" );
          })
          .fail(function() {
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            ////console.log();
          }); 
    return lreturn;      
   }




   function citasAbiertasValidation(codclien){

        var lreturn=false;
        var ajxmed = $.post( "../../clases/controlcitasabiertasvalidations.php",{ q : codclien  }, function(data) {         
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
           // //console.log();
            //alert( "second success" );
          })
          .fail(function() {
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
           // //console.log();
          }); 
    return lreturn;      
   }


 function getUser(user){
  var items;
  var lretncitas;
  var arr = [];
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/getuser.php",
            data:{ q : user }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items[0].cant

                      for(var x in items){
                          arr.push(items[x]);
                      } 
                      lretncitas = arr;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
 
   }


 function checkCitas(user,fechacita){

        var lreturn=false;
        var ajxmed = $.post( "../../clases/checkcitas.php",{ q : user  }, function(data) {         
        var items="";
        var options="";
        var arr = [];
        items= jQuery.parseJSON(data);
        

        if (typeof items!= 'undefined' && items!==null  ) {            
            if(items.length>0){   
              lreturn = true
            }
        }
                //alert( "success" ); 
          })
          .done(function() {
            //console.log();
            //alert( "second success" );
            var   xjson = JSON.stringify(items[0]);
           // //console.log('tba :'+xjson)
          })
          .fail(function() {
            alert( "error" );
          })
          .always(function() {
            //alert( "finished" );
            //console.log();
          }); 
    return lreturn;      
   }


function checkInsertCitas(objPaciente,today,vCitados,fecha,medico,zuser,today,observ,primera_control,vNoCitados,Nconfirmado,vcitacontrol,tipocita,xAsistido,cita){
          
    var fields = {
      codclien : objPaciente,       
      fecha : today,
      citados : vCitados,
      fecha_cita : fecha,
      codmedico : medico,
      usuario : zuser,
      fecreg : today,
      observacion : observ,
      primera_control : primera_control,          
      nocitados : vNoCitados,
      confirmado : Nconfirmado, 
      citacontrol : vcitacontrol,
      servicios : tipocita,
      asistido : xAsistido,
      codconsulta : cita
    }    

      $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/insertcitas.php",
            data:fields, 
            success:  function(respuesta){                        
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items[0].cant
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });
   }


function controlValNumCitas(codclien){
  var items;
  var lretncitas;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/controlvalnumcitas.php",
            data:{ q : codclien }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items[0].cant
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
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
  