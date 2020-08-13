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
 
  loadProducos()


	function getVentasXItem(  page, fechai, fechaf, coditems){

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

    		url="../../controllers/ventasxitemcontroller.php";
     		data = {
      			limit  : 50,
      			page   : page,
      			fechai : fechai,
      			fechaf : fechaf, 
            coditems :coditems
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
            		var telfhabit   = items['data'][i]["telfhabit"];
            		var cantidad     = items['data'][i]["cantidad"];
            		
            		var col=i+1;
                
             		$('#tblLista').append('<tr id='+i+'>'+
						      ' <td  align="left">'+col+'</td>'+
             			' <td  align="left">'+nombres+'</td>'+             			
             			' <td>'+telfhabit+'</td>'+
             			' <td align="center">'+cantidad+'</td>'+             			
             			' </tr>');
        		};
        };


        getVentasXItemDetalle(  page, fechai, fechaf, coditems)

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
    		         
        coditems = $("#sltprod").val(); 
        getVentasXItem(  num, fechai , fechaf, coditems );
        $(".content2").html("Page " + num);
    });
    //---------------------------------------------------------
    $('.epagination').bootpag({
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
                 
        coditems = $("#sltprod").val(); 
        getVentasXItemDetalle(  num, fechai , fechaf, coditems );
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
          coditems = $("#sltprod").val();
            
           	getVentasXItem('', fechai , fechaf, coditems );
            $('.pagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })
            //----------
            $('.epagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })
            //----------
    }



    //-------------------------------
    function loadProducos(){ 

    var serv= getData("../../clases/getprodtoinvoice.php",{prod_serv:'P', orderby:'desitems'},'POST');
    items= jQuery.parseJSON(serv);
    var options;
     options+="<option value=''>Elija Producto</option>"; 
     for (var j = 0; j < items.length; j++) { 
         options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
     }
     $("#sltprod").html(options);
  

}


  function getData(url,data,type){
  if (data==undefined) {
    data = {    }
  };
  if (type==undefined) {
    type = "GET"
  };
    return $.ajax({
        type: type,
        url: url,
        data : data,
        async: false
    }).responseText;

     }


    //-------------------------------

 //   #detalleitemventascontroller.php
      function getVentasXItemDetalle(  page, fechai, fechaf, coditems){

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

        url="../../controllers/detalleitemventascontroller.php";
        data = {
            limit  : 50,
            page   : page,
            fechai : fechai,
            fechaf : fechaf, 
            coditems :coditems
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
        
        var active_user=items['data'][0]["usuario"]; 
        var coluser=0;

            var lenArr=items['data'].length;
            for (var i = 0; i < lenArr; i++) {
                var numfactu    = items['data'][i]["numfactu"];     
                var fechafac    = items['data'][i]["fechafac"];
                var cantidad    = items['data'][i]["cantidad"];
                var nombres     = items['data'][i]["nombres"];
               
                
                var col=i+1;
                
                $('#etblLista').append('<tr id='+i+'>'+
                   ' <td align="left">'+col+'</td>'+
                   ' <td align="center">'+numfactu+'</td>'+ 
                   ' <td align="center">'+fechafac+'</td>'+
                   ' <td align="left">'+nombres+'</td>'+                   
                   ' <td align="center">'+cantidad+'</td>'+ 

                  ' </tr>');
            };
        };

  }
    //-------------------------------

});  