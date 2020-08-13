var from = $('#from').val(); //our date input has the name "date"
var to = $('#to').val()


data ={	
	from  : from,
	to : to
}
var res= getData("../../clases/getcountsentmsg.php",data);
items= jQuery.parseJSON(res);     
//$('#enviados').innerHTML(items[0].num_rows);
$('#enviados').html(items[0].num_rows)
function getData(url,data){

    return $.ajax({
        type: "POST",
        url: url,
        data:data,
        async: false
    }).responseText;
}
