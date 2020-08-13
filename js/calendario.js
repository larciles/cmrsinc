document.querySelectorAll('#calendar_table td')
.forEach(e => e.addEventListener("click", function() {
    // Here, `this` refers to the element the event was hooked on
    $('#modalEventos').modal('show')
   
   var fecha_evento = this.id;
   $('#fecha_evento').html(fecha_evento)
   load_events(fecha_evento);
    console.log(this.id)
}));


$('#guardar_evento').click(function(){
	save_evento();

	$('#modalEventos').modal('hide')
})


//FECHA
  // var date_input=$('input[name="fecha"]'); //our date input has the name "date"
  // var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
  // date_input.datepicker({
  //   format: 'mm/dd/yyyy',
  //   container: container,
  //   todayHighlight: true,
  //   autoclose: true,
  // })

  //   $('.datepicker').datepicker();

//
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10) {
    dd='0'+dd
} 

if(mm<10) {
    mm='0'+mm
} 

today = mm+'/'+dd+'/'+yyyy;


//SET DATE
$('#sd').val(today);


// Save

function save_evento(argument) {


	            var fechaEvent=$('#fecha_evento').html(); 
	            var usuario = $('#loggedusr').val();
	            var creado = today;
	            var evento= $('#evento').val();

	            if (evento=="") {
	            	return;
	            }

				
			   	let  url ;
			   // 	let factura=document.getElementById('numero').value
		     	let params = 'q=saveevent'+'&fechaEvent='+fechaEvent+'&usuario='+usuario+'&creado='+creado+'&evento='+evento; //+'&amp;pwd='+userPwd
		        url='../../handler/EventosHandler.php';
		      	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
    Swal.fire({
   		type: 'success',
   	 	title: "Nice!" ,
   	 	showConfirmButton: true
 	 })    
       location.reload();
		        }   
		      } 	
	// body...
}

function load_events(fecha_evento){
	            var fechaEvent=$('#fecha_evento').html(); 
	            var usuario = $('#loggedusr').val();
	            var creado = today;
	            var evento= $('#evento').val();

				$('#display-events').hide()
			   	let  url ;
			   // 	let factura=document.getElementById('numero').value
		     	let params = 'q=loadevent'+'&fecha_evento='+fecha_evento; //+'&amp;pwd='+userPwd
		        url='../../handler/EventosHandler.php';
		      	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){  

		       	 	let dat;
		       	    dat= this.responseText;
		       	    dat= JSON.parse(dat);

                 
		       	    // 
		       	    if (dat.length>0) {
		       	    	$('#display-events').show()
		       	     for (var i = 0; i < dat.length; i++) {

		       	       var html = '';  
  var cont=0;
  for(var i = 0; i < dat.length; i++){   
      cont=i+1;
      html += '<tr id='+dat[i].id+' ><td align="center" >' + cont    +'</td>'+
              '<td align="left" >' + dat[i].fecha_creado     +'</td>'+
              '<td align="left" >' + dat[i].evento   +'</td>'+ 
              '<td align="left" >' + dat[i].usuario  +'</td>'+ 
              '<td align="center" ><button type="button"  class="btn btn-default btn_remove form-control" id='+dat[i].id+' value="Del"><i class="fas fa-trash-alt"></i>Borrar</button></td>'+                


              '</tr>';
  }
  $("#table_eventos").html(html);	


					 } 
					 }

		       	    // 
  
		        }   
		      } 
}

	//-------------------------------------------------------------------------
	$(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   


           Swal.fire({
  title: 'Are you sure?',
  text: "You won't be able to revert this!",
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Yes, delete it!'
}).then((result) => {
  if (result.value) {

  $('#'+button_id+'').remove();  

	  		let  url ;
	  		let params = 'q=del&id='+button_id; //+'&amp;pwd='+userPwd	
		    url='../../handler/EventosHandler.php';
		    var api = new  XMLHttpRequest();
		      	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){

		      Swal.fire(
      'Deleted!',
      'Your file has been deleted.',
      'success'
    )

		      } 
   location.reload();

  }
})        



    });  


    //---------------------------------------------------------------------------------------------------------------
$('.titulo').change(function() {
  let titulo = $('.titulo').prop('checked');
  if (!titulo) {
  	$('.display-evento').show()
  }else{
  	$('.display-evento').hide()
  }
}) 
