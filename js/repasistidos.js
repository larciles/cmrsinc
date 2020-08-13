$(function(){

	var totalPage;

	

	//FECHA INICIAL #FECHAI		
	var date_input=$('input[name="fechai"]'); 
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
	format: 'mm/dd/yyyy',
	container: container,
	todayHighlight: true,
	autoclose: true,
	});

	//FECHA FINAL #FECHAF		
	var date_input=$('input[name="fechaf"]'); 
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	date_input.datepicker({
	format: 'mm/dd/yyyy',
	container: container,
	todayHighlight: true,
	autoclose: true,
	});
 
  getAsistidos();

	function getAsistidos(  page, fechai, fechaf){

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

    		url="../../controllers/asistidoscontroller.php";
     		data = {
      			limit  : 200,
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
        		$("#tblLista > tr").remove();
        		$("#tblLista").find("tr:gt(0)").remove(); 
				
				var active_user=items['data'][0]["usuario"]; 
				var coluser=0;

        		var lenArr=items['data'].length;
        		for (var i = 0; i < lenArr; i++) {
        			  var nombres     = items['data'][i]["nombres"];
        			  var observacion = items['data'][i]["observacion"];
            		var telfhabit   = items['data'][i]["telfhabit"];
            		var Medicos     = items['data'][i]["Medicos"];
            		var Historia    = items['data'][i]["Historia"];
            		var ProximaCita = items['data'][i]["ProximaCita"];
            		var NumeroCitas = items['data'][i]["NumeroCitas"];
					var usuario     = items['data'][i]["usuario"];
  				    var celular     = items['data'][i]["celular"];
                    usuario = usuario+' / '+celular;
            		
            		
            		var col=i+1;
					
					coluser=coluser+1;
					if (active_user.toLowerCase()!==items['data'][i]["usuario"].toLowerCase()) {
						active_user=items['data'][i]["usuario"];
						coluser=1;  
					};
                
             		$('#tblLista').append('<tr id='+i+'>'+
						' <td  align="left">'+col+'/'+coluser+'</td>'+
             			' <td  align="left">'+nombres+'</td>'+
             			' <td  align="left">'+observacion+'</td>'+         			
             			' <td>'+telfhabit+'</td>'+
             			' <td align="center">'+Medicos+'</td>'+
             			' <td align="center">'+Historia+'</td>'+
             			' <td align="center">'+ProximaCita+'</td>'+
             			' <td align="center">'+NumeroCitas+'</td>'+  
						' <td align="center">'+usuario+'</td>'+ 
             			' </tr>');
        		};
        };

    
    
	}
	//--------------------------------------------------------------
	$('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fechai,fechaf ="";
        fechai =$('#fechai').val();
        fechaf =$('#fechaf').val();

        var hoy = new Date().toLocaleDateString('en-US', {  
  						month : 'numeric',
  						day   : 'numeric',        
  						year  : 'numeric'
  						}).split(' ').join('/');


        if (fechaf =='' &&  fechai =='') {
       			fechaf =hoy;
       			fechai =hoy;
    		};
    		         

        getAsistidos(  num, fechai , fechaf );
        $(".content2").html("Page " + num);
    });
    //---------------------------------------------------------
	$('#submit').click(function(e) {       
            loadDataPage();
   });
   //---------------------------------------------------
    $("body").keypress(function(e){
         if(e.which === 13){
         	loadDataPage();
         }
    });
	//--------------------------------------------------   

    function loadDataPage(){
    	var fechai,fechaf ="";
        	fechai =$('#fechai').val();
        	fechaf =$('#fechaf').val();

           	getAsistidos('', fechai , fechaf );
            $('.pagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })
    }

	// $('#submit').click(function(){
	// 	var fechai = $("#fechai").val();
	// 	var fechaf = $("#fechaf").val();		

	// 	var datasave ={
 //      		fechai : fechai,
 //      		fechaf : fechaf
 //                }

 //  			var ajxmed = $.post( "../../clases/repasistidosgetclass.php",datasave, function(data) { 

 //  				var items="";
 //  				var options="";
 //  				items= jQuery.parseJSON(data);

 //            });
 //        });
});  