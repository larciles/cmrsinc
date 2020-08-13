var totalPage;


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

     url="../../controllers/laserdevolcontroller.php";
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
    


    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    
    $("#tbldevol > tr").remove();
    
    $("#tbldevol").find("tr:gt(0)").remove(); 

    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;


    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var numnotcre = items['data'][i]["numnotcre"];
        var nombres   = items['data'][i]["nombres"];
        var fechanot  = items['data'][i]["fechanot"];
        var Status    = items['data'][i]["destatus"];
        var total     = items['data'][i]["totalnot"];
        var descuento = items['data'][i]["descuento"];
        var titleDev="Devolver";
        var idDev="";
        if (items['data'][i]["numnotcre"]!==null && items['data'][i]["numnotcre"]!=="") {
           titleDev="Devolver*";
           idDev = items['data'][i]["numnotcre"];
           //numnotcre = numnotcre+'-'+idDev;
        }; 

        for (var j = 0; j < usuario.length; j++) { 
            
            if(usuario[j].login.toUpperCase()==items['data'][i]["usuario"].toUpperCase()){              
              options+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              options+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }
            
        }

        let habilitado="disabled";
        if( $('#codperfil').val()=="01" ) {
            habilitado="";
        }

       // total = parseFloat(total)-parseFloat(descuento)
        total=parseFloat(total).toFixed(2)
         $('#tbldevol').append('<tr id='+numnotcre+'>'+
         	' <td  align="center">'+numnotcre+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechanot+'</td>'+
         	' <td align="center">'+Status+'</td>'+
         	' <td align="right">'+total+'</td>'+
         	' <td align="center"> <button type="button" name="consult" id=""  class="btn btn-primary consult">Consultar</td>'+         	
          ' <td align="center"> <button type="button" name="print"  id=""  class="btn btn-success print">Imprimir</td>'+          
         	' <td align="center"> <button type="button" name="void"    id=""  class="btn btn-danger void">Anular</button> </td>'+         	
          ' <td align="center"><select name="users[]"  id='+numnotcre+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' </tr>');
          $("#"+numnotcre+'-'+i).html(options);
          options='';
    };
  }

      $('#tbldevol').on('change', 'tbody tr select', function(event) {
        var elemt = $(this);
        var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
        let user = $("#idusr").val();
        let nuevousuario = elemt.val();
        //.php

        var datasave ={
         docnumber   : numfactu
        ,tipodoc : '04'
        ,usernew : nuevousuario
        ,id_centro : '3'
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
        fecha=$('.datepicker').val();
        search = $('#search').val();

        getConsultasInvoices(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keypress', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getConsultasInvoices( '' , search ,fecha );
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

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val(); 
   getConsultasInvoices( '' , search , fecha );

    $('.pagina').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tbldevol').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("void")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==1){
        voidinvoice=numfactu;
         $("#voiddev").modal();
         //resulset = isConsulta(numfactu);
         //invoicePrinting(numfactu,resulset[0]['cod_subgrupo']); //numfactu;
       }
   }else if(elemt.attr('class').indexOf("consult")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          window.location.href = 'devolucionedit.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("print")>-1){
    let user = $("#idusr").val();
    var result = numfactu.split('-');
      if(result.length==1){
        returnPrinting(result[0],1,'3',user);
      }
   }
   
   

})



 function returnPrinting(devolucion,times,idcentro,user){
    

    var datasave ={
          numfactu : devolucion
         ,times : times
         ,idempresa : idcentro,
         user    
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