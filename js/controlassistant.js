if ('webkitSpeechRecognition' in window ) {
		const speechRecognizer = new webkitSpeechRecognition();
		speechRecognizer.continous=true;
		speechRecognizer.interimResults=true;

		speechRecognizer.addEventListener('result', e=>{
			//console.log(e);

			const transcript = Array.from(e.results)
			.map(result=> result[0])
			.map(result=> result.transcript)
			.join('')

			//console.log(transcript)
		})

		speechRecognizer.lang='es-PR';
		speechRecognizer.start();

		speechRecognizer.onend = function(){
    		speechRecognizer.start();
		}	

		var finalTranscripts ='';
		speechRecognizer.onresult= function(e){
			var interimTranscripts='';
			for (var i = e.resultIndex; i < e.results.length; i++) {
				var transcript =e.results[i][0].transcript;
				transcript.replace('\n','<br>');
				if (e.results[i].isFinal) {
					finalTranscripts +=transcript;
					evalResult(finalTranscripts)
				} else {
					//interimTranscripts+=transcript;
				}
			}
			console.log(finalTranscripts)
			finalTranscripts='';
			//console.log(interimTranscripts)
		}

		// speechRecognizer.onerror = function(e){
		// 	console.log(e)

		// }

	} else {}

	function evalResult(expr){
		var str;
		var containBuscar = RegExp("(BUSCAR|ENCONTRAR)");
		var containCitar = RegExp("(CITA|CITAR)");
		var containLimpiar = RegExp("(LIMPIAR|LIMPIA)");
		var containPresente = RegExp("(PRESENTE)");
		var containCalle = RegExp("(CALLE)");
		var containFacturar = RegExp("(FACTURAR)");
		var containEnconsulta = RegExp("(CONSULTA)");
		var containEnEspera = RegExp("(ESPERA)");
		var containAsistir = RegExp("(ASISTIR)");
		

        
        expr= expr.latinise();
        exp = expr.toUpperCase();

        if ( containBuscar.test(exp)  ) {
        	let valor=exp.replace('BUSCAR','').trim()

        	let isNum= isNaN(valor) 

        	if(!isNum){
        		valor=valor.replace(' ','').trim()
        	}

          $('input[name="valueToSearch"]').val(valor);
          $('#submit').click();
        } else if(containLimpiar.test(exp)) {
        	$('#submit').click();
        }else if(containCitar.test(exp)){
        	$("#exampleModal").modal();
        }else if(containPresente.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {        		
        		changePresente(true)
        	};
        }else if(containCalle.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {
                changePresente(false)
        	};
        }else if(containFacturar.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {
                //changePresente(false)
                
                 aFacturacion($("#tbl-ctrl td:nth-child(2)").text())
        	};
        }

else if(containEnconsulta.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {
                //changePresente(false)
                
               changeEnConsulta(true)
        	};
        }



        else if(containAsistir.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {
                //changePresente(false)
                
                changeAsistir(true)
        	};
        }else if(containEnEspera.test(exp)){
        	if (document.getElementById('tbl-ctrl').rows.length==2 && $('#tbl-ctrl').find('td:first').text()!='') {
                //changePresente(false)
                
               changeEnConsulta(false)
        	};
        }






	}

String.prototype.latinise = function() {
	return this.replace(/[^A-Za-z0-9]/g, function(x) { return latin_map[x] || x; })
};
 
// American English spelling :)
String.prototype.latinize = String.prototype.latinise;
 
String.prototype.isLatin = function() {
	return this == this.latinise();
};

function changePresente(stat){
		$('.llego').trigger('click').prop('checked', stat);
        $('.llego').trigger('click').prop('checked', stat);
}


function changeEnConsulta(stat){
	$('.enconsulta').trigger('click').prop('checked', stat); 
	$('.enconsulta').trigger('click').prop('checked', stat); 	
}


function changeAsistir(stat){
	$('.asistido').trigger('click').prop('checked', stat); 
	$('.asistido').trigger('click').prop('checked', stat); 
	
}
//document.getElementById('tbl-ctrl').rows.length
//$('#tbl-ctrl').find('td:first').text()
//$('#106704-248111').trigger('click').prop('checked', false); enconsulta








// window.onload = function(){
// 	if (annyang) {
// 		var command ={
// 		'buscar *value': function(value){
// 			$('input[name="valueToSearch"]').val(value);
// 			$('#submit').click();
// 		},
// 		'limpiar *value': function(value){
// 			$('#submit').click();
// 		},
// 		'enter *value': function(value){
// 			$('#submit').click();
// 		}
// 	};
// 	annyang.addCommands(command)
// 	annyang.setLanguage('es')
// 	annyang.start()
// 	}

// }