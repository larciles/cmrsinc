<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
// setlocale(LC_MONETARY,"en_US");
// date_default_timezone_set("America/Puerto_Rico");
// header('Content-type: application/pdf');
// // header('Content-Disposition: attachment; filename="myPDF.pdf');
// header('Cache-Control: private');
// header('Pragma: private');
// header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

require('../../controllers/pdf_js.php');
require('../../controllers/MasterInvoiceController.php');
require('../../controllers/DetailsInvoiceController.php');

require('../../controllers/barcode.php');

// $user=$_SESSION['user'];

$number = $_GET['barcode'];


if (isset($_GET['times'])) {
    $times = $_GET['times'];
}

$mascontroller = new MasterInvoiceController();
$detcontroller = new DetailsInvoiceController();

$master=$mascontroller->readUDF("SELECT *, a.plazo -DATEDIFF( CURDATE() ,  (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) ) remain FROM masterinvoice a where barcode='$number'order by a.caso " );
$detail=$detcontroller->readUDF("SELECT *, a.plazo -DATEDIFF( CURDATE() ,  (SELECT b.servicedate FROM detailsinvoice b WHERE b.customerid = a.customerid and b.caso = a.caso LIMIT 1) ) remain FROM detailsinvoice a where barcode='$number' and status=1 order by a.caso" );

  class PDF_AutoPrint extends PDF_JavaScript
 {
    function AutoPrint($printer='')
    {
        // Open the print dialog
        if($printer)
        {
            $printer = str_replace('\\', '\\\\', $printer);
            $script = "var pp = getPrintParams();";
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
            $script .= "pp.printerName = '$printer'";
            $script .= "print(pp);";
        }
        else
            $script = 'print(true);';
        $this->IncludeJS($script);
    }
 }

class PDF extends PDF_Code128
{
     private static $number;
     private static $customername;
     private static $date;
 
    function Header()
    {
        // Logo
        // $this->Image('logo.png',10,6,30);
       
        $this->SetFont('Helvetica','',25);
      
        $this->Cell(80);
      
        $this->Cell(30,10,' A.M.A.S',0,1,'C');
        $this->SetFont('Arial','',11);
        $this->Cell(190 , 5, $this->date ,0,1,'C');
        $this->Cell(190 , 5,' Cliente '  ,0,1,'C');
        $this->SetFont('Arial', 'B', 15);
        $wPage=$this->GetPageWidth()/2;

        $strLen=strlen( $this->customername );
        $xPos= $wPage-$strLen;
        $this->Cell(190 , 5,$this->customername  ,0,1,'C');

        $this->Ln(15);

        $this->SetFont('Arial','',12);
        $this->Cell(8 , 5, '# Caso'  ,0,0,'R');
        $this->Cell(25 , 5, 'Paciente',0,0,'R');
        $this->Cell(86 , 5, 'F. Servicio',0,0,'R');
        $this->Cell(20 , 5, 'Record'  ,0,0,'R');    
        $this->Cell(40 , 5, 'Expira en',0,1,'R');

        $this->Line($this->GetX()-5, $this->GetY(), 210-$this->GetX(), $this->GetY());  //Dibuja una linea
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $this->Code128($this->GetY()+100,12,$this->number,40,10);
        $this->SetXY($this->GetY()+100,23,$number,110,20);
        $this->Write(5,$this->number);
        $this->SetY(55); 
     
   }

   function setNumber($number){
        $this->number = $number;
    }

   function getNumber(){
        return $this->number;
    }


    function setCustomername($customername){
        $this->customername = $customername;
    }

   function getCustomername(){
        return $this->customername;
    }


    function setDate($date){
        $this->date = $date;
    }

   function getDate(){
        return $this->date;
    }

   function Footer(){
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        $this->Cell(-20,10, date("F j, Y, g:i a"),0,0,'C');
    }
}



 $pdf = new PDF();// PDF_AutoPrint();  //FPDF();//  PDF_Code128
 $pdf->setNumber($number);
 $pdf->setCustomername($master[0]['customername']);
 $pdf->setDate($master[0]['date']);
 $pdf->AliasNbPages();
 $pdf->AddPage();
 $pdf->SetFont('Arial', '', 10);
 

 $lenA =count($detail);
 $_name="";
 $rec=0;
 for ($i=0; $i <$lenA ; $i++) { 

        
        $fecha_ser= explode('-',$detail[$i]['servicedate']);
        $fechaser=$fecha_ser[1].'/'.$fecha_ser[2].'/'.$fecha_ser[0];
    
        $pdf->SetY(($pdf->GetY()));
        
  
        if ($i>0) {
            if ($currentrecord!=$detail[$i]['patientname']) {
                $pdf->Line($pdf->GetX()-5, $pdf->GetY(), 210-$pdf->GetX(), $pdf->GetY());  //Dibuja una linea                
            }
        }

        $docLen =strlen( "- ".$detail[$i]['documento'] );
        if ($docLen<100) {
            $fontSize= 10;
        } else if ($docLen>100 && $docLen<108) {
            $fontSize= 9;
        } else if ( $docLen<120) {    
            $fontSize= 8;
        }else{
            $fontSize= 6;
        }

        if ($_name!=$detail[$i]['patientname']) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(10,5, $detail[$i]['caso'] ,0,0,'L');
            $pdf->Cell(35,5, $detail[$i]['patientname'] ,0,0,'J');
            $pdf->Cell(75,5, $fechaser,0,0,'R');
            $pdf->Cell(25,5, $detail[$i]['record'],0,0,'R'); 

            $expi =(int) $detail[$i]['remain'];

            if ($expi>1) {
                $pdf->Cell(33,5, $detail[$i]['remain'].' Dias',0,1,'R'); 
            }else if($expi==1){
                $pdf->Cell(33,5, $detail[$i]['remain'].' Dia',0,1,'L'); 
            }else if($expi==0){
                $pdf->Cell(33,5, $detail[$i]['remain'].' Hoy',0,1,'L');                 
            }else if($expi<0){
                $pdf->Cell(33,5, $detail[$i]['remain'].iconv("UTF-8", "CP1250//TRANSLIT",'  Expiró'),0,1,'L');                 

            }

                     

            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->Cell(10,5, "- ".$detail[$i]['documento'] ,0,1,'L'); 
        }else{
            $pdf->SetFont('Arial', '', $fontSize);
            $pdf->Cell(10,5, "- ".$detail[$i]['documento'] ,0,1,'L');
        }
        $currentrecord=$detail[$i]['patientname'];        
        $_name=$detail[$i]['patientname'];   
 }

 $pdf->Output();

 ?>



