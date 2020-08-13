<?php
include "../../clases/fpdf/fpdf.php";
include '../../db/mysqlconn.php';	
header('Content-Type: text/html; charset=utf-8');
$from="";
$to="";
$stop;
if(isset($_GET['f']))
{
	$from=$_GET['f'];
	$to=$_GET['t'];
	$stop=$_GET['s'];
     

	$from = explode('/', $from);
    $from  = "$from[2]-$from[0]-$from[1]";

    $to = explode('/', $to);
    $to  = "$to[2]-$to[0]-$to[1]";    


}



class RepSMSReplies extends FPDF
{
	public $from;
	public $to;

	public function Header(){
		//$this->Image("../img/phplogo.jpg",10,10,20,20);
		$this->SetFont("Arial","B",16);
		$this->Cell(0,10,"SMS Replies ",0,0,"C");
		$this->Ln(10);
        $df = $this->getUdfObj();
        $from = $df['from'];
        $to = $df['to'];

        
		$this->SetFont("Arial","BI",12);
		$this->Cell(0,8,$from." - ".$to,0,0,"C");
		$this->Ln(10);

		$fecha=date("Y-m-d");
		$this->SetFont("Arial","B",8);
		$this->Cell(350,10,$fecha,0,0,"C");
//		
        $this->Ln(10);

		$titulos = array('Phone','     Message');
		for ($i=0; $i < count($titulos) ; $i++) { 
			$this->SetFont('Arial','B',10);
			$this->Cell(20 , 5, iconv('UTF-8', 'ISO-8859-2', $titulos[$i]) ,0,0,"C", false);	         
		}
		$this->Line(10, 45, 210-20, 45); // 20mm from each edge
		$this->Ln(10);


	}

	function Footer(){
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}


$obj= array("from"=>$from, "to"=>$to);
$report = new RepSMSReplies('P', 'mm', 'A4',$obj);

$report->AliasNbPages();
$report->addPage();
$report->SetTitle('SMS Replies');
//$report->Ln(10);

$dbconn = MysqlConn::getConnection();

//DETALLES

$query="SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies";	
if($stop=="1"){
   $query="SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies where sms_body not like '%stop%' ";	
}
if($from!="" && $to!=""){
   $query="SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies  WHERE  date(sms_received) between '$from' and '$to' ";	
   if($stop=="1"){
     $query="SELECT sms_from,sms_body, date(sms_received) as sms_received from fr_replies WHERE date(sms_received) between '$from' and '$to' and sms_body not like '%stop%' ";
   }
}

$result=$dbconn->query($query);
foreach($result as $row){

	$sms_from=$row['sms_from'];
	$sms_body=$row['sms_body'];
	$sms_received=$row['sms_received'];
	$sms_body=str_replace('"', "",  $sms_body);
    $sms_body=str_replace("'", "",  $sms_body);	
	$sms_body = str_replace(" "," ",preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($sms_body))));
	$text= iconv('UTF-8', 'ISO-8859-2', $sms_body);
    $report->SetFont('Arial','B',9);
	$report->Cell(180 , 5, $sms_from .'  '.$text);	
	$report->Ln(10);

}
$report->Output();

?>