//getsalesrecord.js

$('#recordConsModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
//  $("#bodyproducts").find("tr").remove();
 
  var codclien  = $('#idassoc').val();
  var data = getRecord(codclien);
  var items=[];
  var nFactura=0;
  $.each(data, function(key,val){

    items.push("<tr data-target='#recordConsModal' class='clsrecord'>");
    items.push("<td>"+val.numfactu+"</td>");
    items.push("<td>"+val.fechafac+"</td>");
    items.push("<td>"+val.total.substring(0,val.total.indexOf('.')+3)+"</td>");
    items.push("<td>"+val.id_centro+"</td>");
    items.push("</tr>");
    if(nFactura==0){
      nFactura=val.numfactu;
      idempresa=val.id_centro;
    }

  })
  $("#bodyrecord").find("tr").remove();
  $('#bodyrecord').append(items.join(''));
  var modal = $(this)
  modal.find('.modal-title').text('Record de consultas de  ' + recipient)
  modal.find('.modal-body input').val(recipient)
  if(nFactura!==0){
    var data = findOutProduct(nFactura,idempresa)
    setUpProductData(data)
  }
})

//----------------------
function getRecord(codclien){
  var items;
  var lretncitas;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/pacientesgetrecord.php",
            data:{ q : codclien }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
}

//----------------------
function findOutProduct(factura,idempresa){

 var items;
  var lretncitas;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/pacientesgetproductos.php",
            data:{  q : factura
            	   ,idempresa : idempresa
             }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
}
//----------------------
function setUpProductData(data){
  items = [];
  $.each(data, function(key,val){
    items.push("<tr>");
    items.push("<td>"+val.desitems+"</td>");
    items.push("<td>"+val.cantidad+"</td>");  
    items.push("</tr>");
  })

  $("#bodyproducts").find("tr").remove();
  $('#bodyproducts').append(items.join(''));
}
//----------------------
$("#recordcl").on("click", "tr", function(){  
  var data = getProductos(this); 
  setUpProductData(data);
});
//----------------------
function getProductos(element){
 // console.log(element); 
 var factura = element.firstChild.innerText; 
 var idempresa =element.lastElementChild.innerText;
 var res = findOutProduct(factura, idempresa);
 return res;
}
//----------------------