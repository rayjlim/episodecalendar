<?php

class ConnectionFactory
{
    private static $instance;
    /**
    *  getConnection
    *
    * @return object mysqli
    */
    static public function getConnection()
    {
        if(!isset(self::$instance)){  
            self::$instance = new mysqli(
            DB_HOST, 
            DB_USER, 
            DB_PASSWORD, 
            DB_NAME
        );
            if(self::$instance->error){
                echo "Failed to connect to MySQL: (" 
                . self::$instance->connect_errno . ") " . self::$instance->connect_error;
                throw new Exception('Error MySQL: ' . self::$instance->connect_error);  
            }  
        } 
        // echo 'return self instance';
         return self::$instance;
    }
}
