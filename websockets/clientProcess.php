<?php

//require_once 'Thread.php';

class clientProcess extends Thread 
{
    private $link = NULL;
    private $socket = NULL;
    private $user = NULL;  
    private $message = NULL;
    
    public function __construct($link, $socket, $user, $message)
    {
        $this->link = $link;
        $this->socket = $socket;
        $this->user = $user;
        $this->message = $message;
    }
    
    public function run()            
    {
        $tableName  = $experiment = $isOnePorion = $aggregation = $channelCount = $db_mask = NULL;
        
        $props = explode(";", $this->message);
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
                $columns = $this->socket->formColumns($tableName, $aggregation, $db_mask);
                print_r($columns);
                
                $results = $this->link->selectData($tableName, $experiment, $columns);               
                if($results !== NULL)
                {    
                    $stringToSend = $this->socket->packString($results, $columns);                      
                    $this->socket->send($this->user, $stringToSend);                                          
                }
                else
                {
                    $this->socket->send($this->user, 'No data in request.');
                }                 
            } 
            catch (Exception $ex) 
            {
                $this->socket->send($this->user, $ex->getMessage());
            }          
        }  
        else
        {            
            $this->socket->send($this->user, 'All parameters should be specified.');
        }
        
    }
}

?>