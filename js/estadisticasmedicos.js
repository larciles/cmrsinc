$(function(){

//$('.spinner').hide();
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
$('#xtitulo').html('<b>Reporte Estadísticas Médicos </b>');

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






function reportData(type,sd,ed,rpt){
  //var seleccion=selected();
	data = {
		type : type,
		sd   : sd,
		ed   : ed,
		rpt  : rpt,
    pro  : 'seleccion'
	}
    var res = getData("../../clases/estadisticasmedicosclass.php",data);
    var jsonArray = JSON.parse(JSON.stringify(res));
    var rx = JSON.stringify(res);
    items = jQuery.parseJSON(res);
    return  items;//jsonArray;//items ; //JSON.stringify(items);
}

$('#submit').click(function(evento){  
   evento.preventDefault(); 
   $(".spinner").css("display", "inline");
	reportGenerator();
})


function reportGenerator(){
var DATA 	= [];
  var type    =  $('#tipo').val();
	var sd      =  $('#sd').val();
	var ed      =  $('#ed').val();


  var sales   =  reportData(type,sd,ed,"");
  //TITULOS DE LAS ROWS
  var arrtitulos = ['Facturas por médico', 'Unidades por médico','Unidades por paciente', 'Fórmula por pacientes', 'Pacientes vistos','Facturas por paciente','Fómulas por médico','% Fómulas & Unidades','Productos + Servicios'];
  //CAMPOS QUE CORRESPINDE A CADA ROW
  var arrcampos =  ['facturas_x_medico','unidades_medicos','unidades_pacientes','formula_paciente','numeroPacientes','facturacion_paciente','formula_medicos','formulas_unidades','ventas_prodserv'] 
  //TIPO DE DATO DE LA TABLA
  var arrcampostipo =  ['string','string','string','string','string','string','string','string','string'] 
  var arrtmp=[];
  var arrmedico=[]; 
  
  //TITULOS SUPERIORES  DEL CUADRO  (COLUMNS)
  for (var i = 0; i < sales.length; i++) {
      //  VARIABLE  = TITULO                TIPO DE DATO         
      let item_tit = sales[i]['medico']+'/'+arrcampostipo[i];
      
      arrmedico.push(item_tit.split("/"))  // SE CONVIERTEN ARRAY Y SE AGRERA EN OTRO
  };
  let item_tit = 'Total'+'/'+'string'; //
  arrmedico.push(item_tit.split("/")) 

  //DATOS DEL CUADRO
  let total =0;
  for (var i =0; i< arrtitulos.length ; i++) {

       total =0;
       var items_arr = arrtitulos[i];
       for (var j = 0; j < sales.length; j++) { 
          
          items_arr=items_arr+'/'+parseFloat( isNaN( parseFloat( sales[j][arrcampos[i]] ) ) ? 0 :sales[j][arrcampos[i]] ).toFixed(2) ; //SE UNEN EL NOMBRE DEL ROW Y LOS DATOS ASOCIADOS POR CADA COLUMNA CON ('/') PARA CONVERTIRLO EN UN ARRAY
          total+=  isNaN( parseFloat(  sales[j][arrcampos[i]] ) ) ? 0 : parseFloat(  sales[j][arrcampos[i]]) ;    //CALCULA EL TOTAL DE LA ULTIMA COLUMNA DEL CUADRO

       }
        items_arr=items_arr+'/'+parseFloat( total ).toFixed(2); //AGREGA EL TOTAL EN LA ULTIMA COLUMNA
        arrtmp.push(items_arr.split("/"))  // SE CONVIERTE EN UN ARRAY CON SPLIT CON EL DELIMITADOR ('/') Y SE AGREGA AL ARRAY

  };
  
  
  //PARA LA GRAFICA # 1
  // LA LEYENDA  (NOMBRE DE LOS MEDICOS)
   item_tit=' '+'/';
   let facturacion_paciente=' '+'/';
   arrgraph=[];
   let tmparr=[];
   tmparr.push(' ')
   for (var i = 0; i < sales.length; i++) {
        if ( i < (sales.length)-1 ){
            item_tit = item_tit  + sales[i]['medico']+'/';   //LINEA DE LOS NOBRES DE LOS MEDICOS            
            tmparr.push(parseFloat(parseFloat( sales[i]['facturacion_paciente'] ).toFixed(2) ) )  // LINEA DE LOS DATOS A GRAFICAR EN VALOR NUMERICO
        }else{
            item_tit = item_tit  +  sales[i]['medico'];            
            tmparr.push(parseFloat(parseFloat( sales[i]['facturacion_paciente'] ).toFixed(2) ) )
        }; 
  };

  arrgraph.push(item_tit.split("/"))  // AGREGO LA PRIMERA LINEA PARA LA GRAFICA LOS NOMBRES
  arrgraph.push(tmparr)  //AGREGO LA SEGUNDA LINEA LA VENTA

  //table_titulo ='Facturación Por Pacientes'
  $('#table_titulo').html('Facturación Por Pacientes');    
  chartTest(arrtmp,arrmedico) 

  drawChart(arrgraph,7,'Facturación por Pacientes','columnchart')


    //PARA LA GRAFICA # 2

item_tit=' '+'/';
  // let facturacion_paciente=' '+'/';
   arrgraph=[];
   tmparr=[];
   tmparr.push(' ')
   for (var i = 0; i < sales.length; i++) {
        if ( i < (sales.length)-1 ){
            item_tit = item_tit  + sales[i]['medico']+'/';   //LINEA DE LOS NOBRES DE LOS MEDICOS            
            tmparr.push(parseFloat(parseFloat( sales[i]['ventas_prodserv'] ).toFixed(2) ) )  // LINEA DE LOS DATOS A GRAFICAR EN VALOR NUMERICO
        }else{
            item_tit = item_tit  +  sales[i]['medico'];            
            tmparr.push(parseFloat(parseFloat( sales[i]['ventas_prodserv'] ).toFixed(2) ) )
        }; 
  };

  arrgraph.push(item_tit.split("/"))  // AGREGO LA PRIMERA LINEA PARA LA GRAFICA LOS NOMBRES
  arrgraph.push(tmparr)  //AGREGO LA SEGUNDA LINEA LA VENTA
  drawChart(arrgraph,7,'Ventas Productos + Servicios','graventastotal')

   
  
  $(".spinner").css("display", "none");
}
//----------------------------------------------------------------------
//CUADRO
function chartTest(args,titulo) {
    //  google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTable);
       

      function drawTable() {
        var datos = new google.visualization.DataTable();
        var title='';
        var tipo='';

         datos.addColumn('string', '');  
         for (var i = 0; i < titulo.length; i++) {
            title =titulo[i][0];
            tipo  =titulo[i][1];
            datos.addColumn(tipo,title);     
          }      

        datos.addRows(args);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(datos, {showRowNumber: true, width: '100%', height: '100%'});
      }

}
//----------------------------------------------------------------------

google.charts.load('current', {'packages':['corechart','table']});
//----------------------------------------------------------------------
//GRAFICO
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



//--------------------
function getData(url,data){
  $(".spinner").css("display", "inline");
	return jsonData = $.ajax({
	    type: "POST",
	    url: url,
	    data:data,
	    dataType: "json",
	    async: false
	}).responseText;
}

 })

