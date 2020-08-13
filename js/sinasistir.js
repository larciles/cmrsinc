  var date_input=$('input[name="fechasin"]'); //our date input has the name "date"
  var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  date_input.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
  })


function getSinAsistir(fecha_cita){

    $("#tbodysin > tr").remove();
    $("#tbodysin").find("tr:gt(0)").remove(); 
    var items="";
    var ajxmed = $.post( "../../clases/getnoasistidos.php",{ fecha_cita }, function(data) { 

    items = jQuery.parseJSON(data);         
    if (typeof items!= 'undefined' && items!==null) {      
 
      if(items.length>0){ 
         // loadMedicos(items[0].codmedico);
         //console.log(items);
          fillTrTableSin(items);
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

function fillTrTableSin(data){
	var html = '';
	var asistido='';
	var confirmado='';

	for(var i = 0; i < data.length; i++){   

		  html += '<tr><td align="center" >' + data[i].nombres     +'</td>'+
		              '<td align="center" >' + data[i].Historia    +'</td>'+
		              '<td align="center" >' + data[i].observacion +'</td>'+  
		              '<td align="center" >' + data[i].telfhabit   +'</td>'+ 
		              '<td align="center" >' + data[i].medico      +'</td>'+ 		            
		              '<td align="center" >' + data[i].usuario     +'</td>'+ 		              
		              '<td align="center" >' + data[i].codclien    +'</td>'+ 
                  '<td align="center" >' + data[i].numfactu    +'</td>'+ 
		           '</tr>';

	}
	$("#tbodysin").html(html);
}

    $('#fechasin').change(function(){
    var fecha_cita;
    if (fecha_cita!=='') {
    	fecha_cita  =$('#fechasin').val();
    	getSinAsistir(fecha_cita);
    };
    
 });