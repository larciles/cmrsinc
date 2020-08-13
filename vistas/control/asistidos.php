<div class="container-fluid">
    <div class="row">
        <form> 
            <div class"tblsin" style="padding-left: 10px; padding-right: 10px;">
                <div class="control-group col-sm-2">
                    <label class="control-label"></label> 
                    <div class="form-group">
                      
                            <input type="text" class="form-control" id="fecha_asis" placeholder="MM/DD/YYYY" name="fecha_asis"  >
                      
                    </div>
                </div>
                <div class="control-group col-sm-2">
                    <label class="control-label"></label> 
                    <div class="form-group">
                      
                            <input type="text" class="form-control" id="fecha_asis2" placeholder="MM/DD/YYYY" name="fecha_asis2"  >
                      
                    </div>
                </div>

                <div class="form-group col-sm-2">
                    <label class="control-label"></label> 
                    <div class="form-group">  
                        <button type="button"  class="btn btn-success form-control" id="print_asis" >Imprimir</button>
                    </div>
                </div>
                
            </div>               
        </form>
    </div>   
    <div class="row">  
        <div class="table-responsive">  
    <table class="table  table-hover table-condensed table-striped" id="dynamic_field">                                      
      <thead class='thead-inverse'>
          <tr> 

              <th class='titleatn' >#</th>
              <th class='titleatn' >Nombre</th>
              <th class='titleatn' >Teléfono</th>               
              <th class='titleatn' >Médico</th>
              <th class='titleatn' >Record</th>
              <th class='titleatn' >Próx Cita</th>             
              <th class='titleatn' ># Citas</th>
              <th class='titleatn' >Observaciones</th>   
              
          </tr>
      </thead>
      <tbody id="tblasistidos">
      </tbody>
    </table>
</div>
     </div>   
</div> 