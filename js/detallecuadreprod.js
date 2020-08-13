var totalPage;


//getDataS();



  function getDataS(  page,  search,  fecha){
    $("#startdate").addClass("intro");
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

    let usuario = $('#c_usuario').val(); 

      url="../../clases/getsalesproductosdet.php";
      data = {
      limit  : 500,
      page   : page,
      search : search,
      fecha  : fecha,
      usuario   : usuario
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
   
    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;

    var xdate =  fecha;
    if (fecha == undefined) {
       xdate =   getToDay();
    };

    var paymeth = jQuery.parseJSON(getPayMeth(xdate));   //KEY VALUE CLAVE VALOR
    var  paymeth_obj =  new Array(); 
    if (paymeth.length>0) {
        for (var i = 0; i < paymeth.length; i++) { 
            paymeth_obj[ paymeth[i].numfactu ] = paymeth[i].modopago;
        };
    };
    
    var lenArr=items['data'].length;
    var total =0;
    for (var i = 0; i < lenArr; i++) {

         var fechapago = items['data'][i]["fechapago"];
         var numfactu  = items['data'][i]["numfactu"];
         var nombres   = items['data'][i]["nombres"];

         var subtotal  = parseFloat(items['data'][i]["subtotal"]);
         var descuento = parseFloat(items['data'][i]["descuento"]);
         var impuesto  = parseFloat(items['data'][i]["TotImpuesto"]);
         var envio     = parseFloat(items['data'][i]["monto_flete"]);

         var usuariof  = items['data'][i]["usuario"];
         
         var monto     = items['data'][i]["monto"];
         var desTipoTargeta = paymeth_obj[numfactu] ;//items['data'][i]["DesTipoTargeta"];

         total= total+parseFloat(monto);

         $('#tblLista').append('<tr id='+numfactu+'>'+
           ' <td >'+numfactu+'</td>'+
           ' <td  >'+fechapago+'</td>'+ 
           ' <td  >'+nombres+'</td>'+ 
           ' <td  >'+subtotal.toFixed(2)+'</td>'+ 
           ' <td  >'+descuento.toFixed(2)+'</td>'+ 
           ' <td  >'+impuesto.toFixed(2)+'</td>'+ 
           ' <td  >'+envio.toFixed(2)+'</td>'+ 
           ' <td  align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+ 
           ' <td  align="center">'+desTipoTargeta+'</td>'+       
           ' <td  align="center">'+usuariof+'</td>'+       
           ' </tr>');   
                  $('#tblLista').tablesorter();  
    };
    if (total>0) {
         // $('#tblTotal').append('<tr id=total>'+
         //   ' <td ></td>'+
         //   ' <td ></td>'+
         //   ' <td  align="right">Total</td>'+ 
         //   ' <td  align="center">'+formatCurrency(total,"$")+'</td>'+ 
         //   ' <td  align="center">&nbsp;</td>'+       
         //   ' </tr>');         
    };
     
     getTotalGrupos(fecha);
     getTotalVentas(fecha)
     getDevoluciones(fecha);
     getTotalDevolucion(fecha)
     getTotalGeneral(fecha);
     $("#startdate").removeClass("intro");
  }


     $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('.datepicker').val();
        search = $('#search').val();

        getDataS(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keypress', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getDataS( '' , search ,fecha );
              $('.pagina').bootpag({
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

$('.datepicker').click(function(){

   $("#wait").show(); 
})


$('.datepicker').blur(function(){
   $("#wait").hide(); 
})




$('.datepicker').change(function(){
    
   // $("#startdate").removeClass("intro");
   // $("#startdate").addClass("intro");
   if (!$("#startdate").hasClass("intro")) {
      $("#wait").show(); 
      cleanHide();
         
       var fecha,search ="";
       fecha =$('.datepicker').val();
       search = $('#search').val(); 
       getDataS( '' , search , fecha );

       $('.pagina').bootpag({
           total: totalPage,
           page: 1,
           maxVisible: 10
       })
    };
});


function cleanHide(clearall){
  if (clearall==undefined) {
      clearall=false;
  };
  $('#tblLista').trigger('update');
  $("#tbltotales > tr").remove();
  $("#tbltotales").find("tr:gt(0)").remove(); 
 // $('#tbltotales').hide();
  $("#tblLista > tr").remove();    

  $("#tbltotaldevolucion > tr").remove();
  $("#tbltotaldevolucion").find("tr:gt(0)").remove(); 

  $("#tbltotalventa > tr").remove();
  $("#tbltotalventa").find("tr:gt(0)").remove(); 
     
  $("#tblLista").find("tr:gt(0)").remove(); 
  $('#tblTotal').find("tr:gt(0)").remove(); 
  $("#tblTotal > tr").remove();

  $("#tbldevolucion").find("tr:gt(0)").remove(); 
  $('#tbldevolucion').find("tr:gt(0)").remove(); 
  $("#tbldevolucion > tr").remove();

    
  if (clearall) {
    
  };
}

function getTotalGrupos(fecha){    
    
    //cleanHide();
    let usuario = $('#c_usuario').val(); 
    var jqxhr = $.post( "../../clases/getproductosgrupototales.php",{fecha:fecha,usuario}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
    
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {
             var DesTipoTargeta = items[i]["DesTipoTargeta"];
             var total          = items[i]["total"];
             var facturas       = items[i]["facturas"];
             
             xrow = xrow+parseInt(facturas); 
             tgeneral=tgeneral+parseFloat(total);

             $('#tbltotales').append('<tr>'+
             ' <td align="center">'+facturas+'</td>'+
             ' <td >'+DesTipoTargeta+'</td>'+ 
             ' <td  align="center">'+formatCurrency(parseFloat(total),"$")+'</td>'+                   
             ' </tr>');  
        }
        if (tgeneral>0) {
            $('#tbltotales').append('<tr>'+
             ' <td align="center">'+xrow+'</td>'+
             ' <td align="right">Total</td>'+ 
             ' <td align="center">'+formatCurrency(tgeneral,"$")+'</td>'+                   
             ' </tr>');   
            $('#tbltotales').show();
        };
   
      //alert( "success" ); 
    })
    .done(function() {
     // console.log();
      //alert( "second success" );
      $("#wait").hide(); 
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
     
    });

}



function getDevoluciones(fecha){    
    
    //cleanHide();
    let usuario = $('#c_usuario').val(); 
    var xdate =  fecha;
    if (fecha == undefined) {
       xdate =   getToDay();
    };

    var paymeth = jQuery.parseJSON(getPayMeth(xdate));   //KEY VALUE CLAVE VALOR
    var  paymeth_obj =  new Array(); 
    if (paymeth.length>0) {
        for (var i = 0; i < paymeth.length; i++) { 
            paymeth_obj[ paymeth[i].numfactu ] = paymeth[i].modopago;
        };
    };

    var jqxhr = $.post( "../../clases/getproductosdevolucion.php",{fecha:fecha,usuario}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
        if (items!=null) {
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {

         var fechapago = items[i]["fechapago"];
         var numfactu  = items[i]["numfactu"];
         var nombres   = items[i]["nombres"];
         var subtotal  = items[i]["subtotal"];
         var descuento = items[i]["descuento"];
         var impuesto  = items[i]["TotImpuesto"];
         var envio     = items[i]["monto_flete"];         
         var monto     = items[i]["monto"];

         var desTipoTargeta = paymeth_obj[numfactu];

         //total= total+parseFloat(monto);

         $('#tbldevolucion').append('<tr id='+numfactu+'>'+
           ' <td >'+numfactu+'</td>'+
           ' <td  >'+fechapago+'</td>'+ 
           ' <td  >'+nombres+'</td>'+ 
           ' <td  >'+formatCurrency(parseFloat(subtotal),'$')+'</td>'+ 
           ' <td  >'+formatCurrency(parseFloat(descuento),'$')+'</td>'+ 
           ' <td  >'+formatCurrency(parseFloat(impuesto),'$')+'</td>'+ 
           ' <td  >'+formatCurrency(parseFloat(envio),'$')+'</td>'+ 
           ' <td  align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+ 
           ' <td  align="center">'+desTipoTargeta+'</td>'+       
           ' </tr>');    
        }
   
      };
    })
    .done(function() {    
      //alert( "second success" );
      $("#wait").hide(); 
    })
    .fail(function() {
      //alert( "error" );
    })
    .always(function() {
      //alert( "finished" );     
    });

}

// TOTAL VENTAS

function getTotalVentas(fecha){    
    
    //cleanHide();
    let usuario = $('#c_usuario').val(); 
    var xdate =  fecha;
    if (fecha == undefined) {
       xdate =   getToDay();
    };


    var jqxhr = $.post( "../../clases/getventasprodtotales.php",{fecha:fecha,tipo:'rv',usuario}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
    
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {

         
         var subtotal  = items[i]["subtotal"];
         var descuento = items[i]["descuento"];
         var impuesto  = items[i]["impuesto"];
         var envio     = items[i]["envio"];         
         var monto     = items[i]["total"];

         $('#tbltotalventa').append('<tr>'+
           ' <td > </td>'+
           ' <td >'+formatCurrency(parseFloat(subtotal),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(descuento),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(impuesto),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(envio),'$')+'</td>'+ 
           ' <td align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+     
           ' </tr>');    
        }
   
      
    })
    .done(function() {    
      //alert( "second success" );
      $("#wait").hide(); 
    })
    .fail(function() {
      //alert( "error" );
    })
    .always(function() {
      //alert( "finished" );     
    });

}

// TOTAL DEVOLUCIONES
function getTotalDevolucion(fecha){    
    
    //cleanHide();
    let usuario = $('#c_usuario').val(); 
    var xdate =  fecha;
    if (fecha == undefined) {
       xdate =   getToDay();
    };


    var jqxhr = $.post( "../../clases/getventasprodtotales.php",{fecha:fecha,tipo:'rd',usuario}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
    
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {

         
         var subtotal  = items[i]["subtotal"];
         var descuento = items[i]["descuento"];
         var impuesto  = items[i]["impuesto"];
         var envio     = items[i]["envio"];         
         var monto     = items[i]["total"];

         $('#tbltotaldevolucion').append('<tr>'+
           ' <td > </td>'+
           ' <td >'+formatCurrency(parseFloat(subtotal),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(descuento),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(impuesto),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(envio),'$')+'</td>'+ 
           ' <td align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+ 
    
           ' </tr>');    
        }
   
      
    })
    .done(function() {    
      //alert( "second success" );
      $("#wait").hide(); 
    })
    .fail(function() {
      //alert( "error" );
    })
    .always(function() {
      //alert( "finished" );     
    });

}
// TOTAL GENERAL
function getTotalGeneral(fecha){    
    
    //cleanHide();
    let usuario = $('#c_usuario').val(); 
    var xdate =  fecha;
    if (fecha == undefined) {
       xdate =   getToDay();
    };


    var jqxhr = $.post( "../../clases/getventasprodtotales.php",{fecha:fecha,tipo:'rt',usuario}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
    
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {

         
         var subtotal  = items[i]["subtotal"];
         var descuento = items[i]["descuento"];
         var impuesto  = items[i]["impuesto"];
         var envio     = items[i]["envio"];         
         var monto     = items[i]["total"];

         $('#tblTotal').append('<tr>'+
           ' <td > </td>'+
           ' <td >'+formatCurrency(parseFloat(subtotal),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(descuento),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(impuesto),'$')+'</td>'+ 
           ' <td >'+formatCurrency(parseFloat(envio),'$')+'</td>'+ 
           ' <td align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+  
           ' </tr>');    
        }
   
      
    })
    .done(function() {    
      //alert( "second success" );
      $("#wait").hide(); 
    })
    .fail(function() {
      //alert( "error" );
    })
    .always(function() {
      //alert( "finished" );     
    });

}
// 
function formatCurrency(n, currency) {
    return currency + " " + n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
}


  $("#cuadre").click(function(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#startdate').val();
    let tiporeporte = $('.tiporeporte').prop('checked');
    let start =$('#start').val();
    if (fecha=='') {      
       fecha =$('#efechai').val();
    };
    var p =$( "#excelapo option:selected" ).text();
    //window.open('cuadre.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'1','Cuadre de Consultas', '_blank');
    window.open('cuadre.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'1'+'&start='+start+'&tiporeporte='+tiporeporte,'Cuadre de Productos', '_blank');
    // window.open('pdfreport.php?fecha='+fecha,'titulo', 'width=500, height=500')
  })

  $("#cuadre1").click(function(){
    var usuario = $('#c_usuario').val();
    var fecha =$('#efechai').val();
    if (fecha=='') {      
       fecha =$('#efechai').val();
    };
    var p =$( "#excelapo option:selected" ).text();
    let tiporeporte = $('.tiporeporte').prop('checked');
    let start =$('#start').val();
    // window.open('cuadre.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'1','Cuadre de Consultas', '_blank');
    window.open('cuadre.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'1'+'&start='+start+'&tiporeporte='+tiporeporte,'Cuadre de Productos', '_blank');
    // window.open('pdfreport.php?fecha='+fecha,'titulo', 'width=500, height=500')
  })


function getPayMeth(fecha){
   let usuario = $('#c_usuario').val(); 
    url="../../clases/getmetodopagoproductos.php";
      data = {
      fecha  : fecha,
      usuario : usuario
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    return resp;
}

function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}

// $('#tblLista').on('click', 'tbody tr button', function(event) {
//    var elemt = $(this);
//    var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

//    if(elemt.attr('class').indexOf("devolver")>-1) {
       
//        var result = numfactu.split('-');
//        if(result.length==2){
//           // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
//        }else{
//           window.location.href = 'return.php?fac='+numfactu;
//        }
//    }else if(elemt.attr('class').indexOf("consultar")>-1){
//       var result = numfactu.split('-');
//        if(result.length==1){
//           window.location.href = 'invoicedit.php?fac='+numfactu;
//        }
//    }
   
   

// })