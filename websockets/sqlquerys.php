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
    
    function selectData($tableName, $time)
    {    
        try
        {             
            $handler = $this->connection->prepare('SELECT * FROM `' . $tableName . '` WHERE (`time` <= ?) AND (`time` >= ?) ORDER BY `time`');
                                   
            $handler->bindValue(1, $time->getRightTime(), \PDO::PARAM_STR);
            $handler->bindValue(2, $time->getLeftTime(), \PDO::PARAM_STR); 
            
            $handler->execute();
            
            $result = $handler->fetchAll(\PDO::FETCH_ASSOC); 
            
            return $result;
        }
        catch(\PDOException $ex)
        {   
            throw $ex;
        }
        
    }
}
