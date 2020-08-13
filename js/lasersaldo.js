var totalPage;


//getData();

 

  function getData(  page,  search,  fechastart, fechafinish){
    if (page ==undefined) {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fechastart ==undefined) {
       fechastart ="";
    };

    if (fechafinish ==undefined) {
       fechafinish ="";
    };


  var hoy = new Date().toLocaleDateString('en-US', {  
  month : 'numeric',
  day : 'numeric',        
  year : 'numeric'
  }).split(' ').join('/');
        

     url="../../clases/lasersaldoclass.php";
     data = {
      limit  : 25,
      page   : page,
      search : search,
      fechai  : fechastart,
      fechaf  : fechafinish
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    


    items= jQuery.parseJSON(resp);
    totalPage=items.totpaginas;
    xres = testf( '' , search , fechastart,fechafinish ); 



    var a_ob = new Array();
    for (var i = 0; i < xres.length; i++) {
    	
    	a_ob[xres[i].codclien.toString()] = xres[i].terapias;
    };


   //TOTAL LASER
   var a_ls = new Array();
    tlasers = totalLasers( '' , search , fechastart,fechafinish );
    $('.totales').show();
    $('#lpack').html(tlasers[0]['vendidos']);
    $('#lapli').html(tlasers[1]['aplicados']);
    $('#lpend').html(tlasers[2]['restantes']);

    $("#tblLista > tr").remove();
    
    $("#tblLista").find("tr:gt(0)").remove(); 

    var lenArr=items['data'].length;
    for (var i = 0; i < lenArr; i++) {
    	  var nombres   = items['data'][i]["nombres"];
        var cantidad  = items['data'][i]["cantidad"];
        var codclien  = items['data'][i]["codclien"];
        var record    = items['data'][i]["record"];

        var fecha = $('#startdate').val();
    
       
        var laser='0';
        try{
        	laser=a_ob[codclien];
        }
        catch(err){
        	 laser=0;
        }

        if (laser==undefined) {
        	 laser=0;
        };
        
        var restantes = parseInt(cantidad)-parseInt(laser);


         $('#tblLista').append('<tr id='+codclien+'>'+
         	' <td >'+nombres+'</td>'+
         	' <td  align="center">'+cantidad+'</td>'+ 
         	' <td  align="center">'+laser+'</td>'+ 
          ' <td  align="center">'+restantes+'</td>'+  
          ' <td  align="center">'+record+'</td>'+  
         	' </tr>');
    };
     //console.log(resp);
     //console.log(items);
  
  }


function getLaserAplicados(codclien,fecha){
     

    url="../../clases/getlaseraplicados.php";
    data = {
      fecha_cita : fecha,
      codclien   : codclien
    }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    


    items = jQuery.parseJSON(resp);
    return items;
}



     $('.pagination').bootpag({
       total: totalPage,
       page: 1,
       maxVisible: 10
    }).on('page', function(event, num){
        var fecha,search ="";
        fechai = $('#startdate').val();
        fechaf = $('#finishdate').val();	
        search = $('#search').val();

        getData(  num, search , fechai, fechaf );
        $(".content2").html("Page " + num); // or some ajax content loading...
    });


   $('#search').on('keypress', function (e) {
         if(e.which === 13){         
            
           var fecha,search ="";
               fechai = $('#startdate').val();
               fechaf = $('#finishdate').val();	
               search = $('#search').val();

            
            getData( undefined, search , fechai, fechaf );
              $('.pagination').bootpag({
                   total: totalPage,
                   page: 1,
                   maxVisible: 10
              })

            //Disable textbox to prevent multiple submit
           // $(this).attr("disabled", "disabled");
         }
   });


$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});


 $('.datepicker').change(function(){
    var startdate,finishdate,search ="";
    startdate  =$('#startdate').val();
    finishdate =$('#finishdate').val();	
    search = $('#search').val(); 

    if (finishdate!=='' &&  startdate!=='') {
    	
    	getData( '' , search , startdate,finishdate );

     	$('.pagination').bootpag({
        	total: totalPage,
        	page: 1,
        	maxVisible: 10
   		});
     };
 });


// $('.datepicker').change(function(){
//    var fecha,search ="";
//    fecha =$('.datepicker').val();
//    search = $('#search').val(); 
//    getData( '' , search , fecha );

//     $('.pagination').bootpag({
//        total: totalPage,
//        page: 1,
//        maxVisible: 10
//   })
// });


$('#tblLista').on('click', 'tbody tr button', function(event) {
   var elemt = $(this);
   var numfactu = elemt.parent().parent().attr('id');// elemt.attr('id').toString();

   if(elemt.attr('class').indexOf("devolver")>-1) {
       
       var result = numfactu.split('-');
       if(result.length==2){
          // window.location.href = 'return.php?fac='+result[0]+'&dev='+result[1];
       }else{
          window.location.href = 'return.php?fac='+numfactu;
       }
   }else if(elemt.attr('class').indexOf("consultar")>-1){
      var result = numfactu.split('-');
       if(result.length==1){
          window.location.href = 'invoicedit.php?fac='+numfactu;
       }
   }
   
   

})


function testf(  page,  search,  fechastart, fechafinish){
	     url="../../clases/lasergeneral.php";
     data = {
      limit  : 25,
      page   : page,
      search : search,
      fechai  : fechastart,
      fechaf  : fechafinish
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    


    resp= jQuery.parseJSON(resp);
return resp;
}


function totalLasers(  page,  search,  fechastart, fechafinish){
      url="../../clases/lasertotal.php";
      data = {
     
      fechai  : fechastart,
      fechaf  : fechafinish
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
    


    resp= jQuery.parseJSON(resp);
return resp;
}