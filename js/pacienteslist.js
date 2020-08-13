var linexpageValues = $( "#linexpage" ).val()
var codclien;
var paciente;

if (linexpageValues==""){
   var str="25";
   $( "#linexpage" ).val( str );
} 

$('.btnclcon').click(function(){
  consultarRecord(this);    
})

$('#recordConsModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  $("#bodyproducts").find("tr").remove();
  recipient=paciente;
  var data = getRecord(codclien);
  var items=[];
  var nFactura=0;
  $.each(data, function(key,val){

    items.push("<tr data-target='#recordConsModal' class='clsrecord'>");
    items.push("<td id="+val.id_centro+">"+val.numfactu+"</td>");
    items.push("<td>"+val.fechafac+"</td>");
    items.push("<td>"+val.total.substring(0,val.total.indexOf('.')+3)+"</td>");
    if (val.id_centro==1) {
      items.push("<td>Prodcutos</td>")  
    }else if (val.id_centro==2){
      items.push("<td>Servicos</td>")  
    }else if (val.id_centro==3){   
      items.push("<td>Laser</td>")  
    };
    items.push('<td align="center"> <button type="button" name="imprimir" id="" class="btn btn-success imprimir">Imprimir</button> </td>')
    
    items.push("</tr>");
    if(nFactura==0){
      nFactura=val.numfactu
    }

  })
  $("#bodyrecord").find("tr").remove();
  $('#bodyrecord').append(items.join(''));
  var modal = $(this)
  modal.find('.modal-title').text('Record de consultas de  ' + recipient)
  modal.find('.modal-body input').val(recipient)
  if(nFactura!==0){
    var data = findOutProduct(nFactura, $('#recordcl td:first-child')[0].id)
    setUpProductData(data)
  }
})


function getRecord(codclien){
  var items;
  var lretncitas;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/pacientesgetrecord.php",
            data:{ q : codclien }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
}

function consultarRecord(element){
codclien = element.id;
paciente = element.parentElement.parentElement.firstChild.textContent.toString();
}

$("#recordcl").on("click", "tr td button", function(){  
  let hasclase = $(this).hasClass('imprimir')
  let facn=this.parentElement.parentElement.firstElementChild.innerText; 
  let idem=this.parentElement.parentElement.firstElementChild.id;
  let user=$('#loggedusr').val();

  resulset = isConsulta(facn);
  if (resulset.length>0) {
        invoicePrinting(facn,resulset[0]['cod_subgrupo'],user,idem); //numfactu;
     } else{
        invoicePrinting(facn,'',user,idem); //numfactu;
     };

});

$("#recordcl").on("click", "tr", function(){  
  var data = getProductos(this); 
  setUpProductData(data);
});


