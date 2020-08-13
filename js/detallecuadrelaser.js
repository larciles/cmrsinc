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

      url="../../clases/getsaleslaserdet.php";
      data = {
      limit  : 500,
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

         var fechafac = items['data'][i]["fechafac"];
         var numfactu  = items['data'][i]["numfactu"];
         var nombres   = items['data'][i]["nombres"];
         var monto     = items['data'][i]["monto"];
         var userinvo  = items['data'][i]["usuario"];
         var desTipoTargeta = paymeth_obj[numfactu]; //items['data'][i]["DesTipoTargeta"];

         total= total+parseFloat(monto);

         $('#tblLista').append('<tr id='+numfactu+'>'+
           ' <td >'+numfactu+'</td>'+
           ' <td  >'+fechafac+'</td>'+ 
           ' <td  >'+nombres+'</td>'+ 
           ' <td  align="center">'+formatCurrency(parseFloat(monto),"$")+'</td>'+ 
           ' <td  align="center">'+desTipoTargeta+'</td>'+       
           ' <td  align="center">'+userinvo+'</td>'+ 
           
           ' </tr>');   

           $('#tblLista').tablesorter();  
    };
    if (total>0) {
         $('#tblTotal').append('<tr id=total>'+
           ' <td ></td>'+
           ' <td ></td>'+
           ' <td  align="right">Total</td>'+ 
           ' <td  align="center">'+formatCurrency(total,"$")+'</td>'+ 
           ' <td  align="center">&nbsp;</td>'+       
           ' </tr>');         
    };
     
     

     getTotalGrupos(fecha);
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
  $('#tbltotales').hide();
 $("#tblLista > tr").remove();    
     $("#tblLista").find("tr:gt(0)").remove(); 
     $('#tblTotal').find("tr:gt(0)").remove(); 
     $("#tblTotal > tr").remove();
  if (clearall) {
    
  };
}

function getTotalGrupos(fecha){    
    
    //cleanHide();
    
    var jqxhr = $.post( "../../clases/getlasergrupototales.php",{fecha:fecha}, function(data) { 

      
        var items="";   
        items= jQuery.parseJSON(data);  
    
        var xrow=0;   
        var tgeneral=0;
        for (var i = 0; i < items.length; i++) {
             var DesTipoTargeta = items[i]["modopago"];
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



function formatCurrency(n, currency) {
    return currency + " " + n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
}


function getPayMeth(fecha){
    url="../../clases/getmetodopagolaser.php";
      data = {
      fecha  : fecha
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