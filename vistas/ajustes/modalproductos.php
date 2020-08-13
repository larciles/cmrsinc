<div class="modal fade bd-productos-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="appoimentModalLabel">Producto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row ">
          
          <div class="form-group col-sm-2">
          	<label class="form-control-label" for="idprod" >ID Producto</label>     
            <input type="text" id="idprod" class="form-control" placeholder="ID Producto" name="idprod" maxlength="10" required>   
          </div>

          <div class="form-group col-sm-4">
              <label class="form-control-label" for="productname" >Producto</label>               
              <input type="text" id="productname" class="form-control" placeholder="Producto" name="productname" maxlength="255" required>   
          </div>

          <div class="form-group col-sm-3">
          	  <label class="form-control-label" for="prodnamesht" >Nombre Corto</label>              
              <input type="text" id="prodnamesht" class="form-control" placeholder="Nombre corto" name="prodnamesht" maxlength="100" required>   
          </div>

          <div  class="form-group col-sm-2" >
          	   <label class="form-control-label" for="fechaing" >Ingreso</label>    
                <div class="form-group">    
                    <input type="text" class="form-control" id="fechaing" placeholder="MM/DD/YYYY"   name="fechaing" readonly>
                </div>    
          </div>
      </div>

      <div class="row">
         <div class="form-group col-sm-2">
         	<label class="form-control-label" for="tipoproduct" >Tipo</label>   
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Producto" data-off="Formula" id="tipoproduct" name="tipoproduct">
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="stockmin" >Cant. Mínima</label>  
            <input type="text" id="stockmin" class="form-control" placeholder="Min" name="stockmin" required>   
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="stockmax" >Cant. Máxima</label>  
            <input type="text" id="stockmax" class="form-control" placeholder="Max" name="stockmax" required>   
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="stockcurrent" >Exist. Actual</label> 
            <input type="text" id="stockcurrent" class="form-control" placeholder="Actual" name="stockcurrent" readonly>   
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="cantxunit" >Cant. x Unidad</label> 
            <input type="text" id="cantxunit" class="form-control" placeholder="Can x Unidad" name="cantxunit" required>   
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="activo" >Producto Activo</label> 
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Activo" data-off="Inactivo" id="activo" name="activo">
        </div>



      </div>
	  


      <div class="row">          

      	<div class="form-group col-sm-3">
      		<label class="form-control-label" for="impuesto" >Aplica impuesto</label>     
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Si" data-off="No " id="impuesto" name="impuesto">
        </div>

        <div class="form-group col-sm-3">
        	<label class="form-control-label" for="descuento" >Aplica Descuento</label>     
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Si" data-off="No " id="descuento" name="descuento">
        </div>

        <div class="form-group col-sm-3">
        	<label class="form-control-label" for="commedico" >Aplica comi. Médico</label> 
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Si" data-off="No " id="commedico" name="commedico">
        </div>

        <div class="form-group col-sm-3">
        	<label class="form-control-label" for="comtec" >Aplica comi. Técnico</label> 
        	<input checked data-toggle="toggle" data-width="100" type="checkbox" data-on="Si" data-off="No " id="comtec" name="comtec">
        </div>
      </div> 

      <div class="row dividassoc">
     
          <div id="dividassoc" class="form-group col-sm-3 has-warning">
              <label class="form-control-label" for="idtipprecio" >Tipo de Precio</label> 
              <select id="idtipprecio" name="idtipprecio" class="form-control" >
                   <option value="" selected ></option>             
              </select>              
        
        </div>

        <div class="form-group col-sm-2">
        	<label class="form-control-label" for="idtipprecio" >Precio $</label> 
            <input type="text" id="precio" class="form-control" placeholder="Precio" name="precio" required>   
        </div>

         <div class="form-group col-sm-2">
        	<label class="form-control-label" for="costo" >Costo $</label> 
            <input type="text" id="costo" class="form-control" placeholder="Costo" name="costo" required>   
        </div>


      </div>


	  
  <div class="row">


  </div>
 
        </form>
      </div>
      <div class="modal-footer">

         <div  align="left"  id="erroralert" class="alert alert-danger alert-dismissable" style="Display: none;">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
          <p id="etext" align="left" ></p> 
         </div>

        <button type="button" class="btn btn-secondary" data-dismiss="modal">Salir</button>
        <button type="button" class="btn btn-primary" id="save"  data-dismiss="modal">Guardar</button>
      </div>	
    </div>
  </div>
</div>