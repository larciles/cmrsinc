var previous;
var DOMelemento

 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;      
    window.taxali=0;
    window.applytax = true;
  
  $('#btnpaymnt').hide();

  $('#xtitulo').html('<b>Notas de Entrega</b>');
      var i=1;  
      $('#add').click(function(){  
           i++;  
           var idpro="serv"+i;
           var idtpr="tpre"+i;
           var idseg="tseg"+i;
           var iddes="descuento"+i;
           var tax ="tax"+i;
           var dosis="dosis"+i;
           var sugerido="sugerido"+i;
           var apptax = "apptax"+i;
           var aplicadcto = "aplicadcto"+i;
           var aplicaComMed = "aplicaComMed"+i;
           var aplicaComTec = "aplicaComTec"+i;
           var costo = "costo"+i;

           $('#dynamic_field').append('<tr id="'+i+'">'+
               ' <td><select id="'+idpro+'" name="serv[]"        class="form-control service enterpass enterkey" > <option value="" selected ></option></select> <input type="hidden"  id="coditems'+i+'"   name="coditems[]"  value="" class="coditems" /><input type="hidden"  id='+apptax+' name="apptax[]" value="" class="coditems" /><input type="hidden"  id='+aplicadcto+' name="aplicadcto[]" value="" class="aplicadcto" /><input type="hidden"  id='+aplicaComMed+' name="aplicaComMed[]" value="" class="aplicaComMed" /><input type="hidden"  id='+aplicaComTec+' name="aplicaComTec[]" value="" class="aplicaComTec" /><input type="hidden"  id='+costo+' name="costo[]" value="" class="costo" /></td>'+
               // ' <td><select id="'+idtpr+'" name="listaprecio[]" class="form-control enterkey">          <option value="" selected ></option></select> <input type="hidden"  id="codprecio1'+i+'" name="codprecio[]" value="" class="codprecio" /></td>'+
               // ' <td><input type="text" id ='+dosis+' name="dosis[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="Dosis" class="form-control dosis numbersOnly enterpass enterkey" /></td>'+
               // ' <td><input type="text" id ='+sugerido+' name="sugerido[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="Cantidad sugeridad" class="form-control sugerido numbersOnly enterpass enterkey" /></td>'+
               // ' <td><select id="'+idseg+'" name="seguro[]"      class="form-control">          <option value="" selected ></option></select> <input type="hidden"  id="insurance1'+i+'" name="insurance[]" value="" class="insurance" /></td>'+            
               ' <td><input type="text" name="cantidad[]" style="text-align:right;" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" /></td>'+
               // ' <td><input type="text" name="precio[]" style="text-align:right;" readonly="readonly" placeholder="precio" class="form-control " /></td>'+ 
               //' <td><input type="checkbox" checked data-toggle="toggle" data-size="small" data-on="%" data-off="$" class="percentage newBSswitch" title="edit"  id="percentage" name="percentage[]"></td>  '+
               // ' <td><input type="text" name="descuento[]" style="text-align:right;"  readonly="readonly" id="'+iddes+'" placeholder="Descuento" class="form-control " /> <input type="hidden"  id="detaialprcnt"'+iddes+'" name="detaialprcnt[]" value="" class="detaialprcnt" /></td>'+ 
               // ' <td><input type="text" name="tax[]" style="text-align:right;" readonly="readonly" id="'+tax+'"  placeholder="Impuesto" class="form-control " /></td>'+
               // ' <td><input type="text" name="name[]" style="text-align:right;" readonly="readonly" placeholder="Subtotal" class="form-control name_list subtotal" /></td>'+
               ' <td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  
          
          var longarry=$("select[name='serv[]']").length;
          var notin = new Array(longarry);

          for (var ic = 0; ic < longarry; ic++) {
            if ($("select[name='serv[]']")[ic].value!=='') {
                notin[ic]=$("select[name='serv[]']")[ic].value;
            };
            
          };

           setConsultas(idpro,{prod_serv:'P', orderby:'desitems',notin},'POST');
           setTipoDePrecios(idtpr);
           //setSeguro(idseg);

           $('.newBSswitch').bootstrapSwitch('state', true); 

          $('#'+idpro).focus();
      });  

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
                                   setTotalLines(taxali);          
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
                let tax        = $("input[name='tax[]']")[0].value; 
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
                $("input[name='tax[]']")[0].value=''; 
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
                    setTotalLines(taxali);        
                };

                if (facturaNum!=="") {  
                      
                       let del=1;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);

                       $.ajax({  
                          url:"../../clases/invoiceprodsave.php",  
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
                $("input[name='tax[]']")[0].value=''; 
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
                    setTotalLines(taxali);        
                };

                if (facturaNum!=="") {  
                      
                       let del=1;
                       $("#delallrecords").attr("value", del);
                       $('#delallrecords').val(del);
                       $('#delallrecords').html(del);

                       $.ajax({  
                          url:"../../clases/invoiceprodsave.php",  
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

      });


function ajustaInventario(producto,facturaNum,cantidad){
    data = {
      action    : 'delete',
      coditems  : producto,
      numfactu  : facturaNum,
      cantidad  : cantidad
    } 

    $.ajax({                
          url:"../../clases/updateinvoice.php",  
          method:"POST",  
          data: data ,  
          success:function(data)  
          {               
             //items=data //jQuery.parseJSON(data); // el dato no viene en formato json               
          }  
     }); 
}


      $(document).on('click', '.btn_remove', function(){  

           var row_index = $(this).parent().parent().index();
           var button_id = $(this).attr("id");   
           
           var element = $(this);
           var coditems = $('#serv'+element.parent().parent().attr('id')).val();
           var cantidad  = $("input[name='cantidad[]']")[row_index].value

           $('#'+button_id+'').remove();  

           var facturaNum = $('#invoicen').val().trim();


           if (facturaNum!=="") {

              //updateinvoice.php
                    data = {
                      action    : 'delete',
                      coditems  : coditems,
                      numfactu  : facturaNum,
                      cantidad  : cantidad
                    } 
              $.ajax({                
                    url:"../../clases/updateinvoice.php",  
                    method:"POST",  
                    data: data ,  
                    success:function(data)  
                    {               
                       //items=data //jQuery.parseJSON(data); // el dato no viene en formato json               
                    }  
               }); 

           };


           setTotalLines(taxali);
             if (facturaNum!=="") {
//                $('#submit').click();   
             }
           
      });  

      $('#submit').click(function(){
          if ($('#idassoc').val()!=="" && $('#serv1').val()!=="" && $('#invoicen').val()=="") {          
               $.ajax({  
                    url:"../../clases/savenotasentrega.php",  
                    method:"POST",  
                    data:$('#add_name').serialize(),  
                    success:function(data)  
                    {  
                          //alert(data); 
                          items=data //jQuery.parseJSON(data); // el dato no viene en formato json
                          items.trim();
                          $("#invoicen").attr("value", items);
                          $('#invoicen').val(items);
                          $('#invoicen').html('Invoice # '+items);
                          $('#numfactu').val(items);
                          
                          $('#btnpaymnt').show();
                          $('#btnpaymnt').prop('disabled', false);
                          $('#submit').prop('disabled', true);

                          invoicePrinting();  
                          location.reload(true);
                          //$('#invoicen').val('');


                       //  $('#add_name')[0].reset(); 
                      //  $("#paymentsModal").modal(); 
                    }  
               }); 
           }//else{
          /*  if ($('#invoicen').val()!=="") {
              alert('Factura Ya existe')
            };*/
         //  }; 
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

  function setConsultas(id,data,type){
           
    var serv= getData("../../clases/getprodtoinvoice.php",data,type);
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
     }
     $("#"+id).html(options);
  }

   setConsultas('serv1',{prod_serv:'P', orderby:'desitems'},'POST');
   setTipoDePrecios('tpre1');
   setSeguro('seguro');

    $('#dynamic_field').on('change', 'tbody tr td .sugerido', function(event) {
        var element  = $(this); 
        var row_index = $(this).parent().parent().index(); 
        var sugerido  = $("input[name='sugerido[]']")[row_index].value;
        if (!$.isNumeric(sugerido)) {
           $("input[name='sugerido[]']")[row_index].value="1";
        };
    });

   $('#dynamic_field').on('change', 'tbody tr td .dosis', function(event) {

    var response ="";
    var element  = $(this); 
    var row_index = $(this).parent().parent().index();   
    var xid = $(this).parent().parent().attr('id').toString();

    var coditems  = $('#serv'+xid).val();    
    var dosis  = $("input[name='dosis[]']")[row_index].value;

    if ($.isNumeric(dosis)) {


          response = getData("../../clases/calculadiasdetratamiento.php",{ coditems : coditems },'POST');
          if (response !== 'null') {

              items = jQuery.parseJSON(response);
              var capsxunit =  items[0].CapsulasXUni;
              var diasTratamiento=30;
              var sugerencia = Math.ceil( (dosis*diasTratamiento)/capsxunit );
              if (sugerencia>0) {
                 $("input[name='sugerido[]']")[row_index].value = sugerencia;
                 $("input[name='cantidad[]']")[row_index].value = sugerencia;
                 $("input[name='dosis[]']")[row_index].value = dosis;   
                 setGeneralValues(this,row_index,xid);     
              };    
           }; 
   }else{
      $("input[name='dosis[]']")[row_index].value="";
      $("input[name='dosis[]']")[row_index].focus();
   };
  })
   

$('#dynamic_field').on('keypress', 'tbody tr td .enterpass', function(e) {
   if(e.which == 13){
      var row_index = $(this).parent().parent().index();   
      var xid = $(this).parent().parent().attr('id').toString(); 

      if ($(this).hasClass('dosis')) {        
         $("input[name='cantidad[]']")[row_index].focus();         
         $("input[name='cantidad[]']")[row_index].select();
      };   

      if ($(this).hasClass('cantidad')) {        
         $("#add").focus();
      }; 

      if ($(this).hasClass('service')) {          
          $("input[name='dosis[]']")[row_index].focus();
          $("input[name='dosis[]']")[row_index].select();
      }; 
    
   }

});


  
  $("#taxapply").change(function(){     
        applytax=$(this).prop('checked')
        setTotalLines(taxali);
  })


   $('#dynamic_field').on('focus click', 'tbody tr td select', function(event) {

         DOMelemento=$(this);
      
         previous = this.value; // PRODCUTO PREVIO EN CASO DE SELECCIONAR OTRO Y LA FACTURA YA EXISTE PARA ACTUALIZAR EL INVENTARIO 
    }).change(function() {   

      if(DOMelemento.parent().parent().hasClass("highlight") || $(".service").is(":focus") ){

         console.log(previous);

         var iNum = $('#invoicen').val().trim();

         var row_index = DOMelemento.parent().parent().index();
         var col_index = DOMelemento.parent().index();        
         var xid = DOMelemento.parent().parent().attr('id').toString();
       
         var cantidad =  $('.cantidad').val();
         cantidad  = $("input[name='cantidad[]']")[row_index].value;

         var coditems  = $('#serv'+xid).val();
         coditems  =$("select[name='serv[]']")[row_index].value;
         $("input[name='coditems[]']")[row_index].value = coditems;

         var codtipre  = $('#tpre'+xid).val();         
         if (codtipre==undefined) {
          codtipre="00"
         }
         // codtipre  =  $("select[name='listaprecio[]']")[row_index].value;
         // $("input[name='codprecio[]']")[row_index].value = codtipre;   

    
         // var descuento = $('.descuento').val();  
         // descuento = $("input[name='descuento[]']")[row_index].value;  
     
      }

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {

         // EN CASO QUE LA FACTURA YA EXISTA Y EXITA UNA MODIFICACION Y ELIMINA EL REGISTO EN EL DETALLE DE LA FACTUA Y ACTUALIZA EL INVENTARIO
         if(iNum!=="" && iNum!==undefined){
            if (previous!=="") {
                updateInvoiceChange(iNum,previous,cantidad);
            };          
         }
         //FIN         

             
        // subTotal(coditems,codtipre,descuento,cantidad,row_index);
      };

     // console.log(coditems,codtipre,cantidad,descuento);
         
     });
     //-----------------
     function updateInvoiceChange(facturaNum,coditems,cantidad){
        if (facturaNum!=="") {

            data = {
                      action    : 'delete',
                      coditems  : coditems,
                      numfactu  : facturaNum,
                      cantidad  : cantidad
            } 
            $.ajax({                
                    url:"../../clases/updateinvoice.php",  
                    method:"POST",  
                    data: data ,  
                    success:function(data)  
                    {               
                       //items=data //jQuery.parseJSON(data); // el dato no viene en formato json               
                    }  
            }); 

        };
     }
     //-----------------




  $('#dynamic_field').on('change', 'tbody tr td .cantidad', function(event) {

    var index = $(this).parent().parent().index();
    var id = $(this).parent().parent().attr('id');
    var cantidad  = $("input[name='cantidad[]']")[index].value;

    if ($.isNumeric(cantidad)) {
       setGeneralValues(this,index,id);  
    }else{
       $("input[name='cantidad[]']")[index].value="1";
    };
    

  })


  $('#dynamic_field').on('focus', 'tbody tr td .dosis', function(event) {
    $(this).select();
  })

  $('#dynamic_field').on('focus', 'tbody tr td .cantidad', function(event) {
    $(this).select();
  })


function chkInput(){
    var v = $('input').val().charAt($('input').val().length-1);
    return testPattern.test(v);
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
    
    if ($.isNumeric($('#discprcntg').val())) {
       $('#discamount').prop('readonly', false);
       $("#discamount").removeClass("readonly");
       if ($('#discprcntg').val()!=="" ) {
          $("#discamount").addClass("readonly");     
          $('#discamount').prop('readonly', true);
       }
       setTotalLines(taxali);
  }else{
    $('#discprcntg').val('')
     $('#discprcntg').focus();
  };

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
          setTotalLines(taxali);

     }else{
          $('#discamount').val('0');
          $('#discamount').focus();
     };
  })

  $('#shipping').change(function(){
     var subtotal = $('#tlsubototal').val();
     if ($.isNumeric($('#shipping').val())) {
        if(subtotal!==0 || subtotal!=="" || subtotal!=="null" || subtotal!== undefined){
            calculosTotales(subtotal,taxali);
        }
   }else{
       $('#shipping').val('0');
       $('#shipping').focus();
   };
      //
  })

  function subTotal(coditems,codtipre,descuento,cantidad,id,precio){

    //  paramprecios manuales
    if (precio==undefined) { 
        precio=0;
    };
    //\\

     url="../../clases/calcularsubtotal.php";
     data = {
        coditems : coditems ,
        codtipre : codtipre,
        descuento : descuento,
        cantidad : cantidad,
        precio : precio
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

     var precio = 0;
     var subtotal = 0;
     var impuesto = 0;
     var tax = 0;
     var aplicatax = 0;

     var aplicadcto = 0;
     var aplicaComMed = 0;
     var aplicaComTec = 0;
     var costo = 0;

    if (jQuery.type(items)!=='null') {
        precio=items['precunit'];
        subtotal=items['subtotal'];
        impuesto=items['impuesto'];      //impuesto ya calculao   
        tax=items['tax'];                //  porcentaje de descuento aplicado
        aplicatax = items['taxapply'];

        aplicadcto = items['aplicadcto'];
        aplicaComMed = items['aplicaComMed'];
        aplicaComTec = items['aplicaComTec'];
        costo = items['costo'];

        $("#taxilicuota").val(tax);
        if (tax!=='null' && tax!==undefined && tax!=="") {
          taxali=tax;
        };
          
     }

     $("input[name='aplicadcto[]']")[id].value  = aplicadcto;
     $("input[name='aplicaComMed[]']")[id].value = aplicaComMed;
     $("input[name='aplicaComTec[]']")[id].value = aplicaComTec;
     $("input[name='costo[]']")[id].value = costo;
           
     $("input[name='precio[]']")[id].value = round(precio,2); //document.forms[0].elements["precio[]"][id-1].value=precio;
     $("input[name='apptax[]']")[id].value= aplicatax;
     $("input[name='descuento[]']")[id].value = 0;
      //tax
     if (applytax) {
         $("input[name='tax[]']")[id].value = round(impuesto,2);
     }else{
         $("input[name='tax[]']")[id].value = 0;
     }; 
      
      if (applytax) {
        $("input[name='name[]']")[id].value =subtotal+impuesto; //document.forms[0].elements["name[]"][id-1].value=subtotal;  
      }else{
        $("input[name='name[]']")[id].value =subtotal
      }

      setTotalLines(tax);


      // EN CASO QUE LA FACTURA YA EXISTA Y EXITA UNA MODIFICACION Y ELIMINA EL REGISTO EN EL DETALLE DE LA FACTUA Y ACTUALIZA EL INVENTARIO
      var iNum = $('#invoicen').val().trim();
      if(iNum!=="" && iNum!==undefined){
            var sugerido = $("input[name='sugerido[]']")[id].value;                 
            var dosis = $("input[name='dosis[]']")[id].value;   

            if (previous!=="") {

                datos = {
                  numfactu : iNum
                 ,coditems : coditems
                 ,codseguro :'0'
                 ,codtipre :codtipre
                 ,aplicaiva : aplicatax
                 ,aplicadcto : aplicadcto
                 ,aplicacommed : aplicaComMed
                 ,aplicacomtec : aplicaComTec
                 ,costo : costo
                 ,dosis : dosis
                 ,cant_sugerida :sugerido
                 ,cantidad : cantidad
                 ,precunit : precio
                 ,descuento : descuento
                 ,monto_imp : impuesto
                 ,codmedico : $('#medico').val()
                }
                
            };          
      }
      //FIN
 

  }


  function setTotalLines(tax){

    var lineSubtotal=0;
    var arraySubTotl =  $("input[name='name[]']"); //arraySubTotl = document.forms[0].elements["name[]"] ; 

    if(jQuery.type( arraySubTotl.length )=='undefined'){
        $('#tlsubototal').val(document.forms[0].elements["name[]"].value);
    }else{
        for (var i = 0; i < arraySubTotl.length; i++) {
          if ( $("input[name='name[]']")[i].value!=="") {
              var cantidad=0;
              var precio=0;
              var subtot=0;
              var impuesto=0;

              cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
              precio = parseFloat( $("input[name='precio[]']")[i].value ) ;

              subtotNoTax = (cantidad * precio);
              if (applytax) {   
                   impuesto = $("input[name='tax[]']")[i].value !=="" ? parseFloat( $("input[name='tax[]']")[i].value ) : 0;
                   if (impuesto==0 || impuesto =='0') {

                      if ($("input[name='apptax[]']")[i].value=='1') {
                         impuesto = (subtotNoTax*tax)/100
                      }; 

                   };

                   subtot = (cantidad * precio)+impuesto;
                   $("input[name='tax[]']")[i].value =round(impuesto,2);
               }else{
                   impuesto = 0;
                   subtot = (cantidad * precio);
                   $("input[name='tax[]']")[i].value =impuesto;
               }
              
               $("input[name='name[]']")[i].value =round(subtot,2);  //SUBTOTAL

               if($("#discprcntg").hasClass("readonly")){

               }else{
                  $('#discamount').val('$0');
               }

              lineSubtotal=lineSubtotal + subtotNoTax ;// parseFloat( $("input[name='name[]']")[i].value ) ; //parseFloat(document.forms[0].elements["name[]"][i].value);
             
               //$("input[name='descuento[]']")[i].value = '';              
               //$("input[name='name[]']")[i].value =subt - desc;
              if ($('#discprcntg').val()!=="" && $('#discprcntg')!==0) {
                   var taxLine = 0;
                   var subt= subtotNoTax ;//parseFloat( $("input[name='name[]']")[i].value );
                   var porc=parseFloat($('#discprcntg').val());
                   var desc=(subt*porc)/100;

                   if (applytax) {
                      taxLine =  ((subt-desc)*tax)/100;
                   };                   

                   $("input[name='detaialprcnt[]']")[i].value =desc;
                   $("input[name='descuento[]']")[i].value = round(desc,2);

                   $("input[name='tax[]']")[i].value = round(taxLine,2);  

                   $("input[name='name[]']")[i].value = round((subt - desc)+taxLine ,2);
              }
          };
        };

        lineSubtotal = round(lineSubtotal,2);
        
        $('#tlsubototal').val(lineSubtotal);
        $('#tlsubototal').html('$' + lineSubtotal);
        calculosTotales(lineSubtotal,tax);
     }

   //Si ya Existe la factura entra aqu
    if ( $('#numfactu').val().trim()!=="" ) {
      //  $('#submit').click();   
    }
  }

function calculosTotales(subtotal,tax){
  var montoDes=0;
  var total=0;
  var impuesto=0;
  var shipping=parseFloat($('#shipping').val())

  if ($('#discprcntg').val()!=="" && $('#discprcntg')!==0) {
     var porcentaje=parseFloat($('#discprcntg').val())

     subtotal = parseFloat(subtotal);
     montoDes = (subtotal*porcentaje)/100;
     total = subtotal-montoDes;

     if (applytax) {
        impuesto = (total*tax)/100; 
     }; 
     

     total = total+impuesto+shipping;
     
     subtotal = round(subtotal,2);
     montoDes = round(montoDes,2);
     impuesto = round(impuesto,2);
     total    = round(total,2);     

     $('#discamount').val(montoDes);
     $('#discamount').html(montoDes);
     $('#tltotal').html(total);

     $('#tlimpuesto').html(impuesto);    

     $('#frmsubtotal').val(subtotal);
     $('#frmtax').val(impuesto);
     $('#frmshipping').val(shipping);
     $('#frmtotal').val(total);
  }else{   
     if (applytax) {
         impuesto = (subtotal*tax)/100;
      }
     impuesto = round(impuesto,2);
     subtotal = parseFloat(subtotal);
     total    = round((subtotal+impuesto+shipping),2);     

     $('#tlimpuesto').html(impuesto);
     //$('#tlimpuesto').value('$' + impuesto);
     $('#tltotal').html(total );
     $('#frmtotal').val(total);
     $('#frmsubtotal').val(subtotal);
     $('#frmtax').val(impuesto);
     $('#shipping').val(shipping);
     $('#frmshipping').val(shipping);
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


$('#idassoc').change(function(event){
      event.preventDefault()
      var element = $(this).find('option:selected');
      var codmedico =  element.attr("cdmedico");
      var codclien =  element.val();
      var nombres = element.text();
      loadMedicos(codmedico);   

      if ( $('#numfactu').val().trim()!=="" ) {
            $('#submit').click();   
       }  
     
  })

$('body').on('keydown', 'input, select, text', function(e) {
    var self = $(this)
      , form = self.parents('form:eq(0)')
      , focusable
      , next
      ;
    if (e.keyCode == 13) {
        focusable = form.find('input,a,select,button,text').filter('.enterkey');
        console.log(focusable.index(this)+1);

        if(self.hasClass('idpaciente')) {
            var id=$('#idpaciente').val();
            findOutPacient(id);
        };

        if(self.hasClass('cantidad')){
            next = focusable.eq(3);   //INDICE DEL BOTON ADD MORE
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
            var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 

            items = jQuery.parseJSON(data);         
            if (typeof items!= 'undefined' && items!==null) {      
         
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
     $('#fechaf').html('Nota de Entrega  <span id="invoicen">    #</span>') 
    }


    toDay();



    $('.optradio1').click(function(){
      $('#tdc1').prop('readonly', false);
      var ele=$(this);
      eletdc1=ele [0].id
      console.log(eletdc1);      
    })

   $('.optradio2').click(function(){
     $('#tdc2').prop('readonly', false);
      var ele=$(this);
      eletdc2=ele [0].id
      console.log(eletdc2);
      
    })

   $('.optradio3').click(function(){
     $('#tdc3').prop('readonly', false);
      var ele=$(this);
      eletdc3=ele [0].id
      console.log(eletdc3);
      
    }) 



  $('#save').click(function(){

     if ($('#dueamount').val().substr(1)>0 && $('#pagototal').val()=='') {
        return;
     };
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

    var invoicen = $('#invoicen').val().trim();
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
        ,id_centro : '1'
        ,tipo_doc : '01'
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
      //$('#paymentsModal').modal('hide');
      //$('#add_name')[0].reset();  
      // $('#paymentsModal')[0].reset();    
      invoicePrinting();  
      location.reload(true);

      

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
  var dueamount = totalfactura; // parseFloat(totalfactura.match(/\d+/).toString());
  // dueamount = dueamount.toString();
  $('#dueamount').val(totalfactura);
  $('#saldo').val(totalfactura); 



    $('.pay').click(function(){

      if ( !$(this).is('[readonly]') ) { 

          var ele=$(this);
          var id = ele.attr('id');
          var xsaldo    =     $('#saldo').val()  == '' ? 0: $('#saldo').val(); //parseFloat($('#saldo').val().match(/\d+/).toString() );
          var pagototal = $('#pagototal').val()  == '' ? 0: $('#pagototal').val(); // parseFloat($('#pagototal').val().match(/\d+/).toString() );

           $('#discprcntg').prop('readonly', false);

          if ( $('#'+id).val() =="") {
            if (xsaldo!==0) {
               $('#'+id).val(xsaldo);
            };
            
          };
          
          calPayments(); //dueamount,pagototal
      }
  })
  //var dueamount =parseFloat(totalfactura.match(/\d+/).toString());
  $('.pay').keyup(function(){
      calPayments(); //dueamount,pagototal
  })
  
 function calPayments(){
    var saldo,cambio;

      var cash  = $('#cash').val() == '' ? 0: $('#cash').val(); // parseFloat($('#cash').val().match(/\d+/).toString() );   
      var tdc1  = $('#tdc1').val() == '' ? 0: $('#tdc1').val(); //  parseFloat($('#tdc1').val().match(/\d+/).toString() ); 
      var tdc2  = $('#tdc2').val() == '' ? 0: $('#tdc2').val(); // parseFloat($('#tdc2').val().match(/\d+/).toString() );
      var tdc3  = $('#tdc3').val() == '' ? 0: $('#tdc3').val(); // parseFloat($('#tdc3').val().match(/\d+/).toString() );
      var ath   = $('#ath').val()  == '' ? 0: $('#ath').val();  //  parseFloat($('#ath').val().match(/\d+/).toString() );
      var chek  = $('#check').val()  == '' ? 0: $('#check').val(); //parseFloat($('#check').val().match(/\d+/).toString() );
      
      var pagototal = parseFloat(cash)+parseFloat(tdc1)+parseFloat(tdc2)+parseFloat(tdc3)+parseFloat(ath)+parseFloat(chek);
      pagototal = round(pagototal,2);
   
      $('#pagototal').val(pagototal);
      var xsaldo = round(totalfactura -pagototal,2)
      $('#saldo').val(xsaldo); //parseFloat(totalfactura.match(/\d+/).toString()) -pagototal

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
     console.log(items);
  
  }

  //****************************************************************************************
  $('#medico').change(function(){
  var codmedico = $('#medico').val();
  var numfactu=  $('#factura').val(); 

       var datasave ={
         numfactu  : numfactu
        ,codmedico : codmedico     
    }

    url="../../clases/updatemedicoinvprod.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

  })
  //*********************************************************************************************
    function invoicePrinting(){
    var _numfactu =  $('#invoicen').val().trim();   
  
    var datasave = {
         numfactu   : _numfactu  
        ,times : 1
    }

    url="../../clases/printnotaentrega.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

  }
 });