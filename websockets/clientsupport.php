<?php
require 'timeFormat.php';
require_once("sqlquerys.php");
require_once('websockets.php');
 
class clientSupport extends WebSocketServer 
{   
    private $dataLevel = array(315360000 => "-01-01 00:00:00",
                           31536000 => "-01 00:00:00",
                           2592000 =>  " 00:00:00",                                                 
                           86400 =>  ":00:00",                         
                           3600 => ":00" ,      
                           60 => "" ,
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
        $window = $basicRequest->GetProp("window");
        $time = new timeFormat($window);
        
        $needenLevel = "0";
        $needenLevel = $this->getLevelforTable($levels, $window);
        if($needenLevel == "")
        {
            $needenLevel = "0";
        }
            
        $tableName = "cache" . $needenLevel . $cachePostfix;
        $db_items = explode(",", $request->GetProp("db_mask"));
        try
        {
            $link = new sqlQuerys();
            $results = $link->selectData($tableName, $db_items, $time, $this->getAggregator($window)); 
            if(count($results) != 0)
            {
                $stringToSend = $this->formCsvString($results, $db_items);                
                $this->send($user, $stringToSend);   
            }
            else
            {
                $this->send($user, 'No data.');
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
        $time = str_replace(' ', 'T', $elem->{'time'});
        if(property_exists($elem, 'ns'))
        {
            $time = $time . '.' . $elem->ns;
        }
        else
        {
            $time = $time . '.000000';
        }
        return $time;
    }
    
    protected function formCsvString($results, $channels)
    {        
        foreach ($channels as $channel)
        {
            $stringToSend = $stringToSend . "," . $reqObj->getChannelLabel($channel);
        }
        foreach ($results as $elem)
        {
            $time = $this->formatTime($elem);
            $stringToSend = $stringToSend . "\n" . $time . "," . $elem->{$reqObj->getAggregator() . $reqObj->getDb_mask()};
        }        
    }
    
    protected function getLevelforTable($levels, $window)
    {       
        printf($window);
        foreach($levels as $level)
        {  
            if($level["min"] == $window)
            {
                return $level["res"];
            }
                       
        }
    }
    
    protected function getAggregator($window)
    {
        return $this->dataLevel[$window];
    }
    
    
}
 



