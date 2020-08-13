var totalPage;
var voidinvoice;
$('#xtitulo').html('<b>Notas de entrega Valorizadas</b>');
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

getConsultasInvoices();

 

  function getConsultasInvoices(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

     url="../../controllers/nerepcontroller.php";
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
    

    resp=resp.substr(resp.indexOf('{"page"'))
    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    
    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 

    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var numfactu  = items['data'][i]["numnotent"];
        var nombres   = items['data'][i]["nombres"];
        var fechafac  = items['data'][i]["fechanot"];
        var cantidad  = items['data'][i]["cantidad"];        
        var total     = items['data'][i]["totalregalo"];        
        var titleDev  = "Devolver";
        var idDev     = "";
        
        total    = parseFloat(total);
        total    = parseFloat(total).toFixed(2);
        //subtotal = parseFloat(subtotal).toFixed(2);
        //descuento= parseFloat(descuento).toFixed(2);
        //impuesto = parseFloat(impuesto).toFixed(2);
        //envio    = parseFloat(envio).toFixed(2);

         $('#tblLista').append('<tr id='+numfactu+'>'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+         	
          ' <td align="right">'+cantidad+'</td>'+          
          ' <td align="right">'+total+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-info consultar">Consultar</td>'+          
         	' <td align="center"> <button type="button" name="imprimir"  id=""  class="btn btn-success imprimir">Imprimir</button></td>'+         	
         	' </tr>');
    };
   
  
  }


     $('.paginaf').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('.datepicker').val();
        search = $('#search').val();

        getConsultasInvoices(  num, search , fecha );
        //$(".content2").html("Page " + num); // or some ajax content loading...
    });

  

    $('#search').keyup(function(e){
        if (e.which == 13) {
                      var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getConsultasInvoices( '' , search ,fecha );
              $('.paginaf').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })

        }
  })



$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val(); 
   getConsultasInvoices( '' , search , fecha );

    $('.paginaf').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblLista').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("devolver")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
            let numnotent = $(this).parent().parent().attr('id');
            fitentregados(numnotent);
           $('#myModal').modal('show');
       }
   }else if(elemt.attr('class').indexOf("devolver")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
           voidinvoice=numfactu;
          $("#void").modal();
      }
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          invoicePrinting(numfactu,1);
      }
   }
})

 function invoicePrinting(_numfactu,times){
    if (times==undefined) {
      times=1;
    };

    var datasave ={
         numfactu   : _numfactu
        ,times : times
    }

    url="../../clases/printnotaentrega.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

  }


function fitentregados(numnotent){
    var data = getnepreprodentregado(numnotent);  
    var items=[];
    $.each(data, function(key,val){

    items.push("<tr data-target='#recordConsModal' class='clsrecord'>");
    items.push("<td>"+val.desitems+"</td>");
    items.push("<td>"+val.cantidad+"</td>");
    items.push("<td>"+val.precunit+"</td>");
    items.push("<td>"+val.total+"</td>");    
    items.push("</tr>");   

  })
  $("#bodyrecordne").find("tr").remove();
  $('#bodyrecordne').append(items.join(''));
}

function getnepreprodentregado(numnotent){


var items;
  var entregados;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/getnepreprodentregado.php",
            data:{ numnotent }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      entregados=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return entregados;
    
  }