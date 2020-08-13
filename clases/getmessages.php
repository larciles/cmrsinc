<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mysqlconn.php';
require_once '../db/mysqlcmaconn.php';

$dbconns = MysqlCmaConn::getConnection_s();
$dbconn  = MysqlConn::getConnection_my();

$telefono = $_POST['telefono'];

#
$query="SELECT *, '1' AS modo From fr_replies Where sms_from='$telefono' order by  id asc ";
$res = MysqlConn::Listados($query);

$x = json_decode($res);

if(!is_null($x)) {
 #
  $query="SELECT id, sms_body,service_number as sms_to, telefono as sms_from, '1' as sms_read, ts as sms_received, '0' as modo From (
  SELECT id, sms_text as sms_body, service_number,  
  CASE  substring(sender_number,1,1) WHEN  '1' THEN     
  sender_number ELSE 
  concat('1',sender_number)
  END as telefono
  ,ts
  From sms_out 
  order by sender_number
  ) as t1 Where telefono='$telefono' group by  ts asc";
  $res = MysqlCmaConn::Listados($query);
 
 
 $a_sms;
 if (is_array($x)) {
 	for ($i=0; $i <count($x) ; $i++) { 
                   
 		$a_tmp = array(
 			 'sms_received' => $x[$i]->sms_received
 			,'sms_body' =>$x[$i]->sms_body 
 			,'modo' =>$x[$i]->modo 
                         );
 
 		$a_sms[]=$a_tmp;	
 	}
 }
 
 $y = json_decode($res);
 
 if (is_array($y)) {
 	for ($i=0; $i <count($y) ; $i++) { 
                  
 		$a_tmp = array(
 			 'sms_received' => $y[$i]->sms_received
 			,'sms_body' =>$y[$i]->sms_body 
 			,'modo' =>$y[$i]->modo 
                         );
 
 		$a_sms[]=$a_tmp;	
 	}
 }
 
 
 
 foreach ($a_sms as $key => $row) {
     $sms_received[$key]  = $row['sms_received'];
     $sms_body[$key] = $row['sms_body'];
     $modo[$key] = $row['modo'];
 }
 
 $sms_received  = array_column($a_sms, 'sms_received');
 $sms_body = array_column($a_sms, 'sms_body');
 $modo = array_column($a_sms, 'modo');
 
 
 $x_array=array_multisort( $sms_received, SORT_ASC, $a_sms);
 
 } else{
 $a_sms=null;	
 }
echo json_encode($a_sms)  ;//$res;