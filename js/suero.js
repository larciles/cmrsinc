 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;

    var id_random=undefined;


  $('#xtitulo').html('<b>Suero / Células M / Bloqueo</b>');
      var i=1;  
      $('#add').click(function(){  
           i++;  
           var idpro="serv"+i;
           var idtpr="tpre"+i;
           var idseg="tseg"+i;
           var iddes="descuento"+i;
           var tax ="tax"+i;
           var id_precio=makeid();

           $('#dynamic_field').append('<tr id="'+i+'">'+
               ' <td><select id="'+idpro+'" name="serv[]"        class="form-control service enterpass enterkey" > <option value="" selected ></option></select> <input type="hidden"  id="coditems'+i+'"   name="coditems[]"  value="" class="coditems" /></td>'+
               ' <td><select id="'+idtpr+'" name="listaprecio[]" class="form-control enterkey">          <option value="" selected ></option></select> <input type="hidden"  id="codprecio1'+i+'" name="codprecio[]" value="" class="codprecio" /></td>'+
               //' <td><select id="'+idseg+'" name="seguro[]"      class="form-control">          <option value="" selected ></option></select> <input type="hidden"  id="insurance1'+i+'" name="insurance[]" value="" class="insurance" /></td>'+            
               ' <td><input type="text" name="cantidad[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" /></td>'+
               ' <td><input type="text" name="precio[]" id="'+id_precio+'"  placeholder="precio" class="form-control precio enterkey" /></td>'+ 
               //' <td><input type="checkbox" checked data-toggle="toggle" data-size="small" data-on="%" data-off="$" class="percentage newBSswitch" title="edit"  id="percentage" name="percentage[]"></td>  '+
               ' <td><input type="text" name="descuento[]"  readonly="readonly" id="'+iddes+'" placeholder="Descuento" class="form-control " /> <input type="hidden"  id="detaialprcnt"'+iddes+'" name="detaialprcnt[]" value="" class="detaialprcnt" /></td>'+ 
               // ' <td><input type="text" name="tax[]" readonly="readonly" id="'+tax+'"  placeholder="Impuesto" class="form-control " /></td>'+
               ' <td><input type="text" name="name[]"  readonly="readonly" placeholder="Subtotal" class="form-control name_list subtotal" /></td>'+
               ' <td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  

           setConsultas(idpro);
          // setConsultas('serv1',{servicio:'S', orderby:'desitems',grupo:'004'},'POST');
           setTipoDePrecios(idtpr);
           setSeguro(idseg);
           $('#'+idpro).focus();

           // $('.newBSswitch').bootstrapSwitch('state', true); 


      });  

      //
      function makeid() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
}

      //\ 

      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#'+button_id+'').remove();  
            setTotalLines();;
      });  


      $('#submit').click(function(){
         $('#submit').prop('disabled', true);
         // if ($('#idassoc').val()!=="" && $('#serv1').val()!=="" && $('#invoicen').val()=="") {        
               $.ajax({  
                    url:"../../clases/invoicingservicios.php",  
                    method:"POST",  
                    data:$('#add_name').serialize(),  
                    success:function(data)  
                    {  

                        

                          items= jQuery.parseJSON(data); //alert(data); 
                          $("#invoicen").attr("value", items);
                          $('#invoicen').val(items);
                          $('#invoicen').html('    Invoice # '+items);
                          $('#factura').val(items);

                          $('#btnpaymnt').prop('disabled', false);
                          $('#submit').prop('disabled', true);

                          try{
                              getInvoices();
                          }catch(err){

                          }

                         
                          $("#paymentsModal").modal();
                           $('#submit').prop('disabled', false);
                        // document.getElementById("btnpaymnt").click();
                       //  $('#add_name')[0].reset();  
                    }  
               }); 
           // }else{
           //  if ($('#invoicen').val()!=="") {
           //    alert('Factura Ya existe')
           //  };
           // }; 
      }); 


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

      $(".delete-row").click(function(){

             var facturaNum = $('#invoicen').val().trim();

             let longArtr = $("#dynamic_field tbody").find('select[name="serv[]"]').length;
             if (longArtr>1) {
                for (var i = 1; i <= longArtr; i++) { 

                      try {            
                                
                                let producto  = $("#dynamic_field tbody").find('select[name="serv[]"]')[1].value;
                                let cantidad  = $("#dynamic_field tbody").find('input[name="cantidad[]"]')[1].value;
                               
                                $('#'+$("#dynamic_field tbody").find('select[name="serv[]"]').parent().parent()[1].id).remove();            
          
                                if (facturaNum!=="" && producto!="") {                      
                                   ajustaInventario(producto,facturaNum,cantidad);
                                   setTotalLines();          
                                };
                      }
                    catch(err) {

                    }

                };

                //PRIMER ELEMENTO
              
                console.log('PE prod '+$("#dynamic_field tbody").find('select[name="serv[]"]')[0].value);
                console.log('PE cant '+$("#dynamic_field tbody").find('input[name="cantidad[]"]')[0].value);

                let producto   = $("#dynamic_field tbody").find('select[name="serv[]"]')[0].value;
                let cantidad   = $("#dynamic_field tbody").find('input[name="cantidad[]"]')[0].value;
                let precio     = $("input[name='precio[]']")[0].value ;
                let descuento  = $("input[name='descuento[]']")[0].value;
              //  let tax        = $("input[name='tax[]']")[0].value; 
                let subtotal   = $("input[name='name[]']")[0].value; 
                let stotal     = $('#tlsubototal').val();
                let discprcntg = $('#discprcntg').val();
                let discamount = $('#discamount').val();
                let tlimpuesto = $('#tlimpuesto').text();
                let shipping   = $('#shipping').val();
                let tltotal    = $('#tltotal').text();

                if (facturaNum!=="" && producto!="") {                      
                    ajustaInventario(producto,facturaNum,cantidad);         
                };
                $('#serv1').prop('selectedIndex', 0)
                $("input[name='precio[]']")[0].value='';
                $("input[name='descuento[]']")[0].value=''
              //  $("input[name='tax[]']")[0].value=''; 
                $("input[name='name[]']")[0].value=''; 

                $('#tlsubototal').text(0);
                $('#tlsubototal').val('');

                $('#tlimpuesto').text('0');
                $("input[name='cantidad[]']")[0].value='1';

                $('#tltotal').text('0');
                $('#discprcntg').val('');
                $('#discamount').text();
                $('#discamount').val(0);
                if (facturaNum!=="" && producto!="") {                      
                    setTotalLines();        
                };

                if (facturaNum!=="") {  
                      
                       let del=1;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);

                       $.ajax({  
                          url:"../../clases/invoicingservicios.php",  
                          method:"POST",  
                          data:$('#add_name').serialize(),  
                          success:function(data)  
                          {  
                               
                                items=data;
                                items.trim();
                                $("#invoicen").attr("value", items);
                                $('#invoicen').val(items);
                                $('#invoicen').html('Invoice # '+items);
                                $('#numfactu').val(items);

                          }  
                     }); 


                       del=0;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);    
                       $('#btnpaymnt').hide();               
      
                 };

                    
             }else{
               //
                producto   = $("#dynamic_field tbody").find('select[name="serv[]"]')[0].value;
                cantidad   = $("#dynamic_field tbody").find('input[name="cantidad[]"]')[0].value;
                if (facturaNum!=="" && producto!="") {                      
                    ajustaInventario(producto,facturaNum,cantidad);         
                };
                $('#serv1').prop('selectedIndex', 0)
                $("input[name='precio[]']")[0].value='';
                $("input[name='descuento[]']")[0].value=''
               // $("input[name='tax[]']")[0].value=''; 
                $("input[name='name[]']")[0].value=''; 

                $('#tlsubototal').text(0);
                $('#tlsubototal').val('');

                $('#tlimpuesto').text('0');
                $("input[name='cantidad[]']")[0].value='1';

                $('#tltotal').text('0');
                $('#discprcntg').val('');
                $('#discamount').text();
                $('#discamount').val(0);
                if (facturaNum!=="" && producto!="") {                      
                    setTotalLines();        
                };

                if (facturaNum!=="") {  
                      
                       let del=1;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);

                       $.ajax({  
                          url:"../../clases/invoicingservicios.php",  
                          method:"POST",  
                          data:$('#add_name').serialize(),  
                          success:function(data)  
                          {  
                               
                                items=data;
                                items.trim();
                                $("#invoicen").attr("value", items);
                                $('#invoicen').val(items);
                                $('#invoicen').html('Invoice # '+items);
                                $('#numfactu').val(items);

                          }  
                     }); 


                       del=0;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);
                       $('#btnpaymnt').hide();
      
                 };
               //
             };
   document.getElementById('submit').disabled = false;
   $('#btnpaymnt').show();

      });


