 var productos;
 var listprice;
 var taxes;
 var applytax=true;
 var cxu=1; //CAPSULAS POR UNIDAD
 var crrntProduct=null;
 var tax_p=0;
 var type_discnt;
 $(document).ready(function(){  

 	 //ON Start
     $('#xtitulo').html('<b>Productos</b>');
      findGranTotalCliente() ;

     if (document.getElementById('numero').value.trim()!="") {
     	
     	if (parseFloat( document.getElementById('monto-abonado').value)!=0) {
     	   document.getElementById('printfac').style.display = '';	
     	}

     }


 	 var ii=0;
  
     loadMedicos('');
     setMedios();
     if ($('#fecha').val()=='' ) {
        $('#fecha').val(toDay());
     }
    
     loadOptions("productos"); //  PRODUCTOS
     loadOptions("lp"); 	   //  LISTA DE PRECIOS
     loadOptions("taxes"); 	   //  LISTA DE PRECIOS

	$('#idpaciente').on('keydown', function(e) {
		if (e.keyCode == 13) {
				var id=$('#idpaciente').val();		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&patient=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsprodsHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
		       	   	let dat;
		       	   	let codmd="";
		       	    dat= this.responseText;
		       	    dat= JSON.parse(dat);
		       	    try{		       	    
		       	    	 codmd= dat[0].codmedico;
		       	    }catch(err){
                        console.log(err);
		       	    }

					 let options="";
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
					   
					   try{	
					   		document.getElementById('medico').value=codmd; 
					   		 findGranTotalCliente()  
					   }catch(err){
					   		console.log(err);
					   } 

					   // 

						document.getElementById('add').click()
					   //
					   //checkInvoiceToday();
					   //  	    
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
	                  	//	options+="<option selected value='"+dat[i].codmedico+"'>"+dat[i].medico+"</option>";  	              		
	                	 	              		
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
		        url='../../handler/MsprodsHandler.php';
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

           var tax ="tax"+ii;
           var dosis="dosis"+ii;
           var sugerido="sugerido"+ii;

           var idpro="serv"+ii;
           var idtpr="tpre"+ii;
           var idseg="tseg"+ii;
           var iddes="descuento"+ii;
           var tax ="tax"+ii;
           var id_precio=makeid();
           var id_tr=makeid();

           $('#dynamic_field').append('<tr id="'+id_tr+'">'+
               ' <td><select id="'+idpro+'" name="producto[]"        class="form-control service enterpass enterkey" > <option value="" selected ></option></select> <input type="hidden"  id="coditems'+ii+'"   name="coditems[]"  value="" class="coditems" /></td>'+
               ' <td><select id="'+idtpr+'" name="listaprecio[]" class="form-control pricelist">          <option value="" selected ></option></select> <input type="hidden"  id="codprecio1'+ii+'" name="codprecio[]" value="" class="codprecio" /></td>'+    
               ' <td><input type="text" id ='+dosis+' name="dosis[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="Dosis" class="form-control dosis numbersOnly enterpass enterkey" /></td>'+
               ' <td><input type="text" id ='+sugerido+' name="sugerido[]" value="1" pattern="^[0-9]+([0-9]+)?$" placeholder="Cantidad sugeridad" class="form-control sugerido numbersOnly enterpass" readonly /></td>'+
               ' <td class="qty"><input type="text" name="cantidad[]" value="1" pattern="^[0-9]+([0-9]+)?$"   style="text-align:center;"placeholder="cantidad Enter your Name" class="form-control cantidad numbersOnly enterpass enterkey" autocomplete="off" /></td>'+
               ' <td><input type="text" name="precio[]" id="'+id_precio+'"   style="text-align:right;" placeholder="precio" class="form-control precio" readonly autocomplete="off" /></td>'+     
               ' <td><input type="text" name="descuento[]"  readonly="readonly" id="'+iddes+'"  style="text-align:right;" placeholder="Descuento" class="form-control " /> <input type="hidden"  id="detaialprcnt"'+iddes+'" name="detaialprcnt[]" value="" class="detaialprcnt" /></td>'+                
               ' <td><input type="text" name="tax[]" style="text-align:right;" readonly="readonly" id="'+tax+'"  placeholder="Impuesto" class="form-control " /></td>'+
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
	       	url='../../handler/MsprodsHandler.php';
	       	var api = new  XMLHttpRequest();
	       	api.open('POST',url,true);
	       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	       	api.send(params);
	       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){
	       				
           				//document.getElementById('savei').style.display = '';	       	    		       	    	 
	     		} 	
	     	}

    });  
	//-------------------------------------------------------------------------
	function loadOptions(id){
			   	let  url ;
		       	let params = 'q=' +id; //+'&amp;pwd='+userPwd
		       	url='../../handler/MsprodsHandler.php';
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
		       		}else if(id=="taxes"){
                        taxes= JSON.parse(this.responseText);

                        for (var i = 0; i < taxes.length; i++) {
        						tax_p+=parseFloat(taxes[i].Porcentaje);
        				}        				
        				document.getElementById('tax_p').value=tax_p;
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

                cxu =1;
                if (productos[$('option:selected',this).index()-1].CapsulasXUni!=null && productos[$('option:selected',this).index()-1].CapsulasXUni!=0) {
                   cxu =productos[$('option:selected',this).index()-1].CapsulasXUni	
                }

                crrntProduct=productos[$('option:selected',this).index()-1];
               
		        let row_index = $(this).parent().parent().index();		 		
		 		let id=$(this).val();
			 	
		 		var qty= $("input[name='cantidad[]']")[row_index].value;
		 		let pl = $("select[name='listaprecio[]']")[row_index].value;

                optionsChanged(id,pl,row_index);
                getExclusivo(id);
 
	})
	//-------------------------------------------------------------------------
	$('#dynamic_field').on('change', '.dosis ', function(event) {
		    
		        let row_index = $(this).parent().parent().index();	
		        let dosis=$(this).val();
		        //
		 		 if (cxu!=0) {
		 		 	treatmentDays(dosis,cxu,row_index)
		 		 }
		 		
		 		//	 		
		 		let id=$("select[name='producto[]']")[row_index].value;
		 		var qty= $("input[name='cantidad[]']")[row_index].value;
		 		let pl = $("select[name='listaprecio[]']")[row_index].value;

                optionsChanged(id,pl,row_index);

	}) 
	//-------------------------------------------------------------------------
	function treatmentDays(dosis,capsxunit,row_index){
		 var diasTratamiento=30;
              var sugerencia = Math.ceil( (dosis*diasTratamiento)/capsxunit );
              if (sugerencia>0) {
                 $("input[name='sugerido[]']")[row_index].value = sugerencia;
                 $("input[name='cantidad[]']")[row_index].value = sugerencia;
                // $("input[name='dosis[]']")[row_index].value = dosis;   
                 //setGeneralValues(this,row_index,xid);     
              }; 
	}
	//-------------------------------------------------------------------------
	$('#discprcntg').change(function(){
		type_discnt="p";
		 totales();  
		try	{
		   document.getElementById('savei').style.display = '';	
		}catch(e){

		}
  	})
	//-------------------------------------------------------------------------
	$('#discamount').change(function(){
		type_discnt="m";
		totales();
		try	{
		   document.getElementById('savei').style.display = '';	
		}catch(e){

		}
		
	})
	//-------------------------------------------------------------------------
	$('#discprcntg').focus(function(){
	 	this.select();
  	})
	//-------------------------------------------------------------------------
	$('#discamount').focus(function(){
	 	this.select();
  	})
	//-------------------------------------------------------------------------

	function totales(type){

        var a_servicios= $('.service')
		var montoDescontado=0;
		var porcnt=0;
		var subtotal=0;
		var taxaplicable=0;
		var tax_total=0;
		var descuento_total=0

		$('#tlimpuesto').val(parseFloat(0).toFixed(2));



        //PRIMERA ETAPA PARA CALCULAR EL PORCENTJE DE DESCUENTO SI EXISTIERA
		for (var i = 0; i < a_servicios.length; i++) {
			let cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
            let precio = parseFloat( $("input[name='precio[]']")[i].value ) ;

            if (cantidad=="" || isNaN(cantidad))
            	  cantidad=0;
            if (precio=="" || isNaN(precio))
              	  precio=0; 

            subtotal+= (cantidad*precio)  	
            $("input[name='Subtotal[]']")[i].value= parseFloat(  (cantidad*precio) ).toFixed(2) ;
		}


        //SUBTOTAL GENERAL SIN DESCUENTO NI IMPUESTOS
		$('#tlsubototal').val(parseFloat(subtotal).toFixed(2));

		if(type_discnt=="m"){
			let isdescuento = parseFloat(document.getElementById('discamount').value); 
			if (isdescuento !=0 && !isNaN(isdescuento) ) {
					 montoDescontado = document.getElementById('discamount').value	;
					 porcnt = (montoDescontado*100)  / subtotal ; 	    			 
			}			
		}else{
			let isdescuento = document.getElementById('discprcntg').value;
			if (isdescuento !=0 && !isNaN(isdescuento) ) {
				porcnt =isdescuento ; 
			}


		}


		document.getElementById('discamount').value='';
 		document.getElementById('discprcntg').value='';
		if (porcnt!=0) {
			document.getElementById('discamount').value=montoDescontado;
 			document.getElementById('discprcntg').value=porcnt;
		}
        
        // SEGUNDA ETAPA TODOS LOS IMPUESTO APLICABLES SUMADOS
        for (var i = 0; i < taxes.length; i++) {
        	taxaplicable+=parseFloat(taxes[i].Porcentaje);
        }  


        //TERCERA ETAPA TAXES Y DESCUENTOS
        for (var i = 0; i < a_servicios.length; i++) {
        	let cantidad = parseFloat( $("input[name='cantidad[]']")[i].value ) ;
            let precio = parseFloat( $("input[name='precio[]']")[i].value ) ;

            
            if (cantidad=="" || isNaN(cantidad))
            	  cantidad=0;
            if (precio=="" || isNaN(precio))
              	  precio=0; 

            let line_subtotal =  (cantidad*precio);

            // SI HAY DESCUENTO
            let line_descuento = 0;
            let line_subtotalMenosdescuento=line_subtotal;
            let line_tax=0;
            let line_sub_des_tax=0;
            if (porcnt!=0) {
            	 line_descuento = (line_subtotal*porcnt)/100;
            	 descuento_total+=line_descuento
            	 line_subtotalMenosdescuento = (line_subtotal-line_descuento);
            }

            //LINEA DE DESCUENTO
            $("input[name='descuento[]']")[i].value= parseFloat( line_descuento ).toFixed(2) ;

            // SI APLICA TAX PARA TODA LA FACTURA (EN EL FUTURO SE PUEDE HACER PARA QUE SE CALCULE SI O NO SI EL PRODUCTO APLICA O NO IVU)  con este Objeto > [crrntProduct.aplicaIva]
            // EL OBJETO [crrntProduct] TIENE TODOS LOS ATRIBUTOS DE LA TABLA MINVENTARIO
            if (applytax) {
            	line_tax=(line_subtotalMenosdescuento*taxaplicable)/100;
            	line_tax=line_tax=roundTo(line_tax, 2)
            	line_tax=parseFloat(line_tax)
            	tax_total+=parseFloat(line_tax.toFixed(2));
            }

            //LINEA DE TAX
            $("input[name='tax[]']")[i].value=parseFloat(line_tax).toFixed(2) ;

            //LINEA DE SUBTOTAL
            line_sub_des_tax=(line_subtotal-line_descuento+line_tax) 
            $("input[name='Subtotal[]']")[i].value= parseFloat( line_sub_des_tax ).toFixed(2) ;

            //TAX SI ES QUE EXISTIERA
			$('#tlimpuesto').val(parseFloat(tax_total).toFixed(2));

        }

        //CUARTA
        document.getElementById('discamount').value=parseFloat(descuento_total);
        //SUB TOTAL MENOS DESCUENTO SI ES QUE EXISTIERA
		$('#stmdct').val(parseFloat(subtotal-descuento_total).toFixed(2));
		//TOTAL GENERAL
        let shipping= 0;
        if (parseFloat(document.getElementById('shipping').value)!=0 && !isNaN(parseFloat( document.getElementById('shipping').value) ) ) {
        	shipping= parseFloat( document.getElementById('shipping').value );
        }	

        tax_total=parseFloat(tax_total.toFixed(2));

        let total=parseFloat( subtotal-descuento_total+tax_total+shipping ).toFixed(2)

		$('#tltotal').val(total);

		Totales_Generales()

		document.getElementById('savei').style.display = '';
	    document.getElementById('printfac').style.display = 'none';

        if (document.getElementById('idassoc').value!="") {
				document.getElementById('savei').click();
	     }




	}
	//-------------------------------------------------------------------------
	 function roundTo(n, digits) {
 		var negative = false;
    if (digits === undefined) {
        digits = 0;
    }
		if( n < 0) {
    	negative = true;
      n = n * -1;
    }
    var multiplicator = Math.pow(10, digits);
    n = parseFloat((n * multiplicator).toFixed(11));
    n = (Math.round(n) / multiplicator).toFixed(2);
    if( negative ) {    
    	n = (n * -1).toFixed(2);
    }
    return n;
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

			
			$("#discprcntg").val("");
			$("#discamount").val("");
			$("#shipping").val("");
			


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
	       	url='../../handler/MsprodsHandler.php';
	       	var api = new  XMLHttpRequest();
	       	api.open('POST',url,true);
	       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	       	api.send(params);
	       	api.onreadystatechange = function(){
	       		if(this.readyState === 4 && this.status === 200){

	     		} 	
	     	}

	     //
	
	     //

	 });
	//-------------------------------------------------------------------------
	 $('#savei').click(function(){
   	 	if (type_discnt!='' && type_discnt!=undefined) {
	 		document.getElementById('desxmonto').value=""
	 	}
        
	 	if ( !isNaN(parseInt(document.getElementById('discamount').value) ) && parseInt(document.getElementById('discamount').value) >0) {
		 	if (type_discnt!=undefined) {
		 		document.getElementById('desxmonto').value=type_discnt;
		 	}             
	             	 			
	 	}

	 	    document.getElementById('savei').style.display = 'none';
	 		var form = document.querySelector('#facturacion');
			var formData = serialize(form);

	 	    let row_index = $(this).parent().parent().index();
            let factura = document.getElementById('numero').value;
 
	  		let  url ;
	  		let params =  formData+'&save=y&factura='+factura; //+'&amp;pwd='+userPwd	         	
	       	url='../../handler/MsprodsHandler.php';
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
		       	let url='../../handler/MsprodsHandler.php';
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
		       			try{
		       				document.getElementById('savei').style.display = '';
		       			}catch(e){

		       			}
		       			
		     	} 	
		     }

	}
	//-------------------------------------------------------------------------
	$('#medico').change(function(){
				var id=document.getElementById('medico').value;		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&md=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsprodsHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 let invoice=JSON.parse(this.responseText);	     	    		 
	          		 document.getElementById('medicohd').value=document.getElementById('medico').value;

		        }   
		     } 	
	 
  	})
	//-------------------------------------------------------------------------
	$('#medio').change(function(){
				var id=document.getElementById('medio').value;		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'q=' +id+'&medio=y&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsprodsHandler.php';
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
	    var url='../../handler/MsprodsHandler.php';
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
	          		document.getElementById('medicohd').value=res[0].Codmedico;
	          		$("#medico").attr("disabled","disabled");
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
	//-------------------------------------------------------------------------
	        $('.discount').dblclick(function () {
            var id = $(this).get();
            toggleButtons(id);
        });
	//-------------------------------------------------------------------------
        function toggleButtons(id){
            // Check for attr != disabled here
          //  $('.discount').removeAttr("readonly");
          //  $(id).attr("readonly","readonly");
        }  
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	
 
	//-------------------------------------------------------------------------	
	//-------------------------------------------------------------------------
	$('body').on('keydown', 'input, select, textarea', function(e) {
		    var self = $(this)
		      , form = self.parents('form:eq(0)')
		      , focusable
		      , next
		      ;
		    if (e.keyCode == 13) {
		        focusable = form.find('input,a,select,button,textarea').filter('.enterkey');
		       
		        if(self.hasClass('cantidad')){
		            //next = focusable.eq(4);   //INDICE DEL BOTON ADD MORE
		            document.getElementById('add').click();
		        }else{
		            next = focusable.eq(focusable.index(this)+1);
		        }

		        try{
		        	if (next.length) {
		            next.focus();
		            next.select();
		        } else {
		           // form.submit();
		        }
		    }catch(err){

		    }

		        
		        return false;
		    }
	});
	//-------------------------------------------------------------------------
	$('#nueva').click(function(){
			 
            window.location.href += "?new=true"; 
	 })
	//-------------------------------------------------------------------------
	var getCleanUrl = function(url) {
  		url= url.replace(/#.*$/, '').replace(/\?.*$/, '');
  		 window.history.pushState('',document.title,url)
	};

	getCleanUrl(document.location.href);
	//-------------------------------------------------------------------------
	function checkInvoiceToday(){
				var id=document.getElementById('idassoc').value;		
			   	let  url ;
			   	let factura=document.getElementById('numero').value
		       	let params = 'cliente=' +id+'&today=y'; //+'&amp;pwd='+userPwd
		        url='../../handler/MsprodsHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 let invoice=JSON.parse(this.responseText);	
     	    		 if (invoice.length>0) {
     	    		 	 $('.alert').show(); 
     	    		 }
		        }   
		     } 	
	}
	//-------------------------------------------------------------------------
	$("#taxapply").change(function(){     
        applytax=$(this).prop('checked');
        totales();
  	})
	//-------------------------------------------------------------------------
	$('#shipping').change(function(){
	 	totales();
	 	$('#shipping').focus();
  	})
	//-------------------------------------------------------------------------
	$('#shipping').focus(function(){
	 	this.select();
  	})
	//-------------------------------------------------------------------------
	$('#printfac').click(function(){
	 	 invoicePrinting();
  	})
	//-------------------------------------------------------------------------
	function invoicePrinting(){
		    var _numfactu =  $('#numero').val().trim();   
		    var times=1;
		    var user = $("#idusr").val();
		    var datasave = {
		         numfactu   : _numfactu  
		        ,times : 1
		    }

		    let prninvoice = $('#prninvoice').val();
		    let autoposprn = $('#autoposprn').val();
		    let pathprn = $('#pathprn').val();

		    if (prninvoice!='1' && autoposprn!='1' ) {
		        window.open('../../clases/printprodpdf.php?numfactu='+_numfactu+'&times='+times+'&user='+user,'', '_blank');
		        document.getElementById('nueva').click()
		    }else{
		        url="../../clases/printprod.php";

		        var items;
		        var resp =  $.ajax({
		                          type: "POST",
		                          url: url,
		                          data: datasave,
		                          async: false
		                      }).responseText;
		    
		        items= resp ; //jQuery.parseJSON(resp);
		   }
  	}
	//-------------------------------------------------------------------------
	$('#idassoc').on('change', function(){
		let factura=$('#numero').val().trim();
		let cliente=$('#idassoc').val();

		if (factura!="") {
		
			   	let  url ;
			   	
		       	let params = 'patientupdate=yes&codclien='+cliente+'&factura='+factura; //+'&amp;pwd='+userPwd
		        url='../../handler/MsprodsHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 findGranTotalCliente();
		        }   
		     } 	

		};		
	})
	//-------------------------------------------------------------------------
	function findGranTotalCliente() {


		        // Limpia valores del Table
		         $('#company-1-titulo').text("")
		         $('#company-1-total').text("")
		         $('#company-2-titulo').text("")
		         $('#company-2-total').text("")
		         $('#gran-total').text("")
		        // 
				var id=document.getElementById('idassoc').value;						
			   	let  url ;			   	
		       	let params = 'codclien='+id+'&gtotal=y';
		        url='../../handler/MsprodsHandler.php';
		       	var api = new  XMLHttpRequest();
		       	api.open('POST',url,true);
		       	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		       	api.send(params);
		       	api.onreadystatechange = function(){
		       	if(this.readyState === 4 && this.status === 200){            
     	    		 let res_gtotal =JSON.parse(this.responseText);	
     	    		 let g_total_e=0;
     	    		 if (res_gtotal.length>0) {
     	    		 	 g_total_e=g_total_e+parseFloat( res_gtotal[0].total );
     	    		 	 $('#company-1-titulo').text( res_gtotal[0].servicio)
     	    		 	 $('#company-1-total').text(parseFloat(  res_gtotal[0].total).toFixed(2))
     	    		     if (res_gtotal.length==2) {
     	    		     	g_total_e=g_total_e+parseFloat( res_gtotal[1].total );
     	    		     	$('#company-2-titulo').text( res_gtotal[1].servicio)
     	    		 	    $('#company-2-total').text(parseFloat(  res_gtotal[1].total).toFixed(2))   	    		 	   
     	    		     }

     	    		     g_total_e=g_total_e+ parseFloat($('#tltotal').val()) 


     	    		 	 $('#gran-total').text(parseFloat( g_total_e ).toFixed(2))
     	    		 }
		        }   
		     } 	

	}

	//-------------------------------------------------------------------------
	function Totales_Generales(){

		let total_1=0;
		let total_2=0;

		if ( isNaN(  parseFloat( $('#company-2-total').text() ) ) ) {
            total_2=0;
		}else{
			total_2=parseFloat( $('#company-2-total').text() )
		}

		if ( isNaN(  parseFloat( $('#company-1-total').text() ) ) ) {
            total_1=0;
		}else{
			total_1=parseFloat( $('#company-1-total').text() )
		}


		let gt= total_1+total_2+ parseFloat($('#tltotal').val()) 
		$('#gran-total').text(parseFloat( gt ).toFixed(2))


//787-668-7416

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
	

})