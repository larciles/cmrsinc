var totalPage;
var voidinvoice;
var perfil ;
var newUpdate=false; // true = Nuevo  false=Update
$('#xtitulo').html('<b>Adm Usuario</b>');
$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

var serv= getData("../../clases/getperfiles.php");
perfil = jQuery.parseJSON(serv);
setListPerfiles(perfil);
getConsultasInvoices();



  function getConsultasInvoices(  page,  search,  fecha){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined) {
       fecha ="";
    };

     url="../../controllers/userslistcontroller.php";
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
    
    
   var options;
   var options2;
    for (var i = 0; i < lenArr; i++) {
    	  var login     = items['data'][i]["login"];
        var nombres   = items['data'][i]["Nombre"];
        var apellido  = items['data'][i]["apellido"];
        var codperfil = items['data'][i]["codperfil"];        
        var access    = items['data'][i]["access"];
        var id        = items['data'][i]["Id"];
        var idactivo  = makeid();
        for (var j = 0; j < perfil.length; j++) { 
            
            if(perfil[j].codperfil==items['data'][i]["codperfil"]){              
              options+="<option value='"+perfil[j].codperfil+"' selected >"+perfil[j].desperfil+"</option>";   
            }else{
              options+="<option value='"+perfil[j].codperfil+"'>"+perfil[j].desperfil+"</option>";   
            }
            
        }
        

        options2='<button type="button" name="activo" id="" class="btn btn-warning activo">Inactivo';
        if (items['data'][i]["activo"]=='1') {
           options2='<button type="button" name="activo" id="" class="btn btn-success activo">Activo';  
        };


         $('#tblLista').append('<tr id='+id+'>'+
          ' <td  align="center">'+id+'</td>'+
         	' <td  align="center">'+login+'</td>'+
         	' <td>'+nombres+'</td>'+
         	' <td>'+apellido+'</td>'+          
          ' <td align="center"><select name="codperfil[]"  id='+id+'-'+i+'   class="form-control codperfil" > <option value="" selected ></option></select> </td>'+    
          ' <td align="right">'+access+'</td>'+
         	' <td align="center"> <button type="button" name="consultar" id="" class="btn btn-info consultar">Consultar</td>'+ 
          ' <td align="center"  id='+idactivo+'></td>'+ 
         	' </tr>');
          //PERFIL
          $("#"+id+'-'+i).html(options);
          $("#"+idactivo).html(options2);
          options='';
    };  
  }

//-----------------------------------------------------------------------------------
function makeid() {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  for (var i = 0; i < 5; i++)
       text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}
//-----------------------------------------------------------------------------------

      $('#tblLista').on('change', 'tbody tr select', function(event) {
        var elemt = $(this);
        var iduser = elemt.parent().parent().attr('id');       
        let nuevoperfil = elemt.val();        
        var datasave ={
             iduser
            ,nuevoperfil       
        }
        url="../../clases/changeperfil.php";
        var items;
        var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
        }).responseText;    
        items= resp ;

      });


     $('.paginaf').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fecha=$('.datepicker').val();
        search = $('#search').val();

        getConsultasInvoices(  num, search , fecha );
        //$(".content2").html("Page " + num); // or some ajax content loading...
    });

  

    $('#search').keyup(function(e){
        if (e.which == 13) {
                      var fecha,search ="";
           search = $('#search').val(); 
           fecha=$('.datepicker').val();

           getConsultasInvoices( '' , search ,fecha );
              $('.paginaf').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })

        }
  })



$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val(); 
   getConsultasInvoices( '' , search , fecha );

    $('.paginaf').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
  })
});


$('#tblLista').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("devolver")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
            newUpdate=false;
            let idu = $(this).parent().parent().attr('id');
            fitUser(idu);
           $('#modalusers').modal('show');
       }
   }else if(elemt.attr('class').indexOf("activo")>-1){
      let activo='1';
      let idu = $(this).parent().parent().attr('id');
      if(elemt.attr('class').indexOf("btn-success")>-1){
         activo='0';
      }
      userActive(idu,activo);
      window.location.reload();

   }else if(elemt.attr('class').indexOf("anular")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
           voidinvoice=numfactu;
          $("#void").modal();
      }
   }else if(elemt.attr('class').indexOf("imprimir")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          invoicePrinting(numfactu,1);
      }
   }
})

