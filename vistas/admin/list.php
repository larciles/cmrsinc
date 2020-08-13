    <div class="row"> 
          <div class="col-sm-7">  
             <div class="paginaf"> </div>
        </div>

        <div class="form-group col-sm-2">
            <label class="control-label"></label>  
            <div class="form-group">
                <button type="button" id="newuser" class="btn btn-primary" data-toggle="modal" data-target="#modalusers">Crear Usuario</button>
            </div>
        </div>

        <div class="control-group col-sm-3">
               <label class="control-label"></label>  
               <div class="form-group">
                <!-- data-tip="Id, Record, Nombres,  Apellidos, Médico, Teléfonos, Observación o Código" -->
                  <div>
                    <input type="text" class="form-control" id ="search" name="search" placeholder="Buscar">
                  </div>
               </div>
        </div>
       <!--  <div class="col-sm-2"> 
           <label class="control-label"></label>  
           <div class="form-group">
              <input class="datepicker form-control" data-date-format="mm/dd/yyyy">
           </div>
        </div> -->
   </div>

<table id='tblLista' class='table table-hover table-striped'>  
  <thead class='thead-inverse'>
  <tr>
  <th class='titleatn' >ID</th>
  <th class='titleatn' >Usuario</th>
  <th class='titleatn' >Nombre</th>               
  <th class='titleatn' >Apellido</th>
  <th class='titleatn' >Perfil</th>
  <th class='titleatn' >Acceso</th>
  <th class='titleatn' >Consultar</th>
  <th class='titleatn' >Activo</th>     
  </tr>
  </thead>
  <tbody>
  </tbody>
</table>
