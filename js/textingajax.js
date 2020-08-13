//var xres = ajaxGen("https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJMQP5vVVYKowRnbLbszwq6BI&key=AIzaSyAm9F5yUfpYWqrcagRnMZmZtSx8BEOwBZ0")
//var telefono = xres.result.formatted_phone_number;

//$( "p:first" ).html( telefono );

// @RequestMapping(value="https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJMQP5vVVYKowRnbLbszwq6BI&key=AIzaSyAm9F5yUfpYWqrcagRnMZmZtSx8BEOwBZ0",method = RequestMethod.GET)




// $.ajax({
//     url: "https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJMQP5vVVYKowRnbLbszwq6BI&key=AIzaSyAm9F5yUfpYWqrcagRnMZmZtSx8BEOwBZ0",
//     // headers: {
//     //     'Content-Type': 'application/x-www-form-urlencoded'
//     // },
//     type: "GET",  or type:"GET" or type:"PUT" 
//     dataType: "jsonp",
//     data: {},
//     crossDomain: true,
//     success : function (result) {
//       console.log(result);    
//     },
//     error: function (result) {
//       console.log(result);
//     },
//     always: function(result) {                
//        console.log(result);
//                 }
// });



$.ajax({

    url: 'https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJMQP5vVVYKowRnbLbszwq6BI&key=AIzaSyAm9F5yUfpYWqrcagRnMZmZtSx8BEOwBZ0',
    data: {},
     headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
    },
    type: 'GET',
    crossDomain: true,
    dataType: 'json',
    success: function() { alert("Success"); },
    error: function() { alert('Failed!'); }
});


$.ajax({
    url: "https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJMQP5vVVYKowRnbLbszwq6BI&key=AIzaSyAm9F5yUfpYWqrcagRnMZmZtSx8BEOwBZ0",
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    type: "GET",  // or type:"GET" or type:"PUT" 
    dataType: "json",
    crossDomain: true,
    success : function (result) {
      console.log(result);    
    },
    error: function (result) {
      console.log(result);
    },
    always: function(result) {                
       console.log(result);
                }
});