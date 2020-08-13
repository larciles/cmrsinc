$(function(){
    var screen = $('#loading-screen');
    configureLoadingScreen(screen);

$("#c").hide(); 
$("#s").hide(); 
$("#l").hide(); 
$("#i").hide(); 

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

$('#sd').val(today);
$('#ed').val(today);


$("#wait").hide(); 
$('#xtitulo').html('<b>Reporte Consolidado</b>');

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val();
});

$('#submit').click(function(){
  
          $.get('../../clases/spinner.php')
             .done(function(result){
                  reportGenerator();
                 $('#results').text(JSON.stringify(result, null, 2));

             })
             .fail(function(error){
                 console.error(error);
             });
              getDataGraph()
 
})

function reportGenerator(){
var DATA  = [];
  var type    =  "G"; //general
  var sd      =  $('#sd').val();
  var ed      =  $('#ed').val();
  var datarow =[];
  var sales   =  reportData(type,sd,ed,"");

  var co  =  reportData('C',sd,ed,"");
  var la  =  reportData('L',sd,ed,"");
  var su  =  reportData('S',sd,ed,"");
  var li  =  reportData('I',sd,ed,"");



  if (Array.isArray(li)) {
     $("#i").show();
        $badge = $("#i").find('.badge')
        count = Number($badge.text()),
        $badge.text( li[0].laser);


  }else{
        $("#i").hide();        
  }
  
  if (Array.isArray(co)) {
     $("#c").show();
        $badge = $("#c").find('.badge')
        count = Number($badge.text()),
        $badge.text( formatter.format(co[0].monto));


  }else{
        $("#c").hide();        
  }




    if (Array.isArray(su)) {
    $("#s").show(); 
            $badge = $("#s").find('.badge')
        count = Number($badge.text()),
        $badge.text( formatter.format(su[0].monto));

  }else{     
        $("#s").hide();    
  }



    if (Array.isArray(la)) {
       $("#l").show(); 
        $badge = $("#l").find('.badge')
        count = Number($badge.text()),
        $badge.text( la[0].laser);
    
  }else{
        $("#l").hide(); 
  }
 
  buildReportQuantity(sales);

  //new
  var lt= $("td:contains('Total')").length;
  let e=$("td:contains('Total')")
  for (var i = 0; i < lt; i++) {

     var ar = e[i].nextElementSibling.innerHTML.match(/\d+/g).map(Number);

     if (ar.length==3 ) {
      if (ar[0]>=13) {
          var animation=animate[ getRandomInt(0,43) ];
         e[i].parentElement.setAttribute("class", "democlass animated "+animation)  //animate
      }else{
         e[i].parentElement.setAttribute("class", "triste")
      }
     }else{
      e[i].parentElement.setAttribute("class", "triste")
     }

    
  }
}


function reportData(type,sd,ed,rpt){
  var seleccion=selected();
  data = {
    type : type,
    sd   : sd,
    ed   : ed,
    rpt  : rpt,
    pro  : seleccion
  }
    var res = getData("../../controllers/consolidadocontroller.php",data);
    var jsonArray = JSON.parse(JSON.stringify(res));
    var rx = JSON.stringify(res);
    items = jQuery.parseJSON(res);
    return  items;//jsonArray;//items ; //JSON.stringify(items);
}

function buildReportQuantity(data){
     var arr1=[],arr2=[],arrprincipal=[];
     var data1=data;
     var titulos='Empresa,Subtotal,Descuento,Neto,Impuesto,Envio,Total';
     var bold=false;
     
     arrprincipal.push(titulos.split(","));

     for (var i = 0; i < data1.length; i++) {

        arr1=[];

        if (data1[i][0].substring(0,5)=="Total" ) {
            bold=true;
        }else{
            bold=false;
        }  
       
        for (var j = 0; j < data1[i].length; j++) {
            
            if (j==0) {
                arr1.push(data1[i][j])  
           
            }else{

              var valor = data1[i][j];
              var monto = valor;
              var amount=parseFloat(monto);
              var amountFt = formatter.format(amount);

              if (bold) {
                 amountFt = formatter.format(amount);
                 amountFt="<strong>"+amountFt+"</strong>";
              }
              
              tmp = {
                      'v': amount ,
                      'f': amountFt 
                    }
              arr1.push(tmp)
              
            }

        };
       arrprincipal.push(arr1);
     
     }
    buildReportStruct(arrprincipal);
    
}

var formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2,
})

function buildReportStruct(data,tabledata){	
  len=data.length-1;
  data1=data;
  var arr1=[];
  var arrprincipal=[];
  if (data1[0][1]!=="") {

     if (tabledata== undefined) {       
        chartTest(data1);
     }else{      
        chartTest(tabledata);
     }     
 
  }else{
    $("#columnchart").html("<h1>Información no disponible para este período</h1>");
    $("#table_div").html("");
  }
}



