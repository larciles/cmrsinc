 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;      
    window.taxali=0;
    window.applytax = true;

    var numeroFactura =$('#numfactu').val(); 
    numeroFactura=numeroFactura.trim();
    $('#invoicen').val(numeroFactura);
    $('#invoicen').val(numeroFactura);
    $('#invoicen').html('Invoice # '+numeroFactura);
    $('#devnumber').val(numeroFactura);
    

    displayTotales(numeroFactura);

  $('#xtitulo').html('<b>Devolucion Factura de Productos</b>');

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
               ' <td><select id="'+idtpr+'" name="listaprecio[]" class="form-control enterkey">          <option value="" selected ></option></select> <input type="hidden"  id="codprecio1'+i+'" name="codprecio[]" value="" class="codprecio" /></td>'+
               ' <td><input type="text" id ='+dosis+' name="dosis[]" value="1" placeholder="Dosis" class="form-control dosis numbersOnly enterpass enterkey" /></td>'+
               ' <td><input type="text" id ='+sugerido+' name="sugerido[]" value="1" placeholder="Cantidad sugeridad" class="form-control sugerido numbersOnly enterpass enterkey" /></td>'+
               // ' <td><select id="'+idseg+'" name="seguro[]"      class="form-control">          <option value="" selected ></option></select> <input type="hidden"  id="insurance1'+i+'" name="insurance[]" value="" class="insurance" /></td>'+            
               ' <td><input type="text" name="cantidad[]" value="1" placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" /></td>'+
               ' <td><input type="text" name="precio[]" readonly="readonly" placeholder="precio" class="form-control" style="text-align:right;"/></td>'+ 
               //' <td><input type="checkbox" checked data-toggle="toggle" data-size="small" data-on="%" data-off="$" class="percentage newBSswitch" title="edit"  id="percentage" name="percentage[]"></td>  '+
               ' <td><input type="text" name="descuento[]"  readonly="readonly" id="'+iddes+'" placeholder="Descuento" class="form-control" style="text-align:right;" /> <input type="hidden"  id="detaialprcnt"'+iddes+'" name="detaialprcnt[]" value="" class="detaialprcnt" /></td>'+ 
               ' <td><input type="text" name="tax[]" readonly="readonly" id="'+tax+'"  placeholder="Impuesto" class="form-control" style="text-align:right;" /></td>'+
               ' <td><input type="text" name="name[]" readonly="readonly" placeholder="Subtotal" class="form-control name_list subtotal" style="text-align:right;" /></td>'+
               ' <td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  

           setConsultas(idpro,{prod_serv:'P', orderby:'desitems'},'POST');
           setTipoDePrecios(idtpr);
           //setSeguro(idseg);

           $('.newBSswitch').bootstrapSwitch('state', true); 

          $('#'+idpro).focus();
      });  
      $(document).on('click', '.btn_remove', function(){  

           var row_index = $(this).parent().parent().index();
           var button_id = $(this).attr("id");   
           
           var element  = $(this);
           var coditems = $("select[name='serv[]']")[row_index].value;
           var cantidad = $("input[name='cantidad[]']")[row_index].value

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

           var taxali =$('#taxilicuota').val();
           setTotalLines(taxali);
             if (facturaNum!=="") {
//                $('#submit').click();   
             }
           
      });  

      $('#submit').click(function(){
          //if ($('#idassoc').val()!=="" && $('#serv1').val()!=="" && $('#invoicen').val()=="") {        
               $.ajax({  
                    url:"../../clases/returnprodsave.php",  
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

                          $('#devolucion').val(items);
                          $('#devolucion').text(items);
                          $('#devolucion').html('Devolucion # '+items);
                          

                          $('#btnpaymnt').prop('disabled', false);
                          // $('#submit').prop('disabled', true);

                       //  $('#add_name')[0].reset();  
                    }  
               }); 
           //}else{
          /*  if ($('#invoicen').val()!=="") {
              alert('Factura Ya existe')
            };
           }; */
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
    var dosis  = $("input[name='dosis[]']")[row_index].value
    
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
      $("input[name='dosis[]']")[row_index].value="1";
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
        var taxali =$('#taxilicuota').val();
        setTotalLines(taxali);
  })


   $('#dynamic_field').on('change', 'tbody tr td select', function(event) {
      var element=$(this);
      
      

      if($(this).parent().parent().hasClass("highlight") || $(".service").is(":focus") ){

         var row_index = $(this).parent().parent().index();
         var col_index = $(this).parent().index();        
        
         var cantidad  = $("input[name='cantidad[]']")[row_index].value

         var coditems  = $("select[name='serv[]']")[row_index].value;
         $("input[name='coditems[]']")[row_index].value = coditems;
      
         var codtipre  =  $("select[name='listaprecio[]']")[row_index].value
         $("input[name='codprecio[]']")[row_index].value = codtipre;   

         var descuento = $("input[name='descuento[]']")[row_index].value  
     
      }

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,row_index);
      }else{
        setTotalLines();
      };

      console.log(coditems,codtipre,cantidad,descuento);
         
     });
    // $(".product option[value=somevalue]").prop("selected", "selected")

  $('#dynamic_field').on('change', 'tbody tr td .cantidad', function(event) {
    
    // var row_index = $(this).parent().parent().index();
    // var col_index = $(this).parent().index();        
   
    var index = $(this).parent().parent().index();
    var id = $(this).parent().parent().attr('id');   
   
    var cantidad  = $("input[name='cantidad[]']")[index].value;

    if ($.isNumeric(cantidad)) {   
       setGeneralValues(this,index,id);
    }else{
      $("input[name='cantidad[]']")[index].value="1";
    };
    

  });



