 $(document).ready(function(){  
    var eletdc1;
    var eletdc2; 
    var eletdc3;

    var id_random=undefined;


  $('#xtitulo').html('<b>Edicion de Compras</b>');
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
               ' <td><input type="text" name="cantidad[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" /></td>'+               
               ' <td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  

           setProductos(idpro);
           $('#'+idpro).focus();




      });  

      
//-----------------------------------------------------------------------------------         
 if($('#idcompra').val()!="") {
 

     let factcomp= $('#idcompra').val();
   


     let comprasm= ajaxGen("../../clases/getmastercompras.php",{factcomp});
     console.log(comprasm);
     let proveedor= ajaxGen("../../clases/getproveedor.php",{}); 
     let items= proveedor; //jQuery.parseJSON(res);     
     let options=items;
     for (var j = 0; j < items.length; j++) { 
        if (items[j].codProv==comprasm[0].codprov) {
           options+="<option value='"+items[j].codProv+"' selected >"+items[j].Desprov+"</option>"; 
        }else{
           options+="<option value='"+items[j].codProv+"'>"+items[j].Desprov+"</option>";   
        }
        
     }
    $("#idprove").html(options);

    $("#idnota").val(comprasm[0].observacion); 
    $("#fecha").val(comprasm[0].fecha); 

    if (comprasm[0].facclose=='1') {
        $('#savecompra').hide();     
        $('#saveinv').hide();
        $("#add").attr("disabled", true);

    }else{
       $('#savecompra').show();     
       $('#saveinv').show();
       $("#add").attr("disabled", );
    }
    
 }; 
//-----------------------------------------------------------------------------------
function ajaxGen(file,data){
    var res;
    $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: file,
            data:data, 
            success:  function(respuesta){                        
                //console.log(respuesta);

                if (respuesta!=="") {
                items= jQuery.parseJSON(respuesta);

                if (typeof items!== 'undefined' && items!==null ) { 
                   if(items.length>0){
                      res=items;
                   }
                }
                };
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

    return res;
}

//-----------------------------------------------------------------------------------      
 $('#savecompra').click(function(){  
            $('#transfer').val('0');
             let dts = $('#add_name').serialize();
               $.ajax({  
                    url:"../../clases/savecomprasedit.php",  
                    method:"POST",  
                    data:$('#add_name').serialize(),  
                    success:function(data)  
                    {  
                          items= jQuery.parseJSON(data); //alert(data); 
                          $("#compran").attr("value", items);
                          $('#compran').val(items);
                          $('#compran').html('    Invoice # '+items);
                          $('#factura').val(items);

                          $('#savecompra').prop('disabled', true);
                          $('#saveinv').prop('disabled', true);

          //                  getInvoices();

          //                 $("#paymentsModal").modal();
          //              //  $('#add_name')[0].reset();  
                    }  
               }); 
 })
  //-----------------------------------------------------------------------------------     
      
 $('#saveinv').click(function(){  
             $('#transfer').val('1');
             let dts = $('#add_name').serialize();
               $.ajax({  
                    url:"../../clases/savecomprasedit.php",  
                    method:"POST",  
                    data:$('#add_name').serialize(),  
                    success:function(data)  
                    {  
                          items= jQuery.parseJSON(data); //alert(data); 
                          $("#compran").attr("value", items);
                          $('#compran').val(items);
                          $('#compran').html('    Invoice # '+items);
                          $('#factura').val(items);

                          $('#savecompra').prop('disabled', true);
                          $('#saveinv').prop('disabled', true);

          //                  getInvoices();

          //                 $("#paymentsModal").modal();
          //              //  $('#add_name')[0].reset();  
                    }  
               }); 
 })
//-----------------------------------------------------------------------------------
      function makeid() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
}
//-----------------------------------------------------------------------------------
     $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#'+button_id+'').remove();  
            setTotalLines();;

      });  
//-----------------------------------------------------------------------------------     


//-----------------------------------------------------------------------------------
  function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

     }
//-----------------------------------------------------------------------------------

  function setProductos(id){
           
    var serv= getData("../../clases/getprodcompras.php");
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
     }
     $("#"+id).html(options);
  }
//-----------------------------------------------------------------------------------
   setProductos('serv1');
  // getProveedor();

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


//-----------------------------------------------------------------------------------
   $('#dynamic_field').on('change', 'tbody tr td select', function(event) {
      var element=$(this);
     
      if($(this).parent().parent().hasClass("highlight")   || $(".service").is(":focus") ){

         var row_index = $(this).parent().parent().index();
         var col_index = $(this).parent().index();        
         var xid = $(this).parent().parent().attr('id').toString();
       
         var cantidad  = $("input[name='cantidad[]']")[row_index].value;
         var coditems  = $("select[name='serv[]']")[row_index].value;
      
      }
         
     });
    
//-----------------------------------------------------------------------------------
  $('#dynamic_field').on('change', 'tbody tr td.cantidad', function(event) {
  
    var index = $(this).parent().parent().index();
    var id = $(this).parent().parent().attr('id');
    cantidad  = $("input[name='cantidad[]']")[index].value    

  })

//-----------------------------------------------------------------------------------
$('body').on('keydown', 'input, select, textarea', function(e) {
    var self = $(this)
      , form = self.parents('form:eq(0)')
      , focusable
      , next
      ;
    if (e.keyCode == 13) {
        focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
        //console.log(focusable.index(this)+1);



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
//-----------------------------------------------------------------------------------
function  getProveedor(){
    let res= ajaxGen("../../clases/getproveedor.php",{});
    items= res; //jQuery.parseJSON(res);     
    let options;
    for (var j = 0; j < items.length; j++) { 
        if (j==0) {
           options+="<option value='"+items[j].codProv+"' selected >"+items[j].Desprov+"</option>"; 
        }else{
           options+="<option value='"+items[j].codProv+"'>"+items[j].Desprov+"</option>";   
        }
        
    }
    $("#idprove").html(options);
}
});
//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------
 