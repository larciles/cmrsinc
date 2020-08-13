<!--  CLIENTES BRIEF STARTS -->
<div class="modal fade" id="briefModal" tabindex="-1" role="dialog" aria-labelledby="briefModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="briefModalLabel">Nuevo paciente vista rápida</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>

          <div class="row ">
            <div class="form-group col-sm-6">
              <label for="apellido" class="form-control-label">Apellidos:</label>
              <input type="text" class="form-control" id="apellido" required>
            </div>
            <div class="form-group  col-sm-6">
              <label for="name" class="form-control-label">Nombres:</label>
              <input type="text" class="form-control" id="name" required></textarea>
            </div>

          </div>
          <div class="row ">
            <div class="form-group col-sm-6">
              <label for="idcl" class="form-control-label">Id:</label>
              <input type="text" class="form-control" id="idcl" required>
            </div>
            <div class="form-group  col-sm-6">
              <label for="phonecl" class="form-control-label">Télefono:</label>
              <input type="text" class="form-control" id="phonecl" required></textarea>
            </div>
          </div> 
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closecl" class="btn btn-secondary" data-dismiss="modal">Salir</button>
        <button type="button" id="savecl" class="btn btn-primary confirm">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!--  ENDS  CLIENTES BRIEF -->