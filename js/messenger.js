  var cen_location ="https://www.google.com/maps/place/Centro+M%C3%A9dico+Adapt%C3%B3geno/@18.3994274,-66.1551514,17z/data=!4m5!3m4!1s0x8c036a3076a835ff:0x4d7f5bce3afd9b8a!8m2!3d18.399487!4d-66.155289?hl=en"
  var local=false
  var auto=true; //auto answer
  var saludos='';
  var patient_name='';
  // EN MI PC LOCAL
  if (local) {
      var socket = io.connect('http://localhost:3003'); 
  }else{
      var socket = io.connect('https://192.130.74.2:3003/');
  }; 
  //

  var paciente
  var telefono

    $(".heading-compose").click(function() {
      $(".side-two").css({
        "left": "0"
      });
    });

    $(".newMessage-back").click(function() {
      $(".side-two").css({
        "left": "-100%"
      });
    });


    

    $('#searchText').on('keydown', function(e) {
    if (e.which == 13) {
        e.preventDefault();        
        buscarPaciente($("#searchText").val(),'out') 
    }
});

function buscarPaciente(celphone, type){
  let _time=getTime();
  var  options=''
  $.ajax({
    type:'POST',    
    url: "../../clases/getpacientebyidtelname.php",
    data:{q: celphone },
    dataType:'json',
    async: false,
    success:function(d){
   
   
        if(d.length>0){
                  
                  paciente = d[0].nombres;
                  telefono = d[0].Cedula;
                 //telefono=telefono.replace('-','').replace('-','');
        
                 // NOBRE DEL CONTACTO EN LA PARATE SUPERIOR DE LOS MENSAJES
                 if (type=='out') {
                    $('.heading-name-meta').text(paciente)
                    $('.heading-celphone').text(telefono)
                 }
                 
                 if (!findNode(telefono)) {
                     //SE LLAMA A LA FDUNCION PARA QUE CREE DINAMICAMENTE EL CONTACTO
                     $('.row.sideBar').append(newPeople("",paciente,_time," v a v ","1","Super",telefono));         
            
                     //SE ASIGNA EL EVENTO AL OBJETO RECIEN CREADO DINAMICAMENTE         
                     ev=document.getElementsByClassName('people')[document.getElementsByClassName('people').length-1];
                     ev.addEventListener('click',selectContact)
                 }
          }else if(d.length>1){

                 if (type=='out') {
                    $('#sb').hide() 
                    $('.sltcontact').show()
  
                    for (var mi =0; mi < d.length;  mi++) {                        
                         options+="<option value='"+d[mi].Cedula+"'>"+d[mi].nombres+"</option>";   
                    }
                    $('#sltcontact').html(options);
                    options='';  
                 }else{
                    paciente = d[0].nombres;
                    telefono = d[0].Cedula;
                    if (!findNode(telefono)) {
                         //SE LLAMA A LA FDUNCION PARA QUE CREE DINAMICAMENTE EL CONTACTO
                         $('.row.sideBar').append(newPeople("",paciente,_time," v a v ","1","Super",telefono));         
            
                        //SE ASIGNA EL EVENTO AL OBJETO RECIEN CREADO DINAMICAMENTE         
                        ev=document.getElementsByClassName('people')[document.getElementsByClassName('people').length-1];
                        ev.addEventListener('click',selectContact)
                    }
                 }  



          }else{
            if (!findNode(celphone)) {
                 //SE LLAMA A LA FDUNCION PARA QUE CREE DINAMICAMENTE EL CONTACTO
                 $('.row.sideBar').append(newPeople("",celphone,_time," v a v ","1","Super",celphone));         
                 
        
                 //SE ASIGNA EL EVENTO AL OBJETO RECIEN CREADO DINAMICAMENTE         
                 ev=document.getElementsByClassName('people')[document.getElementsByClassName('people').length-1];
                 ev.addEventListener('click',selectContact)
            }
          }      
    },
    beforeSend: function(){
        // Code to display spinner
    },
    complete: function(){
        // Code to hide spinner.
    }
});
}


function newPeople(img,name,time,message,status,username,telefono) {
  var head = '<div class="row people"> <div class="row sideBar-body" id='+telefono+'> '+
             ' <div class="col-sm-3 col-xs-3 sideBar-avatar">'+
                '<div class="avatar-icon">'+
                  '<img src="http://shurl.esy.es/y">'+
                '</div>'+
              '</div>'+
              '<div class="col-sm-9 col-xs-9 sideBar-main">'+
                '<div class="row">'+
                  '<div class="col-sm-8 col-xs-8 sideBar-name">'+
                    '<span class="name-meta">'+name+
                  '</span>'+
                  '</div>'+
                  '<div class="col-sm-4 col-xs-4 pull-right sideBar-time">'+
                    '<span class="time-meta pull-right">'+time+
                  '</span>'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div> </div>';

  return head;
}

