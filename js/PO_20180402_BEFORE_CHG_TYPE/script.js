$(function(){
	$('#xtitulo').html('<b>Nuevos pedidos</b>');
	var date_input=$('input[name="fecha"]'); //our date input has the name "date"
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
		format: 'mm/dd/yyyy',
		container: container,
		todayHighlight: true,
		autoclose: true,
	})
	
	$('.cancela').click(function(){
      let pon =  $("#cancelar").val();
      var jqxhr = $.post( "../../clases/deletepo.php",{ pon }, function() { 
           
            location.reload();  
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
  })

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
  
   
    $( '#btn-manualpo' ).click(function() {

       if($('#manualpo').val()!=""){
           var pon=$('#manualpo').val();
           var confirmar = confirm('Seguro de agregar esta nueva Orden de Compra # '+pon+' ?');
            if (confirmar){
                                  
                var jqxhr = $.post( "../../controllers/Controller.php",{  action: 'ordenManual',pon : pon }, function() { 
                $('#manualpo').val('');
                  location.reload();  
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

            }
      }
        
     });

   //INICIO CONVIERTE UN PEDIDO SUGERIDO EN UNA ORDEN DE COMPRA
   $( '#btn-newpo' ).click(function() {

      if($('#newpo').val()!=""){
       	  var meses = $( '#meses' ).val();
       	  var criterio1 = $( '#criterio1' ).val();
       	  var criterio2 = $( '#criterio2' ).val();
       	  var chked=0
           
          var pon=$('#newpo').val();
          var obj = [];
          //Recorro la tabla
          $('#tabla-puor tr').each(function (element) {
          var length = $(this).first().children().length;
          var coditems = $(this).find("td").eq(0).attr('id')
          var qty = $(this).find("td").eq(length-1).html();
          console.log(length) 
          console.log(coditems) 
          console.log(qty) 
          
          if(coditems!=undefined){ 
             tmp = {
                  'coditems': coditems,
                   'qty': qty
             };
             obj.push(tmp);
            }          
        });


    var confirmar = confirm('Seguro de agregar esta nueva Orden de Compra # '+pon+' ?');
      if (confirmar){ 
 
        	var jqxhr = $.post( "../../controllers/Controller.php",{  action: 'pedidoAOrden', productos : obj ,pon : pon }, function() { 
        	 $('#newpo').val('');
          	location.reload();  
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
    }
  
}
	});
  
  //INICIO CONVIERTE UN PEDIDO SUGERIDO EN UNA ORDEN DE COMPRA
  $('.tipopedido').change(function() {     
     let tipopedido = $('.tipopedido').prop('checked');
     $('#typepedido').val(tipopedido);
     
     $('#submit').click();
   })

   // let istru=true;
   // if (  $('#typepedido').val()=="false" ) {
   //     istru=false;
   // }
   
   // $(".tipopedido").trigger('click').prop('checked', istru);  
   // $("tipopedido").trigger('click').prop('checked', istru);    
})


function makeEditable(element) { 
	//console.log(element)
	$(element).html('<input id="editbox" size="'+  $(element).text().length +'" type="text" value="'+ $(element).text() +'">');  
	$('#editbox').focus();
	$(element).addClass('current'); 
   //
   var nodoTd = element.parentNode; //Nodo TD
   var nodoTr = nodoTd.parentNode; //Nodo TR 
}

function removeEditable(element) { 
	
	$('#indicator').show();
	
    var nodoTd = element.parentNode; //Nodo TD
    var nodoTr = nodoTd.parentNode; //Nodo TR
    var nodosEnTr = nodoTr.getElementsByTagName('tr');
    

    var criterio1 = $('#criterio1').val();
    var criterio2 = $('#criterio2').val();

    var coditems=$('.current').attr('coditems');
  	var purchaceOrder = new Object();
  	purchaceOrder.id = $('.current').attr('coditems');
  	purchaceOrder.po = $('.current').attr('po');		
  	purchaceOrder.field = $('.current').attr('field');
  	purchaceOrder.newvalue = $(element).val();
    purchaceOrder.average = $('td#av'+'-'+coditems).text();
    purchaceOrder.criterio1 = criterio1;
    purchaceOrder.criterio2 = criterio2;
    
    var rowCount = $(nodoTr).find("td").eq(1).html(); 
     	
	  var userJson = JSON.stringify(purchaceOrder);
    var miID='total'+'-'+coditems
    
	  $('td.current').html($(element).val());
	  $('.current').removeClass('current');	
	  $('#indicator').hide();		

    //columna de promedio    
    //console.log($('td#av'+'-'+coditems).html($(element).val()))
    //columna total CAMBIA EL VALOR
	  //$('td#total'+'-'+coditems).html($(element).val());

    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));


  var jqxhr = $.post( "../../controllers/Controller.php",{ action: 'update_field_data', producto: userJson }, function() {
  //alert( "success" );

  console.log(jqxhr.responseText);
  
  var obj = JSON.parse(jqxhr.responseText.substring(jqxhr.responseText.indexOf('total')-2));
  //columna total CAMBIA EL VALOR
  $('td#total'+'-'+coditems).html(obj.total)  
  //columna de Meses    
  $('td#meses'+'-'+coditems).html(obj.meses)
  //columna de Pedido?
  $('td#pedido'+'-'+coditems).html(obj.pedido) //
  //columna de Nuevo pedido
  $('td#newpedido'+'-'+coditems).html(obj.nuevopedido) //
  //columna de Redondeado
  $('td#rounded'+'-'+coditems).html(obj.redondeado) //
  
  // location.reload() //Reqcar todo el documento pero no me sirve para este caso en particular
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
 

	// $.post('Controller.php',
	// 	{
	// 		action: 'update_field_data',			
	// 		producto: userJson
	// 	},
	// 	function(data, textStatus) {
		
	// 		 window.location.assign("http://locahost/mvc/index.php")	
	// 	}, 
	// 	"json"		
	// );	
}