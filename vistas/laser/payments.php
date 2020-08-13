<div class="modal fade" id="paymentsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title lead" id="exampleModalLabel">Payment Methods</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form>


      <div class="row">
          <div class="col-xs-6 form-group">
            <label for="recipient-name" class="form-control-label">Monto a Pagar:</label>
            <input type="text" class="form-control" id="dueamount">
          </div>
          <div class="col-xs-6 form-group">
            <label for="message-text" class="form-control-label pay">Cambio:</label>
             <input type="text" class="form-control" id="cambio">
          </div>
      </div>

            <table class="table table-condensed">
    <thead>
      <tr>
        <th>Cash $</th>
        <th><input type="text" class="form-control pay" name="cash" id="cash" placeholder="Efectivo / Cash Amount $" ></th>
        <th><img src="../../img/visa.png" alt="Visa"></th>
        <th><img src="../../img/mastercard.png" alt="Mastercard"></th>
        <th><img src="../../img/american-express.png" alt="American Express"></th>  
        <th><img src="../../img/discover.png" alt="Discover"></th>
        <th><img src="../../img/check.png" alt="Check"></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th scope="row">Card # 1 $</th>
        <td> <input type="text" class="form-control pay" id="tdc1" placeholder="Amount $" readonly="readonly" ></td>
        <td> <input type="radio" name="optradio1" id="vs1-02" class="optradio1"></td>
        <td> <input type="radio" name="optradio1" id="mc1-01" class="optradio1"></td>
        <td> <input type="radio" name="optradio1" id="am1-03" class="optradio1"></td>
        <td> <input type="radio" name="optradio1" id="dc1-04" class="optradio1"></td>
        <td> <input type="radio" name="optradio1" id="ck1-05" class="optradio1"></td>
        <td> <input type="text" class="form-control " name="card1" id="card1" placeholder="Ultimos 4 numeros" ></td>      
      </tr>
      <tr>
        <th scope="row">Card # 2 $</th>
        <td> <input type="text" class="form-control pay" id="tdc2" placeholder="Amount $" readonly="readonly" ></td>
        <td> <input type="radio" name="optradio2" id="vs2-02" class="optradio2"></td>
        <td> <input type="radio" name="optradio2" id="mc2-01" class="optradio2"></td>
        <td> <input type="radio" name="optradio2" id="am2-03" class="optradio2"></td>
        <td> <input type="radio" name="optradio2" id="dc2-04" class="optradio2"></td>
        <td> <input type="radio" name="optradio2" id="ck2-05" class="optradio2"></td>
        <td><input type="text" class="form-control " name="card2" id="card2" placeholder="Ultimos 4 numeros" ></td>      
      </tr>
      <tr>
        <th scope="row">Card # 3 $</th>
        <td> <input type="text" class="form-control pay" id="tdc3" placeholder="Amount $" readonly="readonly" ></td>
        <td> <input type="radio" name="optradio3" id="vs3-02" class="optradio3"></td>
        <td> <input type="radio" name="optradio3" id="mc3-01" class="optradio3"></td>
        <td> <input type="radio" name="optradio3" id="am3-03" class="optradio3"></td>
        <td> <input type="radio" name="optradio3" id="dc3-04" class="optradio3"></td>
        <td> <input type="radio" name="optradio3" id="ck3-05" class="optradio3"></td>        
        <td><input type="text" class="form-control " name="card3" id="card3" placeholder="Ultimos 4 numeros" ></td>      

      </tr>
      <tr>
         <th scope="row">ATH $</th>
        <td><input type="text" class="form-control pay" name="ath" id="ath" placeholder="Débito / ATH Amount $" ></td>      
      </tr>
      <tr>
        <th scope="row">Check $</th>
        <td><input type="text" class="form-control pay" name="check" id="check" placeholder="Cheque / Check $" ></td>      
      </tr>
    </tbody>
  </table>

      <div class="row">
          <div class="col-xs-6 form-group">
            <label for="recipient-name" class="form-control-label">Pago total:</label>
            <input type="text" class="form-control" id="pagototal">
          </div>
          <div class="col-xs-6 form-group">
            <label for="message-text" class="form-control-label">Saldo:</label>
             <input type="text" class="form-control" id="saldo">
          </div>
      </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="save" class="btn btn-primary">Send payment</button>
		<button type="button" id="print2" class="btn btn-primary">Print</button>
      </div>
    </div>
  </div>
</div>