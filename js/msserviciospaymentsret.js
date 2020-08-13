$(document).ready(function(){ 

  
  var id_center ='2'
  var doc_type  ='04'
	var eletdc1;
  var eletdc2; 
  var eletdc3;	
  var totalfactura;
  var dueamount;

  $('#paymentsModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  // var modal = $(this)
  // modal.find('.modal-title').text('New message to ' + recipient)
  // modal.find('.modal-body input').val(recipient)



  cleanInputs();

  totalfactura =document.getElementById('tltotal').value;
  dueamount =parseFloat(totalfactura.replace('$',''));
  
  //totalfactura.match(/\d+/).toString()
  
  $('#dueamount').val(totalfactura);
  $('#saldo').val(totalfactura); 


    $('.pay').click(function(){

      if ( !$(this).is('[readonly]') ) { 

          var ele=$(this);
          var id = ele.attr('id');
          setPayments(id);
          // var xsaldo    = $('#saldo').val()  == '' ? 0: parseFloat($('#saldo').val().replace('$','') );
          // var pagototal = $('#pagototal').val()  == '' ? 0: parseFloat($('#pagototal').val().replace('$','') );

          //  $('#discprcntg').prop('readonly', false);

          // if ( $('#'+id).val() =="") {
          //   if (xsaldo!==0) {
          //      $('#'+id).val(xsaldo);
          //   };
            
          // };
          
          // calPayments(); //dueamount,pagototal
      }
  })
  
    //
  let ret=getPayments(); 
  
     
  
  // 

 //------------------------------------------------------------------------------------
  $('.pay').keyup(function(){
      calPayments(); //dueamount,pagototal
  })
//--------------------------------------------------------------------------------------


  $('#print2').hide();

});
  

  $('#save').click(function(){

     // SOLO PARA CONSULTAS SUERO PARA PODER SEPARAR LAS CAJAS

     // var xcod =$("select[name='serv[]']")[0].value
     // var n = xcod.indexOf("ST");
     // var  workstation='ATENCION02';
     // if (n>-1) {
     //    workstation='LASERPC2';
     // };    
     
     if ($('#dueamount').val().substr(1)>0 && $('#pagototal').val()=='') {
        return;
     };
     //FIN\\

   // $('#save').prop('disabled', true);
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

    var invoicen = $('#numero').val();
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
        ,id_centro : id_center
        ,tipo_doc : doc_type   
        ,por: "SUERO"
    }
 

    	 	let  url ;	  		
	  		json_upload = "json_data=" + JSON.stringify( datasave );
	      url='../../handler/MsserviciosPayHandler.php';
	      var api = new  XMLHttpRequest();
	      api.open('POST',url,true);
	      api.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

       	api.send(json_upload);
       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){
	       			  
               $('#paymentsModal').modal('hide');      
               
               returnPrinting(invoicen,1,'2');

               var urlevento = $('#urlevento').val();
               if (urlevento!=="") {
                  window.location.assign("../../vistas/atencionconsultas/atencion.php") 
               }else{
                  location.reload(true);
               };
	     		} 	
	     	}
  })

    $('.optradio1').click(function(){
      $('#tdc1').prop('readonly', false);
      setPayments('tdc1');
      $('#tdc1').focus();
      $('#tdc1').select();

      var ele=$(this);
      eletdc1=ele [0].id
     // console.log(eletdc1);      
    })

   $('.optradio2').click(function(){
      $('#tdc2').prop('readonly', false);
      setPayments('tdc2');
      $('#tdc2').focus();
      $('#tdc2').select();
      var ele=$(this);
      eletdc2=ele [0].id
     // console.log(eletdc2);
      
    })

   $('.optradio3').click(function(){
     $('#tdc3').prop('readonly', false);
      setPayments('tdc3');
      $('#tdc3').focus();
      $('#tdc3').select();
      var ele=$(this);
      eletdc3=ele [0].id
      //console.log(eletdc3);
      
    }) 
//----------------------------------------------
function getPayments(){
           
            var numfactu= $('#numero').val();          

            var datasave ={      
               numfactu : numfactu      
              ,id_centro : id_center
              ,tipo_doc : doc_type
            }

            var params = "pay_data=" + JSON.stringify( datasave );

            var id=document.getElementById('medio').value;    
            let  url ;
            let factura=document.getElementById('numero').value           
            url='../../handler/MsserviciosRetHandler.php';
            var api = new  XMLHttpRequest();
            api.open('POST',url,true);
            api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
            api.send(params);
            api.onreadystatechange = function(){
            if(this.readyState === 4 && this.status === 200){            

               let res=JSON.parse(this.responseText); 

                

               for (var i = 0; i < res.length; i++) {
                   var codtarjeta = res[i].codtipotargeta;
                   var pago = (res[i].monto*-1);
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

                calPayments(); //dueamount,pagototal
            }   
         } 

     
}
//----------------------------------------------
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
   eventFire(document.getElementById(xx), 'click');
}  
//----------------------------------------------
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
//----------------------------------------------
function eventFire(el, etype){
  if (el.fireEvent) {
    el.fireEvent('on' + etype);
  } else {
    var evObj = document.createEvent('Events');
    evObj.initEvent(etype, true, false);
    el.dispatchEvent(evObj);
  }
}
//----------------------------------------------
 function setPayments(id){
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
 //--------------------------------------------------------------------------------------
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

//----------------------------------------------
function cleanInputs(){
   
   radioCleaner(document.getElementsByName('optradio1'));
   radioCleaner(document.getElementsByName('optradio2'));
   radioCleaner(document.getElementsByName('optradio3'));
   document.getElementById('cash').value="";
   document.getElementById('tdc1').value="";
   document.getElementById('tdc2').value="";
   document.getElementById('tdc3').value="";
   document.getElementById('ath').value="";
   document.getElementById('check').value="";

}
//----------------------------------------------
function radioCleaner(ele){
  for(var i=0;i<ele.length;i++)
      ele[i].checked = false;
}
//----------------------------------------------

});
