var insert = false;
$(function(){

$('#xtitulo').html('<b>Productos</b>');


$('.consultar').click(function(){
  element=$(this);
  insert = false;

  var idprod = element.parent().parent().attr('id');
  let index =$(this).parent().parent().index()
  idprod = $('.idprod')[index].innerText;
  getproductinfo(idprod);
  
})

function getproductinfo(idprod){
   
   var datasave ={
         prod_serv : 'P'
        ,idprouct :idprod
    };

    var prodinfo = ajaxGen("../../clases/getproducto.php",datasave);

    var pastillasxunidad = ajaxGen("../../clases/getcantxunidad.php",datasave);

    

   
    //CODIGO DEL PRODUCTO
    $('#idprod').val(prodinfo[0].coditems);
    document.getElementById("idprod").readOnly = true;

    // desitems
    $('#productname').val(prodinfo[0].desitems);
    // nombre_alterno
    $('#prodnamesht').val(prodinfo[0].nombre_alterno); 
    // fecing
    $('#fechaing').val(prodinfo[0].ingreso); 

    $('#costo').val(prodinfo[0].costo);     

    if (prodinfo[0].cod_subgrupo=="1") {
       $("#tipoproduct").trigger('click').prop('checked', true);
       $("#tipoproduct").trigger('click').prop('checked', true);
    }else{
       $("#tipoproduct").trigger('click').prop('checked', false);
       $("#tipoproduct").trigger('click').prop('checked', false);
    };

    // Exisminima
    $('#stockmin').val(prodinfo[0].Exisminima);
    // Exismaxima    
    $('#stockmax').val(prodinfo[0].Exismaxima);    
    //existencia 
    $('#stockcurrent').val(prodinfo[0].existencia);      
    // CapsulasXUni en  NTPRODUCTOS (table)
    $('#cantxunit').val(pastillasxunidad[0].CapsulasXUni);  


    if (prodinfo[0].activo=="1") {
       $("#activo").trigger('click').prop('checked', true);
       $("#activo").trigger('click').prop('checked', true);
    }else{
       $("#activo").trigger('click').prop('checked', false);
       $("#activo").trigger('click').prop('checked', false);
    };


    if (prodinfo[0].aplicaIva=="1") {
       $("#impuesto").trigger('click').prop('checked', true);
       $("#impuesto").trigger('click').prop('checked', true);
    }else{
       $("#impuesto").trigger('click').prop('checked', false);
       $("#impuesto").trigger('click').prop('checked', false);
    };

    if (prodinfo[0].aplicadcto=="1") {
       $("#descuento").trigger('click').prop('checked', true);
       $("#descuento").trigger('click').prop('checked', true);
    }else{
       $("#descuento").trigger('click').prop('checked', false);
       $("#descuento").trigger('click').prop('checked', false);
    };

    if (prodinfo[0].aplicaComMed=="1") {
       $("#commedico").trigger('click').prop('checked', true);
       $("#commedico").trigger('click').prop('checked', true);
    }else{
       $("#commedico").trigger('click').prop('checked', false);
       $("#commedico").trigger('click').prop('checked', false);
    };

    if (prodinfo[0].aplicaComTec=="1") {
       $("#comtec").trigger('click').prop('checked', true);
       $("#comtec").trigger('click').prop('checked', true);
    }else{
       $("#comtec").trigger('click').prop('checked', false);
       $("#comtec").trigger('click').prop('checked', false);
    };


    var res= ajaxGen("../../clases/tipoprecios.php",{});
    items=  res ;//jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        if (j==0) {
           options+="<option value='"+items[j].codtipre+"' selected >"+items[j].destipre+"</option>";       
        }else{
           options+="<option value='"+items[j].codtipre+"'>"+items[j].destipre+"</option>"; 
        }
        ;
        
    }
    $("#idtipprecio").html(options);


   let codtipre = $('#idtipprecio').val();
   let coditems = $('#idprod').val();
   $('#precio').val('0');
   let price =  getprecio(codtipre,coditems);
   if (price !=undefined) {
     $('#precio').val(price[0].precunit)
   };
    
}


  $('#idtipprecio').change(function(event){
     let codtipre = $(this).val();
     let coditems =  $('#idprod').val();
     $('#precio').val('0')
     let price =  getprecio(codtipre,coditems);
     if (price !=undefined) {
       $('#precio').val(price[0].precunit)
     };
    
  })


