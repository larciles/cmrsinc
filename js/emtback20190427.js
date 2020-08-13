$(function(){


//--------------------------------------------------------------------------------------
//CALCULA LA FECHA ACTUAL
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
var hoy = today;
$('#ed').val(today);

//CALCULA EL PRIMER DIA DEL MES ( CUANDO CARGLA PAGINA ), ESTE REPORTE SOLO FUNCIONA DESDE EL PRIMERO DE CADA MES 
var date = new Date(), y = date.getFullYear(), m = date.getMonth();
var firstDay = new Date(y, m, 1);
 dd = firstDay.getDate();
 mm = firstDay.getMonth()+1; //January is 0!
 yyyy = firstDay.getFullYear();

if(dd<10) {
    dd='0'+dd
} 

if(mm<10) {
    mm='0'+mm
} 

today = mm+'/'+dd+'/'+yyyy;
$('#sd').val(hoy);
//-------------------------------------------------------------------------------------------------------------------------------------------

$("#wait").hide(); 
$('#xtitulo').html('<b>Reporte Estadísticas Médicos Totales</b>');

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val();
});

$("#columnchart").click(function(){
  elem=$(this);
})

function buildReportStruct(data,titulo,tabledata,arrGrapTotal,arrGrapSINTotal){	
  len=data.length-1;
  data1=data;
  var arr1=[];
  var arrprincipal=[];
  if (data1[0][1]!=="") {
     if (tabledata== undefined) {
        //drawChart(data1,len,titulo);
        drawChart(arrGrapTotal,len,titulo);
        chartTest(data1);
     }else{
        //drawChart(data1,len,titulo);
        drawChart(arrGrapTotal,len,'Estadísticas Médicos Totales','columnchart');
        drawChart(arrGrapSINTotal,len,'Estadísticas Médicos Totales (Servicios por Seperados)' ,'graphcolumnchart');        
        chartTest(tabledata);
        
        reportGranTotal($('#sd').val(),$('#ed').val());
     }
     
 
  }else{
    $("#columnchart").html("<h1>Información no disponible para este período</h1>");
    $("#table_div").html("");
  }
}

function buildReportQuantity(data){
     var arr1=[],arr2=[],arrprincipal=[],arrGraph=[],arrTotal=[],arrGrapTotal=[],arrGrapSINTotal=[],arrGrapNOTotal=[];
     var data1=data;
     data1[0][0] = data1[0][0].substring(1);
     
     arrprincipal.push(data1[0]);
     arrGraph.push(data1[0]);
     arrGrapTotal.push(data1[0]);
     arrGrapSINTotal.push(data1[0]);

     var titulo = $("#tipo option[value="+$('#tipo').val()+"]").text();
   //  var proselected = $("#sltprod option[value="+$('#sltprod').val()+"]").text();
     for (var i = 1; i < data1.length; i++) {

        arr1=[];
        arr2=[];
        arrTotal=[]; //PARA LA GRAFICA
        arrGrapNOTotal=[];

        for (var j = 0; j < data1[i].length; j++) {


            
            if (j==0) {
                arr1.push(data1[i][j])
                arr2.push(data1[i][j]);
                arrTotal.push(data1[i][j]);

                 //TOMA TODAS LAS LINEAS EXCEPTO LA ULTIMA LINEA DEL TOTAL   
                if(i<data1.length-1){
                   arrGrapNOTotal.push(data1[i][j]);
                }

            }else{

              var valor = data1[i][j];
              var monto = data1[i][j];//valor.substring(0,valor.indexOf("["));
              var qty = valor.substring(valor.indexOf("[")+1 ,valor.indexOf("]")) ;

              var amount=parseFloat(monto);

              
              //,
                  //    'f': '$'+amount.toString()+ '  Qty :'+qty
              tmp = {
                      'v': amount
                    }
              arr1.push(tmp)
              arr2.push(amount);
  
              //TOMA TODAS LAS LINEAS EXCEPTO LA ULTIMA LINEA DEL TOTAL   
              if(i<data1.length-1){
                arrGrapNOTotal.push(amount);
              }


              //SOLO TOMA LA ULTIMA LINEA DEL TOTAL  
              if(i==data1.length-1){
                arrTotal.push(amount);
              }


            }

        };
       arrprincipal.push(arr1);
       arrGraph.push(arr2);

        //TOMA TODAS LAS LINEAS EXCEPTO LA ULTIMA LINEA DEL TOTAL   
       if(arrGrapNOTotal.length>0){
         arrGrapSINTotal.push(arrGrapNOTotal);
       }
     }
     arrGrapTotal.push(arrTotal);  // Solo para graficar Los totales por medicos
     buildReportStruct(arrGraph,' ',arrprincipal, arrGrapTotal,arrGrapSINTotal);
    
}

