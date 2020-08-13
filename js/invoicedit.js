var anulada=false;
function getData(url,data){

    return $.ajax({
        type: "POST",
        url: url,
        data:data,
        async: false
    }).responseText;
}


 var numfactu= $('#invoicen').attr('value');
 $('#factura').val(numfactu);
data ={ 
  empresa  : '2',
  numfactu : numfactu
}
var res= getData("../../clases/getinvoicemaster.php",data);
items= jQuery.parseJSON(res); 

loadMedicos(items[0].codmedico);

if (items[0].statfact=='2') {
   anulada=true;

   $('#idpaciente').prop("disabled", true);
   $('#medico').prop("disabled", true);
   $('#save').prop("disabled", true);     

};

var subtotal = items[0].subtotal;
subtotal=parseFloat(subtotal).toFixed(2)
//SUBTOTAL
$("#tlsubototal").attr("value", subtotal);  
$('#tlsubototal').val(subtotal);
$('#tlsubototal').html(subtotal);   //VALOR MOSTRADO

$('#frmsubtotal').val(subtotal);   //VALOR POST

//IMPUESTO
var frmtax = items[0].TotImpuesto;
frmtax=parseFloat(frmtax).toFixed(2)
$('#frmtax').val(frmtax);   //VALOR POST

//DESCUENTOS % Y MONTO
var porcendes = items[0].Alicuota;
porcendes=parseFloat(porcendes).toFixed(2)

$("#discprcntg").attr("value", porcendes);
$('#discprcntg').val(porcendes);
$('#discprcntg').html(porcendes);

var porcenmonto = items[0].descuento;
porcenmonto=parseFloat(porcenmonto).toFixed(2)

$("#discamount").attr("value", porcenmonto);
$('#discamount').val(porcenmonto);
$('#discamount').html(porcenmonto);

//TOTAL
var total = items[0].total;
total=parseFloat(total).toFixed(2)

$("#tltotal").attr("value", total);
$('#tltotal').val(total);
$('#tltotal').html(total);  //MOSTRDO

$('#frmtotal').val(total);  //POST

//SHIPPING
var shippimg = items[0].monto_flete;
shippimg=parseFloat(shippimg).toFixed(2)

$('#shipping').html(shippimg);  //MOSTRDO
$('#frmshipping').val(shippimg);  //POST


var fecha = items[0].fechafac;
var res = fecha.split(" "); 
fecha = res[0];
fecha =fecha.split("-"); 
fecha= fecha[1]+'-'+fecha[2]+'-'+fecha[0];
fecha = $('#fecha').val(items[0].fechafac2);

getPacientes(items[0].codclien);
//getMedicos(items[0].codmedico);