function ajustaInventario(producto,facturaNum,cantidad){
    data = {
      action    : 'delete',
      coditems  : producto,
      numfactu  : facturaNum,
      cantidad  : cantidad
    } 

    $.ajax({                
          url:"../../clases/updatesueroinvoice.php",  
          method:"POST",  
          data: data ,  
          success:function(data)  
          {               
             //items=data //jQuery.parseJSON(data); // el dato no viene en formato json               
          }  
     }); 
}

  function setTipoDePrecios(id){
    var res= getData("../../clases/tipoprecios.php");
    items= jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        if (j==0) {
           options+="<option value='"+items[j].codtipre+"' selected >"+items[j].destipre+"</option>"; 
        };
        options+="<option value='"+items[j].codtipre+"'>"+items[j].destipre+"</option>"; 
    }
    $("#"+id).html(options);
  }

// 

  function setSeguro(id){
    var res= getData("../../clases/seguros.php");
    items= jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        options+="<option value='"+items[j].codseguro+"'>"+items[j].seguro+"</option>"; 
    }
    $("#"+id).html(options);
  }

  function setMedios(){
    var res= getData("../../clases/medios.php");
    items= jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        options+="<option value='"+items[j].codigo+"'>"+items[j].medio+"</option>"; 
    }
    $("#medio").html(options);
  }

  function setConsultas(id,data,type){
           
    var serv= getData("../../clases/serviciossuero.php",data,type);
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 

         if ( items[j].desitems.indexOf("CINTRON")>-1) {
              options+="<option value='"+items[j].coditems+"' style='background-color: aqua;font-size: 1.1em;'>"+items[j].desitems+"</option>"; 
         }else{
            
             if ( items[j].desitems.indexOf("BLOQUEO")>-1) {
              options+="<option value='"+items[j].coditems+"' style='background-color:#FFA500;font-size: 1.1em;'>"+items[j].desitems+"</option>"; 
             }else{
              options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
             }
         }
     }
     $("#"+id).html(options);
  }

   
   setConsultas('serv1',{servicio:'S', orderby:'desitems',grupo:'004'},'POST');
   setTipoDePrecios('tpre1');
   setSeguro('tseg1');
   setMedios();