$('#tipo').change(function(){
	var sd  =  $('#sd').val();
	var ed  =  $('#ed').val();
	if ( $('#tipo').val() !=='1') {
		$('#sltprod').hide();
	}else{
		$('#sltprod').show();
	}
	
	if (sd!=='' && ed!=='' ) {
       reportGenerator();
	}	
});

function reportData(type,sd,ed,rpt){
  //var seleccion=selected();
	data = {
		type : type,
		sd   : sd,
		ed   : ed,
		rpt  : rpt,
    pro  : 'seleccion'
	}
    var res = getData("../../controllers/emtcontroller.php",data);
    var jsonArray = JSON.parse(JSON.stringify(res));
    var rx = JSON.stringify(res);
    items = jQuery.parseJSON(res);
    return  items;//jsonArray;//items ; //JSON.stringify(items);
}

$('#submit').click(function(){
  
	reportGenerator();
})


function reportGenerator(){
var DATA 	= [];
  var type    =  $('#tipo').val();
	var sd      =  $('#sd').val();
	var ed      =  $('#ed').val();
	var titulo = $("#tipo option[value="+$('#tipo').val()+"]").text();
    var datarow =[];
    var sales   =  reportData(type,sd,ed,"");
if (sales[0][0].substring(0,1)!=="q") {
    for (var i = 0; i < sales.length; i++) {

   	    datarow =[];

   	    for (var j = 0; j < sales[i].length; j++){
   	    	var row = sales[i][j];

   	    	if (i>0 && j>0) {
   	       		datarow.push(parseFloat(row));
   	    	}else{
            if (row.substring(0,1)=="q") {
              datarow.push(row.substring(1));
            }else{
   	       		datarow.push(row);
            }
   	    	} 

   	    }
   	    DATA.push(datarow);
   }
	buildReportStruct(DATA,titulo);
}else{
  buildReportQuantity(sales);
}
}


google.charts.load('current', {'packages':['corechart','table']});
//----------------------------------------------------------------------
function drawChart(args,len,titulo,htmlElement){
 // google.charts.load('current', {'packages':['corechart']});
 google.charts.setOnLoadCallback(dVisualization);

	function dVisualization() {

	var data = google.visualization.arrayToDataTable(args);

	var options = {
		title : titulo,
   animation: {
          duration: 1500,
          easing: 'linear',
          startup: true
        },
		vAxis: {title: titulo+' $'},
		hAxis: {title: ' '},
		seriesType: 'bars',
		series: {len: {type: 'line'}}
	};

	var chart = new google.visualization.ComboChart(document.getElementById(htmlElement));
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

        datos.addRows(args);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(datos, {showRowNumber: true, width: '100%', height: '100%'});
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
   var selectedArray = new Array();
  var selObj = $('#sltprod');
  var i;
  var count = 0;
  var srtSlect="";
  for (i=0; i<selObj[0].options.length; i++) {
    if (selObj[0].options[i].selected) {
      selectedArray[count] = selObj[0].options[i].value;
      count++;
    }
  }
if (selectedArray.length==1) {
    srtSlect=selectedArray[0];
}
if (selectedArray.length>1) {
    for (var i = 0; i < selectedArray.length; i++) {

      if (  i==selectedArray.length-1) {
         srtSlect = srtSlect + selectedArray[i];
      }else{
         srtSlect = srtSlect + selectedArray[i]+"'"+','+"'";
      }
    }
}
return srtSlect;
}


function reportGranTotal(sd,ed){
  //var seleccion=selected();
  data = {    
    sd   : sd,
    ed   : ed    
  }
    var res = getData("../../clases/emtclass.php",data);
    var jsonArray = JSON.parse(JSON.stringify(res));
    var rx = JSON.stringify(res);
    items = jQuery.parseJSON(res);
    //return  items;//jsonArray;//items ; //JSON.stringify(items);

//format2(numbers[i], "$")

//    $('#grantotal').html('Gran Total US$ '+Number(items[0].total));    
    var totalusd=0;
    totalusd=Number(items[0].total);
    $('#grantotal').html('Gran Total '+format2(totalusd,"$"));    
}

$('#submit').click(function(){
  
  reportGenerator();
})


function format2(n, currency) {
    return currency + " " + n.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
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

