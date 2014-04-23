<?php
class sqlQuerys 
{
    var $connection = NULL;
    
    function connect($host = false, $dbname = false)
    {
        if($host !== false && $dbname !== false)
        {
            try
            {
                $this->connection = new \PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8mb4', 
                                 'rakch', 
                                 'adei123', 
                            array(\PDO::ATTR_PERSISTENT => true,
                                  \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                                );                
                if($this->connection === NULL)
                {
                    return NULL;
                }
            }
            catch(\PDOException $ex)
            {                
                throw $ex;
            }
        }
        else
        {
            return NULL;
        }
        
    }
    
    function selectData($tableName = false, $time = false, $columns = false)
    {   
        if($tableName !== false && $time !== false && $columns !== false)
        {          
          $time = explode("-", $time);  
          $rightMilisec = explode(".", $time[0])[1];
          $leftMilisec = explode(".", $time[1])[1];
          $time[0] = explode(".", $time[0])[0];
          $time[1] = explode(".", $time[1])[0];
          
          $stringColumns = $this->formColumns($columns);  
          $isMilisec = $this->isMilliseconds($tableName);          
          $sqlStatement = $this->formSqlStatement($stringColumns, $tableName, $isMilisec);          
          try
          {        
              
              if($this->connection !== NULL)
              {
                $handler = $this->connection->prepare($sqlStatement);  
                print_r($sqlStatement . "\n");
                $handler->bindValue(1, $time[1], \PDO::PARAM_STR);
                $handler->bindValue(2, $time[0], \PDO::PARAM_STR);  
                if($isMilisec)
                {
                    $handler->bindValue(3, $rightMilisec, \PDO::PARAM_STR);
                    $handler->bindValue(4, $leftMilisec, \PDO::PARAM_STR);  
                }
                $handler->execute();            
                $result = $handler->fetchAll(\PDO::FETCH_ASSOC);             
                return $result;              
              }
              else
              {
                  return NULL;
              }
          }
          catch(\PDOException $ex)
          {   
              throw $ex;  
          }

        }
        else
        {
            return NULL;
        }
        
    }
    
    function formColumns($columns)
    {
        $stringColumns = "`time`";
        foreach($columns as $column)
        {
            $stringColumns = $stringColumns . ",`" . $column . "`";            
        }        
        return $stringColumns;
    }
    
    function formSqlStatement($stringColumns, $tableName, $isMilisec)
    {
        $sqlStatement = "";
        if(!$isMilisec)
        {
            $sqlStatement = 'SELECT ' . $stringColumns . ' FROM `' . $tableName . '` WHERE (UNIX_TIMESTAMP(`time`) <= ?) AND (UNIX_TIMESTAMP(`time`) >= ?) ORDER BY `time` LIMIT 5000';        
        }
        else
        {
            $sqlStatement = 'SELECT ' . $stringColumns . ',`ns` FROM `' . $tableName . '` WHERE (UNIX_TIMESTAMP(`time`) <= ?) AND (UNIX_TIMESTAMP(`time`) >= ?) AND (`ns` <= ?) AND (`ns` >= ?) ORDER BY `time` LIMIT 5000';        
        }
        return $sqlStatement;
    }
    
    function isMilliseconds($tableName)
    {
        $sqlStatement = "SELECT * 
                        FROM information_schema.COLUMNS 
                        WHERE TABLE_SCHEMA = 'adei' 
                        AND TABLE_NAME = '" . $tableName . "' 
                        AND COLUMN_NAME = 'ns'";
        $handler = $this->connection->prepare($sqlStatement);   
        $handler->execute();            
        $result = $handler->fetchAll(\PDO::FETCH_ASSOC); 
        if(count($result))
        {
            return true;
        }
        else
        {
            return false;
        }        
    }
    
    function closeConnection()
    {
        $this->connection = null;
    }
    
    function ping()
    {
        return mysql_ping($this->connection);
    }
}
