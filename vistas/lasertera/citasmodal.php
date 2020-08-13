<!-- start modal -->


	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
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
          <div class="form-group col-sm-3">
           <!--  <label for="recipient-name" class="form-control-label">Recipient:</label> -->
            <input type="text" id="idpaciente" class="form-control" placeholder="Record">
          </div>
          <div class="form-group col-sm-4">
              <input  id="medicohd" type="hidden" name="medicohd">
              <select id="medico" name="medico" class="form-control" >
                   <option value="" selected ></option>             
              </select>
          </div>
          <div  class="form-group col-sm-2" id="fgfechacita">
                <div class="form-group">    
                    <input type="text" class="form-control" id="fechaNewApmt" placeholder="MM/DD/YYYY"   name="fechaNewApmt" >
                </div>    
          </div>
          
          <div  class="form-group col-sm-2" id="clock">
                <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                      <input type="text" class="form-control" value="13:14" id="time">
                      <span class="input-group-addon">
                          <span class="glyphicon glyphicon-time"></span>
                      </span>
                </div>
          </div>

      </div>
      <div class="row dividassoc">
          <div id="dividassoc" class="form-group col-sm-6">
              <input  id="idassochd" type="hidden" name="idassochd">
              <select id="idassoc" name="idassoc" class="form-control" >
                   <option value="" selected ></option>             
              </select>
          </div>
          <p id='nameasoc'  style='opacity: 1; float:left;' class='clearfix text' >Pacientes viculados <span class="badge"></span></p>
      </div>
      <div class="row">
        <ul class="form-group col-sm-3">
             <li class="list-group-item">
                  MLS
                  <div class="material-switch pull-right">
                        <input id="mls" name="mls" type="checkbox"/>
                        <label for="mls" class="label-primary"></label>                  
                  </div>
                  <input type="text" id="getmls" class="form-control" placeholder="# terapias" style="margin-top: 10px; display: none;">
              </li>
         </ul>  
         <ul class="form-group col-sm-3">
             <li class="list-group-item">
                  HILT
                  <div class="material-switch pull-right">
                      <input id="hilt" name="hilt" type="checkbox"/>
                      <label for="hilt" class="label-primary"></label>                      
                  </div>
                  <input type="text" id="gethilt" class="form-control" placeholder="# terapias" style="margin-top: 10px; display: none;">
              </li>
         </ul> 

        <div class="col-md-2">  
       <!--  <label for="numterapias" class="form-control-label">N. Terapias:</label> -->
       <li class="list-group-item">

        <select id='numterapias' name='numterapias' class='form-control terapias' style='filter: invert(30%);' >        
              <option value="" selected ></option>             
        </select>
      </li>        
      </div>

        <div class="form-group col-sm-4">
             <li class="list-group-item">
             <input  id="citashd" type="hidden" name="citashd">
              <select id="citas" name="citas" class="form-control" >
                   <option value="" selected ></option>             
              </select>
            </li>   
          </div>
          
      </div>
	  
	 <div class="row" id="rowtipterapia" style="display: none;">


    </div>
	  
  <div class="row">
      <div class="col-md-12">  
                <div class="form-group">
            <label for="messagetext" class="form-control-label">Observación:</label>
            <textarea class="form-control" id="messagetext"></textarea>
          </div>        
      </div>

    </div>
 
        </form>
      </div>
      <div class="modal-footer">

        <div  align="left"  id="erroralert" class="alert alert-danger alert-dismissable" style="Display: none;">
           <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
           <p id="etext" align="left" ></p> 
        </div>
		<button type="button" class="btn btn-danger" id="atnopen" data-dismiss="modal">Abierto</button>
        <button type="button" class="btn btn-secondary" id="closeapm" data-dismiss="modal">Salir</button>
        <button type="button" class="btn btn-primary" id="save">Guardar</button>
      </div>
    </div>
  </div>
</div>
	<!-- finish modal -->