// $('.service').change(function(){
   $('#dynamic_field').on('change', 'tbody tr td select', function(event) {
      var element=$(this);
     
      if($(this).parent().parent().hasClass("highlight")   || $(".service").is(":focus") ){

         var row_index = $(this).parent().parent().index();
         var col_index = $(this).parent().index();        
         var xid = $(this).parent().parent().attr('id').toString();
       
       
         var cantidad  = $("input[name='cantidad[]']")[row_index].value;

         var coditems  = $("select[name='serv[]']")[row_index].value;
         $("input[name='coditems[]']")[row_index].value = coditems;



         //DEDUCIBLE
         if (coditems=='0000000005') {           
           $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", false); 
           id_random=$("input[name='precio[]']")[row_index].id;
         }else if (coditems=='0000000000' || coditems=='01'){
           $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", true); 
         };
         //\


         var codtipre  =  $("select[name='listaprecio[]']")[row_index].value ;     
         $("input[name='codprecio[]']")[row_index].value = codtipre;   

         var codinsurance  = $('#tseg'+xid).val(); 
        // $("input[name='insurance[]']")[row_index].value ='0'; //codinsurance;

         var descuento =  $("input[name='descuento[]']")[row_index].value ;    ;    

     
      }

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,row_index);
      }else{
        setTotalLines();
      };

     //ES EXCLUSIVO? CAMBIA AL MEDICO: IGUAL
     getExclusivo(coditems);
     //------------------------------------------------------------
     
         
     });

    function getExclusivo(coditems){

    var excmedico;  
    var Http = new XMLHttpRequest();
    var url='../../clases/getexclusivo.php'; 
    var params = "coditems="+coditems; 
    Http.open( "POST", url, true );    
    Http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

    Http.onreadystatechange=(e)=>{     
      if (Http.readyState==4 && Http.status==200) {
          var res= JSON.parse( Http.responseText ) ;

          try{
              excmedico = res[0].Codmedico;
              if (excmedico!=undefined) {
                document.getElementById('medico').value=excmedico
                document.getElementById('medicohd').value=excmedico;
                $("#medico").attr("disabled","disabled");
                document.getElementById("medico").setAttribute("readonly", true)
                //document.getElementById("medico").removeAttribute("readonly")
              }
          }catch(err){

          }
          
          

      }else{
        console.log(e)
      }
      
    }
     

    Http.send(params);
  }
    

  $('#dynamic_field').on('change', 'tbody tr td .cantidad', function(event) {
  
    var index = $(this).parent().parent().index();
    var id = $(this).parent().parent().attr('id');
    cantidad  = $("input[name='cantidad[]']")[index].value
    if($.isNumeric( cantidad )){
      setGeneralValues(this,index,id);
    }else{
      $("input[name='cantidad[]']")[index].value=""
      $("input[name='cantidad[]']")[index].focus();
    }

  })

  

  $('.precio').keypress(function(e) {
     if(e.which == 13) {

       //$("input[name='codprecio[]']")[row_index].value
       //$('#'+$("input[name='precio[]']")[row_index].id)

        var row_index = $(this).parent().parent().index();
        var col_index = $(this).parent().index();        
        var xid = $(this).parent().parent().attr('id').toString();   

     
        var cantidad  = $("input[name='cantidad[]']")[row_index].value

        var coditems  = $("select[name='serv[]']")[row_index].value;
        $("input[name='coditems[]']")[row_index].value = coditems;
      
        var codtipre  =  $("select[name='listaprecio[]']")[row_index].value;    
        $("input[name='codprecio[]']")[row_index].value = codtipre;   

        //var codinsurance  = $('#tseg'+xid).val(); 
        //$("input[name='insurance[]']")[row_index].value = codinsurance;
        var descuento = $("input[name='descuento[]']")[row_index].value;

        var precio = $('#'+$("input[name='precio[]']")[row_index].id).val();

        if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
            subTotal(coditems,codtipre,descuento,cantidad,row_index,precio);
            $("#add").focus();
        };


    }
  })


  $('#dynamic_field').on('change', 'tbody tr td .precio', function(event) {


        var row_index = $(this).parent().parent().index();
        var col_index = $(this).parent().index();        
        var xid = $(this).parent().parent().attr('id').toString();    

        var precio = $('#'+$("input[name='precio[]']")[row_index].id).val();
                
        if ($.isNumeric(precio)) {

         
            var cantidad  = $("input[name='cantidad[]']")[row_index].value;
            var coditems  = $("select[name='serv[]']")[row_index].value;
           
            $("input[name='coditems[]']")[row_index].value = coditems;
          
            var codtipre  =  $("select[name='listaprecio[]']")[row_index].value;  
            $("input[name='codprecio[]']")[row_index].value = codtipre;   

            //var codinsurance  = $('#tseg'+xid).val(); 
            //$("input[name='insurance[]']")[row_index].value = codinsurance;
            var descuento = $('.descuento').val();            

            if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
                subTotal(coditems,codtipre,descuento,cantidad,row_index,precio);
                $("#add").focus();
            };
        }else{
            $('#'+$("input[name='precio[]']")[row_index].id).val('');
            $('#'+$("input[name='precio[]']")[row_index].id).focus();
        };  

  })



 function changeItems(){
       $.ajax({  
                    url:"../../clases/changeitemconsultas.php",  
                    method:"POST",  
                    data:$('#add_name').serialize(),  
                    success:function(data)  
                    { 
                     }
                 })
 } 


  function setGeneralValues(element,index,xid){
     
      var cantidad  = $('.cantidad')[index].value;    ;
      var coditems  = $('#serv'+xid).val();
      var codtipre  = $('#tpre'+xid).val();        
      var descuento = $('#descuento'+xid).val();    //por linea 

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,index);
      };
  }


    $('#discprcntg').change(function(){
    
    if($.isNumeric( $('#discprcntg').val() )){

     $('#discamount').prop('readonly', false);
     $("#discamount").removeClass("readonly");
     if ($('#discprcntg').val()!=="" ) {
        $("#discamount").addClass("readonly");     
        $('#discamount').prop('readonly', true);
     }
   
     }else{
      $("#discamount").removeClass("readonly");
      $('#discamount').prop('readonly', false);
      $('#discprcntg').val('');
      $('#discprcntg').focus();
     }
      setTotalLines();
  })


  $('#discamount').change(function(){

    if ($.isNumeric($('#discamount').val())) {

         $('#discprcntg').prop('readonly', false);
         $("#discprcntg").removeClass("readonly");
         if ($('#discamount').val()!=="" || $('#discamount').val()>0 ) {     
             $("#discprcntg").addClass("readonly");       
             $('#discprcntg').prop('readonly', true);
         }
         var monto = parseFloat( $('#discamount').val());
         var subt  =  parseFloat($('#tlsubototal').val());
         var porcn = (monto*100)/subt;
         $('#discprcntg').val(porcn);
       
    }else{
      $("#discprcntg").removeClass("readonly");
      $('#discprcntg').prop('readonly', false);      
      $('#discprcntg').val('');
      $('#discamount').val('');
      $('#discamount').focus();
    }; 
    setTotalLines();
  })

  function subTotal(coditems,codtipre,descuento,cantidad,id,precio){
    if (precio==undefined) {
        precio=0;
    };
     url="../../clases/calcularsubtotal.php";
     data = {
        coditems : coditems ,
        codtipre : codtipre,
        descuento : descuento,
        cantidad : cantidad,
        precio   : precio
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    
     items= jQuery.parseJSON(resp);

     var values   = $("input[name='precio[]']").map(function(){return $(this).val();}).get();
     arrayPrecios = $("input[name='precio[]']") ;//document.forms[0].elements["precio[]"] ;
     arraySubTotl = $("input[name='name[]']");//document.forms[0].elements["name[]"] ; 

     var precio=0;
     var subtotal=0;
   
    if (jQuery.type(items)!=='null') {
        precio=items['precunit'];
        subtotal=items['subtotal'];
     }

      $("input[name='precio[]']")[id].value =precio; //document.forms[0].elements["precio[]"][id-1].value=precio;
      $("input[name='name[]']")[id].value =subtotal; //document.forms[0].elements["name[]"][id-1].value=subtotal;
      setTotalLines();
  }

  


  function setTotalLines(){

    var lineSubtotal=0;
    var arraySubTotl =  $("input[name='name[]']"); //arraySubTotl = document.forms[0].elements["name[]"] ; 

    if(jQuery.type( arraySubTotl.length )=='undefined'){
        $('#tlsubototal').val(document.forms[0].elements["name[]"].value);
    }else{
        for (var i = 0; i < arraySubTotl.length; i++) {
          if ( $("input[name='name[]']")[i].value!=="" && $("select[name='serv[]']")[i].value!=="") {
              var cantidad=0;
              var precio=0;
              var subtot=0;
              cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
              precio = parseFloat( $("input[name='precio[]']")[i].value ) ;
              subtot = cantidad * precio  ;
              $("input[name='name[]']")[i].value =subtot;

               if($("#discprcntg").hasClass("readonly")){

               }else{
                $('#discamount').val('$0');
               }

              
              
              lineSubtotal=lineSubtotal + parseFloat( $("input[name='name[]']")[i].value ) ; //parseFloat(document.forms[0].elements["name[]"][i].value);
             
               $("input[name='descuento[]']")[i].value = ''; 
             
             //  $("input[name='name[]']")[i].value =subt - desc;
              if ($('#discprcntg').val()!=="" && $('#discprcntg')!==0) {
                   var subt=parseFloat( $("input[name='name[]']")[i].value );
                   var porc=parseFloat($('#discprcntg').val());
                   var desc=(subt*porc)/100;
                   $("input[name='detaialprcnt[]']")[i].value = porc;  
                   $("input[name='descuento[]']")[i].value = round(desc,2); 
                   $("input[name='name[]']")[i].value = round(subt - desc,2);
              }
          };
        };
        $('#tlsubototal').val(lineSubtotal);
        $('#tlsubototal').html('$' + lineSubtotal);
        calculosTotales(lineSubtotal);

        var _numfactu =  $('#invoicen').val();   
        if(_numfactu!=="") {
          changeItems();
        };

     }

  }

function calculosTotales(subtotal){
  if ($('#discprcntg').val()!=="" && $('#discprcntg')!==0) {
     var porcentaje=parseFloat($('#discprcntg').val())
     var montoDes=0;
     var total=0;
     subtotal=parseFloat(subtotal);
     montoDes=(subtotal*porcentaje)/100
     total=subtotal-montoDes;
     $('#discamount').val('$' + montoDes);
     $('#discamount').html('$' + montoDes);
     $('#tltotal').html('$' + total);
    
     $('#frmsubtotal').val(subtotal);
     $('#frmtax').val('0');
     $('#frmshipping').val('0');
     $('#frmtotal').val(total);
  }else{   
     $('#tltotal').html('$' + subtotal);
     $('#frmtotal').val(subtotal);
     $('#frmsubtotal').val(subtotal);
     $('#frmtax').val('0');
     $('#frmshipping').val('0');
  }
}


 $('#dynamic_field').on('click', 'tbody tr', function(event) {
       $(this).addClass('highlight').siblings().removeClass('highlight');
  });
 

jQuery('.numbersOnly').keyup(function () { 
    this.value = this.value.replace(/[^0-9]/g,'');
});

jQuery('.numbersporcent').keyup(function () { 
   // this.value = this.value.replace(/[^0-9]*\.?[0-9]\/%/,'');
   this.value = this.value.replace(/[^0-9\.]\/%$/g,'');
});


  // $('#idpaciente').keyup(function(e){
  //       if (e.which == 13) {
  //           var id=$('#idpaciente').val()
  //           tecla=13
  //           findOutPacient(id); 
  //           $('#idassoc').focus();       
  //       }
  // })

// $('#idassoc').keyup(function(e){
//         if (e.which == 13) {
          
//             $('#medico').focus();
//         }
//   })

// $('#medico').keyup(function(e){
//         if (e.which == 13) {
//           $('#serv1').focus();
//            // $('#seguro').focus();
//         }
//   })


// $('#seguro').keyup(function(e){
//         if (e.which == 13) {
          
            
//         }
//   })

$('#dynamic_field').on('keypress', 'tbody tr td .enterpass', function(e) {
   if(e.which == 13){
      var row_index = $(this).parent().parent().index();   
      var xid = $(this).parent().parent().attr('id').toString(); 

   
      if ($(this).hasClass('cantidad')) {        
         $("#add").focus();
      }; 

      if ($(this).hasClass('service')) {          
          $("input[name='cantidad[]']")[row_index].select();
      }; 

   
    
   }

});


$('body').on('keydown', 'input, select, textarea', function(e) {
    var self = $(this)
      , form = self.parents('form:eq(0)')
      , focusable
      , next
      ;
    if (e.keyCode == 13) {
        focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
        //console.log(focusable.index(this)+1);

        if(self.hasClass('idpaciente')) {
            var id=$('#idpaciente').val();
            findOutPacient(id);
        };

        if(self.hasClass('precio')){
            next = focusable.eq(7);   //INDICE DEL BOTON ADD MORE
        }else{
            next = focusable.eq(focusable.index(this)+1);
        }

        if (next.length) {
            next.focus();
        } else {
           // form.submit();
        }
        return false;
    }
});


      function findOutPacient(id){
            var codMedOculto = $("#medicohd").val();
            var items="";
            var ajxmed = $.post( "../../clases/atencioncitar.php",{ q : id }, function(data) { 

            items = jQuery.parseJSON(data);         
            if (typeof items!= 'undefined' && items!==null) {      
              checkFacturaHoy(items[0].codclien)
              if(items.length>0){ 
                 loadMedicos(items[0].codmedico);
                 loadIdsAsociados(items);
               }
            }
            })
                .done(function() {
                
                })
                .fail(function() {
                  
                })
                .always(function() {
                
                }); 

                if(items.length>0){
                  alert();
                }
  }

function checkFacturaHoy(codclien){
//checkfacturahoy.php
 var ajxmed = $.post( "../../clases/checkfacturahoy.php",{codclien:codclien,idempresa:'2'}, function(data) { 
       var items="";
       var options="";
       items = jQuery.parseJSON(data);
       if (items!==null && items!==undefined) {
          if (items.length>0) {
             $('.alert').show();    
          }
         
       }
       })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          });
 }


