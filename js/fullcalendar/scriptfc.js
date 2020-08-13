  background_color='#abb9c3';
  day_number_color='#000000';
  recordnumber='';
  fecha_nueva_cita='';
  mls_val='';
  hilt_val=''
  time='';
  var arr=[];
  var numerico = 0;
  var color ='#1abc9c';

$(document).ready(function() {
     var browser =myFunction()

    // page is now ready, initialize the calendar...
    var today = new Date().toLocaleDateString('en-US', {  
        year : 'numeric',
        month : 'numeric',
        day : 'numeric'        
       
    }).split(' ').join('-');



   date = new Date(today)

   firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

  if (browser == 'Firefox') {
      lastDay = new Date(date.getFullYear(), date.getMonth() + 2, 0)
  }else{
      lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0) 
  };
   

   endfecha = lastDay.getFullYear()+'-'+lastDay.getMonth()+'-'+lastDay.getDate();

      var fecha1 = new Date(firstDay).toLocaleDateString('en-US', {  
        year : 'numeric',
        month : 'numeric',
        day : 'numeric'        
       
    }).split(' ').join('-');


    var fecha2 = new Date(lastDay).toLocaleDateString('en-US', {  
        year : 'numeric',
        month : 'numeric',
        day : 'numeric'        
       
    }).split(' ').join('-');


    //$('#calendar').fullCalendar('getView').intervalStart
    var eventos;
    view='month'
    eventos = realizaProceso(fecha1,view,fecha2);//getLaserAptm(today);


    
  if (view=='day') {

  
    var fechacita ="";
    for (var i = 0; i < eventos.length; i++) {
       if (eventos[i].hora==null) {
           fechastart= eventos[i].fecha_cita.trim().substr(0,10)+' '+'07:00';
           fechaend  = eventos[i].fecha_cita.trim().substr(0,10)+' '+'07:30';
        }else{
           fechastart= eventos[i].fecha_cita.trim().substr(0,10)+' '+eventos[i].hora+':00';

           if (eventos[i].endtime==null) {
                
                var d = new Date(fechastart);                
                
                var t = new Date(d.setMinutes(d.getMinutes()+30));

                fechaend  = t;//eventos[i].fecha_cita.trim().substr(0,10)+' '+eventos[i].endtime;
           }else{
              fechaend  = eventos[i].fecha_cita.trim().substr(0,10)+' '+eventos[i].endtime;
           };
           
        };

       arr.push(
             {
            id     : eventos[i].Historia,
            title  : eventos[i].nombres,
            start  : fechastart,
            end    : fechaend,
            editable:true,
            eventDurationEditable :false
        }
       )
   }
}else  if (view=='month'){

  
  for (var i = 0; i < eventos.length; i++) {
            
           arr.push(
             {
            id     : eventos[i].start,
            title  : eventos[i].title,
            start  : eventos[i].start,
            end    : eventos[i].start,
            editable:false,
            eventDurationEditable :false,
            displayEventTime:false
            // ,
            // eventColor: color
        }
        )
  }


} else  if (view=='listWeek'){

} ;
  
    // var eventos =  {
    //         title  : 'event1',
    //         start  : '2017-11-20'
    //     }

    //     console.log(arr);

    
    $('#calendar').fullCalendar({
 
		    events:arr,

        customButtons: {          
            myCustomButton: {
              text: 'custom!',
              click: function() {
                alert('clicked the custom button!');
              }
            }
        },      
        
          header: {
        	left  : 'prev,next today',
        	center: 'title',
        	right : 'month,agendaDay'
         // right : 'month,agendaDay,myCustomButton'
    	  },      
        selectable: true,
        eventDurationEditable :false,
        minTime: '06:00:00',
        maxTime: '19:00:00',
        timeFormat: 'H:mm',
        allDaySlot: false,
        theme:true,
        eventRender:eventRenderCallback,

       // backgroundColor: '#0095FF',

           eventDrop: function(event, delta){
              // console.log('title : '+ event.title);
              // console.log('start : '+moment(event.start).format());
              // console.log('end   : '+moment(event.end).format());
              // console.log('id    : '+event.id );
              // console.log('key   : '+event.key );
              // console.log('fecha_cita    : '+event.fecha_cita );
              // console.log('codclien    : '+event.codclien );
              // console.log('nombres    : '+event.nombres );

              var time_start = moment(event.start).format().substr(moment(event.start).format().indexOf("T")+1,5);
              var time_end   = moment(event.end).format().substr(moment(event.end).format().indexOf("T")+1,5);
              
              console.log(time_start);
              console.log(time_end);

              var param ={
                fecha_cita : event.fecha_cita,
                codclien   : event.codclien,
                key        : event.key,
                time_start : time_start,
                time_end   : time_end
              }

              $.ajax({
                   url: '../../clases/updatelaserapp.php',
                   data: param,
                   type: "POST",
                   success: function(json) {
                       //alert(json);
                   }
              });

           },
            eventRender: function(event, element) {
              $(element).find(".fc-time").remove();

              if ($('#calendar').fullCalendar('getCalendar').view.name=="agendaDay") {
                 if (numerico==1) {
                  //  return ['', event.historia].indexOf($('#fcrecord').val()) >= 0  
                      return ['', event.historia.substring(0,$('#fcrecord').val().length)].indexOf($('#fcrecord').val()) >= 0                 
                 }else if (numerico==2) {
                      //return ['', event.title.substring(0,$('#fcrecord').val().length)].indexOf($('#fcrecord').val().toUpperCase()) >= 0  
                      return ['', event.title][1].indexOf($('#fcrecord').val().toUpperCase()) >= 0  
                 };
              };


              //element.css('background-color', color );
              //$('.fc-day').css('background-color', background_color)


              
           }
           ,
           eventResize: function(event) {
              console.log('title : '+ event.title);
              console.log('start : '+moment(event.start).format());
              console.log('end   : '+moment(event.end).format());
              console.log('id    : '+event.id );
              $('#calendar').fullCalendar({
            minTime: "07:00:00",
            maxTime: "21:00:00",
            });
           
           },

          dayClick: function(date, jsEvent, view) {

        //alert('Clicked on: ' + date.format());
        //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
        //alert('Current view: ' + view.name);
        //change the day's background color just for fun

       // $(this).css('background-color',background_color);
        if (view.name == 'month') {
           // alert('Clicked on: ' + date.format());
            $('#calendar').fullCalendar('changeView', 'agendaDay');
            $('#calendar').fullCalendar('gotoDate', date);
            //$('.fc-day-number').css('color', '#dddddd');
            fillNewEvents('day',date.format());
            //$('.fc-day').css('background-color',background_color);

        }else{

            fillNewEvents(view.name );   
        }
         eventRenderCallback()

        },
        eventClick: function(calEvent, jsEvent, view) {

        // alert('Event: ' + calEvent.title);
        // alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
        // alert('View: ' + view.name);
        recordnumber='';
        fecha_nueva_cita='';
        mls_val='';
        hilt_val=''
        time=''
        time=calEvent.start._i.substr(11,5);
        hilt_val=calEvent.hilt;
        mls_val = calEvent.mls;
        fecha_nueva_cita=calEvent.start._i.substr(0,10);
        recordnumber = calEvent.id;

        // change the border color just for fun
        $('#exampleModal').modal('show'); 
        $(this).css('border-color', 'red');

    } 

    }).on('click', '.fc-next-button', function() {
      var view = $('#calendar').fullCalendar('getView'); 
      
      //$('.fc-day-number').css('color', '#dddddd');
      fillNewEvents(view.name );
        if (view.name == 'month') {
           //$('.fc-widget-content').css('background-color', background_color);
       }else{
          // $('.fc-day').css('background-color',background_color);
        }
    }).on('click', '.fc-prev-button', function() {
      

       var view = $('#calendar').fullCalendar('getView');
       //$('.fc-day-number').css('color', '#green');
       fillNewEvents(view.name );
       if (view.name == 'month') {
         // $('.fc-widget-content').css('background-color',background_color);
        }else{
        //  $('.fc-day').css('background-color',background_color);
        }
    }).on('click', '.fc-today-button', function() {
       var view = $('#calendar').fullCalendar('getView');
       fillNewEvents(view.name );
    }).on('click', '.fc-month-button', function() {

       var view = $('#calendar').fullCalendar('getView');
      // $('.fc-widget-content').css('background-color',background_color);
       fillNewEvents(view.name );
       //
      // $('.fc-day-number').css('color', '#dddddd');
    }).on('click', '.fc-agendaDay-button', function() {
      
     // $('.fc-day-number').css('color', '#dddddd');
       var view = $('#calendar').fullCalendar('getView');
       fillNewEvents(view.name );
      //$('.fc-day').css('background-color', background_color);

    }).on('click', '.fc-listWeek-button', function() {
       var view = $('#calendar').fullCalendar('getView');
       fillNewEvents(view.name );
       //$('.fc-day').css('background-color',background_color);

    })

   //$('.fc-widget-content').css('background-color',background_color);
   //$('.fc-day-number').css('color', '#dddddd');
   

});



    function realizaProceso(fecha,view,fecha2='',horai,horaf){
        var laser_type ='mls';
       // color ='red'; //#373a3c  // background del evento en month
        if (!$('#laser_type').prop('checked')) {
            laser_type="hilt"
          //  color ='#2c3e50';
        };
	      var result='';
        var parametros = {
               fecha,
               view,
               fecha2,
               laser_type,
               horai,
               horaf
        };
        $.ajax({
                data:  parametros,
                url:   '../../clases/lasercitados.php',
                type:  'post',
                beforeSend: function () {
                    $("#resultado").html("Procesando, espere por favor...");
                },
                success:  function (response) {
					$("#resultado").html("");					
                    result =  jQuery.parseJSON(response); 
					console.log( result.length)  ;
                },
        async: false
        });

return result;
}  

 function myFunction() { 
     let browser=''
     if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ) 
    {
        browser ='Opera';
    }
    else if(navigator.userAgent.indexOf("Chrome") != -1 )
    {
        browser ='Chrome';
    }
    else if(navigator.userAgent.indexOf("Safari") != -1)
    {
        browser='Safari';
    }
    else if(navigator.userAgent.indexOf("Firefox") != -1 ) 
    {
         browser = 'Firefox';
    }
    else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )) //IF IE > 10
    {
      browser = 'IE'; 
    }  
    else 
    {
       browser = 'unknown';
    }
