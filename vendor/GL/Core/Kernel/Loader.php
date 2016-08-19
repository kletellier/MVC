<?php 

namespace GL\Core\Kernel;

use GL\Core\Config\Config;
use Illuminate\Database\Capsule\Manager as Capsule;  
use Symfony\Component\Yaml\Parser;

class Loader 
{
    public static function Init()
    {
        self::InitPath();
        self::InitConfig();
        self::InitDatabase();
    }
    
    public static function InitPath()
    {
        /**
         * Loading path constant
         */
        $root = ROOT . DS;
        $cache = $root . 'cache';
        $app = $root . 'app';
        $config = $root . 'config';

        define('TEMPLATEPATH',$app.DS.'Application'.DS.'Views'); // Path to twig views
        define('CONFIGDIR',$config); // path to config directory
        define('UPLOADPATH',$root . 'uploads'); // Path to upload directory
        define('PUBLICPATH',$root . 'public'); // Path to public directory (directory open to the web)
        define('TMPPATH',$root . 'tmp'); // Temporary path (using for xls export)
        define('CACHEPATH',$cache); // Cache path
        define('SERVICEPATH',$config); // DI service definition directory  path
        define('DICACHE',$cache.DS.'DI'.DS.'Container'.DS.'ServiceContainer.php'); // DI container compiled file
        define('MIGRATIONPATH',$app . DS . "Migrations"); // Migration script path
        define('ROUTECACHE',$cache . DS . 'route'); // Route cache system
        define('DEBUGBAR',$root ."plugins".DS."maximebf".DS."debugbar".DS."src".DS."DebugBar".DS."Resources"); // Debugbar asset folder
    }

    /**
     * Define application constant
     * DEVELOPMENT_ENVIRONMENT : boolean, to write error on output buffer or not
     * BASE_PATH : website base url
     * TWIG_CACHE : cache all twig templates for improving speed
     * AUTORELOADCACHE :recreate twig cache for each request
     */
    public static function InitConfig()
    {   
        // loading configuration from parameters.yml
        
        $value = \Parameters::get('config');

        define('DEVELOPMENT_ENVIRONMENT',$value['debug']);
        define('BASE_PATH',$value['webpath']);
        define('TEMPLATE_ENGINE',$value['template']['engine']);
        define('TEMPLATE_CACHE',$value['template']['cache']);
        define('AUTORELOADCACHE',$value['template']['alwaysreload']);
        define('LOCALE',$value['locale']);
    }   

    public static function InitDatabase()
    {       
        /**
         * Load database configuration from parameters.yml 
         */
         
        $value = \Parameters::get('database');
                
        $capsule = new Capsule;
         
        foreach($value as $name => $conn)
        {
            $port = 3306;
            $connstr = $conn["server"];
            if(isset($conn["port"]))
            {
                 $port = trim($conn["port"]);
            }

            $type = isset($conn["type"]) ? $conn["type"] : "mysql";
            $allowed = array('mysql','sqlite');
            if(!in_array($type, $allowed))
            {
                echo $type." database not allowed.";
                die();       
            }
            $capsule->addConnection( array(
            'driver'    => $type,
            'host'      => $connstr,
            'port'      => $port,
            'database'  => $conn["database"],
            'username'  => $conn["user"],
            'password'  => $conn["password"],
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => ''
            ),$name);            
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}