function loadIdsAsociados(items){    
 var options=""; 
     let codmedico='000';
    for (var i = 0; i < items.length; i++) {  
        if (items[i].codmedico!=undefined && items[i].codmedico!='') {
            codmedico=items[i].codmedico;
        }else{
           codmedico='000';
        };
        //  
        options+="<option cdmedico="+codmedico+" value='"+items[i].codclien+"'>"+items[i].nombres+"</option>";  
    }      
  $("#idassoc").html(options);
  // var $label = $('#nameasoc').find('.text'),
  // $badge = $('#nameasoc').find('.badge'),
  // $count = Number($badge.text())
  // $badge.text(items.length);   
  // $("#nameasoc").addClass("active");                     

}


  function loadMedicos(codMedOculto){
         
    if (typeof codMedOculto === "undefined" || codMedOculto === null) { 
          var xmed= $("#citashd").val();
          if(xmed!==null  || typeof codMedOculto === "undefined" ){
             codMedOculto = xmed;
          }else{
             codMedOculto = ""; 
          } 
      }

        var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
           
        for (var i = 0; i < items.length; i++) {
            if (codMedOculto==items[i].codmedico){
                  options+="<option selected value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
              }else{
                  options+="<option value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";  
              }           
        }
          $("#medico").html(options); 
          })
          .done(function() {
          })
          .fail(function() {
          })
          .always(function() {
          });
        }


    function toDay(){
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth()+1; //January is 0!

      var yyyy = today.getFullYear();
      if(dd<10){
         dd='0'+dd;
      } 
      if(mm<10){
          mm='0'+mm;
      } 
        var today = mm+'/'+dd+'/'+yyyy;
     $('#fecha').val(today)
     $('#fechaf').html('Amount Due '+today+'  <span id="invoicen">   Invoice #</span>') 
    }


    toDay();

    $('#idassoc').change(function(event){
      event.preventDefault()
      var element = $(this).find('option:selected');
      var codmedico =  element.attr("cdmedico");
      var codclien =  element.val();
      var nombres = element.text();
      loadMedicos(codmedico);     
     
   })

    $('.optradio1').click(function(){
      $('#tdc1').prop('readonly', false);
      var ele=$(this);
      eletdc1=ele [0].id
     // console.log(eletdc1);      
    })

   $('.optradio2').click(function(){
     $('#tdc2').prop('readonly', false);
      var ele=$(this);
      eletdc2=ele [0].id
     // console.log(eletdc2);
      
    })

   $('.optradio3').click(function(){
     $('#tdc3').prop('readonly', false);
      var ele=$(this);
      eletdc3=ele [0].id
      //console.log(eletdc3);
      
    }) 


  $('#save').click(function(){

     // SOLO PARA CONSULTAS SUERO PARA PODER SEPARAR LAS CAJAS

     var xcod =$("select[name='serv[]']")[0].value
     var n = xcod.indexOf("ST");
     var  workstation='ATENCION02';
     if (n>-1) {
        workstation='LASERPC2';
     };    
     
     if ($('#dueamount').val().substr(1)>0 && $('#pagototal').val()=='') {
        return;
     };
     //FIN\\

    $('#save').prop('disabled', true);
    var cash  = $('#cash').val(); 

    var tdc1  =$('#tdc1').val(); 
    var radio1 =  eletdc1;

    var tdc2  =$('#tdc2').val();  
    var radio2 =  eletdc2; 

    var tdc3 = $('#tdc3').val(); 
    var radio3 =  eletdc3;   

    var ath = $('#ath').val();  
    var check = $('#check').val(); 

    var pagototal = $('#pagototal').val();  
    var saldo =  $('#saldo').val(); 

    var dueamount = $('#dueamount').val(); 
    var cambio = $('#cambio').val();

    var fecha = $('#fecha').val();

    var invoicen = $('#invoicen').val();
    var idusr = $('#idusr').val();

    var datasave ={
         cash   : cash
        ,tdc1   : tdc1
        ,radio1 : radio1
        ,tdc2   : tdc2
        ,radio2 : radio2
        ,tdc3   : tdc3
        ,radio3 : radio3
        ,ath    : ath
        ,check  : check
        ,pagototal : pagototal 
        ,saldo     : saldo
        ,dueamount : dueamount
        ,cambio    : cambio
        ,fecha : fecha
        ,invoicen : invoicen
        ,idusr : idusr
        ,id_centro : '2'
        ,tipo_doc : '01'
        ,workstation : workstation
    }

    url="../../clases/savepagoscma.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

      $('#btnpaymnt').prop('disabled',true);
      $('#submit').prop('disabled',false);
      $('#save').prop('disabled', false);
      $('#paymentsModal').modal('hide');
      //$('#add_name')[0].reset();  
      // $('#paymentsModal')[0].reset();      
      invoicePrinting(idusr);

      var urlevento = $('#urlevento').val();

      if (urlevento!=="") {
           window.location.assign("../../vistas/atencionconsultas/atencion.php") 
      }else{
          location.reload(true);
      };
      
      

  })


    //*****************************************************************
    //PAGOS

  $('#paymentsModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  // var modal = $(this)
  // modal.find('.modal-title').text('New message to ' + recipient)
  // modal.find('.modal-body input').val(recipient)



 
  var totalfactura = $('#tltotal').html();
  //totalfactura.match(/\d+/).toString()
  var dueamount =parseFloat(totalfactura.replace('$',''));
  $('#dueamount').val(totalfactura);
  $('#saldo').val(totalfactura); 



    $('.pay').click(function(){

      if ( !$(this).is('[readonly]') ) { 

          var ele=$(this);
          var id = ele.attr('id');
          var xsaldo    = $('#saldo').val()  == '' ? 0: parseFloat($('#saldo').val().replace('$','') );
          var pagototal = $('#pagototal').val()  == '' ? 0: parseFloat($('#pagototal').val().replace('$','') );

           $('#discprcntg').prop('readonly', false);

          if ( $('#'+id).val() =="") {
            if (xsaldo!==0) {
               $('#'+id).val(xsaldo);
            };
            
          };
          
          calPayments(); //dueamount,pagototal
      }
  })
  
    $('.pay').keyup(function(){
      calPayments(); //dueamount,pagototal
  })
  
 function calPayments(){
    var saldo,cambio;

      var cash  = $('#cash').val() == '' ? 0: parseFloat($('#cash').val().replace('$','') );   
      var tdc1  = $('#tdc1').val() == '' ? 0: parseFloat($('#tdc1').val().replace('$','') ); 
      var tdc2  = $('#tdc2').val() == '' ? 0: parseFloat($('#tdc2').val().replace('$','') );
      var tdc3  = $('#tdc3').val() == '' ? 0: parseFloat($('#tdc3').val().replace('$','') );
      var ath   = $('#ath').val()  == '' ? 0: parseFloat($('#ath').val().replace('$','') );
      var chek  = $('#check').val()  == '' ? 0: parseFloat($('#check').val().replace('$','') );
      
      var pagototal = cash+tdc1+tdc2+tdc3+ath+chek;
   
      $('#pagototal').val(pagototal);
      $('#saldo').val(parseFloat(totalfactura.replace('$','')) -pagototal);

    if(dueamount - pagototal > 0 ){
        saldo = dueamount - pagototal;
        cambio = 0;

        $('#cambio').val(cambio);
        $('#saldo').val(saldo);
 

      }else{
        cambio  = pagototal - dueamount;
        saldo = 0 ;
        $('#cambio').val(cambio);
        $('#saldo').val(saldo);

      }

 }
  $('#print2').hide();

});

 $('#nueva').click(function(){
   location.reload(true);
 })
    //*****************************************************************
