    
var date_input=$('input[name="from"]'); //our date input has the name "date"
var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
date_input.datepicker({
	format: 'mm/dd/yyyy',
	container: container,
	todayHighlight: true,
	autoclose: true,
})

var date_input2=$('input[name="to"]'); //our date input has the name "date"
var container2=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
date_input2.datepicker({
    format: 'mm/dd/yyyy',
    container: container,
    todayHighlight: true,
    autoclose: true,
})
$(function(){
	$('.datepicker').datepicker();
})


$("#pdfprint").click(function(){
	var f =$( "#from" ).val();  //from day
	var t =$( "#to" ).val();    //to
	var s ="0"; //stop
	if ($('#chkstop').prop('checked')) {
	     s="1"
	}
	var ok1=false;
	var ok2=false;

	check=/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;

	if(f.match(check)){
		ok1=true;
	}

	if(t.match(check)){
		ok2=true;
	}


	if(ok1 && ok2){
		window.open('../../reportes/sms/repsmsreplies.php?f='+f+'&t='+t+'&s='+s,'titulo', 'width=500, height=500')
	}
	
})
