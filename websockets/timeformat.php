<?php

class timeFormat 
{
    private $window;
    private $rightTime;
    private $leftTime;
    
    function __construct($_window)
    {
        $this->window = $_window;
        $this->rightTime = date("Y-m-d H:i:s", $this->splitRightTime());       
        $this->leftTime = date("Y-m-d H:i:s", $this->splitLeftTime());
    }
    
    public function splitLeftTime()
    {
        $leftTime = explode('-', $this->window)[0];
        $leftTime = explode('.', $leftTime)[0];
        return $leftTime;
    }
    
    public function splitRightTime()
    {
        $rightTime = explode('-', $this->window)[1];        
        $rightTime = explode('.', $rightTime)[0];        
        return $rightTime;
    }
    
    public function splitLeftMiliseconds()
    {
        $leftMilisec = explode('-', $this->window)[0];
        $leftMilisec = explode('.', $lefMilisec)[1];
        return $leftMilisec;
    }
    
    public function splitRightMiliseconds()
    {
        $rightMilisec = explode('-', $this->window)[1];
        $rightMilisec = explode('.', $rightMilisec)[1];
        return $rightMilisec;
    }
    
    public function getRightTime()
    {               
        return $this->rightTime;
    }
    
    public function getLeftTime()
    {        
        return $this->leftTime;
    }
    
 
    public function formatDate($format)
    {
        $aggregatorLen = strlen($format);
        $timeLen = strlen($this->rightTime);
        $this->rightTime = substr($this->rightTime, 0, $timeLen - $aggregatorLen) . $format;
        $this->leftTime = substr($this->leftTime, 0, $timeLen - $aggregatorLen) . $format;
    }
    
    
}
