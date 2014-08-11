<?php 

namespace GL\Core;

use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Eloquent ORM Helper
 */
class DbHelper {

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
     * Execute raw select query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function select($query,$connection = "default") {
        Capsule::connection($connection)->setFetchMode(PDO::FETCH_CLASS);
        $queryraw = Capsule::connection($connection)->raw($query);
        return Capsule::connection($connection)->select($queryraw);
    }
    
     /**
     * Execute raw insert query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function insert($query,$connection = "default")
    {
        return Capsule::connection($connection)->insert($query);
    }
    
     /**
     * Execute raw update query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function update($query,$connection = "default")
    {
        return Capsule::connection($connection)->update($query);
    }
    
     /**
     * Execute raw delete query
     * @param string $query
     * @param string $connection
     * @return type
     */
    public static function delete($query,$connection = "default")
    {
        return Capsule::connection($connection)->delete($query);
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