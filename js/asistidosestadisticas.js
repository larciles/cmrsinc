		var totalPage;
	var date_input=$('input[name="efechai"]'); 
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
	format: 'mm/dd/yyyy',
	container: container,
	todayHighlight: true,
	autoclose: true,
	});

	//FECHA FINAL #FECHAF		
	var date_input=$('input[name="efechaf"]'); 
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
	format: 'mm/dd/yyyy',
	container: container,
	todayHighlight: true,
	autoclose: true,
	});



//  getDataS();

	function getDataS(  page, fechai, fechaf){

		    var hoy = new Date().toLocaleDateString('en-US', {  
  						month : 'numeric',
  						day : 'numeric',        
  						year : 'numeric'
  						}).split(' ').join('/');
         
		    if (page ==undefined) {
       			page =1;
    		};

    		if (fechaf ==undefined) {
       			fechaf =hoy;
    		};

    		if (fechai ==undefined) {
       			fechai =hoy;
    		};

    		url="../../clases/asistidosestadisticaclass.php";
     		data = {
      			limit  : 25,
      			page   : page,
      			fechai : fechai,
      			fechaf : fechaf
     		}
     		var items;
    		var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
            	          async: false
                	    }).responseText;

    		items= jQuery.parseJSON(resp);
        if (items!="") {
        		totalPage=items.totpaginas;    
        		$("#etblLista > tr").remove();
        		$("#etblLista").find("tr:gt(0)").remove(); 

        		var lenArr=items['data'].length;
        		for (var i = 0; i < lenArr; i++) {
        			var usuario  = items['data'][i]["nombre"]+' ('+items['data'][i]["usuario"]+')';
        			var confirmado = items['data'][i]["confirmado"];
            	var asisconfirm  = items['data'][i]["asisconfirm"];
              var asisnoconfirm  = items['data'][i]["asisnoconfirm"];

                    //promedio = (parseFloat(asistido) /parseFloat(citados))*100;
            	//	promedio=promedio.toFixed(2)
            		//usuario=usuario.trim();
            		var col=i+1;
             		$('#etblLista').append('<tr id='+i+'>'+
						// ' <td  align="left">'+col+'</td>'+
             			' <td align="left">'+usuario+'</td>'+             			         			
             			' <td align="center">'+confirmado+'</td>'+
                  ' <td align="center">'+asisconfirm+'</td>'+
             			' <td align="center">'+asisnoconfirm+'</td>'+
             			' </tr>');
        		};
        };

    
    
	}
	//--------------------------------------------------------------
	$('.epagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fechai,fechaf ="";
        fechai =$('#efechai').val();
        fechaf =$('#efechaf').val();

        var hoy = new Date().toLocaleDateString('en-US', {  
  						month : 'numeric',
  						day   : 'numeric',        
  						year  : 'numeric'
  						}).split(' ').join('/');


        if (fechaf =='' &&  fechai =='') {
       			fechaf =hoy;
       			fechai =hoy;
    		};
    		         

        getDataS(  num, fechai , fechaf );
        $(".content2").html("Page " + num);
    });
    //---------------------------------------------------------
	$('#esubmit').click(function(e) {       
            loadDataPages();
   });
   //---------------------------------------------------
    $("body").keypress(function(e){
         if(e.which === 13){
         //	loadDataPages();
         }
    });
	//--------------------------------------------------   

    function loadDataPages(){
    	var fechai,fechaf ="";
        	fechai =$('#efechai').val();
        	fechaf =$('#efechaf').val();

           	getDataS('', fechai , fechaf );
            $('.epagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })
    }


$('#tabs').on("click", "li", function (event) {         
   var activeTab = $(this).find('a').attr('href').split('-')[1];
    if (activeTab = $(this).find('a').attr('href')=='#menu1') {
    	$('.pagination').hide();
    }else{    		
    	$('.pagination').show();
    };   
});