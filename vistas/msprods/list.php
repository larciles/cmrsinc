    <div class="row"> 
          <div class="col-sm-6">  
             <div class="paginaf"> </div>
        </div>
         <input type="button" name="pventa" id="pventa" class="btn btn-primary " value="Venta" style="margin-top: .5em;"  />  
        <div class="control-group col-sm-2">
               <label class="control-label"></label>  
               <div class="form-group">
                <!-- data-tip="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" -->
                  <div>
                    <input type="text" class="form-control" id ="search" name="search" placeholder="Buscar">
                  </div>
               </div>
        </div>
        <div class="col-sm-2"> 
           <label class="control-label"></label>  
           <div class="form-group">
              <input class="datepicker form-control fechaf" data-date-format="mm/dd/yyyy">
           </div>
        </div>
   </div>

<table id='tblLista' class='table table-bordered table-hover table-condensed table-striped'>  
  <thead class='thead-inverse'>
  <tr>
  <th class='titleatn' >Factura</th>
  <th class='titleatn' >Paciente</th>               
  <th class='titleatn' >Fecha</th>
  <th class='titleatn' >Status</th> 

  <th class='titleatn' >Subtotal</th>
  <th class='titleatn' >Descuento</th>
  <th class='titleatn' >Impuesto</th>
  <th class='titleatn' >Envio</th>

  <th class='titleatn' >Total</th>
  <th class='titleatn' >Usuario</th>
  <th class='titleatn' >Medico</th>  
  <th class='titleatn' >Record</th> 
  <th class='titleatn' >Consultar</th>
  <th class='titleatn' >Devolver</th>
  <th class='titleatn' >Imprimir</th>
  <th class='titleatn' >Anular</th>

  </tr>
  </thead>
  <tbody>
  </tbody>
</table>
