<?php 

namespace GL\Core\Kernel;

use GL\Core\Config\Config;
use Illuminate\Database\Capsule\Manager as Capsule;  
use Symfony\Component\Yaml\Parser;

class Loader 
{
    public static function InitPath()
    {
        /**
         * Loading path constant
         */
        define('TEMPLATEPATH',ROOT . DS . 'app'.DS.'Application'.DS.'Views'); // Path to twig views
        define('CONFIGDIR',ROOT.DS.'config'); // path to config directory
        define('UPLOADPATH',ROOT . DS . 'uploads'); // Path to upload directory
        define('PUBLICPATH',ROOT. DS . 'public'); // Path to public directory (directory open to the web)
        define('TMPPATH',ROOT . DS . 'tmp'); // Temporary path (using for xls export)
        define('CACHEPATH',ROOT . DS . 'cache'); // Cache path
        define('SERVICEPATH',ROOT . DS . 'config'); // DI service definition directory  path
        define('DICACHE',ROOT.DS.'cache'.DS.'DI'.DS.'Container'.DS.'ServiceContainer.php'); // DI container compiled file
        define('MIGRATIONPATH',ROOT . DS . "app" . DS . "Migrations"); // Migration script path
        define('ROUTECACHE',ROOT . DS . 'cache' . DS . 'route'); // Route cache system
        define('DEBUGBAR',ROOT . DS ."plugins".DS."maximebf".DS."debugbar".DS."src".DS."DebugBar".DS."Resources"); // Debugbar asset folder
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
        // loading configuration from config/config.yml
        $loader = new Config('config');
        $value = $loader->load();

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
         * Load database configuration from config/database.yml 
         */
        $yaml = new Config("database");
        $value = $yaml->load();
                
        $capsule = new Capsule;
         
        foreach($value as $name => $conn)
        {
            $connstr = $conn["server"];
            if(isset($conn["port"]))
            {
                    $port = trim($conn["port"]);
                    if($port!="")
                    {
                    $connstr.=":".$port;
                    }
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