$('#xtitulo').html('<b>Devolución de Servicios</b>');
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
var res= getData("../../clases/getinvoicemaster.php",data);
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

$('#idusr').val(items[0].usuario);

$('#idpaciente').val(items[0].codclien);
var fecha = items[0].fechafac;
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
	data ={	
		q  : id
	}
	var res= getData("../../clases/getmedicobycodigo.php",data);
	items= jQuery.parseJSON(res);     
	var options;
	for (var j = 0; j < items.length; j++) { 
	     options+="<option value='"+items[j].codmedico+"' selected >"+items[j].medico+"</option>"; 	   
	}
	$("#medico").html(options);
}


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

    var invoicen = $('#devolucion').val();  //NUMERO DE LA DEVOLUCION
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
      
      $('#btnpaymnt').prop('disabled',true);
      //$('#submit').prop('disabled',false);
      $('#paymentsModal').modal('hide');
      //$('#add_name')[0].reset();  
      // $('#paymentsModal')[0].reset();

    returnPrinting(invoicen,2,'2');
    updatingDBMasterDev(invoicen,pagototal);

  })

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
      }else{
        setTotalLines();
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
          if ( $("input[name='subtotal[]']")[i].value!==""  &&  $("select[name='serv[]']")[i].value!=="") {
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

 function updatePagos(docnumber,monto){
    var datasave ={
         docnumber   : docnumber
        ,table   : 'CMA_Mnotacredito'
        ,monto : monto 
        ,key     : 'numnotcre'
        ,cancelado : 'cancelado'
        ,status    : 'statnc'
    }

    url="../../clases/updatepagos.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

 }
   //******************* End Pagos   ***********************

    function returnPrinting(devolucion,times,idcentro){
    
    let user = $('#loggedusr').val(); 
    var datasave ={
          numfactu : devolucion
         ,times : times
         ,idempresa : idcentro      
    }

    let prninvoice  = $('#prninvoice').val();
    let autoposprn = $('#autoposprn').val();
    let pathprn = $('#pathprn').val();

   if (prninvoice!='1' && autoposprn!='1' ) {
      window.open('../../clases/printdevserviciospdf.php?numfactu='+devolucion+'&times='+times+'&idempresa='+idcentro+'&user='+user,'', '_blank');
   }else{
      url="../../clases/printindevolucionservicios.php";
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
