<?php
require_once "config.php";

class MysqlCmaConn{
 
    //variable to hold connection object.
    protected static $db;
 
    //private construct - class cannot be instatiated externally.
    private function __construct() {
 
	try {
            // assign PDO object to db variable
			// SQL SERVER Connection
            // self::$db = new PDO("sqlsrv:server=192.130.74.2\SQLSISTEMICA;Database=farmacias",  "sa", "xt3957%432/2016#");  

            self::$db = new PDO( 'mysql:host='.MYSQL_CMA_HOST.';dbname='.MYSQL_CMA_DBNAME.';port='.MYSQL_CMA_PORT.';charset='.MYSQL_CMA_CHAR, MYSQL_CMA_USER, MYSQL_CMA_PASS );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			self::$db->setAttribute( PDO::ATTR_TIMEOUT, 600);
            //
            
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
 
    }
 
    // get connection function. Static method - accessible without instantiation
    public static function getConnection_s() {
 
    //Guarantees single instance, if no connection object exists then create one.
    if (!self::$db) {
        //new connection object.
        new MysqlCmaConn();
    }
 
    //return connection.
    return self::$db;
    }
    
    public function insert($sql){
        return $sql;
    }

    public function queryFunc(){
        return true;
    }
       // $sth = self::$db->prepare("SELECT * FROM sms_out");
      //  $sth->execute();
/* Fetch all of the remaining rows in the result set */
//print("Fetch all of the remaining rows in the result set:\n");
//$result = $sth->fetchAll();
//print_r($result);
    
   public function find($id)
    {
        $stmt = $this->connection->prepare('
            SELECT "User", users.* 
             FROM sms_control 
             WHERE id = :id
        ');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Set the fetchmode to populate an instance of 'User'
        // This enables us to use the following:
        //     $user = $repository->find(1234);
        //     echo $user->firstname;
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }
	
	public static function Listados($query){
      //$query= "select distinct a.v , 'Repeticion de  '+CONVERT(varchar(10), a.v) +' => ' +STR(  (SELECT COUNT (*)  FROM [farmacias].[dbo].[viewRepeatV4] B WHERE  B.v=a.V )) descripcion FROM [farmacias].[dbo].[viewRepeatV4] a  Union  Select 0 v, 'Todos =>' + STR(  (SELECT COUNT (*)  FROM [farmacias].[dbo].[viewRepeatV4] B))  descripcion  order by a.v ";
    //  $query="SELECT * FROM [farmacias].[dbo].[viewRepeatV5] order by v ";
      foreach(self::$db->query($query) as $row){
             $lista[]=    $row;
            }            
           return json_encode($lista);
    }

}//end class
