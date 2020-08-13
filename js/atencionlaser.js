
$(function(){
  // Get the modal  
  var modal = document.getElementById("terapiasModal");

  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');

if($("#fecha").val()==""){
   $("#fecha").val(hoy);
} 

$('[data-toggle="tooltip"]').tooltip()
// 'use strict'
$("#wait").hide(); 
$('#xtitulo').html('<b>TERAPIAS CONTROL DE CITAS</b>');

 $('#timepicker1').timepicker();

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


// maneja el modal
$('#app > tbody > tr').dblclick(function(e){
    
  // When the user clicks the button, open the modal 
  modal.style.display = "block";



  // limpia el table
  $("#tb-terapias > tr").remove();
  

  let targ= e.target.closest('.t-data').id.split("-"); 

  var fecha=targ[0].substring(1,3)+"/"+targ[1]+"/"+targ[2];
  var hora =targ[3];
  let localtime = hora
  
  //muestra la fecha y la hora en la ventana modal
  if (hora>12) {
     localtime =hora-12; 
     localtime=localtime+":00 pm"
  }else if (hora<10) {
     localtime="0"+localtime+":00 am"
  }else if (hora>=10  && hora <=11) {
    localtime=localtime+":00 am"
  }else{
     localtime=localtime+":00 m"
  }
  document.getElementById('modal-titulo').textContent='Pacientes citados '+fecha+' - '+localtime

  var en_buscar_class="";
  var idpaciente= document.getElementById('idassoc').value

 
    
 

  var params = 'fecha='+fecha+'&hora='+hora;
  url='../../handler/TerapiasHandler.php';
  var api = new  XMLHttpRequest();
  api.open('POST',url,true);
  api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
  api.send(params);
  api.onreadystatechange = function(){
    if(this.readyState === 4 && this.status === 200){
        //Swal.fire('Nice!!!')
        console.log(this.responseText);
        const obj = JSON.parse(this.responseText);                 
        console.log(obj);
        if (obj.length>0) {
          for (var i = 0; i < obj.length; i++) {

            en_buscar_class="";
            if (obj[i].codclien==idpaciente) {
               en_buscar_class='en-buscar';
             }
            
            $('#tb-terapias').append("<tr class='"+en_buscar_class+"'  >"+

              "<td class='' >"+obj[i].Historia+"</td>"+
              "<td class='' >"+obj[i].nombres+"</td>"+
              "<td class='' >"+obj[i].descons+"</td>"+
              "<td class='' >"+obj[i].mls+"</td>"+
              "<td class='' >"+obj[i].hilt+"</td>"+
              "<td class='' >"+obj[i].fecha+"</td>"+
              "<td class='' >"+obj[i].timeapp+"</td>"+
              
              "</tr>");
          }

        }

    }   
  }

});

//--------------------------------------^
  
// -------------------------------------^
//start modal
// Get the button that opens the modal
var btn = document.getElementById("myBtn");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
// When the user clicks the button, open the modal 
// btn.onclick = function() { 
// }
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
//end modal


//doble click en el TH citar
var chckCitar= document.getElementById('th-citar');
chckCitar.ondblclick = function(e){
 var x = document.getElementById("tb-disponible").rows.length;
 var chk=document.getElementsByClassName('td-disp-chk');// [i].children[0].checked=true  
 console.log(x)
 var arl=x;
 for (var i = 1; i < x; i++) {
     chk[i-1].children[0].children[0].checked=!(chk[i-1].children[0].children[0].checked);

     if (chk[i-1].previousElementSibling.textContent=="INTRA") {
        document.getElementById('getintra').checked=chk[i-1].children[0].children[0].checked
     }


     if (chk[i-1].previousElementSibling.textContent=="HILT") {
        let gethilt = document.getElementById('gethilt'); 
        if (chk[i-1].children[0].children[0].checked==true) {
           if (gethilt.value.trim()=="" || gethilt.value==0) {
               gethilt.value=1;
           }
           
        }else{
           gethilt.value=0;
       }
     }

      if (chk[i-1].previousElementSibling.textContent=="MLS") {
      let getmls = document.getElementById('getmls'); 
      if (chk[i-1].children[0].children[0].checked==true) {
          if (getmls.value.trim()=="" || getmls.value==0) {
              getmls.value=1;
          }
          
      }else{
          getmls.value=0;
      }
  }

     // if (chk[i-1].previousElementSibling.children[0].value=="INTRA") {
     //    document.getElementById('getintra').checked=chk[i-1].children[0].children[0].checked
     // }

     // if (chk[i-1].previousElementSibling.children[0].value=="VITC") {
     //    document.getElementById('gevitc').checked=chk[i-1].children[0].children[0].checked 
     // }
     

     if (chk[i-1].previousElementSibling.textContent=="VITC") {
        document.getElementById('gevitc').checked=chk[i-1].children[0].children[0].checked
     }

 }
}

 // click sobre el checkbox agregando  addEventListener  por clase
var td_chk = document.getElementsByClassName("td-chk");


/**
e.target.parentNode.parentNode.parentNode.getElementsByClassName('laser-cant')[0].children[0]
e.target.parentNode.parentNode.parentNode.getElementsByClassName('laser-cant')[0].children[0].value=7
*/

var myFunction = function(e) {

   let getnumtera = e.target.parentNode.parentNode.parentNode.getElementsByClassName('laser-cant')[0].children[0];
   if (e.target.checked==true) {
      if (getnumtera.value.trim()=="" || getnumtera.value==0) {
      	  //1ra parte cuando el check esta activado color por defaul un 1 en la cantidad 
          getnumtera.value=1;

          //2da parte verifica que exitan dias checked para  agregar las citas

          let codclien  = document.getElementById('idassoc').value;
          let codmedico = document.getElementById('medico').value
          let cantidad = getnumtera.value;

          let coditems = e.target.value.split('-')[0];
          let numfactu = e.target.value.split('-')[1];
          let clase    = e.target.value.split('-')[2];         

          let disponible = e.target.parentNode.parentNode.parentNode.getElementsByClassName('td-disponi')[0].textContent;
          let compra = e.target.parentNode.parentNode.parentNode.getElementsByClassName('td-compra')[0].textContent;

          var arrsave = [];

          var chk_cita_cal= document.getElementsByClassName('prog-chk');
		  for (var i = 0; i < chk_cita_cal.length; i++) {
		  	if (chk_cita_cal[i].checked) {

		  		let cita_dat= chk_cita_cal[i].value.trim();
		  		let fecha = cita_dat.split('-')[0]+'-'+cita_dat.split('-')[1]+'-'+cita_dat.split('-')[2];
          		let hora = cita_dat.split('-')[3];

          	    var arr = { 
	               'codclien': codclien, 
	               'codmedico': codmedico,
	               'cantidad': cantidad,
	               'coditems': coditems,
	               'numfactu': numfactu,
	               'clase': clase,
	               'fecha': fecha,
	               'hora': hora,
	               'disponible': disponible,
	               'compra':compra
	            }

          		arrsave.push(arr)
            }		    	
		  }
		  const obj =  JSON.stringify(arrsave)
		  var params = 'array='+obj ;
		  url='../../handler/TerapiasHandler.php';
		  var api = new  XMLHttpRequest();
		  api.open('POST',url,true);
		  api.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
		  api.send(params);
		  api.onreadystatechange = function(){
		    if(this.readyState === 4 && this.status === 200){
		        //Swal.fire('Nice!!!')
		        console.log(this.responseText);
		    }   
		  } 
      }
   }else{
   	  // 1ra Parte  si se desmarca el chk la cantida se cola en 0
      getnumtera.value=0;

 
      //2da Parte Elimina todas la citas para el item en particular
      let codclien  = document.getElementById('idassoc').value;
  	  let coditems =  e.target.value.split('-')[0];

  	  var arrsave = [];
 	  var arr = { 
               'codclien': codclien,
               'coditems': coditems
            };

      arrsave.push(arr);
      var obj =  JSON.stringify(arrsave);
      var params = 'delitemappmnt='+obj ;
	  url='../../handler/TerapiasHandler.php';
	  var api = new  XMLHttpRequest();
	  api.open('POST',url,true);
	  api.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
	  api.send(params);
	  api.onreadystatechange = function(){
	    if(this.readyState === 4 && this.status === 200){
	        //Swal.fire('Nice!!!')
	        let n_citas = JSON.parse(this.responseText);      
	        console.log(this.responseText);

	       	let x = document.getElementById("tb-disponible").rows.length;
	        let chk=document.getElementsByClassName('td-disp-chk');
	   
	        if (n_citas[0].n_citas=="0") {

	            // quita los checks de los items y coloca en o las cantidades en el table de disponibles	
	            for (var i = 1; i < x; i++) {
	    	      if (chk[i-1].children[0].children[0].checked) { 
	    	      		chk[i-1].children[0].children[0].checked=false;
	    	      		chk[i-1].parentNode.getElementsByClassName('terapiasn')[0].value=0;
	    	       	} 
	    	    }

	    	    // quita los checks del calendario
	    	    var chk_cita_cal= document.getElementsByClassName('prog-chk');
		  		for (var i = 0; i < chk_cita_cal.length; i++) {
		  			if (chk_cita_cal[i].checked) {
                       chk_cita_cal[i].checked=false;
		  			}
		  		}
	       }  
	    }   
	  }
   }

};

for (var i = 0; i < td_chk.length; i++) {
    td_chk[i].addEventListener('click', myFunction, false);
}

//

// selecion de fecha de la cita pendiente
var fecha_cur_cita= document.getElementById('fecha-cur-cita');

fecha_cur_cita.addEventListener("change", fecha_selected);
function fecha_selected(e) {
//  var elem = document.querySelector('#fecha-cur-cita');
// elem.style.display = 'none';  
  document.getElementById("formterapias").submit();
}

//

// cambio de medico cuando existe mas de un paciente viculado a un mimo numero de telefono
var paciente = document.getElementById('idassoc');
paciente.addEventListener("change", paciente_selected)
function paciente_selected(e){

  // obtengo el medico asignado al paciente guaradado en atributo personalizado llamado medico de select opton de pacientes
  var med=e.target.options[e.target.selectedIndex].attributes.medico.value;

  //obtego el medico seleccionado del select option  de medicos (lista de medicos)
  var selmed = document.getElementById("medico");
  // elimino el atributo selected
  selmed.options[selmed.selectedIndex].removeAttribute('selected')

  //asigo al select options de medico el medico asigano al paciente 
  document.getElementById('medico').value=med;

  // itero sobre el array de medicos
  for (var i = 0; i < selmed.length; i++) {
    if (selmed[i].value==med) {
       document.getElementById('medico').options[i].setAttribute('selected','selected')       
       break;
    }  
  }
}

// 

// click en el chk de INTRA
 // var getintra = document.getElementById('getintra');

 // getintra.addEventListener("change", clickIntra );
 // function clickIntra(e){
 //  var valor=document.querySelector('#getintra').checked;
 //  var x = document.getElementById("tb-disponible").rows.length;
 //  var chk=document.getElementsByClassName('td-disp-chk');
 //  for (var i = 1; i < x; i++) {
 //      if (chk[i-1].previousElementSibling.textContent=="INTRA"){
 //          chk[i-1].children[0].children[0].checked=valor;
 //      }   
 //  }

 // }

// click en el chk de VITC 
// var getvitc = document.getElementById('gevitc');
// getvitc.addEventListener("change", clickvitc);


// function clickvitc(e){
//     var valor=document.querySelector('#gevitc').checked;
//     var x = document.getElementById("tb-disponible").rows.length;
//     var chk=document.getElementsByClassName('td-disp-chk');

//     for (var i = 1; i < x; i++) {
//       if (chk[i-1].previousElementSibling.textContent=="VITC"){
//           chk[i-1].children[0].children[0].checked=valor;
//       }   
//     }
// }

//

// var gethilt = document.getElementById('gethilt'); 
// gethilt.addEventListener("change", hilt_changed);
// function hilt_changed(e){
//     var x = document.getElementById("tb-disponible").rows.length;
//     var chk=document.getElementsByClassName('td-disp-chk');
//     for (var i = 1; i < x; i++) {
//       if (chk[i-1].previousElementSibling.textContent=="HILT"){
//         if ( gethilt.value.trim()=="" ||  gethilt.value<=0 ){
//            chk[i-1].children[0].children[0].checked=false;
//         }else{

//           chk[i-1].children[0].children[0].checked=true;
//         }
//       }   
//     }
//    console.log(gethilt.value)
// }
// //
// var getmls = document.getElementById('getmls'); 
// getmls.addEventListener("change", mls_changed);
// function mls_changed(e){
//     var x = document.getElementById("tb-disponible").rows.length;
//     var chk=document.getElementsByClassName('td-disp-chk');
//     for (var i = 1; i < x; i++) {
//       if (chk[i-1].previousElementSibling.textContent=="MLS"){
//         if ( getmls.value.trim()=="" ||  getmls.value<=0 ){
//            chk[i-1].children[0].children[0].checked=false;
//         }else{

//           chk[i-1].children[0].children[0].checked=true;
//         }
//       }   
//     }
//    console.log(getmls.value)
// }

//---------------------------------------------------------------------------

// lunes click sobre el checkbox agregando  addEventListener  por clase
var mon_chk=document.getElementsByClassName('mon-chk');
for (var i = 0; i < mon_chk.length; i++) {
    mon_chk[i].addEventListener('click', chk_uchk_fun, false);
}

//miercoles click sobre el checkbox agregando  addEventListener  por clase
var tue_chk=document.getElementsByClassName('tue-chk');
for (var i = 0; i < tue_chk.length; i++) {
    tue_chk[i].addEventListener('click', chk_uchk_fun, false);
}

//miercoles click sobre el checkbox agregando  addEventListener  por clase
var wed_chk=document.getElementsByClassName('wed-chk');
for (var i = 0; i < wed_chk.length; i++) {
    wed_chk[i].addEventListener('click', chk_uchk_fun, false);
}

//jueves click sobre el checkbox agregando  addEventListener  por clase
var thu_chk=document.getElementsByClassName('thu-chk');
for (var i = 0; i < thu_chk.length; i++) {
    thu_chk[i].addEventListener('click', chk_uchk_fun, false);
}

//viernes click sobre el checkbox agregando  addEventListener  por clase
var fri_chk=document.getElementsByClassName('fri-chk');
for (var i = 0; i < fri_chk.length; i++) {
    fri_chk[i].addEventListener('click', chk_uchk_fun, false);
}


//sabado click sobre el checkbox agregando  addEventListener  por clase
var sat_chk=document.getElementsByClassName('sat-chk');
for (var i = 0; i < sat_chk.length; i++) {
    sat_chk[i].addEventListener('click', chk_uchk_fun, false);
}


function chk_uchk_fun(e){
  var cls_name=e.target.className.split(" ")[1];
  var cls_chk=document.getElementsByClassName(cls_name);
  
 // tomo el color del elemento con la pseudo clase before
  var color = window.getComputedStyle(  e.target.nextElementSibling, ':before' ).getPropertyValue('color')

  var target=true;
  if (!e.target.checked) {
      target=false;
  }

  for(var i = 0; i < cls_chk.length; i++){
        cls_chk[i].checked = false
  } 

  if (target) {
    e.target.checked=true 
  }
  
  if (e.target.checked) {

    var arrsave = [];
    let codclien  = document.getElementById('idassoc').value;
    let codmedico = document.getElementById('medico').value

    let x = document.getElementById("tb-disponible").rows.length;
    let chk=document.getElementsByClassName('td-disp-chk');
    for (var i = 1; i < x; i++) {
       if (chk[i-1].children[0].children[0].checked) {
          
          let cantidad = chk[i-1].parentNode.getElementsByClassName('laser-cant')[0].children[0].value;
          let coditems =  chk[i-1].children[0].children[0].value.split('-')[0]
          let numfactu = chk[i-1].children[0].children[0].value.split('-')[1]
          let clase = chk[i-1].children[0].children[0].value.split('-')[2];

          let fecha = e.target.value.split('-')[0]+'-'+e.target.value.split('-')[1]+'-'+e.target.value.split('-')[2];
          let hora = e.target.value.split('-')[3];

          let disponible = chk[i-1].parentNode.getElementsByClassName('td-disponi')[0].textContent;
          let compra = chk[i-1].parentNode.getElementsByClassName('td-compra')[0].textContent;

          var arr = { 
               'codclien': codclien, 
               'codmedico': codmedico,
               'cantidad': cantidad,
               'coditems': coditems,
               'numfactu': numfactu,
               'clase': clase,
               'fecha': fecha,
               'hora': hora,
               'disponible': disponible,
               'compra':compra
            }

          arrsave.push(arr)
      
       }
        
   }
  
  const obj =  JSON.stringify(arrsave)
  var params = 'array='+obj ;
  url='../../handler/TerapiasHandler.php';
  var api = new  XMLHttpRequest();
  api.open('POST',url,true);
  api.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
  api.send(params);
  api.onreadystatechange = function(){
    if(this.readyState === 4 && this.status === 200){
        //Swal.fire('Nice!!!')
        console.log(this.responseText);
    }   
  }


    
  }

   if (!e.target.checked)  {
    var arrsave = [];
    let codclien  = document.getElementById('idassoc').value;
    let codmedico = document.getElementById('medico').value

    let x = document.getElementById("tb-disponible").rows.length;
    let chk=document.getElementsByClassName('td-disp-chk');
    for (var i = 1; i < x; i++) {
       if (chk[i-1].children[0].children[0].checked) {
          
          let cantidad = chk[i-1].parentNode.getElementsByClassName('laser-cant')[0].children[0].value;
          let coditems =  chk[i-1].children[0].children[0].value.split('-')[0]
          let numfactu = chk[i-1].children[0].children[0].value.split('-')[1]
          let clase = chk[i-1].children[0].children[0].value.split('-')[2];

          let fecha = e.target.value.split('-')[0]+'-'+e.target.value.split('-')[1]+'-'+e.target.value.split('-')[2];
          let hora = e.target.value.split('-')[3];

          let disponible = chk[i-1].parentNode.getElementsByClassName('td-disponi')[0].textContent;
          let compra = chk[i-1].parentNode.getElementsByClassName('td-compra')[0].textContent;

          var arr = { 
               'codclien': codclien, 
               'codmedico': codmedico,
               'cantidad': cantidad,
               'coditems': coditems,
               'numfactu': numfactu,
               'clase': clase,
               'fecha': fecha,
               'hora': hora,
               'disponible': disponible,
               'compra':compra
            }

          arrsave.push(arr)
      
       }
        
   }
  
  const obj =  JSON.stringify(arrsave)
  var params = 'delappmnt='+obj ;
  url='../../handler/TerapiasHandler.php';
  var api = new  XMLHttpRequest();
  api.open('POST',url,true);
  api.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
  api.send(params);
  api.onreadystatechange = function(){
    if(this.readyState === 4 && this.status === 200){
        //Swal.fire('Nice!!!')
        let n_citas = JSON.parse(this.responseText);      
        console.log(this.responseText);

       	let x = document.getElementById("tb-disponible").rows.length;
        let chk=document.getElementsByClassName('td-disp-chk');
   
        if (n_citas[0].n_citas=="0") {
           for (var i = 1; i < x; i++) {
    	      if (chk[i-1].children[0].children[0].checked) { 
    	      		chk[i-1].children[0].children[0].checked=false;
    	      		chk[i-1].parentNode.getElementsByClassName('terapiasn')[0].value=0;
    	       	} 
    	    }      
       }  
    }   
  }

      

   }
}


//----------------------------------------------------------------------------

// cuando hay cambios en las cantidades de terapias evento change por clases


var terapiasn= document.getElementsByClassName('terapiasn');

for (var i = 0; i < terapiasn.length; i++) {
    terapiasn[i].addEventListener('change', terapiasn_change, false);
}

function terapiasn_change(e){

  if (parseInt( e.target.value) >0) {
    e.target.parentNode.parentNode.getElementsByClassName('td-disp-chk')[0].children[0].children[0].checked=true;
  }else{
    e.target.parentNode.parentNode.getElementsByClassName('td-disp-chk')[0].children[0].children[0].checked=false; 
 }
  
}




//-fin
})


/*
<script type="application/x-javascript">
      function ChangeColor() {
         var btn = document.getElementById('myBtn');
         btn.className = "changed";
      };
</script>

*/