function getTime(){
   d = new Date();
   datetext = d.toTimeString();
   datetext = datetext.split(' ')[0];
   datetext = datetext.split(':')
   _time=datetext[0]+':'+datetext[1];
  return _time;
}


function phoneFormat( phonenumber) {
        phonenumber = phonenumber.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
        return phonenumber;
}


//EVENTS

$('.reply-emojis').on('click',function(e){
        e.preventDefault();
        var en_lo=encodeURIComponent(cen_location)
        wn_lo='<div class="_3hy7L selectable-text invisible-space copyable-text" data-plain-text="https://maps.google.com/maps/search/Centro%20M%C3%A9dico%20Adapt%C3%B3geno/@18.39942741394043,-66.1551513671875,17z?hl=en" style="height: 150px;"> <a href="https://maps.google.com/maps/search/Centro%20M%C3%A9dico%20Adapt%C3%B3geno/@18.39942741394043,-66.1551513671875,17z?hl=en" target="_blank" class="_2i3pg"> <img crossorigin="anonymous" src="https://maps.googleapis.com/maps/api/staticmap?zoom=15&amp;size=270x200&amp;scale=1&amp;language=en&amp;client=gme-whatsappinc&amp;markers=color%3Ared%7C18.39942741394043%2C%20-66.1551513671875&amp;signature=MSscblxI3dO_IO7k-YSI7fW3JCk=" class="_1Qnxi" style="pointer-events: none; width: 270px; visibility: visible;"> </a> </div>'
        $('#comment').val(cen_location);
        send_message()

  
});

$('#comment').on('keydown', function(e) {
    if (e.which == 13) {
        e.preventDefault();        
        send_message()
    }
});

//CLICK PARA ENVIAR EL MENSAJE
$('.reply-send').click(function(){
  send_message()  
});


function send_message(){
  //OBJETO CON LOS DATOS PARA ENVIAR
  let _mensaje ={
              to : telefono,
              from : $('#user >a').text(),
              name : paciente,
              message :  $('#comment').val(),
              id :socket.id,
              type :'send'
  }
   
  socket.emit('message',_mensaje);  
 
  $('#conversation').append( sentMessage(_mensaje.message) );   
  console.log(_mensaje)


  var time = new Date();

  let hora = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true })


 saveData( _mensaje.to,_mensaje.message,hora,2,'i',_mensaje.from )


  //LIMPIA EL INPUT TEXT DE LOS MENSAJES
  $('#comment').val('')

  objDiv = document.getElementById("conversation"); 
  objDiv.scrollTop = objDiv.scrollHeight

}


//Listening Cuando llega un nuevo mensaje
socket.on('message',function(msg,id){    
});




//Crea un nuevo elemento de llegada
function inCommingMesseage(message) {
  var head = '<div class="row message-body"> <div class="col-sm-12 message-main-receiver"> <div class="receiver"> <div class="message-text"> ' + message + '</div> ' +
                '<span class="message-time pull-right"> ' +
                  'Sun ' +
                '</span> ' +
              '</div> ' +
            '</div> ' +
          '</div>';
  return head;
}


//Crea un nuevo elemento de salida (Mensaje Enviado)
function sentMessage(message){
 var head = ' <div class="row message-body"> ' +
            '<div class="col-sm-12 message-main-sender"> '+
              '<div class="sender">'+
                '<div class="message-text">'+message+
                '</div>'+
                '<span class="message-time pull-right">'+
                  'Sun'+
                '</span>'+
              '</div>'+
            '</div>'+
          '</div>';
   return head;         
}

