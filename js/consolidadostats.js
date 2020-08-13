
$( document ).ready(function() {
 

});

function getDataStats(){

var Http = new XMLHttpRequest();
var url='../../clases/getconsolidadostats.php';
 
var fp=$('#monday').val();
var fi=$('#sd').val();
var ff=$('#ed').val(); 
var params = "fi="+fi+"&ff="+ff+"&avg="+fp;

 
Http.open( "POST", url, true );

//Http.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
Http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

Http.onreadystatechange=(e)=>{
 
  if (Http.readyState==4 && Http.status==200) {
     
     var agt = new Array(); // array de grafica total

     var res= JSON.parse( Http.responseText ) ;
    
      if (res.length>0) {
        $("#div-stats").show();
        for (var i = 0; i < res.length; i++) {
           

    


      $('<tr  >'+       
        '<td class="mov-excm ex-fecha bold">$'+addCommas( parseFloat(res[i].hoy.hoy).toFixed(2))+'</td>'+        
        '<td class="mov-excm ex-return bold">$'+addCommas(parseFloat(res[i].anterior.anterior).toFixed(2))+'</td>'+ 
        '<td class="mov-excm ex-porcentaje bold">'+ res[i].porcentaje +'</td>'+
        '<td class="mov-excm ex-ventas bold">$'+addCommas(parseFloat(res[i].avg.avg).toFixed(2))+'</td>'+                
        
        '</tr>').appendTo($("#estadisticas"));
    

        }
        var dt = new Date(); 

        if (dt.getHours()<18) {
           var horastrascurridas= dt.getHours()-7  
        }else{
          var horastrascurridas=10;
        }
        

        initScriptedGauges( parseInt( parseInt(res[0].hoy.hoy)/horastrascurridas) )
      }
     




  }else{
    console.log(e)
  }
  
}
 

Http.send(params);

}


function addCommas(nStr)
{
  nStr += '';
  x = nStr.split('.');
  x1 = x[0];
  x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
}

 

 