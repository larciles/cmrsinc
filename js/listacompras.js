var totalPage;
var voidinvoice;

getComprasLista();

 

  function getComprasLista(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };



     url="../../controllers/compralistacontroller.php";
     data = {
      limit  : 25,
      page   : page,
      search : search,
      fecha  : fecha
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    

try{
  items= jQuery.parseJSON(resp);

    totalPage=items.totpaginas;
    
    $("#tblListacompras > tr").remove();
    
    $("#tblListacompras").find("tr:gt(0)").remove(); 
   
    let display='display: none';
    if ($("#access").val()=='6' || $("#codperfil").val()=='01') {
         display='display: inline';     
     };

    
    
    let trans='';
    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
      	var numfactu = items['data'][i]["numfactu"];
        var nombres  = items['data'][i]["nombres"];
        var fechafac = items['data'][i]["fechafac"];
        var codProv  = items['data'][i]["codprov"];
        var facclose = items['data'][i]["facclose"];
        trans=tranferidoYN(facclose);

        $('#tblListacompras').append('<tr id='+numfactu+' proveedor ='+codProv+'>'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-primary consultar">Consultar</td>'+
         	' <td align="center">'+trans+' </td>'+         	
         	' </tr>');
    };
   
   }    
    catch(err){

    totalPage=0;
    
    $("#tblListacompras > tr").remove();    
    $("#tblListacompras").find("tr:gt(0)").remove(); 
    }  
  
  }

function tranferidoYN(facclose){
   let trans='<button type="button" name="transferir"  id=""  class="btn btn-success " disabled >Transferirdo</button> ';
     if (facclose=='0') {
        trans='<button type="button" name="transferir"  id=""  class="btn btn-warning transferir">Transferir</button> ';
     }; 
  return trans;   
}


     $('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('#fechacompra').val();
        search = $('#search').val();

        getComprasLista(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keydown', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('#fechacompra').val();

           getComprasLista( '' , search ,fecha );
              $('.pagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })

            //Disable textbox to prevent multiple submit
            //$(this).attr("disabled", "disabled");
         }
   });


$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('#fechacompra').change(function(){
   var fecha,search ="";
   fecha =$('#fechacompra').val();
   search = $('#search').val(); 
   getComprasLista( '' , search , fecha );

    $('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblListacompras').on('click', 'tbody tr button', function(event) {
   let elemt = $(this);
   let numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("devolver")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("consultar")>-1){
          window.location.href = 'editcompras.php?idcompra='+numfactu;       
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
      var result = numfactu.split('-');
      let user = $('#idusr').val();
       if(result.length==1){
        
       }
   }else if(elemt.attr('class').indexOf("anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
        voidinvoice=numfactu;
         $("#void").modal();
         //resulset = isConsulta(numfactu);
         //invoicePrinting(numfactu,resulset[0]['cod_subgrupo']); //numfactu;
       }
   }
   
   
})


//---------------------------------------------------------------------------------
// $('#void').on('show.bs.modal', function (event) {
//   var button = $(event.relatedTarget) // Button that triggered the modal
//   var recipient = voidinvoice; //button.data('whatever') // Extract info from data-* attributes
//   // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
//   // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
//   var modal = $(this)
//  // modal.find('.modal-title').text('New message to ' + recipient)
//   modal.find('.modal-body input').val(recipient)
// })
// //---------------------------------------------------------------------------------
//---------------------------------------------------------------------------------