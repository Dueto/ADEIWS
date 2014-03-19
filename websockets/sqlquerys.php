<?php
class sqlQuerys 
{
    var $connection = null;

    
    
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
        $columns = $this->createColumns($channels);
        try
        {
            $state = $this->connection->prepare('SHOW COLUMNS FROM  ' . $tableName . ' LIKE  "%ns%"');
            $state->execute();
            $mscolumn = $state->fetchAll(\PDO::FETCH_OBJ);
            
            if(count($mscolumn) != 0)
            {
                $columns = $columns . ',`ns`';
            }
            
            $handler = $this->connection->prepare('SELECT ' . $columns . ' FROM `' . $tableName . '` WHERE (`time` <= ?) AND (`time` >= ?) AND (`time` LIKE ?) ORDER BY `time`');
                                   
            $handler->bindValue(1, $time->splitRightTime(), \PDO::PARAM_STR);
            $handler->bindValue(2, $time->splitLeftTime(), \PDO::PARAM_STR); 
            $handler->bindValue(3, $aggregator, \PDO::PARAM_STR);            
            
            $handler->execute();

            $result = $handler->fetchAll(\PDO::FETCH_OBJ);

            return $result;            
        }
        catch(\PDOException $ex)
        {   
            throw $ex;
        }
        
    }
    
    function createColumns($channels)
    {
        $string = "";
        foreach($channels as $channel)
        {
            $string .= "`v" . $channel[0] . "`";
        }
        return $string;
    }
}
