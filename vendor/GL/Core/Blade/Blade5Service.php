<?php

namespace GL\Core\Blade;

use Symfony\Component\DependencyInjection\Container;
use Philo\Blade\Blade;

/**
 * Load Blade Environment
 *
 */
class Blade5Service implements \GL\Core\Templating\TemplateServiceInterface
{
     protected $_controller;
     protected $_container;
     protected $_debug;
           
     function __construct($controller = "")
     {
         $this->_controller = $controller;   
         $this->_container = null;
     }
     
     /**
      * set Controller name
      * @return void
      */
     public function setController($controller = "")
     {        
        $this->_controller = $controller;
     }

      /**
     * Embed DI container 
     * 
     * @param \GL\Core\ContainerInterface $container DI contrainer to embed 
     */
     public function setContainer(\Symfony\Component\DependencyInjection\Container $container = null)
     {
         $this->_container = $container;
         if(DEVELOPMENT_ENVIRONMENT)
         {
            $this->_debug = $this->_container->get('debug');
         }
     }

      /**
     * Function for Blade path searching
     * 
     * @return string
     */
    private function getPathArray()
    {
        
        $arr = array();
        if(isset($this->_controller))
        {
            $viewctlpath = TEMPLATEPATH . DS . ucfirst($this->_controller);
            $arr = array($viewctlpath,TEMPLATEPATH);
        }
        else
        {           
            $arr = array(TEMPLATEPATH);
        }        

        return $arr;
    }   
    
    /**
     * Render Blade template
     * 
     * @param string $template template path
     * @param array $params parameters array for template
     */ 
    public function render($template,array $params,$disabledebug=false)
    {   
        $ret = "";
        try 
        {
            if($this->_container==null)
            {
                throw new \Exception("Missing Dependency Container, add it with setContainer Method");                
            }

            if(DEVELOPMENT_ENVIRONMENT)
            {
                 $this->_debug["time"]->startMeasure('initblade','Init blade templating environnment');
            }

            $cachepath = CACHEPATH . DS . 'blade'; 
            $views = $this->getPathArray();
            $blade = new Blade($views, $cachepath); 

            // get blade compiler
            $compiler = $blade->getCompiler();

            // add use directive
            $compiler->directive('use', function($expression){               
                return "<?php use $expression; ?>";
            });   
             
            if(DEVELOPMENT_ENVIRONMENT)
            {
                $this->_debug["time"]->stopMeasure('initblade');
            }
            if(DEVELOPMENT_ENVIRONMENT && $disabledebug==false)
            { 
                $this->_debug["time"]->startMeasure('renderblade','Blade rendering');   
                // render the template file and echo it         
                $ret = $blade->view()->make($template, $params)->render(); 
                $this->_debug["time"]->stopMeasure('renderblade','Blade rendering');
            }
            else
            {
                $ret =  $blade->view()->make($template, $params)->render();  
            }                       
               
        } 
        catch (\Exception $e) 
        {
            throw new \Exception($e->getMessage());          
        }
         
        return $ret;
    }
}
