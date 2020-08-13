  $('#xtitulo').html('<b>Devoluciones Consultas y Suero</b>');
var totalPage;


getLista();

 

  function getLista(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

     url="../../controllers/returnlistcontroller.php";
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
    

    resp=resp.substr(resp.indexOf('{\"page')) 
    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    
    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 
    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;

    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	var numfactu   = items['data'][i]["numnotcre"];
        var nombres  = items['data'][i]["nombres"];
        var fechafac = items['data'][i]["fechanot"];
        var Status   = items['data'][i]["destatus"];
        var total    = items['data'][i]["totalnot"];
        var descuento    = items['data'][i]["descuento"];
        var titleDev="Devolver";
        var idDev="";
        for (var j = 0; j < usuario.length; j++) { 
            
            if(usuario[j].login.toUpperCase()==items['data'][i]["usuario"].toUpperCase()){              
              options+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              options+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }
            
        }
        total = parseFloat(total);//-parseFloat(descuento)
        total=parseFloat(total).toFixed(2)
         $('#tblLista').append('<tr id='+numfactu+'>'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center">'+Status+'</td>'+
         	' <td align="right">'+total+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-primary consultar">Consultar</td>'+
         	' <td align="center"> <button type="button" name="imprimir"    id=""  class="btn btn-success imprimir">Imprimir</button> </td>'+
         	' <td align="center"> <button type="button" name="anular"    id=""  class="btn btn-danger anular">Anular</button> </td>'+
          ' <td align="center"><select name="users[]"  id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' </tr>');
          $("#"+numfactu+'-'+i).html(options);
          options='';
    };
     console.log(resp);
     console.log(items);
  
  }


      $('#tblLista').on('change', 'tbody tr select', function(event) {
        var elemt = $(this);
        var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
        let user = $("#idusr").val();
        let nuevousuario = elemt.val();
        //.php

        var datasave ={
         docnumber   : numfactu
        ,tipodoc : '04'
        ,usernew : nuevousuario
        ,id_centro : '2'
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


     $('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('.datepicker').val();
        search = $('#search').val();

        getLista(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keypress', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getLista( '' , search ,fecha );
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

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val(); 
   getLista( '' , search , fecha );

    $('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblLista').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   if(elemt.attr('class').indexOf("consultar")>-1) {
       var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          window.location.href = 'returndisplay.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
        var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
        var result = numfactu.split('-');
        returnPrinting(result[0],1,'2');
   }else if(elemt.attr('class').indexOf("anular")>-1) {
       
       var numfactu = elemt.parent().parent().attr('id');

        $('#datosdev').html('Devolucion # '+numfactu);
        $("#datosdev").val(numfactu);
        $("#voiddev").modal();
     }
   
   

})


 function returnPrinting(devolucion,times,idcentro){
    
    let user = $('#loggedusr').val();
    var datasave ={
          numfactu : devolucion
         ,times : times
         ,idempresa : idcentro      
    }

    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    if (prninvoice!='1' && autoposprn!='1' ) {
        window.open('../../clases/printdevserviciospdf.php?numfactu='+devolucion+'&times='+times+'&idempresa='+idcentro+'&user='+user,'', '_blank');
    }else{
        url="../../clases/printindevolucionservicios.php";

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


  function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

     }


     $('#savevoiddev').click(function(event){

  var razonanulacion=$('#razonanulaciondev').val();
  let voidinvoice =$("#datosdev").val();
  url="../../clases/voidproddev.php";
    data = {
       numfactu  : voidinvoice
      ,razonanulacion : razonanulacion
      ,idempresa :'2'
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
