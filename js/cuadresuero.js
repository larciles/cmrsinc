$(function(){

 $('#xtitulo').html('<b>Cuadre Sueroterapia</b>');

 var userdefault = $("#c_usuariohd").val();
//-USUARIOS
  var jqxhr = $.post( "../../clases/usersload.php",{}, function(data) { 
	//console.log(data)
	var items="";
	var users="";
	items= jQuery.parseJSON(data);
	//console.log(items)

	for (var i = 0; i < items.length; i++) {
	    if(items[i].login.toLowerCase()==userdefault.toLowerCase()){
			    users+="<option selected value='"+items[i].login+"'>"+items[i].usuario+"</option>";
	    }else{
	        users+="<option value='"+items[i].login+"'>"+items[i].usuario+"</option>";
	    }    	
	}
    $("#c_usuario").html(users); 
      //alert( "success" ); 
    })
    .done(function() {
      console.log();
      //alert( "second success" );
    })
    .fail(function() {
     // alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });
//\-USUARIOS

//-USUARIOS CHANGE
    $('#c_usuario').change(function(event){
      event.preventDefault()
      var iduser = $(this).val();
      console.log('el cod es '+iduser)
      var startdate;

      startdate  =$('#efechai').val(); 
    
      if (startdate!=='' ) {
         getCash(startdate, iduser); 
         getGrupo(startdate,iduser);
         getAllCashByUser(startdate,iduser);
      };

   })

//--

  $('#start').change(function(){
     let totalCash=0;
     totalCash=totalCash+parseFloat($('#totalcash').val())+parseFloat($('#start').val());
     $('#totalcash').val(totalCash);
     totalCajaChash($('#totalscash').val());
  })


 //-FECHA
  var date_input=$('input[name="efechai"]'); 
  var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
  format: 'mm/dd/yyyy',
  container: container,
  todayHighlight: true,
  autoclose: true,
  });
  //\ FECHA


  //\ FECHA  CHANGE
   $('#efechai').change(function(){
    var startdate,user;
    startdate  =$('#efechai').val();
    user = $('#c_usuario').val()
    

    if (startdate!=='' ) {
       getCash(startdate, user);
       getGrupo(startdate,user);
       getAllCashByUser(startdate,user);
 
    };
 });


    $('.tiporeporte').change(function() {
     let tiporeporte = $('.tiporeporte').prop('checked');
     let  startdate,user;
     startdate  =$('#efechai').val();
     user = $('#c_usuario').val()
     $('#div1 *').prop('disabled',!tiporeporte);
     if (startdate!=='' ) {
        getCash(startdate, user);
        getGrupo(startdate, user);
        getAllCashByUser(startdate,user); 
    };

  })   

function getAllCashByUser(fecha, usuario){
  let tiporeporte = $('.tiporeporte').prop('checked');
  let items="";
  let ajxmed = $.post( "../../clases/getallcashbyuser.php",{ fecha,usuario,tiporeporte,id_centro:'S'}, function(data) { 
  let html = '';  
  let monto=0;
  let total=0;

  var Table = document.getElementById("userscash");
  Table.innerHTML = "";

  items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {     
       var hoy = new Date();
       var hoyDateString;
       hoy.setDate(hoy.getDate());  
       hoyDateString = ('0' + (hoy.getMonth()+1)).slice(-2) + '/' +('0' + hoy.getDate()).slice(-2) + '/' +  hoy.getFullYear();

       var selFecha = new Date($('#efechai').val());
       var selFechaDateString;
       selFecha.setDate(selFecha.getDate());  
       selFechaDateString = ('0' + (selFecha.getMonth()+1)).slice(-2) + '/' +('0' + selFecha.getDate()).slice(-2) + '/' +  selFecha.getFullYear();

       d1 = new Date(hoyDateString);
       d2 = new Date(selFechaDateString)

       let display='display: none';
       let cdperfil = $( "#c_cod_perfil_hd" ).val()
       //$("#access").val()=='6' || $("#codperfil").val()=='01'
       //hoyDateString==selFechaDateString
       if (cdperfil=='01') {
           display='display: inline';     
        };  
 
      if(items.length>0){ 
      
         for(var i = 0; i < items.length; i++){

            monto = parseFloat(items[i].monto).toFixed(2)  
            total+=parseFloat(monto);

             html += '<tr id='+items[i].usuario+'>'+                  
                '<td align="left" >' + items[i].usuario +'</td>'+
                '<td align="right" >' + monto +'</td>'+
                '<td align="center"> <button type="button" name="eliminar" id="" class="btn btn-primary eliminar" style="'+display+'";>Eliminar</td>'+              
              '</tr>';           
          } 
             html += '<tr>'+                  
                '<td align="center" > Total </td>'+
                '<td align="left" >' + parseFloat(total).toFixed(2)  +'</td>'+
                '<td align="center"></td>'+              
              '</tr>';
          $("#userscash").html(html);
      }
    }
    })
        .done(function() {
        
        })
        .fail(function() {
          
        })
        .always(function() {
        
        }); 

        if(items.length>0){
        
        }
}

$('#tbluserscash').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var id = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
   let user = $("#idusr").val();
   if(elemt.attr('class').indexOf("eliminar")>-1) {
         let  startdate;
         startdate  =$('#efechai').val();       
         deleteCashUser(startdate,id);
         window.location.reload();              
   }
})

