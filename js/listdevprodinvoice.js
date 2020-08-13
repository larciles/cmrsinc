var totalPage;

getDevoluciones();

  function getDevoluciones(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

     url="../../controllers/productsdevinvoicecontroller.php";
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
    
    $("#tblreturn > tr").remove();
    
    $("#tblreturn").find("tr:gt(0)").remove(); 

    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;


    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var numfactu  = items['data'][i]["numfactu"];
        var nombres   = items['data'][i]["nombres"];
        var fechafac  = items['data'][i]["fechafac"];
        var Status    = items['data'][i]["Status"];
        var subtotal  = items['data'][i]["subtotal"];
        var descuento = items['data'][i]["descuento"];
        var impuesto  = items['data'][i]["TotImpuesto"];
        var envio     = items['data'][i]["monto_flete"];
        var total     = items['data'][i]["total"];
        var descuento = items['data'][i]["descuento"];
        var titleDev  = "Anular";
        var idDev     = "";
        
        total    = parseFloat(total);
        total    = parseFloat(total).toFixed(2);
        subtotal = parseFloat(subtotal).toFixed(2);
        descuento= parseFloat(descuento).toFixed(2);
        impuesto = parseFloat(impuesto).toFixed(2);
        envio    = parseFloat(envio).toFixed(2);

        for (var j = 0; j < usuario.length; j++) { 
            
            if(usuario[j].login.toUpperCase()==items['data'][i]["usuario"].toUpperCase()){              
              options+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              options+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }
            
        }

         $('#tblreturn').append('<tr id='+numfactu+'>'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center">'+Status+'</td>'+
         	' <td align="right">'+subtotal+'</td>'+
          ' <td align="right">'+descuento+'</td>'+
          ' <td align="right">'+impuesto+'</td>'+
          ' <td align="right">'+envio+'</td>'+
          ' <td align="right">'+total+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-primary consultar">Consultar</td>'+
          ' <td align="center"> <button type="button" name="imprimirdev" id=""  class="btn btn-success imprimirdev">Imprimir</td>'+
          ' <td align="center"> <button type="button" name="anular" id=""  class="btn btn-danger anular">Anular</td>'+
          ' <td align="center"><select name="users[]"  id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' </tr>');
          $("#"+numfactu+'-'+i).html(options);
          options='';
    };

  }


      $('#tblreturn').on('change', 'tbody tr select', function(event) {
        var elemt = $(this);
        var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
        let user = $("#idusr").val();
        let nuevousuario = elemt.val();
        //.php

        var datasave ={
         docnumber   : numfactu
        ,tipodoc : '04'
        ,usernew : nuevousuario
        ,id_centro : '1'
        ,user
    }

    url="../../clases/changeuser.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

      })


     $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('.datepickerd').val();
        search = $('#searchd').val();

        getDevoluciones(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });



    $('#searchd').keyup(function(e){
        if (e.which == 13) {
            
           var fecha,search ="";
           search = $('#searchd').val(); 
           fecha=$('.datepickerd').val();

           getDevoluciones( '' , search ,fecha );
              $('.pagina').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })


        }
  })



$('.datepickerd').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepickerd').change(function(){
   var fecha,search ="";
   fecha =$('.datepickerd').val();
   search = $('#searchd').val(); 
   getDevoluciones( '' , search , fecha );

    $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblreturn').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("anular")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          //window.location.href = 'return.php?fac='+numfactu;
       }
        $('#datosdev').html('Devolucion # '+numfactu);
        $("#datosdev").val(numfactu);
        $("#voiddev").modal();
   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          //window.location.href = 'returnedit.php?fac='+numfactu;
          document.getElementById('numfactu').value=numfactu;
          window.location.href = "msprods-ret.php?edicion=true&numero="+numfactu;           

       }
   }else if(elemt.attr('class').indexOf("anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("imprimirdev")>-1){
      var result = numfactu.split('-');
       if(result.length==1){  
         let user = $('#idusr').val()   
         invoicePrintingdev(numfactu,user);
       }
   }
})



  function invoicePrintingdev(_numfactu,user){
  
    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    var datasave ={
         numfactu   : _numfactu  
        ,times : 1
        ,user     
    }
    
    if (prninvoice!='1' && autoposprn!='1' ) {
       window.open('../../clases/printproddevpdf.php?numfactu='+_numfactu+'&times='+'1'+'&user='+user,'', '_blank');
    } else {
      url="../../clases/printproddevolucion.php";
      var items;
      var resp =  $.ajax({
                            type: "POST",
                            url: url,
                            data: datasave,
                            async: false
                        }).responseText;
      
       items= resp ; //jQuery.parseJSON(resp);
   }
  }

$('#savevoiddev').click(function(event){

  var razonanulacion=$('#razonanulaciondev').val();
  let voidinvoice =$("#datosdev").val();
  url="../../clases/voidproddev.php";
    data = {
       numfactu  : voidinvoice
      ,razonanulacion : razonanulacion
      ,idempresa :'1'
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
  

  $('#voiddev').modal('hide');
})

  function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

     }