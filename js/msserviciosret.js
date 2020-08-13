 var productos;
 var listprice;
 $(document).ready(function(){  

 	 var ii=0;

 	 if (document.getElementById('numero').value.trim()=="") {
 	 	 document.getElementById("btnpaymnt").setAttribute("readonly", true)
 	 }
  
     loadMedicos('');
     setMedios();
     if ($('#fecha').val()=='' ) {
         $('#fecha').val(toDay());
     }
    
     loadOptions("productos"); //  PRODUCTOS
     loadOptions("lp"); 	   //  LISTA DE PRECIOS

	$('#idpaciente').on('keydown', function(e) {
		if (e.keyCode == 13) {
				var id=$('#idpaciente').val();		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&patient=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsserviciosRetHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
		       	   	let dat;
		       	    dat= this.responseText;
		       	    dat= JSON.parse(dat);
		       	    let codmd= dat[0].codmedico;
					 let  options="";
					 let codmedico='000';
					    for (var i = 0; i < dat.length; i++) {    
					        if (dat[i].codmedico!=undefined && dat[i].codmedico!='') {
					            codmedico=dat[i].codmedico;
					        }else{
					           codmedico='000';
					        }; 
					        options+="<option  cdmedico="+codmedico+" value='"+dat[i].codclien+"'>"+dat[i].nombres+"</option>";  
					    }      
					   $("#idassoc").html(options);		
					   document.getElementById('medico').value=codmd;     	    
		        }   
		     } 	
		}         
	     
	});

	//-------------------------------------------------------------------------
	function loadMedicos(codMedOculto){
				let  url ;
		        let  options="";
		        let md="";
		        if ( $('#md').val()!='' ) {
		        	 md=$('#md').val();
		        }
		       	url='../../clases/medicos.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send();
		       	api.onreadystatechange = function(){
		       	
		       	if(this.readyState === 4 && this.status === 200){            
		       	   	let dat;
		       	    dat= this.responseText;
		       	    dat= JSON.parse(dat);
		       	    for (var i = 0; i < dat.length; i++) {	  
		       	    if (dat[i].codmedico==md) {
		       	    	options+="<option selected value='"+dat[i].codmedico+"'>"+dat[i].medico+"</option>"; 
		       	    } else  {
		       	    	options+="<option value='"+dat[i].codmedico+"'>"+dat[i].medico+"</option>"; 
		       	    }       		
	                  	            		
	                	 	              		
	        		}
	          		$("#medico").html(options); 		       	    
		        	}   
				}
	}				

	//-------------------------------------------------------------------------
	function setMedios(){		
				let  url ;
		        let  options="";
		        let media="";
		        if ( $('#medio').val()!='0' ) {
		        	 media=$('#medio').val();
		        }
		        let params = 'media=y'; //+'&amp;pwd='+userPwd
		        url='../../handler/MsserviciosRetHandler.php';
		       //	url='../../clases/medios.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	
		       	if(this.readyState === 4 && this.status === 200){            
		       	   	let dat;
		       	    dat= this.responseText;
		       	    dat= JSON.parse(dat);
		       	    for (var i = 0; i < dat.length; i++) {	
		       	        if (dat[i].codigo==media) {
		       	        	options+="<option selected value='"+dat[i].codigo+"'>"+dat[i].medio+"</option>"; 
		       	        }else{        		
		       	        	options+="<option value='"+dat[i].codigo+"'>"+dat[i].medio+"</option>"; 
		                }
	        		}
	          		$("#medio").html(options);
		        	}   
				}
	}
	//-------------------------------------------------------------------------
	function toDay(){
	      var today = new Date();
	      var dd = today.getDate();
	      var mm = today.getMonth()+1; //January is 0!

	      var yyyy = today.getFullYear();
	      if(dd<10){
	         dd='0'+dd;
	      } 
	      if(mm<10){
	          mm='0'+mm;
	      } 
	      var today = mm+'/'+dd+'/'+yyyy;
	      return today;    
	     
    }

	//-------------------------------------------------------------------------
	$('#add').click(function(){ 
           ii++;  
           var idpro="serv"+ii;
           var idtpr="tpre"+ii;
           var idseg="tseg"+ii;
           var iddes="descuento"+ii;
           var tax ="tax"+ii;
           var id_precio=makeid();
           var id_tr=makeid();

           $('#dynamic_field').append('<tr id="'+id_tr+'">'+
               ' <td><select id="'+idpro+'" name="producto[]"        class="form-control service enterpass enterkey" > <option value="" selected ></option></select> <input type="hidden"  id="coditems'+ii+'"   name="coditems[]"  value="" class="coditems" /></td>'+
               ' <td><select id="'+idtpr+'" name="listaprecio[]" class="form-control enterkey pricelist">          <option value="" selected ></option></select> <input type="hidden"  id="codprecio1'+ii+'" name="codprecio[]" value="" class="codprecio" /></td>'+    
               ' <td class="qty"><input type="text" name="cantidad[]" value="1" pattern="^[0-9]+([0-9]+)?$"   style="text-align:center;"placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" autocomplete="off" /></td>'+
               ' <td><input type="text" name="precio[]" id="'+id_precio+'"   style="text-align:right;" placeholder="precio" class="form-control precio enterkey" autocomplete="off" /></td>'+     
               ' <td><input type="text" name="descuento[]"  readonly="readonly" id="'+iddes+'"  style="text-align:right;" placeholder="Descuento" class="form-control " /> <input type="hidden"  id="detaialprcnt"'+iddes+'" name="detaialprcnt[]" value="" class="detaialprcnt" /></td>'+                
               ' <td><input type="text" name="Subtotal[]"  readonly="readonly"  style="text-align:right;" placeholder="Subtotal" class="form-control name_list subtotal" /></td>'+
               ' <td><button type="button" name="remove" id="'+id_tr+'" class="btn btn-danger btn_remove">X</button></td>'+
               ' </tr>');  

           setProductos(idpro);
           setPriceList(idtpr);           
           $('#'+idpro).focus();

      }); 

	//-------------------------------------------------------------------------
	function makeid() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
	}
	//-------------------------------------------------------------------------
	$(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           
           
           let row_index = $(this).parent().parent().index();
           let id_product= $("select[name='producto[]']")[row_index].value;
           let factura = document.getElementById('numero').value;

           $('#'+button_id+'').remove();  
           totales();	

           var form = document.querySelector('#facturacion');
		   var formData = serialize(form);

	  		let  url ;
	  		//let params = 'del=1&coditems='+id_product+'&factura='+factura; //+'&amp;pwd='+userPwd	
	  		let params = formData+'&del=1&coditems='+id_product+'&factura='+factura; //+'&amp;pwd='+userPwd	            	
	       	url='../../handler/MsserviciosRetHandler.php';
	       	var api = new  XMLHttpRequest();
	       	api.open('POST',url,true);
	       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	       	api.send(params);
	       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){
	       				
           				//document.getElementById('savei').style.display = '';	       	    		       	    	 
	     		} 	
	     	}
            document.getElementById('savei').style.display = '';  
    });  
	//-------------------------------------------------------------------------
	function loadOptions(id){
			   	let  url ;
		       	let params = 'q=' +id; //+'&amp;pwd='+userPwd
		       	url='../../handler/MsserviciosRetHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){
		       		if (id=="productos") {
		       			productos= JSON.parse(this.responseText);		       			
		       		}else if(id=="lp"){
		       			listprice= JSON.parse(this.responseText);
		       		}		       	    		       	    	 
		     	} 	
		     }
	}

	//-------------------------------------------------------------------------
	function setProductos(id){
		    var options;
     		options+="<option value=''>Elija Producto</option>"; 
     		for (var j = 0; j < productos.length; j++) { 
         		options+="<option value='"+productos[j].coditems+"'>"+productos[j].desitems+"</option>"; 
     		}
     		$("#"+id).html(options);
	}
	//-------------------------------------------------------------------------
	function setPriceList(id){
			var options;     		
     		for (var j = 0; j < listprice.length; j++) { 
         		options+="<option value='"+listprice[j].codtipre+"'>"+listprice[j].destipre+"</option>"; 
     		}
     		$("#"+id).html(options);
	}
	//-------------------------------------------------------------------------
	$('#dynamic_field').on('change', '.service', function(event) {		 

		        let row_index = $(this).parent().parent().index();		 		
		 		let id=$(this).val();
		 		var qty= $("input[name='cantidad[]']")[row_index].value;
		 		let pl = $("select[name='listaprecio[]']")[row_index].value;

                optionsChanged(id,pl,row_index);
                getExclusivo(id);
 
	})
	//-------------------------------------------------------------------------
	$('#discprcntg').change(function(){

		let porcentaje= parseFloat( $('#discprcntg').val() );

		if (porcentaje>0) {			
			 document.getElementById('discamount').readOnly = true
		}else{
			document.getElementById('discamount').readOnly = false
			document.getElementById('discamount').value='';
 			document.getElementById('discprcntg').value='';
		}
		 totales();  
		 document.getElementById('savei').style.display = '';    
  	})
	//-------------------------------------------------------------------------
	$('#discamount').change(function(){
		let mountdesc= parseFloat( $('#discamount').val() );
		if (mountdesc>0) {
			document.getElementById('discprcntg').readOnly = true
		}else{
		    document.getElementById('discprcntg').readOnly = false
		    document.getElementById('discamount').value='';
 			document.getElementById('discprcntg').value='';	
		}
		totales();
		document.getElementById('savei').style.display = '';
	})
	//-------------------------------------------------------------------------
	function totales(){

		//0.00
		if(!$('#discamount').is('[readonly]')){

			let subto = document.getElementById('tlsubototal').value
			let monto = document.getElementById('discamount').value
			porcn =0;
			if (subto!=0  && monto!=0) {
				let porcn = (monto*100)  / subto ;
			}
			

			if (!isNaN(porcn)) {
				document.getElementById('discprcntg').value=porcn	
			}

            
		}


				       var a_servicios= $('.service')
		       		   var total=0;
		       		   var subtotal=0;
		       		   var descuento=0
		       		   if (a_servicios.length>0) {

		       			for (var i = 0; i < a_servicios.length; i++) {
		       				 cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
              				 precio = parseFloat( $("input[name='precio[]']")[i].value ) ;
              				


              				 let porcentdesc = parseFloat( $('#discprcntg').val() );
              				 let descuentou=0;

              				 subtotal+= (cantidad*precio)
              				 if ( porcentdesc>0 ) {
              				 	descuentou = ((cantidad*precio)*porcentdesc)/100
              				 	total += (cantidad*precio)-descuentou
              				 	descuento +=descuentou;              				 	
              				 	$("input[name='descuento[]']")[i].value= parseFloat( descuentou ).toFixed(2) ;
              				 	$("input[name='Subtotal[]']")[i].value= parseFloat(  ((cantidad*precio)-descuentou) ).toFixed(2) ;
              				 }else{
              				 	 total += cantidad*precio
              				 	$("input[name='descuento[]']")[i].value=descuentou ;
              				 	$("input[name='Subtotal[]']")[i].value= (cantidad*precio)
              				 }

              				 
              				 $('#discamount').val(descuento);

              				 $('#tlsubototal').val(subtotal);
              				 $('#tltotal').val(total);
		       			}
		       		}else{
		       			 $('#tlsubototal').val('');
              			 $('#tltotal').val('');
              			 document.getElementById('discamount').value='';
 						 document.getElementById('discprcntg').value='';
		       		}

	}
	//-------------------------------------------------------------------------
	$('#dynamic_field').on('change', '.cantidad ', function(event) {
			totales();
			document.getElementById('savei').style.display = '';
	})  
	
	//-------------------------------------------------------------------------
	 $('#dynamic_field').on('change', '.precio', function(event) {
			totales();
			document.getElementById('savei').style.display = '';
	})  
	//-------------------------------------------------------------------------
	$('#numero').on('keydown', function(e) {
		 if (e.keyCode == 13) {
			$('#edicion').val("true") 
            let factura=document.getElementById('numero').value
            window.location.href += "?edicion=true&numero="+factura;                
		  }
	})
	
	//-------------------------------------------------------------------------
	$(".delete-row").click(function(){
	
			let longArtr = $("#dynamic_field tbody").find('select[name="producto[]"]').length;
	        if (longArtr>=1) {
	            for (var i = 1; i <= longArtr; i++) { 

	                  try {                                            
	                            let producto  =   $("#dynamic_field tbody").find('select[name="producto[]"]').val();
	                            let cantidad  = $("#dynamic_field tbody").find('input[name="cantidad[]"]').val()
	                            //$("#dynamic_field tbody").find('input[name="cantidad[]"]')[0].value;
	                           
	                            $('#'+$("#dynamic_field tbody").find('select[name="producto[]"]').parent().parent().attr('id') ).remove();                                               
	                  }
	                catch(err) {

	                }

	            };
	         }


			totales();	
    
		   var button_id = $(this).attr("id");   
		   var form = document.querySelector('#facturacion');
		   var formData = serialize(form);
           
           let row_index = $(this).parent().parent().index();
           
           let factura = document.getElementById('numero').value;
    
	  		let  url ;
	  		 
	  		let params = formData+'&del=2&factura='+factura; //+'&amp;pwd='+userPwd	           	
	       	url='../../handler/MsserviciosRetHandler.php';
	       	var api = new  XMLHttpRequest();
	       	api.open('POST',url,true);
	       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	       	api.send(params);
	       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){

	     		} 	
	     	}

	 });
	//-------------------------------------------------------------------------
	 $('#savei').click(function(){
        document.getElementById('desxmonto').value=""
	 	if ( !isNaN(parseInt(document.getElementById('discamount').value) ) && parseInt(document.getElementById('discamount').value) >0) {
             if ($('#discamount').is('[readonly]')) {
             		document.getElementById('desxmonto').value="p"
             }else{
             		document.getElementById('desxmonto').value="m"
             }
	 			
	 	}

	 	    document.getElementById('savei').style.display = 'none';
	 		var form = document.querySelector('#facturacion');
			var formData = serialize(form);

	 	    let row_index = $(this).parent().parent().index();
            let factura = document.getElementById('numero').value;
 
	  		let  url ;
	  		let params =  formData+'&save=y&factura='+factura; //+'&amp;pwd='+userPwd	         	
	       	url='../../handler/MsserviciosRetHandler.php';
	       	var api = new  XMLHttpRequest();
	       	api.open('POST',url,true);
	       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	       	api.send(params);
	       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){
	       			 let invoice=JSON.parse(this.responseText);	
	       			 document.getElementById('numero').value=invoice;
	       			 console.log(invoice);
	       			 document.getElementById("btnpaymnt").disabled = false;
	     		} 	
	     	}
	 })
	//-------------------------------------------------------------------------
	var serialize = function (form) {

	// Setup our serialized data
	var serialized = [];

	// Loop through each field in the form
	for (var i = 0; i < form.elements.length; i++) {

		var field = form.elements[i];

		// Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
		if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue;

		// If a multi-select, get all selections
		if (field.type === 'select-multiple') {
			for (var n = 0; n < field.options.length; n++) {
				if (!field.options[n].selected) continue;
				serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[n].value));
			}
		}

		// Convert field data to a query string
		else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
			serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value));
		}
	}

	return serialized.join('&');

  };
	//-------------------------------------------------------------------------
	$('#dynamic_field').on('change', '.pricelist ', function(event) {
		    
		        let row_index = $(this).parent().parent().index();		 		
		 		let id=$("select[name='producto[]']")[row_index].value;
		 		var qty= $("input[name='cantidad[]']")[row_index].value;
		 		let pl = $("select[name='listaprecio[]']")[row_index].value;

                optionsChanged(id,pl,row_index);

	}) 
	
	//-------------------------------------------------------------------------
	function optionsChanged(id,pl,row_index){
				let params = 'id='+id+'&pl='+pl+'&price=y'; //+'&amp;pwd='+userPwd
		       	let url='../../handler/MsserviciosRetHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       		if(this.readyState === 4 && this.status === 200){

		       			let a_precio=JSON.parse(this.responseText);	
		       			if (a_precio==null) {
		       				a_precio=0;
		       			}

		       			let precio = parseFloat( a_precio ).toFixed(2);


		       			$("input[name='precio[]']")[row_index].value=precio;
		       			
		       			$("input[name='Subtotal[]']")[row_index].value= $("input[name='cantidad[]']")[row_index].value*precio;

		       			
                         
		       			var a_servicios= $('.service')
		       			var subtotal=0;
		       			var descuentot=0;

		       			for (var i = 0; i < a_servicios.length; i++) {
		       				 cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
              				 precio = parseFloat( $("input[name='precio[]']")[i].value ) ;
              				 subtotal += cantidad*precio



              				 let porcentdesc = parseFloat( $('#discprcntg').val() );

              				 if ( porcentdesc>0 ) {

              				 	descuentou = ((cantidad*precio)*porcentdesc)/100
              				 	descuentot+=descuentou;

              				 	$("input[name='descuento[]']")[i].value=descuentou ;
              				 }

              				 $('#tlsubototal').val(subtotal);
              				 $('#tltotal').val(subtotal-descuentot);
              				 
		       			}
		       			totales()
		       			document.getElementById('savei').style.display = '';
		     	} 	
		     }

	}
	//-------------------------------------------------------------------------
	$('#medico').change(function(){
				var id=document.getElementById('medico').value;		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&md=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsserviciosRetHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 let invoice=JSON.parse(this.responseText);	
		        }   
		     } 	
	 
  	})
	//-------------------------------------------------------------------------
	$('#medio').change(function(){
				var id=document.getElementById('medio').value;		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&medio=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsserviciosRetHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 let invoice=JSON.parse(this.responseText);	
		        }   
		     } 	
	 
  	})
	//-------------------------------------------------------------------------
	 function getExclusivo(coditems){

	    var excmedico;  
	    var Http = new XMLHttpRequest();
	    var url='../../handler/MsserviciosRetHandler.php';
	    var params = "coditems="+coditems+'&exclusive=y'; 
	    Http.open( "POST", url, true );    
	    Http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

	    Http.onreadystatechange=(e)=>{     
	      if (Http.readyState==4 && Http.status==200) {
	          var res= JSON.parse( Http.responseText ) ;
	          console.log(res);
	          try{
	          	if (res.length>0) {
	          		document.getElementById('medico').value=res[0].Codmedico;
	          		document.getElementById("medico").setAttribute("readonly", true)
	          		//document.getElementById("medico").removeAttribute("readonly")
	          	}	            
	          }catch(err){

	          }
	          
	      }else{
	        console.log(e)
	      }
	      
	    }
	     

	    Http.send(params);
  }	
 
	//-------------------------------------------------------------------------
	$('body').on('keydown', 'input, select, textarea', function(e) {
		    var self = $(this)
		      , form = self.parents('form:eq(0)')
		      , focusable
		      , next
		      ;
		    if (e.keyCode == 13) {
		        focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
		       
		        if(self.hasClass('precio')){
		            next = focusable.eq(4);   //INDICE DEL BOTON ADD MORE
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
	//-------------------------------------------------------------------------
	function removeParam(parameter)	{
		  var url=document.location.href;
		  var urlparts= url.split('?');

		 if (urlparts.length>=2)
		 {
		  var urlBase=urlparts.shift(); 
		  var queryString=urlparts.join("?"); 

		  var prefix = encodeURIComponent(parameter)+'=';
		  var pars = queryString.split(/[&;]/g);
		  for (var i= pars.length; i-->0;)               
		      if (pars[i].lastIndexOf(prefix, 0)!==-1)   
		          pars.splice(i, 1);
		  url = urlBase+'?'+pars.join('&');
		  window.history.pushState('',document.title,url); // added this line to push the new url directly to url bar .

		}
		return url;
	}	
	//-------------------------------------------------------------------------
	
	var getCleanUrl = function(url) {
  		url= url.replace(/#.*$/, '').replace(/\?.*$/, '');
  		 window.history.pushState('',document.title,url)
	};

	getCleanUrl(document.location.href);

	//-------------------------------------------------------------------------
	 if (document.getElementById('numero').value.trim()=="") {
 	 	 document.getElementById("btnpaymnt").setAttribute("readonly", true)
 	 }
  
	//------------------------------------------------------------------------- 
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	

})