socket.on('response',function(msg,id){

    var hoy = new Date().toLocaleDateString('en-US', {  
              month : 'numeric',
              day   : 'numeric',        
              year  : 'numeric'
              }).split(' ').join('/');
      
    var user_visto= $('#user >a').text() 
    
    if (msg.message!==undefined) {
       console.log('Got on  es aqui',msg, id)  
       //Lo imprime en pantalla creado un nuevo div

        

       if ($('.heading-celphone').text()==msg.to) {
        
            if (msg.message.search('status-dblcheck')==-1 && msg.message.search('status-check')==-1  && msg.message.search('status-time')==-1 ) {
               $('#conversation').append(inCommingMesseage(msg.message));
               var objDiv = document.getElementById("conversation"); 
               objDiv.scrollTop = objDiv.scrollHeight
            } 

       } else{

         
           if (!findNode(msg.to)) {
              buscarPaciente(msg.to,'in') 
           } 
           
       };

       if (msg.message.search('status-dblcheck')==-1 && msg.message.search('status-check')==-1  && msg.message.search('status-time')==-1 ) {
           saveData( msg.to,msg.message ,msg.hora,1,'i',user_visto )
           if ($('.heading-celphone').text()==msg.to) {
                saveData( msg.to,msg.message ,msg.hora,1,'u',user_visto )
           }
        }

       
    }; 
  // }; 

  if (auto) {
     saludos = greetings();
     patient_name='';
     var data ={
        q : msg.to      
    }

     $.ajax({
            async:false,    
            cache:false,   
            dataType:"JSON",
            type: 'GET',   
            url: '../../clases/getpacientebycedula.php',
            data:data, 
            success:  function(respuesta){  

              try{
                if (respuesta[0].nombre.length>0) {
                    patient_name=respuesta[0].nombre;
                }else if(respuesta[0].nombres.length>0){
                    patient_name=respuesta[0].nombres;
                }   
                  setTimeout(()=>{ output(msg); } , Math.floor(Math.random() * (2000 - 1 + 1)) + 1);
              }catch(e){
                  setTimeout(()=>{ output(msg); } , Math.floor(Math.random() * (2000 - 1 + 1)) + 1);
              }

                             

               
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){
               setTimeout(()=>{ output(msg); } , Math.floor(Math.random() * (2000 - 1 + 1)) + 1);
            }
      });

     
  }

});


//CLICK PARA SELECIONAR EL CONTACTO
function selectContact(){
  let ele=this;
  let contactNumber=this.children[0].id;
  console.log(contactNumber)

  paciente = ele.children[0].children[1].children[0].children[0].children[0].innerText
  telefono = contactNumber
  
  if(!findNode($('.heading-celphone').text()!==telefono)) {
    
  }
  $('.heading-name-meta').text(paciente)
  $('.heading-celphone').text(telefono)
  
  removeElement()

  findStoredMessages(telefono)   
  
}

function removeElement(){
      var els = document.getElementsByClassName('message-main-receiver');
      for (var i=els.length;i--;){
          els[i].parentNode.removeChild(els[i]);
      }

      els = document.getElementsByClassName('message-main-sender');
      for (var j=els.length;j--;){
          els[j].parentNode.removeChild(els[j]);
      }
}

//BUSCA LOS MENSAJES GUARDADOS
  function findStoredMessages(telefono) {

    var datasave ={
        telefono : telefono      
    }
    var objDiv
     var xres = ajaxGen("../../clases/getmessages.php",datasave)
     xres = JSON.parse(xres)
       
     if(xres.length>0){
        for (var i = 0; i < xres.length; i++) {

            if (xres[i].modo=='1') {
                $('#conversation').append(inCommingMesseage(xres[i].mensaje));
            }else{
                $('#conversation').append( sentMessage(xres[i].mensaje) );   
            }
            objDiv = document.getElementById("conversation"); 
            objDiv.scrollTop = objDiv.scrollHeight
        };
      
     }
     
      
  }


//BUSCA NO EN LOS CONTACTOS ACTIVOS
function findNode(phone){
  var lret=false
  let nodeCount = $('.row.people').length
  let _cellP=''

  if(nodeCount>0){
     for (var i = 0; i < nodeCount; i++) {
        _cellP = $('.row.people')[i].children[0].id
        if(phone==_cellP){
          lret=true;
          break;
        }
     };
  }
  return lret;
}


//

 $('#sltcontact').on('change', function(event) {
    var este= $(this)
    let el = document.getElementById('sltcontact')
    paciente = el.options[el.selectedIndex].text
    telefono = el.options[el.selectedIndex].value

    $('#sb').show()
    $('.sltcontact').hide() 
    $("#searchText").val('')
    buscarPaciente(telefono, 'in')
 })


