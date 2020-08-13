$('#xtitulo').html('<b>Devolución</b>');
var eletdc1,eletdc2,eletdc3
function getData(url,data){

    return $.ajax({
        type: "POST",
        url: url,
        data:data,
        async: false
    }).responseText;
}


var numfactu= $('#invoicen').attr('value');
data ={	
	empresa  : '2',
	numfactu : numfactu
}
var res= getData("../../clases/getreturnmaster.php",data);
items= jQuery.parseJSON(res); 

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
var porcendes = items[0].alicuota;
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
var total = items[0].totalnot;
total=parseFloat(total).toFixed(2)

$("#tltotal").attr("value", total);
$('#tltotal').val(total);
$('#tltotal').html(total);  //MOSTRADO

$('#frmtotal').val(total);  //POST

//SHIPPING
var shippimg = items[0].monto_flete;
shippimg=parseFloat(shippimg).toFixed(2)

$('#shipping').html(shippimg);  //MOSTRADO
$('#frmshipping').val(shippimg);  //POST


//$('#idpaciente').val(items[0].codclien);
var fecha = items[0].fechanot;
var res = fecha.split(" "); 
fecha = res[0];
fecha =fecha.split("-"); 
fecha= fecha[0]+'-'+fecha[1]+'-'+fecha[2];
fecha = $('#fecha').val(fecha);

getPacientes(items[0].codclien);
getMedicos(items[0].codmedico);



function getPacientes(id){
  data ={ 
    q  : id
  }
  try { 
  var res= getData("../../clases/getpacientebycodigo.php",data);
  items1= jQuery.parseJSON(res);     
  var options;
  for (var j = 0; j < items1.length; j++) { 
       options+="<option value='"+items1[j].codclien+"' selected >"+items1[j].nombres+"</option>";     
  }
  $("#idassoc").html(options);
   } catch(err){
  
 }
}

// function getMedicos(id){
// 	data ={	
// 		q  : id
// 	}
// 	var res= getData("../../clases/getmedicobycodigo.php",data);
// 	items= jQuery.parseJSON(res);     
// 	var options;
// 	for (var j = 0; j < items.length; j++) { 
// 	     options+="<option value='"+items[j].codmedico+"' selected >"+items[j].medico+"</option>"; 	   
// 	}
// 	$("#medico").html(options);
// }

