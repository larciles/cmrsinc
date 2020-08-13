$(".loading").fadeOut( "slow" ); 
$('#okRec').click(function(e){
 		//clearTableRow();
        var id=$('#patient').val()
     
        findOutPacient2(id);
        $('#patientassoc').focus();
    
})

$("#patientassoc").change(function(){

    var id=$('#patientassoc').val();     
    getRecordPaciente2(id);
   
})

function findOutPacient2(id){
    $('.loading').show();
    var items="";
    var ajxmed = $.post( "../../clases/paciente.php",{ q : id }, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         loadIdsAsociados2(items);
       }
    }
    })
        .done(function() {
        
        })
        .fail(function() {
          
        })
        .always(function() {
          $(".loading").fadeOut( "slow" ); 
        }); 


}

function loadIdsAsociados2(items){    
 var options=""; 

    for (var i = 0; i < items.length; i++) {    
         
        options+="<option value='"+items[i].codclien+"'>"+items[i].nombres+"</option>";  
    }      
  $("#patientassoc").html(options);
  getRecordPaciente2( items[0].codclien );

   $(".loading").fadeOut( "slow" ); 
}

function getRecordPaciente2(id){

    var items="";
    var ajxmed = $.post( "../../clases/getpatientrecord.php",{ codclien : id }, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTable2(items);
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

function fillTrTable2(data){
	var html = '';

	var asistido='';
	var confirmado='';

	for(var i = 0; i < data.length; i++){
		asistido='';
		confirmado='';
		descons='';
		if (data[i].CONFIRMADO!=null ) {
			confirmado  =	data[i].CONFIRMADO;
		}
		if (data[i].ASISTIDOS!=null  ) {
			asistido = data[i].ASISTIDOS
		}
		if (data[i].descons !=null  ) {
			descons = data[i].descons 
		}

     var id = data[i].codclien+'-'+data[i].codconsulta+'-'+data[i].coditems;
     var idcitar =data[i].codclien+'-'+data[i].Historia+'-'+data[i].codmedico+'-'+i;

		  html += '<tr><td class ="appmtdate">' + data[i].fecha_cita  +'</td>'+
		              '<td>' + data[i].telfhabit   +'</td>'+
		              '<td>' + confirmado          +'</td>'+  
		              '<td>' + asistido            +'</td>'+ 
		              '<td>' + descons             +'</td>'+ 
		              '<td  contenteditable="false" class="editar observacion" id='+id+' onfocusout="focusOut()">' + data[i].observacion +'</td>'+ 
		              '<td>' + data[i].Medico      +'</td>'+ 
		              '<td>' + data[i].usuario     +'</td>'+ 
		              '<td>' + data[i].Historia    +'</td>'+ 
		              '<td>' + data[i].codclien    +'</td>'+ 
                  '<td align="center"> <button type="button" name="citar"  id='+idcitar+' class="btn btn-success citar">Citar</button> </td>'+
		           '</tr>';
		

		//$('#dynamic_field').append( html );
 //
	}
	$("#tbody").html(html);
}


function clearTableRow(){
	var elmtTable = document.getElementById('dynamic_field');
	var tableRows = elmtTable.getElementsByTagName('tr');
	var rowCount = tableRows.length;

	for (var x=rowCount-1; x>0; x--) {
		elmtTable.removeChild(tableRows[x]);
		}
}

$('#dynamic_field').on('click', 'tbody tr button.citar', function(event) {
  element=$(this);
  idCreate = element.attr('id').toString().split('-')[1];
  console.log(idCreate)
  $("#codmedico2").val(element.attr('id').toString().split('-')[2])
  $("#exampleModal").modal();
})

$(document).on("click", "td.citar", function(){ alert(); });

 $(document).on("dblclick", "td.editar", function(){ makeEditable(this); });
 $(document).on("blur", "input#editbox", function(){ removeEditar(this) }); 

  function removeEditar(element) { 
  
    var datacompuesta =element.parentElement.id;
    var datasplit = datacompuesta.split("-");

    var v_codconsulta ="";
    try{
      v_codconsulta = datasplit[1];
    } catch(err){

    }
    var v_codclien    = datasplit[0];

    var v_producto    ="";
    try{
      var v_producto    = datasplit[2];
    }catch(err){

    }


    var observacion = $('#editbox').val();
    
    var fecha = element.parentElement.parentNode.childNodes[0].innerText;
    if(fecha==''){
            var fecha = new Date().toLocaleDateString('en-US', {  
        month : 'numeric',
        day : 'numeric',        
        year : 'numeric'
        }).split(' ').join('/');
        
}

      var datasave ={
          tipoConsulta : v_codconsulta,
          codclien     : v_codclien,
          fecha_cita   : fecha,
          observacion  : observacion,
          coditems     : v_producto
      }
      var xres = ajaxGen("../../clases/atencionupdateobservacion.php",datasave)
    
    $('td.current').html($(element).val());
    $('.current').removeClass('current'); 

    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));

}

    $("body").keypress(function(e){
         if(e.which === 13){
           var id=$('#patient').val()
              if (id!="") {
                  $('#patient').val('')
                  e.preventDefault();

                  if ($.isNumeric(id)) {
                      if (id.length=10) {
                         var phone = id.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
                         id=phone;
                      };
                  };       
                  findOutPacient2(id);
                  $('#patientassoc').focus();
              }
        };
    });