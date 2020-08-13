<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$coditems = $_POST['coditems'];

$query="SELECT * from exclusivos where coditems='$coditems'";

$result = mssqlConn::Listados($query);
echo $result;

