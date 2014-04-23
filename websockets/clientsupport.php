<?php
require 'timeformat.php';
require_once("sqlquerys.php");
require_once('websockets.php');
 
class clientSupport extends WebSocketServer 
{   
   // private $link = NULL;
    
    protected function process ($user, $message) 
    {       
        $tableName  = $experiment = $isOnePorion = $aggregation = $channelCount = $db_mask = NULL;
        
        $props = explode(";", $message);
        $tableName = $props[0];
        $experiment = $props[1];
        $isOnePortion = $props[2];
        $aggregation = $props[3]; 
        $channelCount = $props[4];
        $db_mask = explode(",", $props[5]);  
        
        if($tableName !== NULL && $experiment !== NULL &&
           $isOnePortion !== NULL && $aggregation !== NULL &&
            $channelCount !== NULL && $db_mask !== NULL)
        {              
            try
            {               
                $columns = $this->formColumns($tableName, $aggregation, $db_mask);   
                //$this->connected($user); 
                //print_r($user->link);
//                $user->link->closeConnection();
//                $user->link = null;
                $user->link = new sqlQuerys();               
                $user->link->connect("ipekatrinadei:3306", "adei");
                $results = $user->link->selectData($tableName, $experiment, $columns); 
                if($results !== NULL)
                {    
                    $stringToSend = $this->packString($results, $columns);                      
                    $this->send($user, $stringToSend);                     
                    print_r("Process dead\n");
                    exit(1);
                }
                else
                {
                    $this->send($user, 'No data in request.');
                    print_r("Process dead\n");
                    exit(1);
                }                 
            } 
            catch (Exception $ex) 
            {
                $this->send($user, $ex->getMessage());
                print_r("Process dead\n");
                exit(1);
            }          
        }  
        else
        {            
            $this->send($user, 'All parameters should be specified.');
            print_r("Process dead\n");
            exit(1);
        }
        print_r("Process dead\n");
        exit(1);
    }
    
    protected function connected ($user) 
    {   
//        if($user->link === null)
//        {
//            $user->link = new sqlQuerys();           
//            try
//            {
//                print_r("Initialized new mysql connection\n");
//                $user->link->connect("ipekatrinadei:3306", "adei");
//            }
//            catch(Exception $ex)
//            {
//                throw $ex;
//            }        
//        }
//        else
//        {
//            if(!$user->link->ping()) 
//            {
//                try
//                {
//                    print_r("Initialized new mysql connection\n");
//                    $user->link->connect("ipekatrinadei:3306", "adei");
//                }
//                catch(Exception $ex)
//                {
//                    throw $ex;
//                } 
//            }
//        }
    }
    
    protected function closed ($user) 
    {    
        //$user->link->closeConnection();
        //print_r("Closed mysql connection\n");
    }
    
    protected function formatTime($elem)
    {        
        $time = $elem["time"];
        if(isset($elem["ns"]))
        {
            $time = $time . '.' .$elem["ns"];
        }
        else
        {
            $time = $time . '.000000000';
        }     
        return $time;
    }   
    
    protected function packString($results, $columns)
    {
        $stringToSend = '';
        foreach($results as $elem)
        {            
           $time = $this->formatTime($elem); 
           $stringToSend = $stringToSend . pack("a*", $time);     
           foreach($columns as $value)
           { 
                $stringToSend = $stringToSend . pack("d*", $elem[$value]); 
           }   
        }          
        return $stringToSend;
        
    }
    
    protected function formColumns($tableName = NULL, $aggregation = NULL, $db_mask = NULL)
    {
        if($tableName !== NULL && $aggregation !== NULL && $db_mask !== NULL)
        {
            $level = explode("__", $tableName)[0];
            $columns = array();
            if($level === "cache0")
            {                
                foreach($db_mask as $db_item)
                {
                    array_push($columns , ("v" . $db_item));
                }               
                return $columns;
            }
            else
            {
                foreach($db_mask as $db_item)
                {
                    array_push($columns , ($aggregation . $db_item));
                }  
                return $columns;                
            }
        }
        else 
        {
            return NULL;
        }
        
    }
}



 



