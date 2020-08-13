<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mysqlcmaconn.php';
$dbmsql = MysqlCmaConn::getConnection();


$from = $_POST['from'];
$to = $_POST['to'];

$sd = explode('/',$from);
$from = $sd[2].$sd[0].$sd[1];

$ed = explode('/',$to);
$to = $ed[2].$ed[0].$ed[1];

if($from=="" and $to==""){
    $query = "SELECT COUNT(*) num_rows FROM sms_out" ;
}elseif($from!=="" and $to!==""){
    $query = "SELECT COUNT(*) num_rows FROM sms_out WHERE sent_dt between '$from' and '$to'" ;
}


$result = MysqlCmaConn::Listados($query);
echo $result;
