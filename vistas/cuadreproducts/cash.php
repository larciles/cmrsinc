                      <div class="container">
              <div class="row">

                <div class="form-group linea">

                    <div class="col-sm-2" style="margin-top: 9px;">    
                        <label class="control-label"></label> 
                          <div class="form-group">  
                              <div class="input-group date" >
                              <input type="text" class="form-control" id="efechai" placeholder="MM/DD/YYYY"   name="efechai" >                    
                              <div class="input-group-addon"></div>
                             </div>
                          </div>
                    </div>  

 <!--                  <label for="datecuadre" class="col-sm-1 control-label">Fecha</label>
                    <div class="col-sm-2">
                        <div class="input-group date" data-provide="datepicker">
                          <input type="text" class="form-control" id="datecuadre" name="datecuadre">
                          <div class="input-group-addon">
                          <span class="glyphicon glyphicon-th"></span> -->
                        <!--   </div>
                        </div>  
                    </div> --> 
                </div>

                <div class="form-group linea">
                    <div class="col-sm-2">
                        <input type="checkbox" class="tiporeporte" checked data-toggle="toggle" data-on="Usuario" data-off="General" data-onstyle="success" data-offstyle="info">
                    </div>
                </div>

                <div class="form-group linea">
                     <label for="c_usuario" class="col-sm-1  control-label">Usuario</label>        
                     <div class="col-sm-3">                
                      <input  id="c_usuariohd" type="hidden" name="c_usuariohd" value=<?php echo $user ;?>>     
                      <input  id="c_cod_perfil_hd" type="hidden" name="c_cod_perfil_hd" value=<?php echo $codperfil ;?>>     
                               
                        <select id="c_usuario" name="c_usuario" class="form-control" >
                            <option value="" selected ></option>             
                        </select>
                      </div>
                </div>

                        <div class="control-group col-sm-3">
             <div class="form-group">
                  <div>
                   <button type="button" class="btn btn-primary btn-sm" id="cuadre1" name="cuadre1">
                  <span class="glyphicon glyphicon-print"></span> Imprimir
                </button>
              </div> 
              </div>   
         </div>   
              </div>
            </div>
            <form>
        <div id="div1"  class="col-sm-5"><div class="row ">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$100's&nbsp;</span>
                    <input id="cien" type="text" class="form-control enterkey"  name="cien" placeholder="$100 Bills">
                    <span class="input-group-addon addons" id="tcien"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$50's&nbsp;&nbsp;&nbsp;</span>
                    <input id="cincuenta" type="text" class="form-control enterkey"  name="cincuenta" placeholder="$50 Bills">
                    <span class="input-group-addon addons" id="tcincuenta"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$20's&nbsp;&nbsp;&nbsp;</span>
                    <input id="twenty" type="text" class="form-control enterkey"  name="twenty" placeholder="$20 Bills">
                    <span class="input-group-addon addons" id="ttwenty"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$10's&nbsp;&nbsp;&nbsp;</span>
                    <input id="ten" type="text" class="form-control enterkey"  name="ten" placeholder="$10 Bills">
                    <span class="input-group-addon addons" id="tten"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$5's&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <input id="five" type="text" class="form-control enterkey"  name="five" placeholder="$5 Bills">
                    <span class="input-group-addon addons" id="tfive"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$1's&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <input id="one" type="text" class="form-control enterkey"  name="one" placeholder="$1 Bills">
                    <span class="input-group-addon addons" id="tone"></span>
                </div>             
              </div>
