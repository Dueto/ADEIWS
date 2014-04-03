<?php
require 'timeformat.php';
require_once("sqlquerys.php");
require_once('websockets.php');
 
class clientSupport extends WebSocketServer 
{   
    private  $link;
   
    
    protected function process ($user, $message) 
    {        
        $props = explode("<>", $message);
        $tableName = $props[0];
        $experiment = $props[1];
        $isOnePortion = $props[2];
        $aggregation = $props[3];        
                
        $time = new timeFormat($experiment);  
        
        try
        {         
            $this->link = new sqlQuerys();
            $results = $this->link->selectData($tableName, $time);
            $dataCount = count($results);          
            if($dataCount != 0)
            {    
                if($isOnePortion)
                {   
                    $stringToSend = $stringToSend . pack("I*", $dataCount);  
                    foreach($results as $elem)
                    {  
                       $time = $this->formatTime($elem);                        
                       $stringToSend = $stringToSend . pack("A*", $time);  
                       $i = 0;
                       foreach($elem as $property => $value)
                       { 
                           if(substr_count($property, $aggregation) != 0)
                           {                                     
                               $stringToSend = $stringToSend . pack("d*", $value);                                     
                           }                          
                        }
                    }                    
                    $this->send($user, $stringToSend);   
                }
                else
                {
                    $this->send($user, $stringToSend);   
                }               
            }
            else
            {
                $this->send($user, 'No data in requested source.');
            }
        } 
        catch (Exception $ex) 
        {
            $this->send($user, $ex->getMessage());
        }           
    }
    
    protected function connected ($user) 
    {         
    }
    
    protected function closed ($user) 
    {       
    }
    
    protected function formatTime($elem)
    {
        $time = str_replace(' ', 'T', $elem["time"]);
        if(isset($elem["ns"]))
        {
            $time = $time . '.' .$elem["ns"];
        }
        else
        {
            $time = $time . '.000000';
        }
        return $time;
    }   
}
 



