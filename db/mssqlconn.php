<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
  $user='log-in';
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}
    $url = $_SERVER['SERVER_NAME'] . dirname(__FILE__);
 date_default_timezone_set("America/Puerto_Rico");
    //require_once "./config/config.php";
    require_once "config.php";
    include('mylib.php');

//db connection class using singleton pattern
class mssqlConn{
 
    //variable to hold connection object.
    protected static $db;
 
    //private construct - class cannot be instatiated externally.
    private function __construct() {
 
    try {
            // assign PDO object to db variable
            // SQL SERVER Connection
            self::$db = new PDO("sqlsrv:server=".MSQL_HOST.";Database=".MSQL_DBNAME,  MSQL_USER, MSQL_PASS);  
            //self::$db = new PDO( 'mysql:host=localhost;dbname=cma_cma;charset=utf8', 'root', '' );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            //
            
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
 
    }
 
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
 
    //Guarantees single instance, if no connection object exists then create one.
    if (!self::$db) {
        //new connection object.
        new mssqlConn();
    }
 
    //return connection.
    return self::$db;
    }
    
    public function insert($sql){

        if(!isset($_SESSION['username'])){
             $user='log-in';
        }else{
             $user=$_SESSION['username'];
             $codperfil=$_SESSION['codperfil'];
        }

       try {
           $rc=0;
           $stmt = self::$db->query($sql);
           $li=self::$db->lastInsertId();
           $rc=$stmt->rowCount();           
           self::write_log($sql,'[query->'.$user.']');
           
          
        }catch (Exception $e) {
           self::write_log($sql.' - '.$e,'[error->'.$user.']');
           $rc=-1;
        }

        $result= array('rowsaffected'=>$rc
                      ,'lastInsertId'=>$li);
        
      return  $result;
    }

    public function queryFunc(){
        return true;
    }
       

    public static function Listados($query){
      //$query= "select distinct a.v , 'Repeticion de  '+CONVERT(varchar(10), a.v) +' => ' +STR(  (SELECT COUNT (*)  FROM [farmacias].[dbo].[viewRepeatV4] B WHERE  B.v=a.V )) descripcion FROM [farmacias].[dbo].[viewRepeatV4] a  Union  Select 0 v, 'Todos =>' + STR(  (SELECT COUNT (*)  FROM [farmacias].[dbo].[viewRepeatV4] B))  descripcion  order by a.v ";
	//  $query="SELECT * FROM [farmacias].[dbo].[viewRepeatV5] order by v ";
    
    if(!isset($_SESSION['username'])){
        $user='log-in';
    }else{
         $user=$_SESSION['username'];
         $codperfil=$_SESSION['codperfil'];
    }

    $lista=null;
      try {
            $rr=self::$db->query($query);
            $lista=$rr->fetchAll(PDO::FETCH_ASSOC);
           // foreach(self::$db->query($query) as $row){
           //     $lista[]=$row;
           // }  
          self::write_log($query,'[query->'.$user.']');
      } catch (Exception $e) {
         self::write_log($query.' - '.$e,'[error->'.$user.']');
      }
             
       return json_encode($lista);
    }

    public static function stexec($cmaci){

        $sp= self::$db->prepare("{ CALL $cmaci() }");
        $sp->execute();
        //$proc = mssql_init('some_proc', $conn);
        //$proc_result = mssql_execute($proc);
    }

    public static function phoneNumbers($nveces) {
        
    if ($nveces==0){
            $query="SELECT  b.nombres,SUBSTRING (replace( replace( replace(b.telfhabit,'-',''),'/',''),' ',''),1,10) cel 
            FROM [farmacias].[dbo].[viewRepeatWHOLE] a 
            inner join MClientes b on a.codclien=b.codclien where len( b.telfhabit)>=10 and b.nombre is not null";
        }else  if($nveces>0){
            $query="  SELECT  b.nombres,SUBSTRING (replace( replace( replace(b.telfhabit,'-',''),'/',''),' ',''),1,10) cel 
            FROM [farmacias].[dbo].[viewRepeatV4] a 
            inner join MClientes b on a.codclien=b.codclien where len( b.telfhabit)>=10 and a.v='$nveces'
            order by nombres";
       }
                
	    $numbers[] ='7876053218';
        $numbers[] ='13058739609';        
        $numbers[] ='7867202377';
       	$numbers[] ='19392081944';
        foreach(self::$db->query($query) as $row){
           $numbers[] =    $row['cel'];
         }

         $len=sizeof($numbers);          
	     $numbers[] ='13058739609';
          // ;//'7876053218'; //13058739609   $numbers[] ='9392081944';   
        echo json_encode($numbers);
        //
    }



 public static function write_log($cadena,$tipo)
{
   // var_dump(realpath( '.' )."/log_".date("Y-m-d").".txt");
    try {
            // $arch = fopen('/tmp/log'."/log_".date("Y-m-d").".txt", "a+"); 

            //  fwrite($arch, "[".date("Y-m-d H:i:s.u")." ".$_SERVER['REMOTE_ADDR']." ".
            //        $_SERVER['HTTP_X_FORWARDED_FOR']." - $tipo ] ".$cadena."\n");
            // fclose($arch);
        $log = new Logging();
        $d=__DIR__;
        $log->lfile( $d."\\tmp\\log_".date("Y-m-d").".txt");
        $log->lwrite("[".date("Y-m-d H:i:s.u")." ".$_SERVER['REMOTE_ADDR']." ".$_SERVER['HTTP_X_FORWARDED_FOR'],$tipo,$cadena);
    } catch (Exception $e) {
        
    }

}
}
 
