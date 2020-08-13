<div class="modal fade" id="modalEventos" tabindex="-1" role="dialog" aria-labelledby="modalEventosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEventosLabel">Eventos <span id="fecha_evento"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="modal-events">
        <div class="row" >
                <div class="col-sm-3">    
        <label class="control-label"></label> 
        <div class="form-group">  
              <!-- <input type="text" class="form-control" id="fecha" placeholder="MM/DD/YYYY"   name="fecha" value=""> -->
                <input class="datepicker form-control" id="sd" placeholder="Fecha inicial"  data-date-format="mm/dd/yyyy" name="sd" readonly="true">
            </div>
      </div>

                  <div class="control-group col-sm-6">
               <label class="control-label"></label>  
               <div class="form-group">
                  <!-- <div data-tip="Id, Record, Nombres" > -->
                    <input type="text" class="form-control" name="evento" id="evento" placeholder="Evento" autocomplete="off">
                  </div>
               <!-- </div> -->
            </div>

            <!-- <div class="form-group col-sm-2">
                 <label class="control-label"></label> 
                <div class="form-group">  
                  <i class="fas fa-trash-alt"></i>
                  <button type="button"  class="btn btn-default  form-control" id="delete" value="Del"><i class="fas fa-trash-alt"></i>Borrar</button>
                </div>
              </div> -->

        </div>  
        <div class="row" >
                  <div class="table-responsive " id="display-events" style="display: none;">  
    <table class="table  table-hover table-condensed table-striped" id="dynamic_field">                                      
      <thead class='thead-inverse'>
          <tr> 

              <th class='titleatn' >#</th>
              <th class='titleatn' style ="text-align: left;" >Desde</th>
              <th class='titleatn' style ="text-align: left;">Evento</th> 
              <th class='titleatn' style ="text-align: left;">Aprobado por</th>               
              <th class='titleatn' >Eliminar</th>              
          </tr>
      </thead>
      <tbody id="table_eventos">
      </tbody>
    </table>
</div>
        </div>  

      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Salir</button>
        <button type="button" id="guardar_evento" class="btn btn-primary">Guardar evento</button>
      </div>
    </div>
  </div>
</div>