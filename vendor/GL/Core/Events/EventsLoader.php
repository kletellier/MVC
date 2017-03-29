<?php

namespace GL\Core\Events;
 
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class EventsLoader
{

    protected $configname;
    protected $events;


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
        return CONFIGDIR . DS . "events.yml";
    }

    private function getPathPHP()
    {
        return CACHEPATH . DS . "events";
    }

    private function getPathClass()
    {
        return $this->getPathPHP() . DS . "Events.php";
    }

    

    /**
     * Return all parameters
     * @return arrray
     */
    public function getAll()
    {
        return $this->events;
    }

    /**
     * Function to put events in PHP Classes
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

        $events = $this->events;
        $factory = new BuilderFactory;
        $node = $factory->namespace('Events')
                        ->addStmt($factory->class('Events')
                        ->addStmt($factory->property('_events')->makePrivate()->setDefault($events))
                        ->addStmt($factory->method('getEvents')
                                ->makePublic()                
                                ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('this->_events')))
                                )
                    )->getNode();

        $stmts = array($node);
        $prettyPrinter = new PrettyPrinter\Standard();
        $php = $prettyPrinter->prettyPrintFile($stmts); 
         
        file_put_contents($path, $php);             
    }

    /**
     * Load  events data from yaml file
     */
    private function load()
    {
        
        $this->events = null;
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
                // events.yml changed or new install, create Php class
                $yaml = new Parser();
                $this->events = $yaml->parse(file_get_contents($this->getPath()));
                $this->createClass();
            }
            if(!class_exists('\Events\Events'))
                throw new \Exception("Class Events not created");
            $param = new \Events\Events();
            $this->events = $param->getEvents();            
            
        } 
        catch (Exception $e) 
        {
            
        }       
        
    }
 
      

    
}
