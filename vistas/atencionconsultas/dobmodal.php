<!-- start modal --> 


	<div class="modal fade" id="dbomodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" cconsulta-id="" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Datos del Paciente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row ">
          <div class="form-group col-sm-8">           
            <input type="text" id="nombrepaciente" class="form-control" placeholder="Record Id Nombres">
          </div>
          
          <div  class="form-group col-sm-4" id="fgfechacita">
                <div class="form-group">    
                    <input type="text" class="form-control datepicker" id="fechanacimiento" placeholder="MM/DD/YYYY"   name="fechanacimiento" >
                </div>    
          </div>
      </div>
      <div class="row dividassoc">
          <div class="form-group col-sm-6"> Municipio / Pueblo </div>
            <div class="form-group col-sm-6">
              <input type="text" id="municipio" class="form-control" placeholder="Municipio / Pueblo">
            </div>
      </div>
      <div class="row">
          <div class="form-group col-sm-6"> Codigo Postal / Zip Code </div>
          <div class="form-group col-sm-6">
          <input type="text" id="zipcode" class="form-control" placeholder="Codigo Postal / Zip Code">
          </div>      
      </div>

      <div class="row">
          <div class="form-group col-sm-6"> Sexo </div>
          <div class="form-group col-sm-6">
            <select id="sex" name="sex" class="form-control">
                <option value="0">Femenino</option>
                <option value="1">Masculino</option>
            </select>          
          </div>      
      </div>
	  
 
        </form>
      </div>
      <div class="modal-footer">

        <div  align="left"  id="erroralert" class="alert alert-danger alert-dismissable" style="Display: none;">
           <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
           <p id="etext" align="left" ></p> 
        </div>
		
        
        <button type="button" class="btn btn-primary" id="savepd">Guardar</button>
      </div>
    </div>
  </div>
</div>
	<!-- finish modal