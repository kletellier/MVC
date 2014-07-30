<?php
/**
 * Start Eloquent ORM
 */
use Illuminate\Database\Capsule\Manager as Capsule;  
use Symfony\Component\Yaml\Parser;

/**
 * Load database configuration from config/database.yml 
 */
$yaml = new Parser();
$value = $yaml->parse(file_get_contents(DATABASEPATH));
        
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
    
    $capsule->addConnection( array(
    'driver'    => 'mysql',
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