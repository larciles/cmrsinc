$('#void').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('.modal-datos').text('Factura # ' + voidinvoice)
  modal.find('.modal-body input').val(recipient)
})

$('#savevoid').click(function(event){
	
	var razonanulacion=$('#razonanulacion').val();

	url="../../clases/void.php";
    data = {
       numfactu  : voidinvoice
      ,razonanulacion : razonanulacion
      ,idempresa :'3'
     }
    var items;
    var resp =  $.ajax({
                          type: "POST",
                          url: url,
                          data: data,
                          async: false
                      }).responseText;
  

  $('#void').modal('hide');
})