function getMedicos(id){
        var ajxmed = $.post( "../../clases/medicos.php",{}, function(data) { 
        var items2="";
        var options="";
        items2= jQuery.parseJSON(data);
           
        for (var i = 0; i < items2.length; i++) {
            if (id==items2[i].codmedico){
                  options+="<option selected value='"+items2[i].codmedico+"'>"+items2[i].medico+"</option>";  
              }else{
                  options+="<option value='"+items2[i].codmedico+"'>"+items2[i].medico+"</option>";  
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
  var numfactu  = $('#numfactu').val(); 

       var datasave ={
         numfactu  : numfactu
        ,codmedico : codmedico
    }

    url="../../clases/updatemedicoreturnconsultas.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

  })
  
$('#submit').click(function(){
     
       $.ajax({  
            url:"../../clases/savedevservicios.php",  
            method:"POST",  
            data:$('#returnserv').serialize(),  
            success:function(data)  
            {  
                 //alert(data); 
                 items= jQuery.parseJSON(data);
                 $('#id_dev').val(items);
                 $('#devolucion').val(items);
                 $('#devolucion').html('Devolución # '+items);

                 $('#btnpaymnt').prop('disabled', false);
                 $('#submit').prop('disabled', true);
               //  $('#add_name')[0].reset();  
            }  
       }); 
    
}); 

  $('#save').click(function(){
    $('#save').prop('disabled', true);
    $('#submit').prop('disabled', true);
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

    var invoicen =  $('#numfactu').val();//$('#devolucion').val();  //NUMERO DE LA DEVOLUCION
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
        ,tipo_doc : '04'
    }

    url="../../clases/pagos.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
      


       updatingDBMasterDev(invoicen,pagototal);

       
      $('#btnpaymnt').prop('disabled',true);
      //$('#submit').prop('disabled',false);
      $('#paymentsModal').modal('hide');
      //$('#add_name')[0].reset();  
      // $('#paymentsModal')[0].reset();


  })


    //--------------------------------------------------------
   function updatingDBMasterDev(devolucion,monto){

        var datasave ={
          monto : monto
         ,idempresa : '2'
         ,numdoc : devolucion
         ,tipo : '04'      
    }

    url="../../clases/updatemasterpagosdev.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
   }
//********************************* CALCULO DE SUBTOTAL *********************************

  $('#dynamic_field').on('change', 'tbody tr td .cantidad', function(event) {
  
    var index = $(this).parent().parent().index();
    var id = $(this).parent().parent().attr('id');
    setGeneralValues(this,index,id);

  })

  function setGeneralValues(element,index,xid){
     
      var cantidad  = $('.cantidad')[index].value;    ;
      var coditems  = $('#serv'+xid).val();
      var codtipre  = $('#tpre'+xid).val();        
      var descuento = $('#descuento'+xid).val();    //por linea 

      if (coditems!=="" && codtipre!=="" && cantidad!=="" ) {
         subTotal(coditems,codtipre,descuento,cantidad,index);
      };
  }


  function subTotal(coditems,codtipre,descuento,cantidad,id){
    //  url="../../clases/calcularsubtotal.php";
    //  data = {
    //     coditems : coditems ,
    //     codtipre : codtipre,
    //     descuento : descuento,
    //     cantidad : cantidad
    //  }
    // var items;
    // var resp =  $.ajax({
    //                       type: "POST",
    //                       url: url,
    //                       data: data,
    //                       async: false
    //                   }).responseText;
    
    //  items= jQuery.parseJSON(resp);

     var values   = $("input[name='precio[]']").map(function(){return $(this).val();}).get();
     arrayPrecios = $("input[name='precio[]']") ;//document.forms[0].elements["precio[]"] ;
     arraySubTotl = $("input[name='subtotal[]']");//document.forms[0].elements["name[]"] ; 

      var precio=arrayPrecios[id].value;
      var subtotal=arraySubTotl[id].value;
   
    // if (jQuery.type(items)!=='null') {
    //     precio=items['precunit'];
    //     subtotal=items['subtotal'];
    //  }
      if (precio!==undefined) {
        $("input[name='precio[]']")[id].value =precio; //document.forms[0].elements["precio[]"][id-1].value=precio;
        $("input[name='subtotal[]']")[id].value =subtotal; //document.forms[0].elements["name[]"][id-1].value=subtotal;  
      };
      
      setTotalLines();
  }


  function setTotalLines(){

    var lineSubtotal=0;
    var arraySubTotl =  $("input[name='subtotal[]']"); //arraySubTotl = document.forms[0].elements["name[]"] ; 

    if(jQuery.type( arraySubTotl.length )=='undefined'){
        $('#tlsubototal').val(document.forms[0].elements["subtotal[]"].value);
    }else{
        for (var i = 0; i < arraySubTotl.length; i++) {
          if ( $("input[name='subtotal[]']")[i].value!=="") {
              var cantidad=0;
              var precio=0;
              var subtot=0;
              cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
              precio = parseFloat( $("input[name='precio[]']")[i].value ) ;
              subtot = cantidad * precio  ;
              $("input[name='subtotal[]']")[i].value =subtot;

               if($("#discprcntg").hasClass("readonly")){

               }else{
                $('#discamount').val('$0');
               }

              
              
              lineSubtotal=lineSubtotal + parseFloat( $("input[name='subtotal[]']")[i].value ) ; //parseFloat(document.forms[0].elements["name[]"][i].value);
             
               $("input[name='descuento[]']")[i].value = ''; 
             
             //  $("input[name='name[]']")[i].value =subt - desc;
              if ($('#discprcntg').val()!=="" && $('#discprcntg')!==0) {
                   var subt=parseFloat( $("input[name='subtotal[]']")[i].value );
                   var porc=parseFloat($('#discprcntg').val());
                   var desc=0;
                   if (porc!==0) {
                      var desc=(subt*porc)/100; 
                   };                   
                   $("input[name='detaialprcnt[]']")[i].value = porc;  
                   $("input[name='descuento[]']")[i].value = round(desc,2); 
                   $("input[name='subtotal[]']")[i].value = round(subt - desc,2);
              }
          };
        };
        $('#tlsubototal').val(lineSubtotal);
        $('#tlsubototal').html('$' + lineSubtotal);
        calculosTotales(lineSubtotal);
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

 //*************************   ROUND  *****************************
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

function toDate(dateStr) {
    var parts = dateStr.split("-");
    return new Date(parts[2], parts[1] - 1, parts[0]);
}

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
  fechaDev = fechaDev.split('-');
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


  if (fechaDev!==today) {    
     $("#save").hide();
  };


  res = getPayments(); 
if (res!==null) {
  for (var i = 0; i < res.length; i++) {
       var codtarjeta = res[i].codtipotargeta;
       var pago = res[i].pago;

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
// 



 
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


});

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


   //******************* End Pagos   ***********************

   function getPayments(){
    $query="";


    // $('#save').prop('disabled', true);
    // $('#submit').prop('disabled', true);
    // var cash  = $('#cash').val(); 

    // var tdc1  =$('#tdc1').val(); 
    // var radio1 =  eletdc1;

    // var tdc2  =$('#tdc2').val();  
    // var radio2 =  eletdc2; 

    // var tdc3 = $('#tdc3').val(); 
    // var radio3 =  eletdc3;   

    // var ath = $('#ath').val();  
    // var check = $('#check').val(); 

    // var pagototal = $('#pagototal').val();  
    // var saldo =  $('#saldo').val(); 

    // var dueamount = $('#dueamount').val(); 
    // var cambio = $('#cambio').val();

    // var fecha = $('#fecha').val();

    var invoicen = $('#numfactu').val();  //NUMERO DE LA DEVOLUCION
    // var idusr = $('#idusr').val();

  
    var datasave ={      
        numfactu : invoicen      
        ,id_centro : '2'
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
//---------------------------
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
//---------------------------
function findOutPacient(id){
        if (id!=="") {
          
            var items="";
            var ajxmed = $.post( "../../clases/atencioncitar.php",{ q : id }, function(data) { 

            items = jQuery.parseJSON(data);         
            if (typeof items!= 'undefined' && items!==null) {      
         
              if(items.length>0){ 
                
                 loadIdsAsociados(items);

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
                  alert();
                }
        };
  }
//---------------------------
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
//---------------------------
function updateClientes(codclien,numfactu){

  var datasave ={
         numfactu : numfactu         
        ,codclien : codclien
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