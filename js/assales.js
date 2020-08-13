var totalPage;
var voidinvoice;
var currentpage=1;
getConsultasInvoices();

    //totalPage= getData("../../clases/paginacionassaleshandler.php",{totalPage:'S',search,fecha,grupo},'POST');
    let grupo = $('#grupo').val();
    totalPage= getData("../../clases/paginacionassaleshandler.php",{totalPage:'S',grupo},'POST');
    Muestra_Paginacion(totalPage);

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

    var items;


    let pagedat = getData("../../clases/paginacionassaleshandler.php",{page,search,fecha,grupo});
    items = jQuery.parseJSON(pagedat);  


    

try{
   


    var lenArr=items.length;
    for (var i = 0; i < lenArr; i++) {
    	  var fecha_cita = items[i].fecha_cita;
        var nombres  = items[i].nombres;
        var Historia = items[i].Historia;
        var telfhabit   = items[i].telfhabit;
        var medico  = items[i].medico;
        var usuario  = items[i].usuario;
		    
         $('#tblassalesLista').append('<tr >'+
          ' <td align="center">'+(i+1)+'</td>'+
          ' <td align="center">'+Historia+'</td>'+
          ' <td>'+nombres+'</td>'+
          ' <td align="center">'+telfhabit+'</td>'+
          ' <td align="center">'+medico+'</td>'+         
          ' <td align="center">'+usuario+'</td>'+          
          ' <td  align="center">'+fecha_cita+'</td>'+
          ' </tr>'); 
    };
     
 
    }    
    catch(err){

    totalPage=0;
    
    $("#tblassalesLista > tr").remove();
    
    $("#tblassalesLista").find("tr:gt(0)").remove(); 
    }  
  }


    $('#tblassalesLista').on('change', 'tbody tr select.users', function(event) {
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


      $('#tblassalesLista').on('change', 'tbody tr select.medicos', function(event) {

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
    //  $('.pagination').bootpag({
    //    total: totalPage,
    //    page: 1,
    //    maxVisible: 10
    // }).on('page', function(event, num){
    //     var fecha,search ="";
    //     fecha=$('.datepicker').val();
    //     search = $('#search').val();

    //     getConsultasInvoices(  num, search , fecha );
    //     $(".content2").html("Page " + num); // or some ajax content loading...
    // });


   $('#search').on('keydown', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();
           
           let grupo = $('#grupo').val();
           totalPage= getData("../../clases/paginacionassaleshandler.php",{totalPage:'S',grupo,search,fecha},'POST');
           Muestra_Paginacion(totalPage);
           getConsultasInvoices( 1 , search ,fecha );
              // $('.pagination').bootpag({
              //      total: totalPage,
              //      page: 1,
              //      maxVisible: 10
              // })

            //Disable textbox to prevent multiple submit
            //$(this).attr("disabled", "disabled");
         }
   });


$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('#date_assale').change(function(){
  $("#tblassalesLista").find("tr:gt(0)").remove()
   var fecha,search ="";
   fecha =$('#date_assale').val();
   search = $('#search').val(); 

  let grupo = $('#grupo').val();
  totalPage= getData("../../clases/paginacionassaleshandler.php",{totalPage:'S',grupo,search,fecha},'POST');
  Muestra_Paginacion(totalPage);

   getConsultasInvoices( 1 , search , fecha );


});


$('#tblassalesLista').on('click', 'tbody tr button', function(event) {
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
          window.location.href = 'invoicedit.php?fac='+numfactu;
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
         alert();
         //resulset = isConsulta(numfactu);
         //invoicePrinting(numfactu,resulset[0]['cod_subgrupo']); //numfactu;
       }
   }
   
})

  function invoicePrinting(_numfactu,service,user){
  
    var datasave ={
         numfactu   : _numfactu  
        ,times : 1
        ,service : service
        ,user     
    }

     let prninvoice = $('#prninvoice').val();
     let autoposprn = $('#autoposprn').val();
     let pathprn    = $('#pathprn').val();

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

    url="../../clases/isconsulta.php";

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

function getData(url,data,type){
  if (data==undefined) {
    data = {    }
  };
  if (type==undefined) {
    type = "GET"
  };
    return $.ajax({
        type: type,
        url: url,
        data : data,
        async: false
    }).responseText;

}

function Muestra_Paginacion (totalPage) {
 
 $('.pagina_ass').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        currentpage=num;        
        var fecha,search ="";
        fecha=$('.datepicker').val();
        search = $('#search').val();
        getConsultasInvoices(  num, search , fecha );


        $(".content2").html("Page " + num); // or some ajax content loading...
    });

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