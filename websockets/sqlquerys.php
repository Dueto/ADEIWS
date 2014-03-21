<?php
class sqlQuerys 
{
    var $connection = null;
    var $columns;
    
    
    function sqlQuerys()
    {
        try
        {
            $this->connection = new \PDO('mysql:host=localhost:3306;dbname=adei;charset=utf8mb4', 
                             'root', 
                             'root', 
                        array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                            );
        }
        catch(\PDOException $ex)
        {
            throw $ex;
        }
        
    }
    
    function selectData($tableName, $channels, $time, $aggregator)
    {    
        try
        { 
            $this->checkMs($channels, $tableName);
            
            $handler = $this->connection->prepare('SELECT ' . $this->columns . ' FROM `' . $tableName . '` WHERE (`time` <= ?) AND (`time` >= ?) AND (`time` LIKE ?) ORDER BY `time`');
                                   
            $handler->bindValue(1, $time->getRightTime(), \PDO::PARAM_STR);
            $handler->bindValue(2, $time->getLeftTime(), \PDO::PARAM_STR); 
            $handler->bindValue(3, "%" . $aggregator . "%", \PDO::PARAM_STR);            
            
            $handler->execute();
            
            $result = $handler->fetchAll(\PDO::FETCH_ASSOC); 
            
            return $result;
        }
        catch(\PDOException $ex)
        {   
            throw $ex;
        }
        
    }
    
    function createColumns($channels, $name)
    {
        $string = "`time`";
        foreach($channels as $channel)
        {
            $string .= ",`" . $name . $channel . "`";
        }
        return $string;
    }
    
    function checkMs($channels, $tableName)
    {
        $state = $this->connection->prepare('SHOW COLUMNS FROM  ' . $tableName);
        $state->execute();        
        $mscolumns = $state->fetchAll(\PDO::FETCH_OBJ); 
        $flag = false;  
        $this->columns = $this->createColumns($channels, "v");
        foreach($mscolumns as $mscolumn)
        {
            if($mscolumn->Field == "ns")
            {
                $this->columns = $this->columns . ',`ns`';
                $flag = true;
            }
            if($mscolumn->Field == "mean0")
            {
                $this->columns = $this->createColumns($channels, "mean");
                if($flag == true)
                {
                    $this->columns = $this->columns . ',`ns`';
                }
            }
        }        
    }
    
    function getColumns()
    {
        return $this->columns;
    }
    
    
}
