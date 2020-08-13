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


		  html += '<tr><td>' + data[i].fecha_cita  +'</td>'+
		              '<td>' + data[i].telfhabit   +'</td>'+
		              '<td>' + confirmado          +'</td>'+  
		              '<td>' + asistido            +'</td>'+ 
		              '<td>' + descons             +'</td>'+ 
		              '<td>' + data[i].observacion +'</td>'+ 
		              '<td>' + data[i].Medico      +'</td>'+ 
		              '<td>' + data[i].usuario     +'</td>'+ 
		              '<td>' + data[i].Historia    +'</td>'+ 
		              '<td>' + data[i].codclien    +'</td>'+ 
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