<div class="modal fade" id="recordConsModal" tabindex="-1" role="dialog" aria-labelledby="recordConsModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">New message</h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="row">
          <div class="form-group col-sm-6">
            <table class="table table-striped table-hover" id="recordcl">
              <thead>
                <tr>
                  <th>Factura</th>
                  <th>Fecha</th>
                  <th>Total</th>
                  <th>Centro</th>
                  <th>Imprimir</th>
                </tr>
              </thead>
              <tbody id="bodyrecord">
              </tbody>
            </table>
          </div>
          
          
          <div class="form-group col-sm-6">
            <table class="table table-striped table-inverse table-hover">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>                 
                </tr>
              </thead>
              <tbody id="bodyproducts">
              </tbody>
            </table>
          </div>

          </div>
          <div class="row">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
       <!--  <button type="button" class="btn btn-primary">Send message</button> -->
      </div>
    </div>
  </div>
</div>