function getprecio(codtipre,coditem){
  var precio= ajaxGen("../../clases/getprecio.php",{codtipre:codtipre,idprouct:coditem});
  return precio;
}


function ajaxGen(url,data){
    var res;
    $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: url,
            data:data, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                try{
                      items= jQuery.parseJSON(respuesta);
      
                      if (typeof items!== 'undefined' && items!==null ) { 
                         if(items.length>0){
                            res=items;
                         }
                      }
                  }
                  catch(err){

                  }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

    return res;
}




$('#prodnew').click(function(){
  document.getElementById("idprod").readOnly = false;
  insert = true;
  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day   : 'numeric',        
  year  : 'numeric'
  }).split(' ').join('/');


    $('#idprod').val('');
    // desitems
    $('#productname').val('');
    // nombre_alterno
    $('#prodnamesht').val(''); 
    // fecing
    $('#fechaing').val(hoy); 
    $('#costo').val(0);  
    
    $("#tipoproduct").trigger('click').prop('checked', true);
    $("#tipoproduct").trigger('click').prop('checked', true);
      
    // Exisminima
    $('#stockmin').val(10);
    // Exismaxima    
    $('#stockmax').val(100);    
    //existencia 
    $('#stockcurrent').val(0);      
    // CapsulasXUni en  NTPRODUCTOS (table)
    $('#cantxunit').val(0);  

    $("#activo").trigger('click').prop('checked', true);
    $("#activo").trigger('click').prop('checked', true);

    $("#impuesto").trigger('click').prop('checked', true);
    $("#impuesto").trigger('click').prop('checked', true);

    $("#descuento").trigger('click').prop('checked', true);
    $("#descuento").trigger('click').prop('checked', true);

    $("#commedico").trigger('click').prop('checked', true);
    $("#commedico").trigger('click').prop('checked', true);

    $("#comtec").trigger('click').prop('checked', false);
    $("#comtec").trigger('click').prop('checked', false);
    
    var res= ajaxGen("../../clases/tipoprecios.php",{});
    items=  res ;//jQuery.parseJSON(res);     
    var options;
    for (var j = 0; j < items.length; j++) { 
        if (j==0) {
           options+="<option value='"+items[j].codtipre+"' selected >"+items[j].destipre+"</option>";       
        }else{
           options+="<option value='"+items[j].codtipre+"'>"+items[j].destipre+"</option>"; 
        };  
    }
    $("#idtipprecio").html(options);
    $('#precio').val('0');

})


$('#save').click(function(){
//MINVENTARIO
let coditems     = $('#idprod').val();
let desitems     = $('#productname').val();
let activo       = $("#activo").prop('checked') ? "1":"0";
let Exisminima   = $('#stockmin').val();
let Exismaxima   = $('#stockmax').val();   
let Prod_serv    = "P";
let aplicaIva    = $("#impuesto").prop('checked') ? "1":"0";
let aplicadcto   = $("#descuento").prop('checked') ? "1":"0";
let aplicaComMed = $("#commedico").prop('checked') ? "1":"0";
let aplicaComTec = $("#comtec").prop('checked') ? "1":"0";
let cod_subgrupo = $("#tipoproduct").prop('checked') ? "1":"2";
let costo        = $('#costo').val();  
let nombre_alterno = $('#prodnamesht').val(); 
//MPRECIOS
let codtipre    = $('#idtipprecio').val();
let precunit    = $('#precio').val();
let sugerido    = $('#precio').val();

//NTPRODUCTOS
let CapsulasXUni = $('#cantxunit').val();  


data = {
   insert
  ,coditems
  ,desitems
  ,activo
  ,Exisminima
  ,Exismaxima
  ,Prod_serv
  ,aplicaIva
  ,aplicadcto
  ,aplicaComMed
  ,aplicaComTec
  ,cod_subgrupo
  ,costo
  ,nombre_alterno
  ,codtipre
  ,precunit
  ,sugerido
  ,CapsulasXUni
}


 var upd = ajaxGen("../../clases/setprodandprice.php",data);
$('.bd-productos-modal-lg').hide();
 window.location.reload();
})


$('.prodon').change(function(){
   var idprod = '';
   let activo = $(this).prop('checked') == true? '1' : '0';
   let index  = $(this).parent().parent().parent().index();
   idprod = $('.idprod')[index].innerText; 
   var upd = ajaxGen("../../clases/setactiveprod.php",{idprod,activo});

})

});