function isConsulta(numfactu){
    var datasave ={
         numfactu   : numfactu              
    }

    url="../../clases/isconsulta.php";

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

function invoicePrinting(_numfactu,service,user,idempresa){

  if (idempresa==2) {
      window.open('../../clases/printconsultaspdf.php?numfactu='+_numfactu+'&times='+'1'+'&service='+service+'&user='+user,'', '_blank');  
  }else if(idempresa==1){
      window.open('../../clases/printprodpdf.php?numfactu='+_numfactu+'&times='+'1'+'&user='+user,'', '_blank');
  }else if(idempresa==3){
      window.open('../../clases/printlaserpdf.php?numfactu='+_numfactu+'&times='+'1'+'&user='+user,'', '_blank');
  };
    
      
  }


function setUpProductData(data){
  items = [];
  $.each(data, function(key,val){
    items.push("<tr>");
    items.push("<td>"+val.desitems+"</td>");
    items.push("<td>"+val.cantidad+"</td>");  
    items.push("</tr>");
  })

  $("#bodyproducts").find("tr").remove();
  $('#bodyproducts').append(items.join(''));
}

function getProductos(element){
 // console.log(element); 
 var factura = element.firstChild.innerText; 
 var res = findOutProduct(factura, element.firstChild.id);
 return res;
}

function findOutProduct(factura,idempresa){

 var items;
  var lretncitas;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/pacientesgetproductos.php",
            data:{ q : factura,
              idempresa 
             }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      lretncitas=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return lretncitas;
}




//dblclick

// $('#editar')
//tabla-smsrpl
$('.editar').click(function(){
  getCodCl(this);    
})
//$('#editar').on("click", "td", function(){ getCodCl(this); });

function getCodCl(element) { 
  var nodoTd = element.parentNode; //Nodo TD
  var nodoTr = nodoTd.parentNode; //Nodo TR
  var nodosEnTr = nodoTr.getElementsByTagName('tr');
  var coditems = element.parentElement.parentElement.id.toString();
    //console.log(nodoTd.id)
  var currentURL = window.location.href;
    //console.log(currentURL)

  var ajxmed = $.post( "../../clases/pacienteslist.php",{c:coditems}, function(data) { 
  //console.log(data)
  var items="";
  var options="";
  items= jQuery.parseJSON(data);
  console.log(items)
  console.log("listo")

  var strNombres = items[0].nombres.trim().split(" ");
   if (items[0].apellido.trim() !="" && items[0].nombre.trim() ) {
      apellidos = items[0].apellido.trim()
      nombre = items[0].nombre.trim()      
   }else{

      if (strNombres.length==2) {
           apellidos = strNombres[0]
           nombre = strNombres[1]
      }else if(strNombres.length==3){
           apellidos = strNombres[0]+ ' '+strNombres[1]
           nombre = strNombres[2]
      }else{
           apellidos = strNombres[0]+ ' '+strNombres[1]
           nombre = strNombres[2]+ ' '+strNombres[3]
      }
   }
    var str = items[0].telfhabit.split("-");
    var telefono=str[0]+str[1]+str[2]
    
    var telefono2="";
    if(items[0].telfofic!==null){
        str = items[0].telfofic.split("-");
        telefono2=str[0]+str[1]+str[2];    
    }
    
    var sexo
    if (items[0].sexo==null  || items[0].sexo=="0" ) {
       sexo=0;
    }else{
         sexo=1;
    }

    var state
    if (items[0].ESTADO!=null) {
       state=items[0].ESTADO;
    }

    var pais 
    if (items[0].Pais!=null) {
      pais = items[0].Pais
    }

    var dob
    if (items[0].NACIMIENTO!=null) {
      dob = items[0].NACIMIENTO
    }

    var desde
    if (items[0].CliDesde!=null) {
      desde = items[0].CliDesde
    }
     
   var record
   if (items[0].Historia!=null) {
      record = items[0].Historia
    }

   var codclien
   if (items[0].codclien!=null) {
      codclien = items[0].codclien
    }

   var dirl1
   if (items[0].direccionH!=null) {
      dirl1 = items[0].direccionH
    }

   var codpostal
   if (items[0].codpostal!=null) {
      codpostal = items[0].codpostal
    }

   var activo
   if (items[0].inactivo==null || items[0].inactivo=="0" ) {
      activo = 0
    }else{
      activo = 1
    }

   var vigente
   if (items[0].fallecido==null || items[0].fallecido=="0" ) {
      vigente = 0
    }else{
      vigente = 1
    }

    var dirl2
    if (items[0].Eaddress!=null) {
      dirl2 = items[0].Eaddress
    }
    
    var ciudad
    if (items[0].hCiudad!=null) {
      ciudad = items[0].hCiudad
    }    

    var email
    if (items[0].email!=null) {
      email = items[0].email
    }    
  
   var id=items[0].Cedula
   var medico=items[0].codmedico
   
   var celular
   if (items[0].celular!=null) {
      celular = items[0].celular
    }

   var empleado
   if (items[0].exonerado==null || items[0].exonerado=="0" ) {
      empleado = 0
    }else{
      empleado = 1
    }


   var postData = {
       apellidos : apellidos,
       nombre : nombre,
       cedula : id,
       telefono : telefono,
       telefono2 : telefono2,
       sexo : sexo,
       medico : medico,
       dob : dob,
       desde : desde,
       record : record,
       codclien : codclien,
       usuariocl : celular,
       dirl1 : dirl1,
       codpostal : codpostal,
       pais : pais,
       state : state,
       activo : activo,
       empleado : empleado,
       vigente : vigente,
       dirl2 : dirl2,
       ciudad : ciudad,
       email : email
    } 
  
    $.redirect("edit.php",postData ); 

      //alert( "success" ); 
    })
    .done(function() {
    //  console.log();
      //alert( "second success" );
    })
    .fail(function() {
      alert( "error" );
    })
    .always(function() {
      //alert( "finished" );
     // console.log();
    });  
}


      $('#tabla-smsrpl').on('change', 'tbody tr select.medicos', function(event) {

        var este= $(this)
        var newMedico= $("#"+este.attr('id')).val()
        var arrDat= este.attr('id').split('-')
        var cdclien=arrDat[1]
        var datasave ={
         codmedico   : newMedico
        ,codclien : cdclien
    }

    url="../../clases/updatemedicoenclientes.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);

      })


//  IMPORTANTE REDIRECCIONES
//// similar behavior as an HTTP redirect
// window.location.replace("http://stackoverflow.com");

// // similar behavior as clicking on a link
// window.location.href = "http://stackoverflow.com";


 // var ajxmed = $.post( "../../clases/pacienteslist.php",{}, function(data) { 
  // console.log(data)
  // var items="";
  // var options="";
  // items= jQuery.parseJSON(data);
  // console.log(items)

  // for (var i = 0; i < items.length; i++) {
  //    ///     options+="<option value='"+items[i].codmedico+"'>"+items[i].medico+"</option>";   
  // }
 //   // $("#medico").html(options); 
 //      //alert( "success" ); 
 //    })
 //    .done(function() {
 //      console.log();
 //      //alert( "second success" );
 //    })
 //    .fail(function() {
 //      alert( "error" );
 //    })
 //    .always(function() {
 //      //alert( "finished" );
 //      console.log();
 //    });


  // $('#appoimentModal').on('show.bs.modal', function (event) {

  //   var button = $(event.relatedTarget) // Button that triggered the modal
  // var recipient = button.data('whatever') // Extract info from data-* attributes

  //     findOutPacient(codclien); 
     
  //     $('#idpaciente').keyup(function(e){
  //         idCreate="";
  //         var tecla = undefined
  //         if (e.which == 13) {
  //         var id=$('#idpaciente').val()
  //         tecla=13
  //         findOutPacient(codclien);        
  //         }
  //     })


  // })

  //   function findOutPacient(id){
  //               var codMedOculto = $("#medicohd").val();
  //           var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 
  //           ////console.log(data)
  //           var items="";
  //           var options="";
  //           if (typeof data == "string"){
  //               var xerr=data.indexOf('Fatal error')
  //               if (xerr<0) {
  //                 items = jQuery.parseJSON(data);
  //               };
  //           }
            
            
  //           modal.find('.modal-title').text('Nueva cita ' )
  //           if (typeof items!= 'undefined' && items!==null) {      
      
  //             if(items.length>0){   
  //                   modal.find('.modal-title').text('Nueva cita  a  ' + items[0].nombres)
  //                   objPaciente = items[0].codclien
  //                   loadMedicos(items[0].codmedico)
  //                   $(".dividassoc").hide();
  //                   //controlcitaprevia.php
  //                   findCitaPrevia(objPaciente);
          
  //         //OBSERVACIONES
  //                    findObservacion(items[0].codclien,$( "#citas" ).val(),$('#fecha').val(), '2');
  //                if(items.length>1){  
  //                   $(".dividassoc").show();
  //                   loadIdsAsociados(items)
  //                }
  //              }else{
  //                modal.find('.modal-title').text('Nueva cita ' )
                  
  //              }
  //              }else{
  //                idCreate=$('#idpaciente').val();
  //                $("#exampleModal").modal('hide');
  //                $("#myModal").modal();
  //              }
  //           })
  //               .done(function() {
  //               })
  //               .fail(function() {
  //                // alert( "error" );
  //               })
  //               .always(function() {
               
  //               }); 
  // }