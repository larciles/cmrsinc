<?php
set_time_limit(0);
$nserver=1;
if ($nserver==1) {
	define('MSQL_HOST','192.130.74.2,50069'); //AQUI VA TU HOST 192.130.74.2\SQLSISTEMICA  localhost
	define('SERVER_HOST','192.130.74.115');
	define('MSQL_PASS',''); //superv  cmaweb1
}elseif($nserver==2){
    define('MSQL_HOST','localhost'); //AQUI VA TU HOST 192.130.74.2\test localhost
    define('SERVER_HOST','localhost');
    define('MSQL_PASS','superv'); //superv  cmaweb1
}else{
	define('MSQL_HOST','192.130.74.2\TEST'); //AQUI VA TU HOST 192.130.74.2\test localhost
	define('SERVER_HOST','localhost');
	define('MSQL_PASS',''); //superv  cmaweb1
}

define('MSQL_USER','sa');  //

define('MSQL_DBNAME','farmacias');
define('MYSQL_HOST','104.214.114.99'); 
define('MYSQL_USER','netogica_reply1');
define('MYSQL_PASS','Net0g1cR3ply');
define('MYSQL_DBNAME','netogica_replies');
define('MYSQL_PORT',3306);
define('MYSQL_CHAR', 'utf8');

define('MYSQL_xCMA_HOST','104.214.114.99'); //AQUI VA TU HOST 104.214.114.99 -209.217.249.78
define('MYSQL_xCMA_USER','netogica_admsql');
define('MYSQL_xCMA_PASS',',3VT*N5O0f#Z');
define('MYSQL_xCMA_DBNAME','netogica_cma_sms');
define('MYSQL_xCMA_PORT',3306);

define('MYSQL_CMA_HOST','104.214.114.99'); 
define('MYSQL_CMA_USER','netogica_admsql');
define('MYSQL_CMA_PASS',',3VT*N5O0f#Z');
define('MYSQL_CMA_DBNAME','netogica_cma_sms');
define('MYSQL_CMA_PORT',3306);
define('MYSQL_CMA_CHAR', 'utf8');

#ENVIO DE SMS
define('DELAY',1);  // TIEMPO MAXIMO DEL RAMDON PARA ENVIO DE MSG
define('MAXFREEMSG',8000); // MAXIMA CANT DE SMS GRATIS
define('ID',2); //ID DEL CONTADOR DE LA CANT MAXIMA DE SMS GRATIS
define('TEST',false); //ID DEL CONTADOR DE LA CANT MAXIMA DE SMS GRATIS


#SERVICIO FLOWROUTE
define('SECRETKEY','dmkT5ptBfLbfR29cUFwscHdmpoVrvqaq');
define('ACCESSKEY','69795151');
define('FLOWRTNUM','19892560054');

define('ENDI_MYSQL_CONN',0); #PARA HABLILITAR O NO LA CONEXION MYSQL EN CASO DE PROBLEMAS CON EL WWWW SERVER  0= DESHABILITADO 1= HABILITADO
define('sms_ON_OFF',0); #PARA HABLILITAR O NO LA BUSQUEDA DE SMS  EL WWWW SERVER  0= DESHABILITADO 1= HABILITADO
define('FEEE',1); #FECHA_ENTREGA_EDIT_ENABLED
define('ADD_ENTREGA', 1); #HABILITA EL DOBLE CLICK SOBRE EL NUMERO DE FACTURA PARA AGREGAR LAS ENTREGAS ANTES DEL 15 DE ENERO 2020