<?php 

namespace GL\Core\Helpers;

use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Eloquent ORM Helper
 */
class DbHelper {


    /**
     * Get Schema Builder
     * @param string $connection 
     * @return Schema Builder
     */
    public static function getSchema($connection="default")
    {
        return Capsule::connection($connection)->getSchemaBuilder();
    }

     /**
     * Get Pdo instance for selected connection
     * @param string $connection
     * @return PDO object
     */
    public static function getPdo($connection="default")
    {
        return Capsule::connection($connection)->getPdo();
    }

    /**
     * Start transaction
     * @param string $connection
     * @return type
     */
    public static function beginTransaction($connection="default")
    {
        return Capsule::connection($connection)->beginTransaction();
    }
    
    /**
     * Commit transaction
     * @param string $connection
     * @return type
     */
    public static function commit($connection="default")
    {
        return Capsule::connection($connection)->commit();
    }
    
    /**
     * Cancel transaction
     * @param string $connection
     * @return type
     */
    public static function rollback($connection="default")
    {
        return Capsule::connection($connection)->rollback();
    }

    /**
     * Return raw statement
     * @param string $query 
     * @return Expression Raw for eloquent
     */
    public static function raw($query,$connection="default")
    {
        return Capsule::connection($connection)->raw($query);
    }
    
    /**
     * Execute raw select query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function select($query,$bindings = array(),$connection = "default") {
        $pdo = Capsule::connection($connection)->getPdo();
        $q = $pdo->prepare($query);
        $q->execute($bindings);
        return $q->fetchAll(\PDO::FETCH_CLASS); 
    }
 
    public static function selectPDO($query,$connection="default")
    {
        $pdo = Capsule::connection($connection)->getPdo();
        $q = $pdo->prepare($query);
        $q->execute();
        return $q->fetchAll(\PDO::FETCH_OBJ); 
    }
    
     /**
     * Execute raw insert query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function insert($query,$bindings = array(),$connection = "default")
    {        
        $queryraw = self::raw($query,$connection);
        return Capsule::connection($connection)->insert($queryraw,$bindings);
    }
    
     /**
     * Execute raw update query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function update($query,$bindings = array(),$connection = "default")
    {
        $queryraw = self::raw($query,$connection);
        return Capsule::connection($connection)->update($queryraw,$bindings);
    }
    
     /**
     * Execute raw delete query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function delete($query,$bindings = array(),$connection = "default")
    {
        $queryraw = self::raw($query,$connection);
        return Capsule::connection($connection)->delete($queryraw,$bindings);
    }
    
     /**
     * Execute raw statement query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function statement($query,$connection = "default")
    {
        return Capsule::connection($connection)->statement($query);
    }

    /**
     * Get query log for provided connection
     * @param string $connection
     * @return array
     */
    public static function getLog($connection = "default")
    {
        return Capsule::connection($connection)->getQueryLog();
    }    
}