function saveData(telefono,mensaje,hora,msgInorOut,insert_or_updated,user_visto){

    var datasave ={
        telefono : telefono
      , mensaje : mensaje
      , hora : hora
      , msgInorOut :msgInorOut
      , insert_or_updated : insert_or_updated  
      , user_visto : user_visto    
    }

       var xres = ajaxGen("../../clases/messengersave.php",datasave)
}


 function ajaxGen(file,data){
    var res;
    $.ajax({
            async:false,    
            cache:false,   
            dataType:"html",
            type: 'POST',   
            url: file,
            data:data, 
            success:  function(respuesta){                        
               res=respuesta;  
              // console.log(' respuesta del saveData :', res);
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });

    return res;
}

function output(input){
  if (input.message.search('status-dblcheck')==-1 && input.message.search('status-check')==-1  && input.message.search('status-time')==-1 ) {
      if ( input.message==lastMessage && input.to==lastPhone) {
         return;
      }

      //from

      var data ={
        telefono : input.to
      , mensaje : input.message
    }

          $.ajax({
            async:false,    
            cache:false,   
            dataType:"JSON",
            type: 'POST',   
            url: '../../clases/messengerchkmsg.php',
            data:data, 
            success:  function(respuesta){                        
              if (respuesta=='0') {

                  lastMessage = input.message;
                  lastPhone = input.to;

                  try{
                    var product = input.message + "=" + eval(input.message);
                  } catch(e){
                    var text = (input.message.toLowerCase()).normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/[^\w\s\d]/gi, ""); //remove all chars except words, space and 
                    text = text.replace(/ a /g, " ").replace(/i feel /g, "").replace(/whats/g, "what is").replace(/please /g, "").replace(/ please/g, "");

                    if( /precio/.test(text)){
                      text='precio';
                    }else if(/ubicacion|localid|direcci|ubicad|localiza|pin|maps/.test(text)){
                      text='informacion'; 
                    }else if(/plan|medico/.test(text)){
                      text='planes medicos';
                    }else if( /quie/.test(text) && /mensaj/.test(text)  || /quie/.test(text) &&  /habl/.test(text) ){
                      text='cma';
                    }else if( /que/.test(text) && /hace/.test(text) || /dedican/.test(text)   ){
                      text='que haces';
                    }else if(text.match( /(informac)/ )!==null && text.match( /(tratamien)/ )!== null){
                      text='info trata';
                    }else if(text.match( /(informac|terapia)/ )!==null && text.match( /(laser|terapia)/ )!== null){
                      text='info laser';
                    }else if(text.match( /(cuanto)/ )!==null && text.match( /(consulta|medica)/ )!== null){
                      text='precio consulta';
                    }else if(text.match( /(horari)/ )!==null ){
                      text='horarios';
                    }else if(text.match( /(cuanto|cuesta)/ )!==null && text.match( /(laser)/ )!== null){
                      text='precios laser';
                    }else if(text.match( /(adios|ciao|chao|luego|gracia|grasia|ok)/ )!==null ){
                      text='despedida';
                    }else if(text.match( /(chk|cheque|check|cheke|ath|credi|debi)/ )!==null ){
                      text='pagos';
                    }
                    

                    if(compare(trigger, reply, text)){
                      var product = compare(trigger, reply, text);
                    } else {
                      var product = alternative[Math.floor(Math.random()*alternative.length)];
                    }
                  }
                  
                  if(text.match( /(clima|temperat|weather|climate|calor|frio|hot|warm|cold)/ )!==null ){
                     getWeather( function(temperature){
                        product = getMessageWeather(temperature) ;
                        autoAnswerSendMessage(input,product);
                     } );
                  }else{
                     autoAnswerSendMessage(input,product);
                  }
                  
                  //   let _mensaje ={
                  //             to : input.to,
                  //             from : 'auto answer',
                  //             name : input.to,
                  //             message :  product,
                  //             id :socket.id,
                  //             type :'send'
                  //   }
                   
                  // socket.emit('message',_mensaje); 
                  // console.log(" respuesta auto : ", _mensaje);
                  // var time = new Date();
                  // let hora = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true })
                  // saveData( _mensaje.to,_mensaje.message,hora,2,'i',_mensaje.from )

              } ;  
              
            },
            beforeSend:function(){},
            error:function(objXMLHttpRequest){}
          });
      //


  
      // document.getElementById("chatbot").innerHTML = product;
      // speak(product);
      // document.getElementById("input").value = ""; //clear input value

     // to


    }
}

function autoAnswerSendMessage(input,product){
 let _mensaje ={
    to : input.to,
    from : 'auto answer',
    name : input.to,
    message :  product,
    id :socket.id,
    type :'send'
  }
         
  socket.emit('message',_mensaje); 
  console.log(" respuesta auto : ", _mensaje);
  var time = new Date();
  let hora = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true })
  saveData( _mensaje.to,_mensaje.message,hora,2,'i',_mensaje.from )
}

