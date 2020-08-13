 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;

    var id_random=undefined;


  $('#xtitulo').html('<b>Facturación Laser</b>');
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
               ' <td><input type="text" name="tax[]" readonly="readonly" id="'+tax+'"  placeholder="Impuesto" class="form-control " /></td>'+
               ' <td><input type="text" name="name[]"  readonly="readonly" placeholder="Subtotal" class="form-control name_list subtotal" /></td>'+
               ' <td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  

           setConsultas(idpro);
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
          if ($('#idassoc').val()!=="" && $('#serv1').val()!=="" && $('#invoicen').val()=="") {        
               $.ajax({  
                    url:"../../clases/Invoicinglaser.php",  
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

                           getInvoices();

                          $("#paymentsModal").modal();
                       //  $('#add_name')[0].reset();  
                    }  
               }); 
           }else{
            if ($('#invoicen').val()!=="") {
              alert('Factura Ya existe')
            };
           }; 
      }); 


  // function getData(url){

  //   return $.ajax({
  //       type: "GET",
  //       url: url,
  //       async: false
  //   }).responseText;

  //    }


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

  function setMedios(){
    var res= getData("../../clases/medios.php");
    items= jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        options+="<option value='"+items[j].codigo+"'>"+items[j].medio+"</option>"; 
    }
    $("#medio").html(options);
  }

  function setConsultas(id){
           
    var serv= getData("../../clases/servicios.php",{ servicio : 'M' },'POST');
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
     }
     $("#"+id).html(options);
  }

   setConsultas('serv1');
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
       
         var cantidad =  $('.cantidad').val();
         cantidad  = $("input[name='cantidad[]']")[row_index].value

         var coditems  = $('#serv'+xid).val();
         $("input[name='coditems[]']")[row_index].value = coditems;
         //DEDUCIBLE
         if (coditems=='0000000005') {           
           $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", false); 
           id_random=$("input[name='precio[]']")[row_index].id;
         }else{
         //  $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", true); 
         };
         //\


         var codtipre  = $('#tpre'+xid).val();     
         $("input[name='codprecio[]']")[row_index].value = codtipre;   

         var codinsurance  = $('#tseg'+xid).val(); 
        // $("input[name='insurance[]']")[row_index].value ='0'; //codinsurance;

         var descuento = $('.descuento').val();    
     
      }

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,row_index);
      }else{
        setTotalLines();
      };



     //console.log(coditems,codtipre,cantidad,descuento);
         
     });
    // $(".product option[value=somevalue]").prop("selected", "selected")

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
        var cantidad =  $('.cantidad').val();
        cantidad  = $("input[name='cantidad[]']")[row_index].value
        var coditems  = $('#serv'+xid).val();
        $("input[name='coditems[]']")[row_index].value = coditems;
      
        var codtipre  = $('#tpre'+xid).val();     
        $("input[name='codprecio[]']")[row_index].value = codtipre;   

        //var codinsurance  = $('#tseg'+xid).val(); 
        //$("input[name='insurance[]']")[row_index].value = codinsurance;
        var descuento = $('.descuento').val();

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

            var cantidad =  $('.cantidad').val();
            cantidad  = $("input[name='cantidad[]']")[row_index].value
            var coditems  = $('#serv'+xid).val();
            $("input[name='coditems[]']")[row_index].value = coditems;
          
            var codtipre  = $('#tpre'+xid).val();     
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
                    url:"../../clases/changeitemlaser.php",  
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
    setTotalLines();
     }else{
      $('#discprcntg').val('');
      $('#discprcntg').focus();
     }

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
         setTotalLines();
    }else{
      $('#discamount').val('');
      $('#discamount').focus();
    }; 

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
          if ( $("input[name='name[]']")[i].value!=="" &&  $("select[name='serv[]']")[i].value!=="") {
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
        console.log(focusable.index(this)+1);

        if(self.hasClass('idpaciente')) {
            var id=$('#idpaciente').val();
            findOutPacient(id);
        };

        if(self.hasClass('precio')){
            next = focusable.eq(6);   //INDICE DEL BOTON ADD MORE
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

    var invoicen = $('#invoicen').val();
    var idusr = $('#idusr').val();
    //nuevo
    var idcard1 = $('#card1').val();
    var idcard2 = $('#card2').val();
    var idcard3 = $('#card3').val();
    //f nuevo

    


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
        ,id_centro : '3'
        ,tipo_doc : '01',
        idcard1,
        idcard2,
        idcard3
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
      invoicePrinting(2,idusr);
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
  var dueamount =parseFloat(totalfactura.match(/\d+/).toString());
  $('#dueamount').val(totalfactura);
  $('#saldo').val(totalfactura); 



    $('.pay').click(function(){

      if ( !$(this).is('[readonly]') ) { 

          var ele=$(this);
          var id = ele.attr('id');
          var xsaldo    =   $('#saldo').val()  == '' ? 0: parseFloat($('#saldo').val().match(/\d+/).toString() );
          var pagototal = $('#pagototal').val()  == '' ? 0: parseFloat($('#pagototal').val().match(/\d+/).toString() );

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

      var cash  = $('#cash').val() == '' ? 0: parseFloat($('#cash').val().match(/\d+/).toString() );   
      var tdc1  = $('#tdc1').val() == '' ? 0: parseFloat($('#tdc1').val().match(/\d+/).toString() ); 
      var tdc2  = $('#tdc2').val() == '' ? 0: parseFloat($('#tdc2').val().match(/\d+/).toString() );
      var tdc3  = $('#tdc3').val() == '' ? 0: parseFloat($('#tdc3').val().match(/\d+/).toString() );
      var ath   = $('#ath').val()  == '' ? 0: parseFloat($('#ath').val().match(/\d+/).toString() );
      var chek  = $('#check').val()  == '' ? 0: parseFloat($('#check').val().match(/\d+/).toString() );
      
      var pagototal = cash+tdc1+tdc2+tdc3+ath+chek;
   
      $('#pagototal').val(pagototal);
      $('#saldo').val(parseFloat(totalfactura.match(/\d+/).toString()) -pagototal);

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
	  
	  $('#print2').hide();

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


  
   //Print
  $('#print2').click(function(){
     let user = $("#idusr").val();
     invoicePrinting(2,user);
    
  })
  //End Print


  function invoicePrinting(times,user){
     if (times==undefined) {
      times=1;
    };
    var _numfactu =  $('#invoicen').val();   

    var datasave ={
         numfactu   : _numfactu 
        ,times :times,
        user         
    }

    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

    if (prninvoice!='1' && autoposprn!='1' ) {
        window.open('../../clases/printlaserpdf.php?numfactu='+_numfactu+'&times='+times+'&user='+user,'', '_blank');
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
     console.log(resp);
     console.log(items);
  
  }