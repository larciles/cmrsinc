<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit(0);
require_once '../db/mssqlconn.php';

$dbmsql = mssqlConn::getConnection();

$query = "Select 'Todos' as Descripcion, 1 as id  
union  
select 'Consultas' as Descripcion, 2 as id  
union  
select 'Sueroterapia' as Descripcion, 3 as id  
union  
select 'Laser' as Descripcion, 4 as id  
union 
select 'Intravenoso' as Descripcion, 5 as id  
union 
select 'Bloqueo' as Descripcion, 6 as id  
union 
select 'Celulas Madres' as Descripcion, 7 as id  
order by id";
$tconsultas = mssqlConn::Listados($query);
echo $tconsultas;