function compare(arr, array, string){
  var item;
  for(var x=0; x<arr.length; x++){
    for(var y=0; y<array.length; y++){
      if(arr[x][y] == string){
        items = array[x];
        item =  items[Math.floor(Math.random()*items.length)];
        if(item=='Hi'){
           item=saludos+' '+patient_name+', gracias por comunicarse con el Centro Médico Adaptógeno, ¿cómo podemos ayudarle?';
        }else if(item=='Hello'){
           item=saludos+' '+patient_name+', bienvenido al Centro Médico Adaptógeno, será un placer ayudarle';
        }
      }
    }
  }
  return item;
}
var lastMessage,lastPhone;
var trigger = [
  ["hi","hey","hello","hola","buenas","buenos","buen","que tal","que mas","saludos"], 
  ["how are you", "how is life", "how are things","como estas","como esta","como te va","como le va"],
  ["what are you doing", "what is going on"],
  ["how old are you"],
  ["who are you", "are you human", "are you bot", "are you human or bot"],
  ["who created you", "who made you"],
  ["your name please",  "your name", "may i know your name", "what is your name"],
  ["i love you"],
  ["happy", "good"],
  ["bad", "bored", "tired"],
  ["help me", "tell me story", "tell me joke"],
  ["ah", "yes", "ok", "okay", "nice", "thanks", "thank you"],
  ["bye", "good bye", "goodbye", "see you later"],
  ['cual es el precio','precio','costo'],
  ['quien'],
  ['trata'],
  ['planes medicos','plan medico'],
  ['horarios'],
  ['terapia de que','que terapia es','de que las terapia', 'd que las terapia','de q las terapia','terapia laser para que','para que están utilizando la terapia de láser','para que sirve la terapia laser','para que se usa la terapia laser'],
  ['cma'],
  ['informacion'],
  ['donde me hablan','donde hablan','quien habla','quien es','que','q','quien me habla','de donde es este mensaje','de donde me escriben','qué es cma','que es cma','quien eres','quien es cma'],
  ['no entiendo'],
  ['helicobacter'],
  ['En donde es eso'],
  ['que haces'],
  ['info trata'],
  ['info laser'],
  ['precio consulta'],
  ['precios laser'],
  ['despedida'],
  ['pagos']
];
var reply = [
  ["Hi","Hello"], 
  ["Fine", "Pretty well", "Fantastic"],
  ["Nothing much", "About to go to sleep", "Can you guest?", "I don't know actually"],
  ["I am 1 day old"],
  ["I am just a bot", "I am a bot. What are you?"],
  ["Kani Veri", "My God"],
  ["I am nameless", "I don't have a name"],
  ["I love you too", "Me too"],
  ["Have you ever felt bad?", "Glad to hear it"],
  ["Why?", "Why? You shouldn't!", "Try watching TV"],
  ["I will", "What about?"],
  ["Tell me a story", "Tell me a joke", "Tell me about yourself", "You are welcome"],
  ["Bye", "Goodbye", "See you later"],
  ['Los precios dependen del tratamiento','Llame a nuestros números','Cada tratamiento es difrente y de ello dependen los precios'],
  ['Centro Médico Adaptógeno'],
  ['trata'],
  ['Se aceptan todos los planes médicos con la excepción de Reforma y Medicare','Aceptamos los planes médicos pero no la Refoma ni Medicare','Con la excepción de la Reforma y Medicare aceptamos Todos los planes médicos'],
  ['Desde las 7am a 4pm de Lunes a Sábado','Nuestra clínica está abierta desde las 7am a 4pm de Lunes a Sábado','Estamos abiertos desde las 7am a 4pm de Lunes a Sábado','El horario del CMA  es desde las 7am a 4pm de Lunes a Sábado'],
  ['terapia de que','que terapia es','de que las terapia', 'd que las terapia','de q las terapia','terapia laser para que','para que están utilizando la terapia de láser','para que sirve la terapia laser','para que se usa la terapia laser'],
  ['Centro Medico Adaptogeno (CMA)','Centro Medico Adaptogeno (CMA) Bayamon'],
  [cen_location],
  ['donde me hablan','donde hablan','quien habla','quien es','que','q','quien me habla','de donde es este mensaje','de donde me escriben?','¿qué es cma?','que es cma?','quien eres?','quien es cma'],
  ['no entiendo','No se de que me habla','Puede ser mas preciso, gracias' ],
  ['Helicobacter'],
  [cen_location],
  ['El en Centro Médico Adaptógeno ayudamos a personas con patatologías cronicas', 'En Centro Médico Adaptógeno le damos al paciente una mejor calidad de vidad'],
  ['En el CMA se trata todo tipo de condición de salud, puede llamar al 787-780-7575','Tenemos médicos generalistas especializados en medicina natural que pueden ayudarle con su condición de salud'],
  ['En el CMA tenemos diferentes tipos de terapias LASER y todo depende de la consulta con su médico, paya mayor información puede llame al 787-780-7575','Tenemos médicos especializados en medicina natural para ayudarle con su condición de salud'],
  ['El precio de la consulta médica es $20 la primera y $10 las de seguimiento','Nuestro precio de la consulta médica es $20 la primera y $10 las de seguimiento'],
  ['Los precios para en trat de LASER puede variar según sea su condición médica','Depende la consulta con su  médico y su condición dependerá el precio para la terapia LASER','Hay varios precios para la trapia LASER y dependerá de su estado'],
  ['Gracias por comunircase con nosotros','Siempre estamos a su orden, lo esperamos','Que esté bien','Agradecemos su tiempo gracias, esperamos su vistia','Hasta luego estamos para ayudarle'],
  ['Aceptamos todas las formas de pago VISA MASTERCARD ATH']
];
var alternative = ["Haha...", "Eh...","Centro Medico Adaptogeno"];

