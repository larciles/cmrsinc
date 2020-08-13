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

     url="../../controllers/productsinvoicecontroller.php";
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
    
    //<USER
    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;
    //USER>

    //<MEDICOS
    var moptions=''
    var res = getData("../../clases/medicos.php",{filtro:'si' });
    var medico = jQuery.parseJSON(res); 
    //MEDICOS>

    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 
    
    let display='display: none';
    if ($("#access").val()=='6' || $("#codperfil").val()=='01') {
         display='display: inline';     
     };

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
        var record = items['data'][i]["Historia"];
        var codmd = items['data'][i]["codmedico"];
        var idf =items['data'][i]["id"];
        
        var titleDev  = "Devolver";
        var idDev     = "";


        var pagado="";
      
        if ( items['data'][i]["statfact"]=='1') {
            pagado="sin-pagar";        
        }
        
        total    = parseFloat(total);
        total    = parseFloat(total).toFixed(2);
        subtotal = parseFloat(subtotal).toFixed(2);
        descuento= parseFloat(descuento).toFixed(2);
        impuesto = parseFloat(impuesto).toFixed(2);
        envio    = parseFloat(envio).toFixed(2);
        //USERS
        for (var j = 0; j < usuario.length; j++) { 
            if(usuario[j].login.toUpperCase()==items['data'][i]["usuario"].toUpperCase()){              
              options+="<option value='"+usuario[j].login+"' selected >"+usuario[j].login+"</option>";   
            }else{
              options+="<option value='"+usuario[j].login+"'>"+usuario[j].login+"</option>";   
            }         
        }
        //USERS>
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

         // s
         var paid    = items['data'][i]["paid"];
         let clasemiss='';
         let paidtotal = parseFloat(paid).toFixed(2)
         if (total!==paidtotal) {
            clasemiss = ' misspaid ';
         }else{
           clasemiss = '';
         }          
         // e 

         $('#tblLista').append('<tr id='+numfactu+' class="'+clase+clasemiss+'">'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center">'+Status+'</td>'+
         	' <td align="right">'+subtotal+'</td>'+
          ' <td align="right">'+descuento+'</td>'+
          ' <td align="right">'+impuesto+'</td>'+
          ' <td align="right">'+envio+'</td>'+
          ' <td align="right">'+total+'</td>'+
          ' <td align="center"><select name="users[]" '+habilitado+' id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+ 
          ' <td align="center"><select name="medic[]"  id='+i+'-'+numfactu+'-'+idf+'      class="form-control medicos enterpass enterkey" > <option value="" selected ></option></select> </td>'+      
          ' <td align="right">'+record+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-info consultar">Consultar</td>'+
          ' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-warning devolver  '+pagado+' ">Devolver</td>'+
         	' <td align="center"> <button type="button" name="imprimir"  id=""  class="btn btn-success imprimir  '+pagado+' ">Imprimir</button></td>'+
         	' <td align="center"> <button type="button" name="anular"    id=""  class="btn btn-danger anular" style="'+display+'";>Anular</button></td>'+   

         	' </tr>');
          $("#"+numfactu+'-'+i).html(options);
          $("#"+i+'-'+numfactu+'-'+idf).html(moptions);
          options='';
          moptions='';
    };
   
  
  }
//USERS
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
//USERS>

//MEDICOS
      $('#tblLista').on('change', 'tbody tr select.medicos', function(event) {

        var este= $(this)
        var newMedico= $("#"+este.attr('id')).val()
        var arrDat= este.attr('id').split('-')
        var factnum=arrDat[1]
        var idcompny=1
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
//MEDICOS>

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
         // window.location.href = 'return.php?fac='+numfactu;
          document.getElementById('numfactu').value=numfactu;
          window.location.href = "msprods-ret.php?return=true&numero="+numfactu;
       }
   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
         // window.location.href = 'edit.php?fac='+numfactu;
          window.location.href += "?edicion=true&numero="+numfactu;   
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

    let user = $('#loggedusr').val();
    var datasave ={
         numfactu   : _numfactu
        ,times : times
    }

   
    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();


    if (prninvoice!='1' && autoposprn!='1' ) {
      window.open('../../clases/printprodpdf.php?numfactu='+_numfactu+'&times='+times+'&user='+user,'', '_blank');
    } else {

      url="../../clases/printprod.php";

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

       $("#pventa").click(function(){
    var usuario = $('#loggedusr').val();
    var fecha =$('.fechaf').val();
    if (fecha=='') {      
       fecha =$('.fechaf').val();

    };
    if (fecha=='') {
       fecha=getToDay();
    } 
   let start =$('#start').val();
   let tiporeporte = 'false';
   window.open('../cuadreproducts/venta.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'3'+'&start='+start+'&tiporeporte='+tiporeporte,'Ventas', '_blank');
   

  })

         function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}