function deleteCashUser(fecha,usuario){  
  let ajxmed = $.post( "../../clases/deletecashuser.php",{ fecha,usuario,id_centro:'S'}, function(data) { 
  items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {  
         window.location.reload(); 
    }
    })
        .done(function() {
           window.location.reload();      
        })
        .fail(function() {
          
        })
        .always(function() {
           window.location.reload(); 
        });    
}

function getGrupo(fecha, usuario){
  let tiporeporte = $('.tiporeporte').prop('checked');
  var jqxhr = $.post( "../../clases/cuadresuerosgrupo.php",{fecha:fecha,tiporeporte,usuario}, function(data) { 
  var items="";
  var users="";
  items= jQuery.parseJSON(data);
  var totalCash=0;
  var totalCard=0;

  $('#mastercard').val(0); 
  $('#visa').val(0);
  $('#amex').val(0);
  $('#ath').val(0);
  $('#salescash').val(0);
  $('#totalcash').val(0);
  $('#totalcard').val(0); 
  $('#totalcma').val(0);  

  if (items!==null) {
      for (var i = 0; i < items.length; i++) {
       // total=total+(items[i].valor*items[i].monto);
        if (items[i].modopago=='MASTERCARD') {
          $('#mastercard').val(items[i].monto); 
          totalCard = totalCard + parseFloat(items[i].monto);   
        }else if(items[i].modopago=='VISA'){
          $('#visa').val(items[i].monto);
          totalCard = totalCard + parseFloat(items[i].monto); 
        }else if(items[i].modopago=='AMEX'){
          $('#amex').val(items[i].monto);
          totalCard = totalCard + parseFloat(items[i].monto); 
        }else if(items[i].modopago=='ATH'){
          $('#ath').val(items[i].monto);
          totalCard = totalCard + parseFloat(items[i].monto); 
        }else if(items[i].modopago=='CASH'){
          $('#salescash').val(items[i].monto);
          totalCma = parseFloat(items[i].monto);
          totalCash=totalCash+parseFloat(items[i].monto)+parseFloat($('#start').val());
          $('#totalcash').val(totalCash);
          //$('#depositcash').val(items[i].monto);        
        };
           
      }

      $('#totalcard').val('$'+totalCard); 
      var totalCma=totalCma+totalCard;

      $('#totalcma').val('$'+totalCma); 


      totalCajaChash($('#totalscash').val());
      
  };
   // $("#c_usuario").html(users); 
      //alert( "success" ); 
    })
    .done(function() {
      console.log();
      //alert( "second success" );
    })
    .fail(function() {
     // alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });

}
//----------------------
   function getCash(fecha, usuario){
  let tiporeporte = $('.tiporeporte').prop('checked');  
  var jqxhr = $.post( "../../clases/getcashsuero.php",{fecha:fecha,usuario:usuario,tiporeporte}, function(data) { 
  var items="";
  var users="";
  items= jQuery.parseJSON(data);
  var total=0;
   $('#cien').val(0);
   $('#cincuenta').val(0);
   $('#twenty').val(0);
   $('#ten').val(0);
   $('#five').val(0);
   $('#one').val(0);
   $('#halfdollars').val(0);
   $('#quarters').val(0);
   $('#dimes').val(0);
   $('#nickels').val(0);
   $('#pennies').val(0);
   $('#totalscash').val(0);
   $('#cash').val(0);
   $('#depositcash').val(0);


   $('#tcien').text('$0');
   $('#tcincuenta').text('$0');
   $('#ttwenty').text('$0');
   $('#tten').text('$0');
   $('#tfive').text('$0');
   $('#tone').text('$0');
   $('#thalfdollars').text('$0');
   $('#tquarters').text('$0');
   $('#tdimes').text('$0');
   $('#tnickels').text('$0');
   $('#tpennies').text('$0');
   $('#dif').text('$0');
  if (items!==null) {
      for (var i = 0; i < items.length; i++) {
        total=total+(items[i].valor*items[i].monto);
        if (items[i].valor=='100.0000') {
          $('#cien').val(items[i].monto);
          $('#tcien').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='50.0000'){
          $('#cincuenta').val(items[i].monto);
          $('#tcincuenta').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='20.0000'){
          $('#twenty').val(items[i].monto);
          $('#ttwenty').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='10.0000'){
          $('#ten').val(items[i].monto);
          $('#tten').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='5.0000'){
          $('#five').val(items[i].monto);
          $('#tfive').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='1.0000'){
          $('#one').val(items[i].monto);
          $('#tone').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='.5000'){
          $('#halfdollars').val(items[i].monto);
          $('#thalfdollars').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='.2500'){
          $('#quarters').val(items[i].monto);
          $('#tquarters').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='.1000'){
          $('#dimes').val(items[i].monto);
          $('#tdimes').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='.0500'){
          $('#nickels').val(items[i].monto);
          $('#tnickels').text('$'+items[i].valor*items[i].monto);
        }else if(items[i].valor=='.0100'){
          $('#pennies').val(items[i].monto);
          $('#tpennies').text('$'+items[i].valor*items[i].monto);
        };
           
      }

      $('#totalscash').val(total);
      totalCajaChash(total);

  };
   // $("#c_usuario").html(users); 
      //alert( "success" ); 
    })
    .done(function() {
      console.log();
      //alert( "second success" );
    })
    .fail(function() {
     // alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
      console.log();
    });
   }
 //----------------------
  $('#cien').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,100)
  })
  //----------------------
  $('#cincuenta').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,50)
  })
  //----------------------
  $('#twenty').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,20)
  })
  //----------------------
  $('#ten').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,10)
  })
  //----------------------
  $('#five').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,5)
  })
  //----------------------
  $('#one').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,1)
  })
  //----------------------
  $('#halfdollars').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,.5)
  })
  //----------------------
  $('#quarters').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,.25)
  })
  //----------------------
  $('#dimes').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,.1)
  })
  //----------------------
  $('#nickels').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,.05)
  })
  //----------------------
  $('#pennies').change(function(){    
    cash=parseFloat($(this).val())    
    totalChash(cash,.01)
  })  
 //----------------------
 function totalChash(cash,denominacion){
  if ($.isNumeric(cash) && $.isNumeric(denominacion) ) {

     var total =parseFloat( $('#totalscash').val() ); 
     if (!$.isNumeric(total)) {
        total=0;
     };
     total=total+(cash*denominacion)
     $('#totalscash').val(total);
      totalCajaChash(total);
    // $('#totalscash').val('$'+total);

      console.log(cien);
      SaveTotalChash()
     //savecuadreconsultas.php

   };
 }
 //---------------------
 function SaveTotalChash(){
    var cien      = !$.isNumeric(parseFloat( $('#cien').val() ))? 0:parseFloat( $('#cien').val() );
    var cincuenta = !$.isNumeric(parseFloat( $('#cincuenta').val() ))? 0:parseFloat( $('#cincuenta').val() );
    var twenty    = !$.isNumeric(parseFloat( $('#twenty').val() ))? 0:parseFloat( $('#twenty').val() );
    var ten       = !$.isNumeric(parseFloat( $('#ten').val() ))? 0:parseFloat( $('#ten').val() );
    var five      = !$.isNumeric(parseFloat( $('#five').val() ))? 0:parseFloat( $('#five').val() );
    var one       = !$.isNumeric(parseFloat( $('#one').val() ))? 0:parseFloat( $('#one').val() );
    var halfdollars = !$.isNumeric(parseFloat( $('#halfdollars').val() ))? 0:parseFloat( $('#halfdollars').val() );
    var quarters    = !$.isNumeric(parseFloat( $('#quarters').val() ))? 0:parseFloat( $('#quarters').val() );
    var dimes       = !$.isNumeric(parseFloat( $('#dimes').val() ))? 0:parseFloat( $('#dimes').val() );
    var nickels     = !$.isNumeric(parseFloat( $('#nickels').val() ))? 0:parseFloat( $('#nickels').val() );
    var pennies     = !$.isNumeric(parseFloat( $('#pennies').val() ))? 0:parseFloat( $('#pennies').val() );  
    
    var arrCash = [];
    arrCash.push({monto:cien,valor:100}); 
    arrCash.push({monto:cincuenta,valor:50}); 
    arrCash.push({monto:twenty,valor:20}); 
    arrCash.push({monto:ten,valor:10}); 
    arrCash.push({monto:five,valor:5}); 
    arrCash.push({monto:one,valor:1}); 
    arrCash.push({monto:halfdollars,valor:.5}); 
    arrCash.push({monto:quarters,valor:.25}); 
    arrCash.push({monto:dimes,valor:.1}); 
    arrCash.push({monto:nickels,valor:.05}); 
    arrCash.push({monto:pennies,valor:.01}); 

    //usuario =$('#c_usuariohd').val();
    usuario = $("#c_usuario").val()  
    fecha   =$('#efechai').val();  
     //Hoy
    var hoy = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
    }).split(' ').join('/');

    if (fecha=="") {
       fecha=hoy;
    };
    let tiporeporte = $('.tiporeporte').prop('checked');  
    var datasave ={
         cash : arrCash
        ,usuario : usuario
        ,Id_centro : 'S'
        ,tipo : 'S'
        ,estacion : 'CMA'        
        ,fecha : fecha
        ,tiporeporte
    };

    ajaxGen("../../clases/savecuadreconsultas.php",datasave);

 }
 //---------------------
 function ajaxGen(url,data){
    var res;
    $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: url,
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


// Habilitar o deshabilitar usuario dependeien del codigo de perfil

var codperfil = $( "#c_cod_perfil_hd" ).val()
if (codperfil=='02') {
       if ( $('#access').val()=='6' ) {} else{
       $( "#c_usuario" ).prop( "disabled", true );  
    };
};

function totalCajaChash(cash){
   $('#cash').val(cash);
   $('#dif').text('');
   $('#dif').text('0')
   var inicio = parseFloat( $('#start').val());
   var ttotal = parseFloat(cash);
  // $('#totalcash').val(ttotal);
   ttotal
   $('#depositcash').val(ttotal-inicio); 

   $('#depositcash').val(ttotal-inicio); 
   vtefectivo =parseFloat($('#totalcash').val());
  var dif=ttotal-vtefectivo;
  var difs=dif.toString(); 
   $('#dif').text(difs);        

}

var hoy = new Date().toLocaleDateString('en-US', {  
    month : 'numeric',
    day : 'numeric',        
    year : 'numeric'
}).split(' ').join('/');

$('#efechai').val(hoy);
user = $('#c_usuario').val();
 $('#startdate').val(hoy);
getCash(hoy, user);
getGrupo(hoy,user);


// var greeting = "Good" + ((now.getHours() > 17) ? " evening." : " day.");  


});


  //-------------------------------------------------------------------------
  $('body').on('keydown', 'input, select, textarea', function(e) {
        var self = $(this)
          , form = self.parents('form:eq(0)')
          , focusable
          , next
          ;
        if (e.keyCode == 13) {
            focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
           
            if(self.hasClass('cantidad')){
                //next = focusable.eq(4);   //INDICE DEL BOTON ADD MORE
                document.getElementById('add').click();
            }else{
                next = focusable.eq(focusable.index(this)+1);
            }

            try{
              if (next.length) {
                next.focus();
                next.select();
            } else {
               // form.submit();
            }
        }catch(err){

        }

            
            return false;
        }
  });
  //-------------------------------------------------------------------------
  $( "#cien" ).focus();