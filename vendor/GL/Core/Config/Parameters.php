<?php

namespace GL\Core\Config;
 
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Parameters
{

    protected $configname;
    protected $parameters;


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
        return CONFIGDIR . DS . "parameters.yml";
    }

    private function getPathPHP()
    {
        return CACHEPATH . DS . "parameters";
    }

    private function getPathClass()
    {
        return $this->getPathPHP() . DS . "Parameters.php";
    }

    /**
     * Return section of parameters file
     * @param string $key key value
     * @return tyarraype
     */
    public function get($key,$refresh=false)
    {
        $ret = null;
        try 
        {
            if($refresh)
            {
                $this->load();
            }
           if(isset($this->parameters[$key]))
           {
            $ret = $this->parameters[$key];
           } 
        } 
        catch (Exception $e) 
        {
            
        }
        return $ret;
    }

    /**
     * Return all parameters
     * @return arrray
     */
    public function getAll()
    {
        return $this->parameters;
    }

    /**
     * Function to put parameters in PHP Classes
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

        $parameters = $this->parameters;
        $factory = new BuilderFactory;
        $node = $factory->namespace('Parameters')
                        ->addStmt($factory->class('Parameters')
                        ->addStmt($factory->property('_parameters')->makePrivate()->setDefault($parameters))
                        ->addStmt($factory->method('getParameters')
                                ->makePublic()                
                                ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('this->_parameters')))
                                )
                    )->getNode();

        $stmts = array($node);
        $prettyPrinter = new PrettyPrinter\Standard();
        $php = $prettyPrinter->prettyPrintFile($stmts); 
         
        file_put_contents($path, $php);             
    }

    /**
     * Load  parameters data from yaml file
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
                // parameters.yml changed or new install, create Php class
                $yaml = new Parser();
                $this->parameters = $yaml->parse(file_get_contents($this->getPath()));
                $this->createClass();
            }
            if(!class_exists('\Parameters\Parameters'))
                throw new \Exception("Class Parameters not created");
            $param = new \Parameters\Parameters();
            $this->parameters = $param->getParameters();            
            
        } 
        catch (Exception $e) 
        {
            
        }       
        
    }
 
      

    
}