$('.optradio1').prop('checked')

function round(value, exp) {
  if (typeof exp === 'undefined' || +exp === 0)
    return Math.round(value);

  value = +value;
  exp = +exp;

  if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
    return NaN;

  // Shift
  value = value.toString().split('e');
  value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));

  // Shift back
  value = value.toString().split('e');
  return +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));
}
//****************************************************************************************

  function getConsultasInvoices(){
     url="../../clases/consultascontroller.php";
     data = {
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: true
                      }).responseText;
    
     items= jQuery.parseJSON(resp);

    
    // console.log(items);
  
  }


  
   //Print
  $('#print2').click(function(){
     var idusr = $('#idusr').val();
     invoicePrinting(idusr);
    
  })
  //End Print


  function invoicePrinting(user){
    var _numfactu =  $('#invoicen').val();   
   
   var xserv = $("select[name='serv[]']")[0].value;
   service="CONSULTA";
   if (xserv.indexOf('ST')>-1) { 
       service= "SUEROTERAPIA";
   };

    var datasave ={
         numfactu   : _numfactu  
        ,times : 2
        ,service :service,
        user      
    }
    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    if (prninvoice!='1' && autoposprn!='1' ) {
          window.open('../../clases/printconsultaspdf.php?numfactu='+_numfactu+'&times='+'2'+'&service='+service+'&user='+user,'', '_blank');
    }else{      
          url="../../clases/printinvservice.php";
          var items;
          var resp =  $.ajax({
                                type: "POST",
                                url: url,
                                data: datasave,
                                async: false
                            }).responseText;
          
           items= resp ; //jQuery.parseJSON(resp)
    }      

  }


