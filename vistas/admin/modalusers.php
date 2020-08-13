<div class="modal fade" id="modalusers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="titleModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <input  id="iduser" type="hidden"  name="iduser">
         <div class="row">              
             <div class="col-sm-6" >
                <label for="nombre" class="col-form-label">Nombre</label>
                  <input type="text" class="form-control" placeholder="Nombre" id="nombre" name="nombre" required>
            </div>
            <div class="col-sm-6" >
                <label for="apellido" class="col-form-label">Apellido</label>
                  <input type="text" class="form-control" placeholder="Apellido" id="apellido" name="apellido" required>
            </div>
         </div> 
         <div class="row">
            <div class="col-sm-6" >
                  <label for="usuar" class="col-form-label">Usuario</label>
                  <input type="text" class="form-control" placeholder="Usuario" id="usuar" name="usuar" required>
            </div>
            <div class="col-sm-6" >
                <label for="contrase" class="col-form-label">Contraseña</label>
                  <input type="password" class="form-control" placeholder="Contraseña" id="contrase" name="contrase" required>
            </div>
         </div> 
         <div class="row">
           <div class="col-sm-4" >
              <label for="perfil" class="col-form-label">Perfil</label>
              <select id="perfil" name="perfil" class="form-control" >
                  <option value="" selected ></option>             
              </select>
            </div>

            <div class="col-sm-4">
              <label for="access" class="col-form-label">Acceso</label>
              <select id="access" name="access" class="form-control" >
                  <option value="" selected ></option>             
              </select>
            </div> 
            <div class="col-sm-4">
              <label for="controlc" class="col-form-label">
                    Control de Citas 
                    <div>
                      <input style="float: right;" id="controlc" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="primary" type="checkbox">
                    </div>                    
            </label>
            </div>             
         </div>
         <br>
         <div class="row ">

          
            <div class="col-sm-3 ">
              <label for="prnfact" class="col-form-label">
                    Imprime Factura 
                    <div >
                      <input  class="form-control" style="float: right;" id="prnfact" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="primary" type="checkbox">
                    </div>                    
            </label>
            </div>  

            <div class="col-sm-3">
              <label for="autoprnfact" class="col-form-label">
                    Auto Impresion
                    <div>
                      <input style="float: right;" id="autoprnfact" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="primary" type="checkbox">
                    </div>                    
            </label>
            </div>  

            <div class="col-sm-6" >
                <label for="pathprn" class="col-form-label">Path de la impresora</label>
                  <input type="text" class="form-control" placeholder="hostname/shared name printer" id="pathprn" name="pathprn" >
            </div>






         </div>

      </div> 
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary"   data-dismiss="modal" id="saveuser">Guardar</button>
      </div>
    </div>
  </div>
</div>