greetings = function(){
     var myDate = new Date();
    var hrs = myDate.getHours();

    var greet;

    if (hrs < 12)
        greet = 'Buenos días';
    else if (hrs >= 12 && hrs <= 17)
        greet = 'Buenas tardes';
    else if (hrs >= 17 && hrs <= 24)
        greet = 'Saludos';
  
  return greet;
}

function getWeather(callback){
   let url="https://api.openweathermap.org/data/2.5/weather?id=4562831&APPID=8ade6c2ef8043c8717ef99f332e746f9&units=imperial";

   var api = new  XMLHttpRequest();
   api.open('GET',url,true);
   api.send();

   api.onreadystatechange   = function(){
    if(this.readyState === 4 && this.status === 200){
      let dat = JSON.parse(this.responseText);
      let temperature =dat.main;
      callback(temperature);
    }   
   }
}

function getMessageWeather(temperature){
   // OTRAS OPCIONES {temp: 79.38, pressure: 1013, humidity: 74, temp_min: 75.92, temp_max: 82.04}
  var msgOptions='';
  if (temperature.temp>78) {
    msgOptions = ["Haha, ese calor está terrible la temperatura es "+parseInt(temperature.temp)+"°F"
                 ,"Hoy en Bayamón tenemos una temperatura de "+parseInt(temperature.temp)+"°F"+ "\nHumedad "+parseInt(temperature.humidity)+"%"+ "\nTemperatura máxima :" +parseInt(temperature.temp_max)+"°F"+"\nTemperatura mínima :"+parseInt(temperature.temp_min)+"°F"
                 ,"Agarrate ese calor nos va derretir hoy mira la temperatura "+parseInt(temperature.temp)+"°F"+ "\nHumedad "+parseInt(temperature.humidity)+"%"+ "\nTemperatura máxima :" +parseInt(temperature.temp_max)+"°F"+"\nTemperatura mínima :"+parseInt(temperature.temp_min)+"°F"
                 ,"En Bayamón hace mucho calor la temperatura es "+parseInt(temperature.temp)+"°F"]; 
    return msgOptions[Math.floor(Math.random()*msgOptions.length)]; // "En Bayamón hace mucho calor la temperatura es "+parseInt(temperature.temp)+"°F";
  }else if(temperature.temp>75 && temperature.temp<=78){
     return "Hace un poco de calor en Bayamón la temperatura está en "+parseInt(temperature.temp)+'°F';
  }else{
     return "El clima está fresco en Bayamón  el termómetro indica "+parseInt(temperature.temp)+'°F';
  }

}

function time_Unix(){
// Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
var date = new Date(unix_timestamp*1000);
// Hours part from the timestamp
var hours = date.getHours();
// Minutes part from the timestamp
var minutes = "0" + date.getMinutes();
// Seconds part from the timestamp
var seconds = "0" + date.getSeconds();

// Will display time in 10:30:23 format
var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
}