return browser;
 }

   function GetCalendarDateRange() {
        var calendar = $('#calendar').fullCalendar('getCalendar');
        var view = calendar.view;
        var start = view.start._d;
        var end = view.end._d;
        var dates = { start: start, end: end };
        return dates;
    }

    function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

function fillNewEvents(view,startday='',horai='',horaf=''){
  let cd= $('#calendar').fullCalendar( 'getDate' )
  if (view == 'month') {
        arr=[];
        $('#calendar').fullCalendar( 'removeEvents');
          var nuevafechai= $('#calendar').fullCalendar('getView').intervalStart
          var range = GetCalendarDateRange();
          start_a=formatDate(range.start.toDateString());
          end_a=formatDate(range.end.toDateString());
          eventos_a = realizaProceso(start_a,view,end_a);
          console.log(eventos_a);
          for (var i = 0; i < eventos_a.length; i++) {

               arr.push(
                 {
                id     : eventos_a[i].start,
                title  : eventos_a[i].title,
                start  : eventos_a[i].start,
                end    : eventos_a[i].start,
                editable:false,
                eventDurationEditable :false,
                displayEventTime: false

                  }
                )
          }          

          $('#calendar').fullCalendar( 'removeEvents');
          $('#calendar').fullCalendar( 'addEventSource', arr);
  }else if (view == 'agendaDay' || view == 'day' ) {
     if (view == 'agendaDay') {
         let fa = cd.toString().replace('GMT+0000','');
         date = new Date(fa)    

          startday = new Date(fa).toLocaleDateString('en-US', {  
          year : 'numeric',
          month : 'numeric',
          day : 'numeric'        
       
    }).split(' ').join('-');

     };

      arr=[];
      $('#calendar').fullCalendar( 'removeEvents' );      
      eventos_a = realizaProceso(startday,'day','',horai,horaf);

          for (var i = 0; i < eventos_a.length; i++) {

               if (eventos_a[i].Historia=='54424') {
                  console.log( eventos_a[i].endtime)
              }; 

              if (eventos_a[i].hora==null || eventos_a[i].hora=='') {
                  fechastart= eventos_a[i].fecha_cita.trim().substr(0,10)+' '+'07:00';
                  fechaend  = eventos_a[i].fecha_cita.trim().substr(0,10)+' '+'07:30';
              }else{

                if ( parseInt(eventos_a[i].hora)>=1 && parseInt(eventos_a[i].hora)<=6 ) {
                    eventos_a[i].hora = eventos_a[i].hora.replace(eventos_a[i].hora.substring(0,2),parseInt(eventos_a[i].hora)+12)
                 };

                  fechastart= eventos_a[i].fecha_cita.trim().substr(0,10)+' '+eventos_a[i].hora+':00';

                  if (eventos_a[i].endtime==null) {
                
                      var d = new Date(fechastart);                                
                      var t = new Date(d.setMinutes(d.getMinutes()+30));
                      fechaend  = t;//eventos[i].fecha_cita.trim().substr(0,10)+' '+eventos[i].endtime;
                  }else{
                      if ( parseInt(eventos_a[i].endtime)>=1 && parseInt(eventos_a[i].endtime)<=6 ) {
                           eventos_a[i].endtime = eventos_a[i].endtime.replace(eventos_a[i].endtime.substring(0,2),parseInt(eventos_a[i].endtime)+12)
                      };
                      fechaend  = eventos_a[i].fecha_cita.trim().substr(0,10)+' '+eventos_a[i].endtime;              
                  };
           
        };

       arr.push(

             {
            id     : eventos_a[i].Historia.trim(),
            title  : eventos_a[i].nombres+' | '+eventos_a[i].Historia,
            start  : fechastart,
            end    : fechaend,
            editable:true,
            eventDurationEditable :false,
            historia : eventos_a[i].Historia.trim(),
            key : eventos_a[i].id,
            fecha_cita : eventos_a[i].fecha_cita.substring(0,10),
            codclien : eventos_a[i].codclien,
            nombres : eventos_a[i].nombres,
            rendering: 'inverse-backgroun',
            // backgroundColor: '#0054d3',
            // textColor : '#D35400',            
            mls  : eventos_a[i].mls,
            hilt : eventos_a[i].hilt
        }
       )
   }
     $('#calendar').fullCalendar( 'removeEvents');
     $('#calendar').fullCalendar( 'addEventSource', arr);
  }

}

