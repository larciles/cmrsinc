
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css"> -->
<?php 

session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}
setlocale(LC_MONETARY, 'en_US');
require "../../controllers/CMA_MFacturaController.php";
$cma_MFacturaController= new CMA_MFacturaController();

$fecha=date('Ymd');
$query=" SELECT  SUM( a.total) monto ,ISNULL ( d.desTipoTargeta , 'EXCENTO') modopago  , max(d.codforpa) codforpa
   FROM    VentasDiariasCMACST a  
   INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc  
   LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu  
   WHERE  a.cod_subgrupo = 'CONSULTA' and  a.statfact=3  and d.desTipoTargeta is  not null AND  a.fechafac ='$fecha' AND  a.usuario='$user'
   GROUP BY  desTipoTargeta  Order by   desTipoTargeta  ";

   $ventas=$cma_MFacturaController->readUDF($query);
   $salesU  = array();
   if (count($ventas)>0) {
      for ($i=0; $i <count($ventas) ; $i++) { 
         setlocale(LC_MONETARY, 'en_US');
         $detalles = array(
           'monto'  =>  '$'.number_format((float)$ventas[$i]['monto'], 2,'.',',') 
          ,'codigo' =>$ventas[$i]['codforpa'] 
           );
          $salesU[$ventas[$i]['modopago']][]=$detalles;
      }
   }
   // var_dump($salesU['ATH'] );
   // var_dump($salesU['ATH'][0]['monto']);
   //  var_dump($salesU );

  $query="SELECT  COUNT(*) invoices, sum(total) total
   FROM    VentasDiariasCMACST a  
   INNER JOIN MDocumentos   b ON a.doc = b.codtipodoc  
   LEFT JOIN VIEWpagosPRCMA d ON a.numfactu = d.numfactu  
   WHERE  a.cod_subgrupo = 'CONSULTA' and  a.statfact=3  and d.desTipoTargeta is  not null AND  a.fechafac ='$fecha' AND  a.usuario='$user'";
  $facturas=$cma_MFacturaController->readUDF($query);
    // var_dump($facturas );

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">




<style type="text/css">
	body { width: 100%; height: 100%; }
.btn-group-fab {
  z-index: 10;	
  position: fixed;
  width: 50px;
  height: auto;
  right: 20px; bottom: 20px;
}
.btn-group-fab div {
  position: relative; width: 100%;
  height: auto;
}
.btn-group-fab .btn {
  position: absolute;
  bottom: 0;
  border-radius: 50%;
  display: block;
  margin-bottom: 4px;
  width: 40px; height: 40px;
  margin: 4px auto;
}
.btn-group-fab .btn-main {
  width: 50px; height: 50px;
  right: 50%; margin-right: -25px;
  z-index: 9;
}
.btn-group-fab .btn-sub {
  bottom: 0; z-index: 8;
  right: 50%;
  margin-right: -20px;
  -webkit-transition: all 2s;
  transition: all 0.5s;
}
.btn-group-fab.active .btn-sub:nth-child(2) {
  bottom: 60px;

}
.btn-group-fab.active .btn-sub:nth-child(3) {
  bottom: 110px;
}
.btn-group-fab.active .btn-sub:nth-child(4) {
  bottom: 160px;
}


.btn-group-fab.active .btn-sub:nth-child(5) {
  bottom: 210px;
}


.btn-group-fab.active .btn-sub:nth-child(6) {
  bottom: 260px;
}

.btn-group-fab.active .btn-sub:nth-child(7) {
  bottom: 310px;
}

.btn-group-fab.active .btn-sub:nth-child(8) {
  bottom: 360px;
}



.btn.active.focus, .btn.active:focus, .btn.focus, .btn:active.focus, .btn:active:focus, .btn:focus {
    outline: none !important;
     /*outline: -webkit-focus-ring-color auto 5px; */
     outline-offset: -2px; 
}

.fa-download:before {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.fab-center{
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

  .floatingmenu_label {
  width: 150px;
  text-align: right;
  padding-right: 10px;
  position: absolute;
  left: -160px;
  color: #fff;
  white-space: nowrap;
  display: none;
  background-color: teal;
  margin-top: -33%;

   font-weight: bold;
   border: 1px solid transparent;
   border-radius: 4px;
/*
   -webkit-box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);
   -moz-box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);
    box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);
    */

    -webkit-box-shadow: 10px 10px 5px 0px rgba(0,0,0,0.75);
-moz-box-shadow: 10px 10px 5px 0px rgba(0,0,0,0.75);
box-shadow: 10px 10px 5px 0px rgba(0,0,0,0.75);
}

#facturas, #invoices{
  background: #ff9ff3;
  color: #000;
  font-weight: bold;
}


#facturas:hover{
  background: #fd79a8;  
}


