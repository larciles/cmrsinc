<?php
date_default_timezone_set("America/Puerto_Rico");
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");

$to=$_POST['to']; //"7867202377";
//$to="7876474004";
//$to="7876053218";
//$to="7867202377";
//$result =$to;
$body="https://www.google.com/maps/place/Centro+M%C3%A9dico+Adapt%C3%B3geno/@18.399487,-66.1574777,17z/data=!3m1!4b1!4m5!3m4!1s0x8c036a3076a835ff:0x4d7f5bce3afd9b8a!8m2!3d18.399487!4d-66.155289" ;//"Hola! at last!";

$body=$_POST['body']; 
$usuario=$_POST['usuario']; 


$URL="https://api.flowroute.com/v2/messages";
$data= array('to'=>$to
    , 'from' => '19892560054'
    , 'body' => $body);

$accessKey ="69795151";
$secretKey ="dmkT5ptBfLbfR29cUFwscHdmpoVrvqaq";

$data_string = json_encode($data);            
$ch = curl_init($URL);                                                                      
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
curl_setopt($ch, CURLOPT_USERPWD, $accessKey . ":" . $secretKey);                                                                    
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                         'Content-Length: ' . strlen($data_string))                                                                       
);                                                                                                           
$result = curl_exec($ch);


require_once '../db/mysqlcmaconn.php';

try {    
       $dbconn = MysqlCmaConn::getConnection_s();
       //$flid = $result['data']['id'];
       $fecha=date("Y-m-d");
       // $query="INSERT INTO sms_out (sms_text,sender_number,service_number,flow_id,sent_dt,usuario) VALUES('$body','$to','19892560054','$result','$fecha','$usuario')";
       //  MysqlCmaConn::insert($query);

	   $stm=$dbconn->prepare("INSERT INTO sms_out (sms_text,sender_number,service_number,flow_id,sent_dt,usuario) VALUES(?,?,?,?,?,?)");
	   $stm->bindValue(1,$body);
	   $stm->bindValue(2,$to);
	   $stm->bindValue(3,FLOWRTNUM);
	   $stm->bindValue(4,$result);
	   $stm->bindValue(5,date("Y-m-d")); 
     $stm->bindValue(6,$usuario); 
	   $stm->execute();

      $arrayResult = array('Exito' => $fecha );

} catch (Exception $exc) {
    $arrayResult = array('Error' => $exc );
    echo $exc->getTraceAsString();
}


echo( json_encode($arrayResult) );



function writeMsg($numero,$message,$flid,$dbmysql ) {

        try {

        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }     
}