$('#fcrecord').keyup(function(){
  if (!isNaN($('#fcrecord').val())) {
    numerico = 1;
  }else if (isNaN($('#fcrecord').val())){
    numerico = 2;
  };
    
    $('#calendar').fullCalendar('rerenderEvents');
    numerico = 0;
})


$('#fcrecord').keypress(function(){
  if (!isNaN($('#fcrecord').val())) {
    numerico = 1;
  }else if (isNaN($('#fcrecord').val())){
    numerico = 2;
  };
    
    $('#calendar').fullCalendar('rerenderEvents');
    numerico = 0;
})

$('#laser_type').change(function() {

    var view = $('#calendar').fullCalendar('getView');

    if ( $('#fcrecord').val()!='' ) {
        fillNewEvents(view.name );
        if (!isNaN($('#fcrecord').val())) {
            numerico = 1;
        }else if (isNaN($('#fcrecord').val())){
            numerico = 2;
        };
    
        $('#calendar').fullCalendar('rerenderEvents');
        numerico = 0;
    } else{
      
      fillNewEvents(view.name );
   };
     


})

$('#calendar').on('click','tbody tr td',function(event) {
  event.preventDefault();
  event.stopPropagation();
  var target = $( event.target )
  var horainicial ="";
  var horafinal ="";

  if ( target.parent().parent().is( "tr" ) ) {
     horainicial= target.parent().parent().attr("data-time").substr(0,3)+'00';
     horafinal  = target.parent().parent().attr("data-time").substr(0,3)+'59';
  }else if(target.parent().is( "tr" )){
    horainicial= target.parent().attr("data-time").substr(0,3)+'00';
    horafinal  = target.parent().attr("data-time").substr(0,3)+'59';
  }
 if (horainicial!=="") {
    var view = $('#calendar').fullCalendar('getView'); 
    fillNewEvents(view.name,'',horainicial,horafinal );
 };
  


})

$('#calendar').on('click','thead th.fc-axis',function(event) {
  event.preventDefault();
  event.stopPropagation();
  var target = $( event.target )
  var view = $('#calendar').fullCalendar('getView'); 
  fillNewEvents(view.name);
})

function eventRenderCallback(event, element, view){
    var title = element.find(".fc-title").val();
    element.find(".fc-title").html("<b>"+title+"</b>");
}