function userActive(id,activo){
   

    var datasave ={
         id
        ,activo
    }

    url="../../clases/useractive.php";

    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: datasave,
                          async: false
                      }).responseText;
    
     items= resp ; //jQuery.parseJSON(resp);
}


 function invoicePrinting(_numfactu,times){
    if (times==undefined) {
      times=1;
    };

    var datasave ={
         numfactu   : _numfactu
        ,times : times
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


function getUserData(q){


var items;
  var response;
  $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: "../../clases/getuserbyid.php",
            data:{ q }, 
            success:  function(respuesta){                        
                //console.log(respuesta);
                items= jQuery.parseJSON(respuesta);
                if (typeof items!= 'undefined' && items!==null ) { 
                   if(items.length>0){
                      response=items;
                   }
                }
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

  return response;
    
  }


function getData(url){

    return $.ajax({
        type: "GET",
        url: url,
        async: false
    }).responseText;

}

function setListPerfiles(perfil,codperfil){
    let options;
    for (var j = 0; j < perfil.length; j++) { 

      if (j==0 && codperfil==undefined) {        
            options+="<option value='"+perfil[j].codperfil+"' selected >"+perfil[j].desperfil+"</option>";
      }else{
        if (codperfil!==undefined && codperfil==perfil[j].codperfil) {

            options+="<option value='"+perfil[j].codperfil+"' selected >"+perfil[j].desperfil+"</option>";

        } else{
          options+="<option value='"+perfil[j].codperfil+"'>"+perfil[j].desperfil+"</option>";   
        };
          
      }
    }
    $("#perfil").html(options);

}


function setAcces(access){
  let options;
   for (var j = 0; j < 11; j++) { 
       if (access!==null && access!==undefined) {
          if (access==j) {
            options+="<option selected value='"+j+"'>"+j+"</option>";
          } else{
            options+="<option value='"+j+"'>"+j+"</option>";
          };                     

       } else{
          options+="<option value='"+j+"'>"+j+"</option>";
       };
       
   }
   $("#access").html(options);
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


//-- MODAL FUNCTIONS UPDATE

 $('#controlc').change(function(e){
    e.stopPropagation();
    
     e.preventDefault();
 })

 $('#laser_type').change(function(e) {
      e.stopPropagation()
     $('#lasertype').val('mls');
     
     if (!$('#laser_type').prop('checked')) {             
          $('#lasertype').val('hilt');          
     };
     $('#submit').click();
     e.preventDefault();
});

 $('#saveuser').on('click', function(event) {
    
  let perfil =  $("#perfil").val();
  let access =  $("#access").val();    
  let nombre =  $("#nombre").val();
  let apellido = $("#apellido").val();
  let usuar    = $("#usuar").val();
  let contrase = $("#contrase").val();   
  let iduser   = $("#iduser").val(); 
  let ctrlcita = $('#controlc').prop('checked');

  let prnfact = $('#prnfact').prop('checked');
  let autoprnfact = $('#autoprnfact').prop('checked');
  let pathprn   = $("#pathprn").val();

  data = {
       newUpdate
      ,perfil
      ,access
      ,nombre
      ,apellido
      ,usuar
      ,contrase
      ,iduser
      ,ctrlcita
      ,prnfact
      ,autoprnfact
      ,pathprn
  }


 var upd = ajaxGen("../../clases/setuser.php",data);
$('.bd-productos-modal-lg').hide();
 window.location.reload();
 })


function fitUser(idu){
    var data = getUserData(idu);  
     $("#nombre").val(data[0].Nombre);
     $("#apellido").val(data[0].apellido);
     $("#usuar").val(data[0].login);
     $("#iduser").val(idu); 
     //$("#access").val();     
     $("#perfil").empty();
     setListPerfiles(perfil,data[0].codperfil);
     setAcces(data[0].access);
     if (data[0].controlcita=='1') {
        $('#controlc').prop('checked', true).change()
     } else{
        $('#controlc').prop('checked', false).change()
     };

     if (data[0].prninvoice=='1') {
        $('#prnfact').prop('checked', true).change()
     } else{
        $('#prnfact').prop('checked', false).change()
     };

     if (data[0].autoposprn=='1') {
        $('#autoprnfact').prop('checked', true).change()
     } else{
        $('#autoprnfact').prop('checked', false).change()
     };

     $("#pathprn").val(data[0].pathprn);
}

$('#newuser').on('click', function(event) {
    $("#perfil").empty();
    $("#access").empty();    
    $("#nombre").val('');
    $("#apellido").val('');
    $("#usuar").val('');
    $("#contrase").val(''); 
    $("#iduser").val(''); 
    $("#pathprn").val('');    
    $('#controlc').prop('checked', false).change()
    $('#autoprnfact').prop('checked', false).change()
    $('#prnfact').prop('checked', false).change()
    newUpdate=true;
    setListPerfiles(perfil);
    setAcces();
})

//-- MODAL FUNCTIONS END  

