$(document).ready(function(){ 


    //productos Select
 	let  url,items,options ;		       
   	url="../../clases/getprodcompras.php";
   	var api = new  XMLHttpRequest();
   	api.open('POST',url,true);
   	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
   	api.send();
   	api.onreadystatechange = function(){
   	if(this.readyState === 4 && this.status === 200){
	   		items=JSON.parse(this.responseText);	
	   		options+="<option value='-'>Elija Producto</option>"; 	
	   		for (var j = 0; j < items.length; j++) { 
          if (items[j].cod_subgrupo=="CEL MADRE" || items[j].cod_subgrupo=="EXOSOMAS") {
             options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>";   
          }
					
				}
				$("#productos").html(options);
	 	} 	
	}

	document.querySelector('#productos').addEventListener('change', function(){
		get_data_callback();

	} )
//-------------------------------------------------------------------------------------------------------
//*******************************************************************************************************
});


var url='../../handler/AjustesHandler.php';
    

$( document ).ready(function() {
   // se genera el paginador
   paginador = $(".pagination");
  // cantidad de items por pagina
  var items = 25, numeros =10;  
  // inicia el paginador
  init_paginator(paginador,items,numeros);
  // se envia la peticion ajax que se realizara como callback
  set_callback(get_data_callback);
  cargaPagina(0);
});
//------------------------------------------------------------------------------------------------------- 
// peticion ajax enviada como callback
function get_data_callback(){
  $.ajax({
    data:{
    limit: itemsPorPagina,              
    offset: desde,
    producto: $("#productos").val()                  
    },
    type:"POST",
    url:url
  }).done(function(data,textStatus,jqXHR){    
    // obtiene la clave lista del json data
    var lista = data.lista;
    $("#move-exos-tbl").html("");
    
    // si es necesario actualiza la cantidad de paginas del paginador
    if(pagina==0){
      creaPaginador(data.cantidad);
    }
    // genera el cuerpo de la tabla
    $.each(lista, function(ind, elem){     

        let ventas = isNaN( parseInt(elem.ventas)) ? 0 : parseInt(elem.ventas);
        let devolu = isNaN( parseInt(elem.return)) ? 0 : parseInt(elem.return);
        let ajustep = isNaN( parseInt(elem.ajustep)) ? 0 : parseInt(elem.ajustep);
        let ajusten = isNaN( parseInt(elem.ajusten)) ? 0 : parseInt(elem.ajusten);
        let total =(devolu+ajustep)-(ventas+ajusten);

      $('<tr id='+elem.fecha+'>'+       
        '<td class="mov-excm ex-fecha bold">'+elem.fecha+'</td>'+               
        '<td class="mov-excm ex-ventas bold">'+elem.ventas+'</td>'+        
        '<td class="mov-excm ex-return bold">'+elem.return+'</td>'+        
        '<td class="mov-excm ex-ajustp bold">'+elem.ajustep+'</td>'+        
        '<td class="mov-excm ex-ajustn bold">'+elem.ajusten+'</td>'+
        // '<td class="mov-excm ex-total bold">'+total+'</td>'+
        '</tr>').appendTo($("#move-exos-tbl"));
    });     
  }).fail(function(jqXHR,textStatus,textError){
    alert("Error al realizar la peticion dame".textError);
  });
}
//-------------------------------------------------------------------------------------------------------
//*******************************************************************************************************