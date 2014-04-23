<?php
require 'adei.php';
require_once 'websockets/clientsupport.php';
ini_set("memory_limit",-1);
ini_set('mysql.connect_timeout', -1);
        $host = 'localhost';
        $port = 12345;         
        $server = new clientSupport($host , $port );
?>