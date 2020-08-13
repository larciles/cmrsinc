$(function(){


getSelectOpProd();

function getSelectOpProd(){
    data = {
    prod_serv : "p",
    orderby   : "desitems"
  }
    var res = getData("../../clases/getprodtoinvoice.php",data);
    items = jQuery.parseJSON(res);
    var options;
    for (var j = 0; j < items.length; j++) { 
        options+="<option value='"+items[j].coditems+"'>"+items[j].desitems.toUpperCase()+"</option>"; 
    }
    $("#sltprod").html(options);
}


$("#wait").hide(); 
$('#xtitulo').html('<b>Reporte Comparativo</b>');

$('.datepicker').datepicker({
  todayHighlight: true,
  autoclose:true
});

$('.datepicker').change(function(){
   var fecha,search ="";
   fecha =$('.datepicker').val();
   search = $('#search').val();
});


function buildReportStruct(data,titulo){	
  len=data.length-1;
  data1=data;
  if (data1[0][1]!=="") {
     drawChart(data1,len,titulo);
     chartTest(data1);
  }else{
    $("#columnchart").html("<h1>Información no disponible para este período</h1>");
    $("#table_div").html("");
  }
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
  var seleccion=selected();
	data = {
		type : type,
		sd   : sd,
		ed   : ed,
		rpt  : rpt,
    pro  : seleccion
	}
    var res = getData("../../controllers/comparativocontroller.php",data);
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

    for (var i = 0; i < sales.length; i++) {

   	    datarow =[];

   	    for (var j = 0; j < sales[i].length; j++){
   	    	var row = sales[i][j];

   	    	if (i>0 && j>0) {
   	       		datarow.push(parseFloat(row));
   	    	}else{
   	       		datarow.push(row);
   	    	} 

   	    }
   	    DATA.push(datarow);
   }
	buildReportStruct(DATA,titulo);
}


google.charts.load('current', {'packages':['corechart','table']});
//----------------------------------------------------------------------
function drawChart(args,len,titulo){
 // google.charts.load('current', {'packages':['corechart']});
 google.charts.setOnLoadCallback(dVisualization);

	function dVisualization() {

	var data = google.visualization.arrayToDataTable(args);

	var options = {
		title : 'Reporte comparativo de '+titulo,
		vAxis: {title: titulo+' $'},
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

