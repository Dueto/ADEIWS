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
        
        print_r($tableName);
        print_r($experiment);
        print_r($isOnePortion);
        print_r($aggregation);
        print_r($channelCount);
        print_r($db_mask);
        
       
        if($tableName !== NULL && $experiment !== NULL &&
           $isOnePortion !== NULL && $aggregation !== NULL &&
            $channelCount !== NULL && $db_mask !== NULL)
        {              
            try
            {               
                $columns = $this->formColumns($tableName, $aggregation, $db_mask);
                print_r($columns);
                
                $results = $this->link->selectData($tableName, $experiment, $columns);               
                if($results !== NULL)
                {    
                    $stringToSend = $this->packString($results, $columns);                      
                    $this->send($user, $stringToSend);                                          
                }
                else
                {
                    $this->send($user, 'No data in request.');
                }                 
            } 
            catch (Exception $ex) 
            {
                $this->send($user, $ex->getMessage());
            }          
        }  
        else
        {            
            $this->send($user, 'All parameters should be specified.');
        }
    }
    
    protected function connected ($user) 
    {        
        /*if($this->link === NULL)
        {
            try
            {
                $this->link = new sqlQuerys("localhost:3306", "adei");                
                if($this->link === NULL)
                {         
                    $this->send($user, 'Error in connecting to database');
                    $this->disconnect($user);
                }            
            }
            catch(Exception $ex)
            {
                $this->send($user, $ex->getMessage());
                $this->disconnect($user);
            }        
        }*/
    }
    
    protected function closed ($user) 
    {       
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



 



