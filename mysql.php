<?php

class ConnectMysql{
    var $servername;
    var $username;
    var $password;
    var $database;
    var $dbport;
    
    var $db;
    
    function __construct(){
        $servername = getenv('IP');
        $username = getenv('C9_USER');
        $password = "";
        $database = "c9";
        $dbport = 3306;
        
        // Create connection
        $mysql = new mysqli($servername, $username, $password, $database, $dbport);
    }
    
    function getConnectError(){
        // Check error
        if($mysql->connect_error){
            return 1;
        }
        else{
            return 0;
        }
    }
    
    
}

?>
