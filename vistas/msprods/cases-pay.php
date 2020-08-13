<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<style type="text/css">
    [type=radio] { 
       position: absolute;
       opacity: 0;
       width: 0;
       height: 0;
       }
       /* IMAGE STYLES */
       [type=radio] + img {
       cursor: pointer;
       }
       /* CHECKED STYLES */
       [type=radio]:checked + img {
       outline: 2px solid #909090;
       -webkit-box-shadow: 0px 0px 10px 5px #909090;
       -moz-box-shadow: 0px 0px 10px 5px #909090;
       box-shadow: 0px 0px 10px 5px #909090;
       }
       .sombra{
       }
</style>

 <input type="hidden" id="xx" value="<?php  if (isset($_POST["amount"])) echo($_POST['inumber']);?>">
  <?php 
  header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  header("Connection: close");
 require('./controllers/MasterInvoiceController.php');
 require('./controllers/PaymentsController.php');
 $paycontroller = new PaymentsController();
 if (isset($_POST["amount"])) {
   
   
  $arrtypes['visa']="3";
  $arrtypes['master']="2";
  $arrtypes['amex']="4";
  $arrtypes['discover']="5";
  $arrtypes['visadebit']="6";

   $saveArray = array();
   $cash=$_POST["cash"];    
   $array = array("type"=>"1","typename"=>"cash", "amount"=>$cash);
   array_push($saveArray,$array); 
   //************************************
   $ath=$_POST["ath"];
   $array = array("type"=>"7","typename"=>"ath", "amount"=>$ath);
   array_push($saveArray,$array);
   //************************************
   $check=$_POST["check"];
   $array = array("type"=>"8","typename"=>"check", "amount"=>$check);
   array_push($saveArray,$array);
   //************************************
   if (isset($_POST["tdc1"])) {
        $tdc1=$_POST["tdc1"];
        $radio1=$_POST["radio1"];
        $array = array("type"=> $arrtypes[$radio1],"typename"=>$radio1, "amount"=>$tdc1);
        array_push($saveArray,$array);
    } 
    //************************************
    if (isset($_POST["tdc2"])) {
        $tdc2=$_POST["tdc2"];
        $radio2=$_POST["radio2"];
        $array = array("type"=> $arrtypes[$radio2],"typename"=>$radio2, "amount"=>$tdc2);
        array_push($saveArray,$array);
    }
    //************************************
    if (isset($_POST["tdc3"])) {
       $tdc3=$_POST["tdc3"];
       $radio3=$_POST["radio3"];        
       $array = array("type"=>$arrtypes[$radio3],"typename"=>$radio3, "amount"=>$tdc3);
       array_push($saveArray,$array);
    } 

    $new = array(
       'number' =>  $_POST['inumber'], 
       'payment' =>  $saveArray
    );
    
    $resp = $paycontroller->read($_POST['inumber']);
    if (!empty($resp)) {
       $paycontroller->del($_POST['inumber'],1,1);
    }
    $paycontroller->create($new);

    if (isset($_POST["rest"])) {
      if (intval($_POST["rest"])==0) {
            $masterinvoicecontroller = new MasterInvoiceController();
            $masterinvoicecontroller->updateFieldValue("paid",1,'number',$_POST['inumber']);
            $masterinvoicecontroller->updateFieldValue("paysname","paid",'number',$_POST['inumber']);
            $masterinvoicecontroller->updateFieldValue("datepaid",date("Y-m-d"),'number',$_POST['inumber']);
      }
    }

     $template = '
         <div class="container">
            <p class="item  add">Invoice # <b>%s</b> was edited</p>
         </div>
         <script>
            window.onload = function () {
               
            console.log(document.getElementById("xx").value.trim())
            window.open("./views/cases/invoicing-prn.php?number="+document.getElementById("xx").value.trim()+"&times="+"1", "_blank")
               
               reloadPage("cases")
            }
         </script>
      ';    
      printf($template,$_POST['inumber']);
    

 }else if (isset($_POST["number"])) {
  
  // require('./controllers/MasterInvoiceController.php');
   $micontroller = new MasterInvoiceController();
  
   $resmi=$micontroller->read($_POST["number"]);
   $total_invoice=$resmi[0]["total"];
   $resm=$paycontroller->read($_POST["number"]);
   
   if(!empty($resm)) {
      
    
    $arr1=["2","3","4","5","6"];
    
    $cardArray = array();
    $otherPay = array();
    for ($i=0; $i <count($resm); $i++) { 
      if ( in_array($resm[$i]["idpayform"], $arr1) ) {
        array_push($cardArray ,$resm[$i]); 
      } else {

        if ($resm[$i]["idpayform"]=="1"){
           $cash=$resm[$i]["amount"];
        }else if($resm[$i]["idpayform"]=="7"){
           $ath= $resm[$i]["amount"];
        }else if($resm[$i]["idpayform"]=="8"){
           $chk=$resm[$i]["amount"];
        }

      }

    }
   
    $chkd="checked";
   }
   
   

?>
 <div class=" " role=" ">

    <div class=" ">
        <h5 class="modal-title lead" id="exampleModalLabel">Payment Methods invoice # <?php echo($_POST["number"]);?> </h5>
    </div>
    <div class="modal-body">
        <form method="post">
            <div class="row">
                <div class="col-xs-3 form-group">
                    <label for="recipient-name" class="form-control-label">Monto a Pagar:</label>
                    <input type="text" name="amount" class="form-control" id="dueamount" value="<?php echo($total_invoice);?>">
                      <input type="hidden" name="inumber" value=" <?php echo($_POST["number"]);?>">
                </div>
                <div class="col-xs-3 form-group">
                    <label for="message-text" class="form-control-label pay">Cambio:</label>
                    <input type="text" name="change" class="form-control" id="cambio">
                </div>
                <div class="col-xs-3 form-group">
                    <label for="recipient-name" class="form-control-label">Pago total:</label>
                    <input type="text" name="topay" class="form-control" id="pagototal">
                </div>
                <div class="col-xs-3 form-group">
                    <label for="message-text" class="form-control-label">Saldo:</label>
                    <input type="text" name="rest" class="form-control" id="saldo">
                </div>
            </div>

            <table class="table table-condensed">

                <tr>

                    <td>
                        <div class="input-field">
                            <input type="text" class="form-control pay" name="cash" id="cash" value="<?php echo $cash; ?>" />
                            <label for="cash">Cash $ </label>
                        </div>
                    </td>

                    <td>
                        <div class="input-field">
                            <input type="text" class="form-control pay" name="ath" id="ath" value="<?php echo $ath; ?>" />
                            <label for="ath">ATH $</label>
                        </div>
                    </td>

                    <td>
                        <div class="input-field">
                            <input type="text" class="form-control pay" name="check" id="check" value="<?php echo $chk; ?>" />
                            <label for="check">Check $</label>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="table table-condensed">

                <tbody>
                    
                    <?php 
                      $iId=1;
                      if(count( $cardArray )>0){
                        for ($i=0; $i <count( $cardArray ); $i++) { 
                          $ot=$cardArray[$i]["amount"];
                           ?>
                    <tr class="box1">
                        <th scope="row">Card # <?php echo $iId;?> $</th>
                        <td> <input type="text" class="form-control pay" name="tdc<?php echo $iId;?>" value="<?php echo $cardArray[$i]["amount"]; ?>" id="tdc<?php echo $iId;?>" placeholder="Amount $" readonly="readonly"></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="visa<?php echo $iId;?>" <?php if ($cardArray[$i]["idpayform"]=="3") { echo $chkd; } ?> name="radio<?php echo $iId;?>" value="visa"><img src="./public/img/visa.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="mast<?php echo $iId;?>" <?php if ($cardArray[$i]["idpayform"]=="2") { echo $chkd; }  ?> name="radio<?php echo $iId;?>" value="master"><img src="./public/img/mastercard.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="amex<?php echo $iId;?>" <?php if ($cardArray[$i]["idpayform"]=="4") { echo $chkd; }  ?> name="radio<?php echo $iId;?>" value="amex"><img src="./public/img/american-express.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="dis<?php echo $iId;?>" <?php if ($cardArray[$i]["idpayform"]=="5") { echo $chkd; }  ?> name="radio<?php echo $iId;?>" value="discover"><img src="./public/img/discover.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="visad<?php echo $iId;?>" <?php if ($cardArray[$i]["idpayform"]=="6") { echo $chkd; }  ?> name="radio<?php echo $iId;?>" value="visadebit"><img src="./public/img/visadebit.jpg"></label></td>
                    </tr>
                          <?php 
                          $iId++;

                        }
                      }

               
                       for ($i =$iId; $i  <4 ; $i++) { 
                           ?>
                       <tr class="box1">
                        <th scope="row">Card # <?php echo $iId;?> $</th>
                        <td> <input type="text" class="form-control pay" name="tdc<?php echo $iId;?>" value="<?php echo $cardArray[$i]["amount"]; ?>" id="tdc<?php echo $iId;?>"placeholder="Amount $" readonly="readonly"></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="visa<?php echo $iId;?>" name="radio<?php echo $iId;?>" value="visa"><img src="./public/img/visa.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="mast<?php echo $iId;?>" name="radio<?php echo $iId;?>" value="master"><img src="./public/img/mastercard.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="amex<?php echo $iId;?>" name="radio<?php echo $iId;?>" value="amex"><img src="./public/img/american-express.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="dis<?php echo $iId;?>" name="radio<?php echo $iId;?>" value="discover"><img src="./public/img/discover.png"></label></td>

                        <td> <label><input type="radio" class="radio<?php echo $iId;?> pay" id="visad<?php echo $iId;?>" name="radio<?php echo $iId;?>" value="visadebit"><img src="./public/img/visadebit.jpg"></label></td>
                       </tr>
                     <?php 
                      $iId++;
                     }
                    
                    ?>
                  <tbody>
              </table>
             <div class="row">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" id="save" class="btn btn-primary">Send payment</button>
                <button type="button" id="print2" class="btn btn-primary">Print</button>
                <input type="hidden" name="r" value="invoicing-pay">
            </div>
        </form>
    </div>

</div>

<?php
 } else{
?>

 <?php 
 }
?>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="./public/js/payments.js"></script>