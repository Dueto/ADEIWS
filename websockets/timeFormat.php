<?php

class timeFormat 
{
    var $window;
    
    
    public function timeFormat($_window)
    {
        $this->window = $_window;
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
}
