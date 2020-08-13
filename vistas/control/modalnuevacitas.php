<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Nueva Cita</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row ">
          <div class="form-group col-sm-4">
           <!--  <label for="recipient-name" class="form-control-label">Recipient:</label> -->
            <input type="text" id="idpaciente" class="form-control">
          </div>
          <div class="form-group col-sm-4">
              <select class="form-control">
                <option value="volvo">Volvo</option>
                <option value="saab">Saab</option>
                <option value="vw">VW</option>
                <option value="audi" selected>Audi</option>
            </select>
          </div>
          <div  class="form-group col-sm-4">
                <div class="form-group">    
                    <input type="text" class="form-control" id="fechaNewApmt" placeholder="MM/DD/YYYY"   name="fechaNewApmt" >
                </div>
    
          </div>

      </div>
      <div class="row">
         <div class="form-group col-sm-3">
          <input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Servicios" data-off="Control">
        </div>
        <div class="form-group col-sm-3">
          <input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Suero" data-off="Laser">
      </div>
        <div class="form-group col-sm-6">
            <select class="form-control">
                <option value="volvo">Volvo</option>
                <option value="saab">Saab</option>
                <option value="vw">VW</option>
                <option value="audi" selected>Audi</option>
            </select>
          </div>
      </div>
  <div class="row">
      <div class="col-md-12">  
                <div class="form-group">
            <label for="message-text" class="form-control-label">Observación:</label>
            <textarea class="form-control" id="message-text"></textarea>
          </div>        
      </div>

    </div>
 
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Send message</button>
      </div>
    </div>
  </div>
</div>