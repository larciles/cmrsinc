var totalPage;
var voidinvoice;

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

    let grupo = $('#grupo').val();

     url="../../controllers/suerocontroller.php";
     data = {
      limit  : 25,
      page   : page,
      search : search,
      fecha  : fecha,
      grupo
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

    let display='display: none';
    if ($("#access").val()=='6' || $("#codperfil").val()=='01') {
         display='display: inline';     
     };
    //USERS
    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;
    //USER>

    //<MEDICOS
    var moptions=''
    var res = getData("../../clases/medicos.php",{filtro:'si' });
    var medico = jQuery.parseJSON(res); 
    //MEDICOS>


    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var numfactu   = items['data'][i]["numfactu"];
        var nombres  = items['data'][i]["nombres"];
        var fechafac = items['data'][i]["fechafac"];
        var Status   = items['data'][i]["Status"];
		    var subtotal = items['data'][i]["subtotal"]; 
		    var descuento = items['data'][i]["descuento"]; 
        var total    = items['data'][i]["total"];
        var descuento    = items['data'][i]["descuento"];
        var md    = items['data'][i]["Medico"];
        var record = items['data'][i]["Historia"];
        var codmd = items['data'][i]["codmedico"];
        var idf =items['data'][i]["id"];
        var titleDev="Devolver";
        var idDev="";
        if (items['data'][i]["numnotcre"]!==null && items['data'][i]["numnotcre"]!=="") {
           titleDev="Devolver*";
           idDev = items['data'][i]["numnotcre"];
           numfactu = numfactu+'-'+idDev;
        }; 
        //USERS
        for (var j = 0; j < usuario.length; j++) { 
            
            if(usuario[j].login.toUpperCase()==items['data'][i]["usuario"].toUpperCase()){              
              options+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              options+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }  
        }
        //USERS
        //<MEDICOS        
        for (var mi =0; mi < medico.length;  mi++) {
           if(medico[mi].codmedico==codmd){ 
              moptions+="<option value='"+medico[mi].codmedico+"' selected >"+medico[mi].medico+"</option>";   
           }else{
              moptions+="<option value='"+medico[mi].codmedico+"'>"+medico[mi].medico+"</option>";   
           }             
        };
        //MEDICOS>

        let habilitado="disabled";
        if( $('#codperfil').val()=="01" ) {
            habilitado="";
        }
        

         let clase='';
         if (items['data'][i]["statfact"]=='2') {
            clase = 'table-danger';
         }else if (items['data'][i]["statfact"]=='1') {
           clase = 'table-info';
         } 

        //total = parseFloat(total)-parseFloat(descuento)
        subtotal=parseFloat(subtotal).toFixed(2)
		    descuento=parseFloat(descuento).toFixed(2)
		    total=parseFloat(total).toFixed(2)
         $('#tblLista').append('<tr id='+numfactu+' class="'+clase+'">'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center">'+Status+'</td>'+
		    	' <td align="center">'+subtotal+'</td>'+
			    ' <td align="center">'+descuento+'</td>'+
         	' <td align="right">'+total+'</td>'+
          ' <td align="center"><select name="users[]" '+habilitado+' id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' <td align="center"><select name="medic[]"  id='+i+'-'+numfactu+'-'+idf+'      class="form-control medicos enterpass enterkey" > <option value="" selected ></option></select> </td>'+      
          ' <td align="right">'+record+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-primary consultar">Consultar</td>'+
         	' <td align="center"> <button type="button" name="imprimir"  id=""  class="btn btn-success imprimir">Imprimir</button> </td>'+
         	' <td align="center"> <button type="button" name="anular"    id="'+numfactu+'"  class="btn btn-danger  anular" style="'+display+'";>Anular</button> </td>'+
         	' <td align="center"> <button type="button" name="devolver"  id=""  class="btn btn-warning devolver">'+titleDev+'</button> </td>'+

          ' </tr>');
          $("#"+numfactu+'-'+i).html(options);
          $("#"+i+'-'+numfactu+'-'+idf).html(moptions);
          options='';
          moptions='';
    };
     
  
  }


      $('#tblLista').on('change', 'tbody tr select.users', function(event) {
        var elemt = $(this);
        var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
        let user = $("#idusr").val();
        let nuevousuario = elemt.val();
        //.php

        var datasave ={
         docnumber   : numfactu
        ,tipodoc : '01'
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

      $('#tblLista').on('change', 'tbody tr select.medicos', function(event) {

        var este= $(this)
        var newMedico= $("#"+este.attr('id')).val()
        var arrDat= este.attr('id').split('-')
        var factnum=arrDat[1]
        var idcompny=2
        var id=arrDat[2]
        var datasave ={
         factura   : factnum
        ,idempresa : idcompny
        ,fieLd : newMedico
        ,typefield : 'm'
        ,id
    }

    url="../../clases/updatefacmeduser.php";

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

        getConsultasInvoices(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keydown', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getConsultasInvoices( '' , search ,fecha );
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
   getConsultasInvoices( '' , search , fecha );

    $('.pagination').bootpag({
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
          document.getElementById('numfactu').value=numfactu;
          window.location.href = "mslaser-ret.php?return=true&numero="+numfactu;
       }
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
      var result = numfactu.split('-');
      let user = $('#idusr').val();
       if(result.length==1){
         resulset = isConsulta(numfactu);
         invoicePrinting(numfactu,resulset[0]['cod_subgrupo'],user); //numfactu;
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

  function invoicePrinting(_numfactu,service,user){
  

    var datasave ={
         numfactu   : _numfactu  
        ,times : 1
        ,service : service,
        user     
    }

    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    if (prninvoice!='1' && autoposprn!='1' ) {

          window.open('../../clases/printconsultaspdf.php?numfactu='+_numfactu+'&times='+'1'+'&service='+service+'&user='+user,'', '_blank');
    }else{
          url="../../clases/printinvservice.php";

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


function isConsulta(numfactu){

    var datasave ={
         numfactu   : numfactu              
    }

    url="../../clases/isservicios.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     //items= resp ; //jQuery.parseJSON(resp);
     items = jQuery.parseJSON(resp);
     return items;
}

 function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

     }
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