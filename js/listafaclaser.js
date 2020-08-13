var totalPage;


getLaserInvoices();

 

  function getLaserInvoices(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

     url="../../controllers/lasercontroller.php";
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
    
    //USER
    var serv= getData("../../clases/getusers.php");
    let usuario = jQuery.parseJSON(serv);
    var options;
    //USER>
    //<MEDICOS
    var moptions=''
    var res = getData("../../clases/medicos.php",{filtro:'si' });
    var medico = jQuery.parseJSON(res); 
    //MEDICOS>


    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    
    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 

    let display='display: none';
    if ($("#access").val()=='6' || $("#codperfil").val()=='01') {
         display='display: inline';     
     };
     
    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var numfactu = items['data'][i]["numfactu"];
        var nombres  = items['data'][i]["nombres"];
        var fechafac = items['data'][i]["fechafac"];
        var Status   = items['data'][i]["Status"];
        var total    = items['data'][i]["total"];
        var descuento    = items['data'][i]["descuento"];
        var subtotal    = items['data'][i]["subtotal"];        
        var md    = items['data'][i]["Medico"];
        var titleDev="Devolver";
        var record = items['data'][i]["Historia"];
        var codmd = items['data'][i]["codmedico"];
        var idf =items['data'][i]["id"];
        var idDev="";

        var disable ='';
        if (items['data'][i]["statfact"]!==1) {
           disable ='disabled';
        };
        
        //USER
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


        if (items['data'][i]["numnotcre"]!==null && items['data'][i]["numnotcre"]!=="") {
           titleDev="Devolver*";
           idDev = items['data'][i]["numnotcre"];
           numfactu = numfactu+'-'+idDev;
        }; 
        
        subtotal = parseFloat(subtotal).toFixed(2)
        descuento = parseFloat(descuento).toFixed(2)

        total = parseFloat(subtotal)-parseFloat(descuento)
        total = parseFloat(total).toFixed(2)

         let clase='';
         if (items['data'][i]["statfact"]=='2') {
            clase = 'table-danger';
         }else if (items['data'][i]["statfact"]=='1') {
           clase = 'table-info';
         } 


         $('#tblLista').append('<tr id='+numfactu+' class="'+clase+'">'+
         	' <td  align="center">'+numfactu+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td align="center">'+fechafac+'</td>'+
         	' <td align="center">'+Status+'</td>'+
          ' <td align="right">'+subtotal+'</td>'+
          ' <td align="right">'+descuento+'</td>'+
         	' <td align="right">'+total+'</td>'+
          ' <td align="center"><select name="users[]" '+habilitado+' id='+numfactu+'-'+i+'      class="form-control users enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' <td align="center"><select name="medic[]"  id='+i+'-'+numfactu+'-'+idf+'      class="form-control medicos enterpass enterkey" > <option value="" selected ></option></select> </td>'+
          ' <td align="right">'+record+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id="" class="btn btn-primary consultar " >Consultar</td>'+
         	' <td align="center"> <button type="button" name="imprimir"  id="imprimir" class="btn btn-success btn_imprimir">Imprimir</button> </td>'+
         	' <td align="center"> <button type="button" name="anular"    id="" class="btn btn-danger btn_anular" style="'+display+'";>Anular</button> </td>'+
         	' <td align="center"> <button type="button" name="devolver"  id="" class="btn btn-warning devolver">'+titleDev+'</button> </td>'+

         	' </tr>');
          $("#"+numfactu+'-'+i).html(options);
          $("#"+i+'-'+numfactu+'-'+idf).html(moptions);
          options='';
          moptions='';
    };
     //console.log(resp);
     //console.log(items);
  
  }

  
    // $('.users').change(function(event) {
    //    alert();
    // })

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

      $('#tblLista').on('change', 'tbody tr select.medicos', function(event) {

        var este= $(this)
        var newMedico= $("#"+este.attr('id')).val()
        var arrDat= este.attr('id').split('-')
        var factnum=arrDat[1]
        var idcompny=3
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


     $('.pagination2').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha  =$('.fechaf').val();
        search =$('#searchf').val();

        getLaserInvoices(  num, search , fecha );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#searchf').on('keydown', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
           search = $('#searchf').val(); 
           fecha=$('.fechaf').val();

           getLaserInvoices( '' , search ,fecha );
              $('.pagination2').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })

            //Disable textbox to prevent multiple submit
            //$(this).attr("disabled", "disabled");
         }
   });


$('.fechaf').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.fechaf').change(function(){
   var fecha,search ="";
   fecha =$('.fechaf').val();
   search = $('#searchf').val(); 
   getLaserInvoices( '' , search , fecha );

    $('.pagination2').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblLista').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();
   let user = $("#idusr").val();
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
   }else if(elemt.attr('class').indexOf("btn_imprimir")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          invoicePrinting(numfactu,1,user);
       }else if(result.length>1){
          invoicePrinting(result[0],1,user);
       }
   }else if(elemt.attr('class').indexOf("btn_anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
         voidinvoice=numfactu;
         $("#void").modal();
         //resulset = isConsulta(numfactu);
         //invoicePrinting(numfactu,resulset[0]['cod_subgrupo']); //numfactu;
       }       else{
        if($('#codperfil').val()=='01'){
          numfactu= result[0];
          voidinvoice=numfactu;
          $("#void").modal();
        }
       }
   }
})



 function invoicePrinting(_numfactu,times,user){
         if (times==undefined) {
      times=1;
    };

    var datasave ={
         numfactu   : _numfactu
		    ,times : times
        ,user
    }
   
    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    if (prninvoice!='1' && autoposprn!='1' ) {
        window.open('../../clases/printlaserpdf.php?numfactu='+_numfactu+'&times='+'1'+'&user='+user,'', '_blank');
    }else{
      url="../../clases/printinglaser.php";

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

  $("#lventa").click(function(){
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
   window.open('../cuadrelaser/venta.php?fecha='+fecha+'&usuario='+usuario+'&idempresa='+'3'+'&start='+start+'&tiporeporte='+tiporeporte,'Ventas', '_blank');
   

  })

  function getToDay(){
    var hoy = new Date().toLocaleDateString('en-US', {  
            month : 'numeric',
            day : 'numeric',        
            year : 'numeric'
            }).split(' ').join('/');
     return hoy;
}