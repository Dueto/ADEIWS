<?php
require 'timeformat.php';
require_once("sqlquerys.php");
require_once('websockets.php');
 
class clientSupport extends WebSocketServer 
{   
    private  $link;
    private $dataLevel = array(31536000 => "-01-01 00:00:00",
                                2592000 => "-01 00:00:00",
                                86400 =>  " 00:00:00",                                                 
                                3600 =>  ":00:00",                         
                                60 => ":00" ,      
                                1 => "" ,
                                0 => "");
    
    
    protected function process ($user, $message) 
    {        
        $query = parse_url($message, PHP_URL_QUERY);

        $basicRequest = new BASICRequest($query);
        $sourceRequest = new SOURCERequest($basicRequest->GetProps());
        $request = new REQUEST($basicRequest->GetProps());
        $cacheDb = new CACHEDB($sourceRequest);      
        
        $cachePostfix = $cacheDb->GetCachePostfix();
        $options = $request->GetOptions();
        $levels = $options->Get("cache_config");
        $channelLabels = $options->Get("items");
        $maxLevel = $options->Get("min_resolution");
        $window = $basicRequest->GetProp("window");        
        $time = new timeFormat($basicRequest->GetProp("experiment"));         
        $needenLevel = $this->getLevelforTable($levels, $window, $maxLevel);
        
        $time->formatDate($this->dataLevel[$window]);
        
        if($needenLevel == "")
        {
            $needenLevel = "0";
        }            
        $tableName = "cache" . $needenLevel . $cachePostfix;
        $db_items = explode(",", $request->GetProp("db_mask"));
        
        $aggregator =  $this->getAggregator($window);
        
        try
        {         
            $this->link = new sqlQuerys();
            $results = $this->link->selectData($tableName, $db_items, $time, $aggregator); 
            if(count($results) != 0)
            {
                $stringToSend = $this->formCsvString($results, $db_items, $channelLabels);                
                $this->send($user, $stringToSend);   
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
    
    protected function formCsvString($results, $channels, $channelLabels)
    {        
        $columns = explode(",", str_replace("`", "", $this->link->getColumns()));        
        $stringToSend = "Date/Time";
        foreach ($channels as $channel) 
        {
            $stringToSend = $stringToSend . "," . $channelLabels[$channel]["name"];
        }
        foreach ($results as $elem)
        {
            $time = $this->formatTime($elem);
            $buffer = "";
            for($i = 1; $i < count($columns); $i++)
            {                
                $buffer = $buffer . "," . $elem[$columns[$i]];
            }
            
            $stringToSend = $stringToSend . "\n" . $time . $buffer;
        }         
        return $stringToSend;
    }
    
    protected function getLevelforTable($levels, $window, $maxLevel)
    {  
        print_r($window);
        $min = 999999999; 
        $needenLevel;
        foreach($levels as $level)
        {  
            $buffer = $window/$level["res"];
            if($min > abs($buffer - 1))
            {
                $min = abs($buffer - 1);
                $needenLevel = $level["res"];                
            }               
        }        
        if($maxLevel <= $needenLevel)
        {           
            return $maxLevel;
        }
        else
        {           
            return $needenLevel;
        }
    }
    
    protected function getAggregator($window)
    {
        return $this->dataLevel[$window];
    }
    
    
}
 