google.charts.load('current', {'packages':['corechart','table']});
//----------------------------------------------------------------------
function drawChart(args,len){
 // google.charts.load('current', {'packages':['corechart']});
 google.charts.setOnLoadCallback(dVisualization);

	function dVisualization() {

	var data = google.visualization.arrayToDataTable(args);

	var options = {
		title : 'Reporte comparativo de ',
   animation: {
          duration: 1500,
          easing: 'linear',
          startup: true
        },
		vAxis: {title: ' $'},
		hAxis: {title: 'Months'},
		seriesType: 'bars',
		series: {len: {type: 'line'}}
	};

	var chart = new google.visualization.ComboChart(document.getElementById('columnchart'));
	chart.draw(data, options);
	}
}
//----------------------------------------------------------------------
function chartTest(args) {
    //  google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTable);
      
      function drawTable() {
        var datos = new google.visualization.DataTable();

        for (var i = 0; i < args[0].length; i++) {
        	var datTi=args[0][i];
        	console.log(datTi);
        	if (i==0) {
        		datos.addColumn('string',datTi);
        	}else{
 				datos.addColumn('number',datTi);
        	}
         } 
        args.shift();


 var options = {

      animation:{
        duration: 1000,
        easing: 'out'
      }
    };


        datos.addRows(args);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        var formatter = new google.visualization.ColorFormat();
        formatter.addRange(-20000, 0, 'black', '#ff7979');
       // formatter.addRange(12000, 11000, 'black','#f6e58d');
       // formatter.addRange(13000, null, 'black','#55efc4');
        formatter.format(datos, 1);
        //formatter.format(datos, 2); // descuento
        formatter.format(datos, 3);
        formatter.format(datos, 4);
        formatter.format(datos, 5);
        formatter.format(datos, 6);



        table.draw(datos, {allowHtml: true,showRowNumber: true, width: '100%', height: '100%'});
      }

}


// do this: google.charts.setOnLoadCallback(drawCharts);

// not this: google.charts.setOnLoadCallback(drawCharts());


//-------------------------
   // return  items;//jsonArray;//items ; //JSON.stringify(items);

  //       items= jQuery.parseJSON(res);     
  //   var options;
  //   for (var j = 0; j < items.length; j++) { 
  //       if (j==0) {
  //          options+="<option value='"+items[j].codtipre+"' selected >"+items[j].destipre+"</option>"; 
  //       };
  //       options+="<option value='"+items[j].codtipre+"'>"+items[j].destipre+"</option>"; 
  //   }
  //   $("#"+id).html(options);
  // }

//--------------------
function getData(url,data){

	return jsonData = $.ajax({
	    type: "POST",
	    url: url,
	    data:data,
	    dataType: "json",
	    async: false
	}).responseText;
}

function selected(){
//    var selectedArray = new Array();
//   var selObj = $('#sltprod');
//   var i;
//   var count = 0;
//   var srtSlect="";
//   for (i=0; i<selObj[0].options.length; i++) {
//     if (selObj[0].options[i].selected) {
//       selectedArray[count] = selObj[0].options[i].value;
//       count++;
//     }
//   }
// if (selectedArray.length==1) {
//     srtSlect=selectedArray[0];
// }
// if (selectedArray.length>1) {
//     for (var i = 0; i < selectedArray.length; i++) {

//       if (  i==selectedArray.length-1) {
//          srtSlect = srtSlect + selectedArray[i];
//       }else{
//          srtSlect = srtSlect + selectedArray[i]+"'"+','+"'";
//       }
//     }
// }
// return srtSlect;
}







//********************************************************
// var obj = [];
// var elems = $("input[class=email]");

// for (i = 0; i < elems.length; i += 1) {
//     var id = this.getAttribute('title');
//     var email = this.value;
//     tmp = {
//         'title': id,
//         'email': email
//     };

//     obj.push(tmp);
// }
//jsonString = JSON.stringify(jsonObj);
//********************************************************
// var obj = [];
// var elems = $("input[class=email]");

// for (i = 0; i < elems.length; i += 1) {
//     var id = this.getAttribute('title');
//     var email = this.value;
//     tmp = {
//         'title': id,
//         'email': email
//     };

//     obj.push(tmp);
// }
//********************************************************
 })

function configureLoadingScreen(screen){
    $(document)
        .ajaxStart(function () {
            screen.fadeIn();
        })
        .ajaxStop(function () {
            screen.fadeOut();
        });
}


 

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

var animate = [
"bounce"
,"flash"
,"pulse"
,"rubberBand"     
,"shake"
,"headShake"
,"swing"
,"tada"
,"wobble"
,"jello"
,"bounceIn"
,"bounceInDown"
,"bounceInLeft"
,"bounceInRight"
,"bounceInUp"
,"fadeIn"
,"fadeInDown"
,"fadeInDownBig"
,"fadeInLeft"
,"fadeInLeftBig"
,"fadeInRight"
,"fadeInRightBig"
,"fadeInUp"
,"fadeInUpBig"
,"flipInX"
,"flipInY"
,"lightSpeedIn"
,"rotateIn"
,"rotateInDownLeft"
,"rotateInDownRight"
,"rotateInUpLeft"
,"rotateInUpRight"
,"jackInTheBox"
,"rollIn"
,"zoomIn"
,"zoomInDown"
,"zoomInLeft"
,"zoomInRight"
,"zoomInUp"
,"slideInDown"
,"slideInLeft"
,"slideInRight"
,"slideInUp"
,"heartBeat"];