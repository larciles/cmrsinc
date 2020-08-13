	$("#pdfprint").click(function(){
		var p =$( "#excelapo option:selected" ).text();
		window.open('../../reportes/pdfreport.php?p='+p,'titulo', "_blank")
	})


	$("#btn-excel").click(function(){
		 $('#tabla-puor').table2excel({
		        name : "new File",
		        filename : "newfile",
		        fileext : ".xls"
		})
	})

	   