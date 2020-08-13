
if (window.location.protocol != "https:") {
  var socket = io.connect('http://192.130.74.2:3000', {secure:true});  
}else{
  
  var socket = io.connect('https://localhost:3001');
}
socket.on('message',function(msg,id){
    if (socket.id!==id) {
      blink(msg);  
    };
  
});

socket.on('noanswered',function(msg,id){
	noanswered(msg)
});

function blink(id) { 
  $('#'+id).addClass("blinking"); 
};

function noanswered(id) { 	
     let el=$('#'+id);
     if(el.hasClass("table-info")){
           $('#'+id).removeClass("table-info");           
     }else{
           $('#'+id).addClass("table-info");
    
     }
};

// $(function() {
//       var FADE_TIME = 150; // ms
//       var TYPING_TIMER_LENGTH = 400; // ms
//       var COLORS = [
//         '#e21400', '#91580f', '#f8a700', '#f78b00',
//         '#58dc00', '#287b00', '#a8f07a', '#4ae8c4',
//         '#3b88eb', '#3824aa', '#a700ff', '#d300e7'
//       ];

//       // variables
//       var $window = $(window);
//       var $miMensaje=$('.miMensaje');
//       var $messages = $('.messages'); // Messages area
//       var conectado = false;
//       var escribiendo = false;
//       var typing=false;
//       var usuario="";

      
//       $(".messages").hide();
//       $(".messages").append(usuario+' está escribiendo' );

//       $(function(){

//           //metodo que envia los mensajes y valida que no envie mensaje en blanco
//           $("form").submit(function(){
//             var mensaje=$("#msj").val();
//             if(mensaje=='') return false;
//             socket.emit('message',mensaje);
//             $("#msj").val('').focus();
//             return false;
//           });

//           //metodo de cambio de sala 'Room'
//           $("#room").change(function(){
//             socket.emit('cambio room',$("#room").val());
//           });

//           // metodo de desconexion del socket
//           $("#exit").click(function(){
//             socket.disconnect(); 
//           });

//           //metodo reconecta con el mismo socket ID
//           $("#reconnect").click(function(){
//             socket.connect();
//           });
//       });

//     //metodo listener que recibe los nuevos mensajes
//     socket.on('message',function(msg,id){
//      // $("#message").append($('<li>').text(id+': '+msg));
//      blink(msg);
//       if(typing){
//         typing=false;
//         detenTyping();
//       }     
//     });

//     //listener que nos avisa del cambio de room 'sala'
//     socket.on('cambio room',function(room){
//       $("#message").html('').append($('<li>').text('System : bienvenida a la nueva Sala  '+room+'!'));
//     });

//     //metodo listener de bienvenida
//     socket.on('mensaje', function(data){
//       //$("#mensajes").append('<li>' + data.text + ' </p>');
//       $("#message").append($('<li>').text( data.bienvenida ));
//       connected = true;
//       usuario=data.id;
//     });
      
//      // actualiza el evento de escritura, si alquien esta escribiendo
//     function actualizaEscritura () {
//       if (connected) {

//         if (!escribiendo) {
//           escribiendo = true;
//           socket.emit('escribiendo');
//         }
//         lastTypingTime = (new Date()).getTime();

//         setTimeout(function () {
//           var typingTimer = (new Date()).getTime();
//           var timeDiff = typingTimer - lastTypingTime;
//           if (timeDiff >= TYPING_TIMER_LENGTH && escribiendo) {
//             socket.emit('stop typing');
//             escribiendo = false;
//           }
//         }, TYPING_TIMER_LENGTH);

//       }
//     }

//     // captura si alquien esta escribiendo
//     $miMensaje.on('input', function() {
//          actualizaEscritura();
//     });

//     //oculta elmensaje de escribiendo
//     function detenTyping()
//        {      
//         $( ".messages" ).fadeOut(300);
//     }

//     //Listener si alguien esta escribiendo  muestra el mensaje
//     socket.on('escribiendo', function (data) {
//         if (!typing){
//             $(".messages").html("<b>"+data.username+"</b>"+"<span>"+" está escribiendo"+"</span>");
//             //$( ".messages" ).css("display", "block").fadeIn(13000);
//             $( ".messages" ).fadeIn(3000);;
//            typing=true
//         }
//     });


//   function blink(id) { 
//   $('#'+id).addClass("blinking"); 
// };
    
// });