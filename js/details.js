$(document).ready(function(){ 


    //productos Select
 	let  url,items,options ;		       
   	url="../../clases/getprodcompras.php";
   	var api = new  XMLHttpRequest();
   	api.open('POST',url,true);
   	api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
   	api.send();
   	api.onreadystatechange = function(){
   	if(this.readyState === 4 && this.status === 200){
	   		items=JSON.parse(this.responseText);	
	   		options+="<option value='-'>Elija Producto</option>"; 	
	   		for (var j = 0; j < items.length; j++) { 
					options+="<option value='"+items[j].coditems+"'>"+items[j].desitems+"</option>"; 
				}
				$("#productos").html(options);
	 	} 	
	}

	document.querySelector('#productos').addEventListener('change', function(){
		getList(  1,  '',  '');

	} )


     
       function getList(  page,  search,  fecha){

     // PERFECTO CUANDO LA Paginacion ES POR PHP 
     /*
     var $_GET=[];
     window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(a,name,value){$_GET[name]=value;});
     page=$_GET.page
     if ($_GET.page !=undefined) {
      $('.nav-pills li a[href="#menu1"]').trigger('click')
     }
     */
     //
    if (page ==undefined || page=="") {
       page =1;
    };

     if (search ==undefined) {
       search ="";
    };

     if (fecha ==undefined || fecha=="") {
       fecha ="";
    }else{
      fecha =fecha.split('/')[2]+'-'+fecha.split('/')[0]+'-'+fecha.split('/')[1]
    }
    let filtro ="";
    if (document.querySelector('#productos').value!="-") {
       filtro =document.querySelector('#productos').value;
    }
    


           $("#tbldetails > tr").remove();    
           $("#tbldetails").find("tr:gt(0)").remove(); 


          let  url ;        
          let params = 'limit=25&page='+page+'&search='+search+'&fecha='+fecha+'&filtro='+filtro; //+'&amp;pwd='+userPwd               
          url='../../handler/AjustesHandler.php';
          var api = new  XMLHttpRequest();
          api.open('POST',url,true);
          api.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
          api.send(params);
          api.onreadystatechange = function(){
            if(this.readyState === 4 && this.status === 200){
                 let dat;
                 dat= this.responseText;
                 dat= JSON.parse(dat);
                 if (dat!="") {

                    setReport(dat[0]);

                    $("#otra").html(dat[1]);
                 }
                 
          }   
        }

  
  }
//-------------------------------------------------------------------------------------------------------
function setReport(items){
  var options;
  var moptions=''
  totalPage=items.totpaginas;

try{
  $('.paginaf').bootpag({
        total: totalPage,
         maxVisible: 10
    })
}catch(err){

}

  $("#tbldetails > tr").remove();    
  $("#tbldetails").find("tr:gt(0)").remove(); 

  var lenArr=items['data'].length;

      for (var i = 0; i < lenArr; i++) {
      	var fechacierre = items['data'][i]["fechacierre"];

        var InvActual   = items['data'][i]["InvActual"];
        var ventas      = items['data'][i]["ventas"];
        var compras     = items['data'][i]["compras"];        
        var devcompra   = items['data'][i]["devcompra"];
        var devVentas   = items['data'][i]["devVentas"];
        var ne          = items['data'][i]["ne"];
        var nc          = items['data'][i]["nc"];        
        var Ajustes_mas = items['data'][i]["Ajustes_mas"];      
        var Ajustes_neg = items['data'][i]["Ajustes_neg"];    
        var existencia  = items['data'][i]["existencia"];
        var desitems    = items['data'][i]["desitems"];    
        var InvPosible  = items['data'][i]["InvPosible"];    
        
     
         let clase='';


         $('#tbldetails').append('<tr class="'+clase+'">'+
          ' <td  align="center">'+fechacierre+'</td>'+
          ' <td align="center">'+existencia+'</td>'+ 
          ' <td align="center">'+ventas+'</td>'+
          ' <td align="center">'+compras+'</td>'+
          ' <td align="center">'+devcompra+'</td>'+
          ' <td align="center">'+devVentas+'</td>'+
          ' <td align="center">'+ne+'</td>'+
          ' <td align="center">'+nc+'</td>'+
          ' <td align="center">'+Ajustes_mas+'</td>'+
          ' <td align="center">('+Ajustes_neg+')</td>'+                    
          ' <td align="center">'+InvPosible+'</td>'+ 
          ' </tr>')
       
    };
   
    

} 
//-------------------------------------------------------------------------------------------------------21
 $('#otra').on('click', '.pagination', function(event) {       
     event.preventDefault()
     $('#otra .pagination li.active').removeClass('active');
     $(event.target).parent().addClass('active')

       var num,fecha,search ="";
       num= event.target.text
       fecha=$('.datepicker').val();
       search = $('#search').val();
       getList(  num, search , fecha );
    
      console.log(event.target.text);
  }) 



//*******************************************************************************************************
});