//------------------automatizar la facturacion
  var atn_cciente = $('#atn_cciente').val();
  if(atn_cciente!==""){
     findOutPacient(atn_cciente);
     $("#serv1").show().focus().click();
  }
   
 });  



 

  function getInvoices(  page,  search,  fecha){
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
     url="../../controllers/consultascontroller.php";
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
    

    resp=resp.substr(resp.indexOf('{"page"'))
    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    
    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 

    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
        var numfactu = items['data'][i]["numfactu"];
        var nombres  = items['data'][i]["nombres"];
        var fechafac = items['data'][i]["fechafac"];
        var Status   = items['data'][i]["Status"];
        var total    = items['data'][i]["total"];
        var descuento    = items['data'][i]["descuento"];
        var titleDev="Devolver";
        var idDev="";
        if (items['data'][i]["numnotcre"]!==null && items['data'][i]["numnotcre"]!=="") {
           titleDev="Devolver*";
           idDev = items['data'][i]["numnotcre"];
           numfactu = numfactu+'-'+idDev;
        }; 
        total = parseFloat(total)-parseFloat(descuento)
        total=parseFloat(total).toFixed(2)
         $('#tblLista').append('<tr id='+numfactu+'>'+
          ' <td  align="center">'+numfactu+'</td>'+
          ' <td>'+nombres+'</td>'+
          ' <td align="center">'+fechafac+'</td>'+
          ' <td align="center">'+Status+'</td>'+
          ' <td align="right">'+total+'</td>'+
          ' <td align="center"> <button type="button" name="consultar" id=""  class="btn btn-primary consultar">Consultar</td>'+
          ' <td align="center"> <button type="button" name="editar"    id=""  class="btn btn-success btn_editar">Editar</button> </td>'+
          ' <td align="center"> <button type="button" name="anular"    id=""  class="btn btn-danger btn_anular">Anular</button> </td>'+
          ' <td align="center"> <button type="button" name="devolver"  id=""  class="btn btn-warning devolver">'+titleDev+'</button> </td>'+
          ' </tr>');
    };
    // console.log(resp);
     //console.log(items);
  
  }
  //----------------------------
  $('#lockdescuento').click(function(event){
      event.preventDefault()
      $('#pass').val('');
      $('#user').val('');     
   });
  //============================
  $('#login').click(function(){
     var s_pass  = $('#pass').val();
     var s_user  = $('#user').val();
     $('#authorization').modal('toggle');

     url="../../clases/getauthorization.php";
     data = {
      s_user  : s_user,
      s_pass  : s_pass
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
  
    items= jQuery.parseJSON(resp);
  
    if (items!==null) {
        $('#discprcntg').prop('readonly', false);
        $('#discamount').prop('readonly', false);
    }else{
        $('#discprcntg').prop('readonly', true);
        $('#discamount').prop('readonly', true);
    };

  })
  //============================
  // MODIFICADO PARA LA PROMOCION
  // $('#discprcntg').prop('readonly', true);
  // $('#discamount').prop('readonly', true);
    $('#lockdescuento').hide(); 
  //
  //============================
  //============================
  //----------------------------

    $('#medico').change(function(){
      document.getElementById('medicohd').value=document.getElementById('medico').value;
   
    })