<!--               <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$0.5's&nbsp;&nbsp;</span>
                    <input id="halfdollars" type="text" class="form-control" name="halfdollars" placeholder="$ Half-Dollars Coins">
                    <span class="input-group-addon" id="thalfdollars"></span>
                </div>             
              </div> -->

              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$0.25's</span>
                    <input id="quarters" type="text" class="form-control enterkey"  name="quarters" placeholder="$ Quarters Coins">
                    <span class="input-group-addon addons" id="tquarters"></span>
                </div>             
              </div>

              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$0.10's</span>
                    <input id="dimes" type="text" class="form-control enterkey"  name="dimes" placeholder="$ Dimes Coins">
                    <span class="input-group-addon addons" id="tdimes"></span>
                </div>             
              </div>
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$0.05's</span>
                    <input id="nickels" type="text" class="form-control enterkey"  name="nickels" placeholder="$ Nickels Coins">
                    <span class="input-group-addon addons" id="tnickels"></span>
                </div>             
              </div> 
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">$0.01's</span>
                    <input id="pennies" type="text" class="form-control enterkey"  name="pennies" placeholder="$ Pennies Coins">
                    <span class="input-group-addon addons" id="tpennies"></span>
                </div>             
              </div> 
              <div class="row">
                <div class="input-group col-sm-5">
                  <span class="input-group-addon">Total $</span>
                    <input id="totalscash" type="text" class="form-control" name="totalscash" placeholder="Total Cash$">
                </div>             
              </div> 
            </div>
            </form>
                        <div id="div2" class="col-sm-3 container-fluid input-group">
              <!-- <div class="container-fluid"> -->
              <table id='tbluserscash' class='table table-bordered table-hover table-condensed table-striped'>  
                  <thead class='thead-inverse'>
                    <tr>
                      <th class='titleatn' >Usuario</th>
                      <th class='titleatn' >Monto</th>               
                      <th class='titleatn' >Eliminar</th>                      
                    </tr>
                  </thead>
                  <tbody id="userscash">
                  </tbody>
              </table>
              <!-- </div>   -->
            </div>  

        <div id="div2" class="col-sm-4">
            <div class="container">
              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Inicio</span>
                    <input id="start" type="text" class="form-control" value="50" name="start" placeholder="$ 50" style="text-align:right;">
                </div>             
              </div>
              
              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Sales Cash</span>
                    <input id="salescash" type="text" class="form-control"  name="salescash" placeholder="$ Sales Cash" style="text-align:right;">
                </div>             
              </div> 
              
              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Total Cash</span>
                    <input id="totalcash" type="text" class="form-control"  name="totalcash" placeholder="$ Total Cash" style="text-align:right;">
                </div>             
              </div> 

              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Cash en Caja</span>
                    <input id="cash" type="text" class="form-control"  name="cash" placeholder="$ Cash" style="text-align:right;">
                    <span class="input-group-addon rr-addons" id="dif"></span>
                </div>             
              </div> 

            <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Deposit Cash</span>
                    <input id="depositcash" type="text" class="form-control"  name="depositcash" placeholder="$ Deposit Cash" style="text-align:right;">
                </div>             
              </div> 
            <div id="cards" card="col-sm-6">
            </div> 
              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Total Tarjetas</span>
                    <input id="totalcard" type="text" class="form-control"  name="totalcard" placeholder="$ Total Tarjetas" style="text-align:right;">
                </div>             
              </div>

              <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Total</span>
                    <input id="totalcma" type="text" class="form-control"  name="totalcma" placeholder="$ Total CMR" style="text-align:right;">
                </div>             
              </div> 
            <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Fact. Bruta&</span>
                    <input id="facbruta" type="text" class="form-control"  name="facbruta" placeholder="$ Total Venta Bruta" style="text-align:right;">
                </div>             
              </div> 

            <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Devoluciones</span>
                    <input id="devolucion" type="text" class="form-control"  name="devolucion" placeholder="$ Devolucion" style="text-align:right;">
                </div>             
              </div> 

            <div class="row">
                <div class="input-group col-sm-3">
                  <span class="input-group-addon r-addons">Fact Neta</span>
                    <input id="neta" type="text" class="form-control"  name="neta" placeholder="$ Total Neto" style="text-align:right;">
                </div>             
              </div>

          </div>
                       <!-- COINS -->

        </div>
        <!-- <div id="div33">Texto que va en el div3</div> -->