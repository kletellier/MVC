<?php
/**
 * Start Eloquent ORM
 */
use Illuminate\Database\Capsule\Manager as Capsule;  
use Symfony\Component\Yaml\Parser;
use GL\Core\Config\Config;

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