#cash{
  background-color: #5cb85c;
}

#ath{
  background-color: #0275d8;
}

#visa{
  background-color: #f0ad4e;s
}

#mc{
  background-color: #d9534f;
}

#amex{
  background-color: #5bc0de;
}

#totalusd{
  background-color: #55efc4;
  color: #000;
}

#btn-total{
  background-color: #55efc4!important;
  color: #000;
}


#btn-total:hover{
  background-color: #00cec9!important;
  color: #000;
}

#burger{
  -webkit-box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);
   -moz-box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);
    box-shadow: 9px 9px 30px 5px rgba(0,0,0,0.61);;
}


#hamburger.show {
  box-shadow: 7px 7px 10px 0px rgba(0, 0, 0, 0.48);
}
</style>

<div class="btn-group-fab" role="group" aria-label="FAB Menu">
  <div>

    <button id="burger" type="button" class="btn btn-main btn-primary has-tooltip" data-placement="left" title="Menu"> 
    	<i class="fa fa-bars"></i> 
    </button>

    <button id="btn-total" type="button" class="btn btn-sub btn-info has-tooltip totalusd" data-placement="left" title="Total $">
      <span id="totalusd" class="floatingmenu_label">Total $</span>
      <i class="fa fa-usd fab-center" aria-hidden="true"></i> 
    </button>
    
   <button type="button" class="btn btn-sub btn-info has-tooltip" data-placement="left" title="Amex">
    	<span id="amex" class="floatingmenu_label">Amex</span>
    	<i class="fa fa-cc-amex fab-center" aria-hidden="true"></i> 
 	 </button>
    
    <button type="button" class="btn btn-sub btn-danger has-tooltip" data-placement="left" title="Mastercard"> 
    	<span id="mc" class="floatingmenu_label">Mastercard</span>
    	<i class="fa fa-cc-mastercard fab-center" aria-hidden="true"></i> 
    </button>

    <button type="button" class="btn btn-sub btn-warning has-tooltip" data-placement="left" title="Visa"> 
    	<span id="visa" class="floatingmenu_label">Visa</span>
    	<i class="fa fa-cc-visa fab-center" aria-hidden="true"></i> 
    </button>

    <button type="button" class="btn btn-sub btn-primary  has-tooltip" data-placement="left" title="ATH"> 
      <span id="ath" class="floatingmenu_label">ATH</span>
      <i class="fa fa-credit-card fab-center" aria-hidden="true"></i> 
    </button>


    <button type="button" class="btn btn-sub btn-success has-tooltip" data-placement="left" title="Cash">
    	<span id="cash" class="floatingmenu_label">Cash</span>
    	<i class="fa fa-money fab-center" aria-hidden="true"></i> 
    </button>    
    
    <button type="button" id="facturas" class="btn btn-sub btn-invert has-tooltip" data-placement="left" title="Facturas"> 
    	<span id="invoices" class="floatingmenu_label">Facturas</span>
    	<i class="fa fa-shopping-cart fab-center" aria-hidden="true"></i> 
	</button>

<!-- ATH fa-credit-card" -->
  </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/js/bootstrap.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/popper.min.js"></script>

<script type="text/javascript">
	$(function() {

    
  $('#amex').html('<?php echo(  $salesU['AMEX'][0]['monto']   ); ?>');
  $('#ath').html('<?php echo(  $salesU['ATH'][0]['monto']   ); ?>');
  $('#cash').html('<?php echo(  $salesU['CASH'][0]['monto']   ); ?>');
  $('#visa').html('<?php echo(  $salesU['VISA'][0]['monto']   ); ?>');
  $('#mc').html('<?php echo(  $salesU['MASTERCARD'][0]['monto']   ); ?>');
  $('#invoices').html('<?php echo(  'Facturas # '.$facturas[0]['invoices']  ); ?>');

  $('#totalusd').html('<?php echo(  'Total $ '.number_format((float)$facturas[0]['total'], 2,'.',',')  ); ?>');

  
  
  document.getElementById('burger').setAttribute("title", "<?php echo( $user )?>" )



  $('.btn-group-fab').on('click', '.btn', function() {
    $('.btn-group-fab').toggleClass('active');
    $('.floatingmenu_label').toggle( "slow", function(){})
  });
  $('has-tooltip').tooltip();



  if ($('#amex').html().trim()=='') {
    $('#amex').hide();
  }
});
</script>