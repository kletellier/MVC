<?php

namespace GL\Core\Routing;
 
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class RouteArray
{

    protected $configname;
    protected $routes;


    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        $this->load();
    }
 

    /**
     * Get absolute path of the config file
     * @return string path
     */
    private function getPath()
    {
        return CONFIGDIR . DS . "routes.yml";
    }

    private function getPathPHP()
    {
        return CACHEPATH . DS . "route";
    }

    private function getPathClass()
    {
        return $this->getPathPHP() . DS . "RouteArray.php";
    }

    

    /**
     * Return all routes
     * @return arrray
     */
    public function getAll()
    {
        return $this->routes;
    }

    /**
     * Function to put routes in PHP Classes
     * @return type
     */
    private function createClass()
    {
        $fs = new Filesystem();
        $directory = $this->getPathPHP();
        $path  = $this->getPathClass();

        if(!$fs->exists($directory))
        {
            $fs->mkdir($directory);
        }

        $routes = $this->routes;
        $factory = new BuilderFactory;
        $node = $factory->namespace('Route')
                        ->addStmt($factory->class('RouteArray')
                        ->addStmt($factory->property('_routes')->makePrivate()->setDefault($routes))
                        ->addStmt($factory->method('getRoutes')
                                ->makePublic()                
                                ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('this->_routes')))
                                )
                    )->getNode();

        $stmts = array($node);
        $prettyPrinter = new PrettyPrinter\Standard();
        $php = $prettyPrinter->prettyPrintFile($stmts); 
         
        file_put_contents($path, $php);             
    }

    /**
     * Load  routes data from yaml file
     */
    private function load()
    {
        
        $this->parameters = null;
        try 
        {
            $pathPHP = $this->getPathClass();
            $pathXml = $this->getPath();

            $create = false;
            if(is_file($pathPHP))
            {
                if(filemtime($pathPHP)<filemtime($pathXml))
                {
                    $create = true;
                }
            }
            else
            {
                $create = true;
            }
            if($create)
            {
                // routes.yml changed or new install, create Php class
                $yaml = new Parser();
                $this->routes = $yaml->parse(file_get_contents($this->getPath()));
                $this->createClass();
            }
            if(!class_exists('\Route\RouteArray'))
                throw new \Exception("Class RouteArray not created");
            $param = new \Route\RouteArray();
            $this->routes = $param->getRoutes();            
            
        } 
        catch (Exception $e) 
        {
            
        }  
    }
}