function getPacientes(id){
  data ={ 
    q  : id
  }
  var res= getData("../../clases/getpacientebycodigo.php",data);
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

function getMedicos(id){
        var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
        var items="";
        var options="";
        items= jQuery.parseJSON(data);
           
        for (var i = 0; i < items.length; i++) {
            if (id==items[i].codmedico){
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

$('#medico').change(function(){
  var codmedico = $('#medico').val();
  var numfactu=  $('#factura').val(); 

       var datasave ={
         numfactu  : numfactu
        ,codmedico : codmedico     
    }

    url="../../clases/updatemedicoconsultas.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

  })

  function loadMedicos(codMedOculto){
         
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





 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;

    var id_random=undefined;


  $('#xtitulo').html('<b>Facturación Consultas y Servicios</b>');
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
               ' <td><input type="text" name="cantidad[]" value="1" placeholder="cantidad Enter" class="form-control cantidad numbersOnly enterpass enterkey" /></td>'+
               ' <td><input type="text" name="precio[]" id="'+id_precio+'" placeholder="precio" class="form-control precio enterkey" /></td>'+ 
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
                    url:"../../clases/invoicingserviciose.php",  
                    method:"POST",  
                    data:$('#returnserv').serialize(),  
                    success:function(data)  
                    {  
                          items= jQuery.parseJSON(data); //alert(data); 
                          $("#invoicen").attr("value", items);
                          $('#invoicen').val(items);
                          $('#invoicen').html('    Invoice # '+items);
                          $('#factura').val(items);

                          $('#btnpaymnt').prop('disabled', false);
                          $('#submit').prop('disabled', true);
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


  function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
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

  function setConsultas(id){
           
    var serv= getData("../../clases/servicios.php");
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
     }
     $("#"+id).html(options);
  }

   //setConsultas('serv1');
  // setTipoDePrecios('tpre1');
 //  setSeguro('tseg1');


   $('#dynamic_field').on('change', 'tbody tr td select', function(event) {

       if (anulada) {
          return;
       };
      var element=$(this);
     
      if($(this).parent().parent().hasClass("highlight")   || $(".service").is(":focus") ){

         var row_index = $(this).parent().parent().index();
         var col_index = $(this).parent().index();        
         var xid = $(this).parent().parent().attr('id').toString();
       
   
         var cantidad  = $("input[name='cantidad[]']")[row_index].value

          
         var coditems  = $("[name='serv[]']")[row_index].value
         $("input[name='coditems[]']")[row_index].value = coditems;
         //DEDUCIBLE
         if (coditems=='0000000005') {           
           $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", false); 
           id_random=$("input[name='precio[]']")[row_index].id;
         }else{
           $('#'+$("input[name='precio[]']")[row_index].id).prop("readonly", false); 
         };
         //\

         var codtipre  =  $("[name='listaprecio[]']")[row_index].value;  
         $("input[name='codprecio[]']")[row_index].value = codtipre;   

         var codinsurance  = '0';//$('#tseg'+xid).val(); 

         var descuento =  $("[name='descuento[]']")[row_index].value;     
     
      }

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,row_index);
      }else{
        setTotalLines();
      };
         
     });


  $('#dynamic_field').on('change', 'tbody tr td .cantidad', function(event) {
  
    var index = $(this).parent().parent().index();
    var id = $("[name='serv[]']")[index].value;//$(this).parent().parent().attr('id');
    
    var cantidad = $("input[name='cantidad[]']")[index].value;
    if ($.isNumeric(cantidad)) {
        setGeneralValues(this,index,id);
    }else{
       $("input[name='cantidad[]']")[index].value="";
       $("input[name='cantidad[]']")[index].focus();
    };

  })

  

  $('.precio').keypress(function(e) {
     if(e.which == 13) {

       //$("input[name='codprecio[]']")[row_index].value
       //$('#'+$("input[name='precio[]']")[row_index].id)

        var row_index = $(this).parent().parent().index();
        var col_index = $(this).parent().index();        
        var xid = $(this).parent().parent().attr('id').toString();       
     
        var cantidad  = $("input[name='cantidad[]']")[row_index].value;

        var coditems  = $("[name='serv[]']")[row_index].value;
        $("input[name='coditems[]']")[row_index].value = coditems;
      
        var codtipre  = $("[name='listaprecio[]']")[row_index].value  ;     
        $("input[name='codprecio[]']")[row_index].value = codtipre;   

      
        var descuento =$("[name='descuento[]']")[row_index].value ;

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

        if ($.isNumeric(precio) ) {

              var cantidad =  $("input[name='cantidad[]']")[row_index].value;

              var coditems  = $("[name='serv[]']")[row_index].value;
              $("input[name='coditems[]']")[row_index].value = coditems;
            
              var codtipre  = $("[name='listaprecio[]']")[row_index].value  ;  
              $("input[name='codprecio[]']")[row_index].value = codtipre;   

              //var codinsurance  = $('#tseg'+xid).val(); 
              //$("input[name='insurance[]']")[row_index].value = codinsurance;
              var descuento =$("[name='descuento[]']")[row_index].value ;              

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
                    data:$('#returnserv').serialize(),  
                    success:function(data)  
                    { 
                     }
                 })
 } 


  function setGeneralValues(element,index,xid){
     
      var cantidad  = $('.cantidad')[index].value;    ;
      var coditems  = xid;//$('#serv'+xid).val();
      var codtipre  = $("[name='codprecio[]']")[index].value ;// $('#tpre'+xid).val();        
      var descuento = $("[name='descuento[]']")[index].value ;//$('#descuento'+xid).val();    //por linea 

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
     
     //precio Manua
      if (precio==undefined) {
          var precio=0;
      }
      var subtotal=0;
   
    if (jQuery.type(items)!=='null') {
        precio=items['precunit'];
        subtotal=items['subtotal'];
     }

      $("input[name='precio[]']")[id].value =precio; //document.forms[0].elements["precio[]"][id-1].value=precio;
      $("input[name='name[]']")[id].value =subtotal; //document.forms[0].elements["name[]"][id-1].value=subtotal;
      try{
            $("input[name='subtotal[]']")[id].value =subtotal; 
           }catch(err){
            
           }
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

        var _numfactu =  $('#factura').val();   
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
        console.log(focusable.index(this)+1);

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
        if (id!=="") {
          
            var items="";
            var ajxmed = $.post( "../../clases/atencioncitar.php",{ q : id }, function(data) { 

            items = jQuery.parseJSON(data);         
            if (typeof items!= 'undefined' && items!==null) {      
         
              if(items.length>0){ 
                
                 loadIdsAsociados(items);

                 updateClientes(items[0].codclien,$('#factura').val());
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
        };
  }


function updateClientes(codclien,numfactu){

  var datasave ={
         numfactu : numfactu
        ,codclien : codclien     
    }

    url="../../clases/updateclietconsulta.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
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
   //  $('#fecha').val(today)
    // $('#fechaf').html('Amount Due '+today+'  <span id="invoicen">   Invoice #</span>') 
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

    var invoicen = $('#factura').val();
    var idusr = $('#idusr').val();
    var nota = $('#nota').val();

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
        ,por: 'consulta'
        ,nota: nota
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
      //invoicePrinting();

      

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
  
  
  var mes = fechaDev[0].length == 1 ? '0'+fechaDev[0] :  fechaDev[0];
  var day = fechaDev[1].length == 1 ? '0'+fechaDev[1] :  fechaDev[1];

  fechaDev = mes+'-'+day+'-'+fechaDev[2];


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


  if (fechaDev!==today) {    
    // $("#save").hide();
  };


  res = getPayments(); 
if (res!==null) {
  for (var i = 0; i < res.length; i++) {
       var codtarjeta = res[i].codtipotargeta;
       var pago = res[i].monto;

       if (codtarjeta=="00" && pago>0) {
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
   
  var res_nota=getNotePayments();
  if (res_nota!==null) {
       $('#nota').val(res_nota[0].nota);
  }

 
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
          var xsaldo    = $('#saldo').val()      == '' ? 0: parseFloat($('#saldo').val().replace('$','') );
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

      var cash  = $('#cash').val()   == '' ? 0: parseFloat($('#cash').val().replace('$','') );   
      var tdc1  = $('#tdc1').val()   == '' ? 0: parseFloat($('#tdc1').val().replace('$','') ); 
      var tdc2  = $('#tdc2').val()   == '' ? 0: parseFloat($('#tdc2').val().replace('$','') );
      var tdc3  = $('#tdc3').val()   == '' ? 0: parseFloat($('#tdc3').val().replace('$','') );
      var ath   = $('#ath').val()    == '' ? 0: parseFloat($('#ath').val().replace('$','') );
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
        ,id_centro : '2'
        ,tipo_doc : '01'
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
   function getNotePayments(){
    $query="";

    var invoicen = $('#numfactu').val();  //NUMERO DE LA DEVOLUCION
  
    var datasave ={      
        numfactu : invoicen      
        ,id_centro : '2'
        ,tipo_doc : '01'
    }

    url="../../clases/getnotepagos.php";

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


  
   //Print
  $('#print2').click(function(){
    
     invoicePrinting();
    
  })
  //End Print


  function invoicePrinting(){
    var _numfactu =  $('#factura').val();   
   
   var xserv = $("select[name='serv[]']")[0].value;
   service="CONSULTA";
   if (xserv.indexOf('ST')>-1) { 
       service= "SUEROTERAPIA";
   };

    var datasave ={
         numfactu   : _numfactu  
        ,times : 2
        ,service :service      
    }

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

  $('#discprcntg').prop('readonly', true);
  $('#discamount').prop('readonly', true);
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
  var attr = $('#serv').attr('disabled');
  if (typeof attr !== typeof undefined && attr !== false) {
    $('#lockdescuento').hide();
  }

 });  