function chkInput(){
    var v = $('input').val().charAt($('input').val().length-1);
    return testPattern.test(v);
}

  function setGeneralValues(element,index,xid){
     //serv[]
      var cantidad  = $('.cantidad')[index].value;    ;
      var coditems  = $("select[name='serv[]']")[index].value; //$('#serv'+xid).val();
      var codtipre  = $("select[name='listaprecio[]']")[index].value; //$('#tpre'+xid).val();        
      var descuento = $("input[name='descuento[]']")[index].value; //$('#descuento'+xid).val();    //por linea 
    
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
        var taxali =$('#taxilicuota').val();
        setTotalLines(taxali);
    }else{
       $('#discprcntg').val('0')
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
          var taxali =$('#taxilicuota').val();
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
            var taxali =$('#taxilicuota').val();
            calculosTotales(subtotal,taxali);

             //Si ya Existe la factura entra aqu
            if ( $('#numfactu').val().trim()!=="" ) {
                 $('#submit').click();   
            }   
          }
      }else{
         $('#shipping').val('0');
         $('#shipping').focus();
      } 
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
  }


  function setTotalLines(tax){

    var lineSubtotal=0;
    var arraySubTotl =  $("input[name='name[]']"); //arraySubTotl = document.forms[0].elements["name[]"] ; 

    if(jQuery.type( arraySubTotl.length )=='undefined'){
        $('#tlsubototal').val(document.forms[0].elements["name[]"].value);
    }else{
        for (var i = 0; i < arraySubTotl.length; i++) {
          if ( $("input[name='name[]']")[i].value!=="" &&  $("select[name='serv[]']")[i].value!=="") {
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
        $('#submit').click();   
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

$('body').on('keydown', 'input, select, textarea', function(e) {
    var self = $(this)
      , form = self.parents('form:eq(0)')
      , focusable
      , next
      ;
    if (e.keyCode == 13) {
        focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
        console.log(focusable.index(this)+1);

        if(self.hasClass('idpaciente')) {

            var id=$('#idpaciente').val();
            if (id!=="") {
               findOutPacient(id);               
            }else{
              $('#idpaciente').focus();
              return;
            };
        };

        if(self.hasClass('cantidad')){
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
            var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 

            items = jQuery.parseJSON(data);         
            if (typeof items!= 'undefined' && items!==null) {      
         
              if(items.length>0){ 
                 loadMedicos(items[0].codmedico);
                 loadIdsAsociados(items);
                 //PARA ACTUALIZAR EL MEDICO EN LA FACTURA CORRESPONDIENTE AL PACIENTE
                 var numfactu= $('#numfactu').val().trim();  
                 upDateMedico(numfactu,items[0].codmedico);
                 //ACTUALIZA CLIENTES
                  updateClientes(items[0].codclien,$('#numfactu').val());


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
               //   alert();
                }
  }
//--------------------------------
  function updateClientes(codclien,numfactu){

  var datasave ={
         numfactu : numfactu         
        ,codclien : codclien
        ,idcentro : '1'    
    }

    url="../../clases/updateclientreturn.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
}
//--------------------------------
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

  if ( $('#numfactu').val().trim()!=="" ) {
         $('#submit').click();   
  }

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
    // $('#fechaf').html('Amount Due '+today+'  <span id="invoicen">   Invoice #</span>') 
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
        ,tipo_doc : '04'
        ,workstation : 'FARMACIA'
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

  
  var fechaDev = $('#fecha').val();
 // fechaDev = fechaDev.split('-');
    
 if (fechaDev.indexOf('-')>-1) {
  fechaDev = fechaDev.split('-'); 
 }else{
   fechaDev = fechaDev.split('/'); 
 }
  
  
  var mes = fechaDev[1].length == 1 ? '0'+fechaDev[1] :  fechaDev[1];
  var day = fechaDev[2].length == 1 ? '0'+fechaDev[2] :  fechaDev[2];

  fechaDev = mes+'-'+day+'-'+fechaDev[0];


  var today = new Date().toLocaleDateString('en-US', {  
      month : 'numeric',
      day : 'numeric',        
      year : 'numeric'
      }).split(' ').join('-');

   today = today.split('/').join('-');
   today = today.split('-')
  
   var mon = today[0].length == 1 ? '0'+today[0] : today[0];
   var day = today[1].length == 1 ? '0'+today[1] : today[1]; 

   today = mon+'-'+day+'-'+today[2]


  //if (fechaDev!==today) {    
    // $("#save").hide();
  //};


  res = getPayments(); 
if (res!==null) {
  for (var i = 0; i < res.length; i++) {
       var codtarjeta = res[i].codtipotargeta;
       var pago = res[i].monto;

       if (codtarjeta=="00") {
           $('#cash').val(pago);  
           $( "#cash" ).click()
       }else if(codtarjeta=="01" || codtarjeta=="02" || codtarjeta=="03" || codtarjeta=="04" || codtarjeta=="05"){
          $('#tdc1').prop('readonly', false);
           if($('#tdc1').val()==""){              
               setValueAndCheck('tdc1',pago,codtarjeta);
           }else if($('#tdc2').val()==""){
             $('#tdc2').prop('readonly',false);
             setValueAndCheck('tdc2',pago,codtarjeta);              
           }else if($('#tdc3').val()==""){
              $('#tdc3').prop('readonly',false);
              setValueAndCheck('tdc3',pago,codtarjeta);             
           }
       }else if(codtarjeta=="06"){
              $('#ath').val(pago);
       }else if(codtarjeta=="09"){
               $('#check').val(pago);
       };

  };
  };

 
  var totalfactura = $('#tltotal').html();
  //totalfactura.match(/\d+/).toString()
  var dueamount =parseFloat(totalfactura.replace('$',''));
  $('#dueamount').val(totalfactura);
  $('#saldo').val(totalfactura); 

calPayments();

    $('.pay').click(function(){

      if ( !$(this).is('[readonly]') ) { 

          var ele=$(this);
          var id = ele.attr('id');
          var xsaldo    =   $('#saldo').val()  == '' ? 0: parseFloat($('#saldo').val().replace('$','') );
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


$("#paymentsModal").on("hidden.bs.modal", function (event) {
   console.log();
   $('input[name="optradio1"]').prop('checked', false);
   $('input[name="optradio2"]').prop('checked', false);
   $('input[name="optradio3"]').prop('checked', false);

   $('#tdc1').val('');
   $('#tdc2').val('');
   $('#tdc3').val('');

});


});
//*********************************************************************
   function getPayments(){
    $query="";

    var invoicen = $('#numfactu').val();  //NUMERO DE LA DEVOLUCION
  
    var datasave ={      
        numfactu : invoicen      
        ,id_centro : '1'
        ,tipo_doc : '04'
    }

    url="../../clases/getpagos.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items = jQuery.parseJSON(resp);
    return items;
   }
 //*********************************************************************
function setValueAndCheck(id,value,card){
   $('#'+id).prop('readonly',false);
   $('#'+id).val(value);
   $('#'+id).click();
   $('#'+id).trigger( "click" )
   var cdfp=id.match(/\d+/).toString();
   var xx='';

   if (card=='01') {
      xx= 'mc'+cdfp+'-'+card;
      $('#'+'mc'+cdfp+'-'+card).prop("checked", true);
   }else if (card=='02'){
      xx= 'vs'+cdfp+'-'+card;
      $('#'+xx).prop("checked", true);
   }else if (card=='03'){
      xx = 'am'+cdfp+'-'+card;
      $('#'+xx).prop("checked", true);
   }else if (card=='04'){
      xx =  'dc'+cdfp+'-'+card;
      $('#'+xx).prop("checked", true);
   }else if (card=='05'){
    xx = 'ck'+cdfp+'-'+card;
      $('#'+xx).prop("checked", true);
   };
}  
//*********************************************************************


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
  var numfactu=  $('#numfactu').val(); 
  
  upDateMedico(numfactu,codmedico);
  

  })
  //*****************************************************
  function upDateMedico(numfactu,codmedico){
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
  }

  //*****************************************************
  function displayTotales(numfactu){
    data ={ 
       empresa  : '1',
       numfactu : numfactu
    }

    var res= getData("../../clases/geteditinvprod.php",data,'POST');
    itemsf= jQuery.parseJSON(res);



    var subtotal = itemsf[0].subtotal;
    subtotal=parseFloat(subtotal).toFixed(2)
    //SUBTOTAL
    $("#tlsubototal").attr("value", subtotal);  
    $('#tlsubototal').val(subtotal);
    $('#tlsubototal').html(subtotal);   //VALOR MOSTRADO

    $('#frmsubtotal').val(subtotal);   //VALOR POST

    //IMPUESTO
    var frmtax = itemsf[0].TotImpuesto;
    frmtax=parseFloat(frmtax).toFixed(2)
    $('#tlimpuesto').val(frmtax);   //VALOR POST
    $('#tlimpuesto').html(frmtax);   //VALOR POST
    $('#frmtax').html(frmtax);   //VALOR POST
    $('#frmtax').val(frmtax);   //VALOR POST
    
    
    //% DE IMPUSTO
     var porcentImpuesto = itemsf[0].iva;
     porcentImpuesto=parseFloat(porcentImpuesto).toFixed(2)
    $('#taxilicuota').val(porcentImpuesto);   //VALOR POST
    

    //DESCUENTOS % Y MONTO
    var porcendes = itemsf[0].alicuota;
    porcendes=parseFloat(porcendes).toFixed(2)

    $("#discprcntg").attr("value", porcendes);
    $('#discprcntg').val(porcendes);
    $('#discprcntg').html(porcendes);

    var porcenmonto = itemsf[0].descuento;
    porcenmonto=parseFloat(porcenmonto).toFixed(2)

    $("#discamount").attr("value", porcenmonto);
    $('#discamount').val(porcenmonto);
    $('#discamount').html(porcenmonto);

    //SHIPPING
    var shippimg = itemsf[0].monto_flete;
    shippimg=parseFloat(shippimg).toFixed(2)

   
    $('#frmshipping').val(shippimg);  //POST
    $('#shipping').val(shippimg);  //MOSTRDO
    $('#shipping').html(shippimg);  //MOSTRDO

    //TOTAL
    var total = itemsf[0].totalnot;    
    total= parseFloat(total)+parseFloat(shippimg);
    total= parseFloat(total).toFixed(2)
    $("#tltotal").attr("value", total);
    $('#tltotal').val(total);
    $('#tltotal').html(total);  //MOSTRDO

    $('#frmtotal').val(total);  //POST



    getPacientes(itemsf[0].codclien); //BUSCA EL CLIENTE
    loadMedicos(itemsf[0].codmedico); //BUSCA MEDICO PARA ESE PACIENTE EN ESA FACTURA
  }
//******************************************************
function getPacientes(id){
     data ={ 
       q  : id
     }
     var res= getData("../../clases/getpacientebycodigo.php",data,"POST");
     try { 
     items= jQuery.parseJSON(res);     
     var options;
     for (var j = 0; j < items.length; j++) { 
          options+="<option value='"+items[j].codclien+"' selected >"+items[j].nombres+"</option>";     
     }
     $("#idassoc").html(options);
      } catch(err){
  
 }
}
//*****************************************************

 });