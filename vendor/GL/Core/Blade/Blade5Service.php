<?php

namespace GL\Core\Blade;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Stopwatch\Stopwatch;
use Philo\Blade\Blade;

/**
 * Load Blade Environment
 *
 */
class Blade5Service implements \GL\Core\Templating\TemplateServiceInterface
{
     protected $_controller;
     protected $_container;
     protected $_profile;
     
     function __construct($controller = "")
     {
         $this->_controller = $controller;   
         $this->_container = null;
     }
     
     /**
      * set Controller name
      * @return void
      */
     private function setController($controller = "")
     {        
        $this->_controller = $controller;
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
     * Embed DI container 
     * 
     * @param \GL\Core\ContainerInterface $container DI contrainer to embed 
     */
     private function setContainer(\Symfony\Component\DependencyInjection\Container $container = null)
     {
         $this->_container = $container;
     }
    
    /**
     * Render Blade template
     * 
     * @param string $template template path
     * @param array $params parameters array for template
     */ 
    public function render($template,array $params,\Symfony\Component\DependencyInjection\Container $container = null,$controller="",$disabledebug=false)
    {   
        $ret = "";
        try 
        {

            $stopwatch = new Stopwatch();
            $stopwatch->start('render');
            $this->setContainer($container);  
            $this->setController($controller);                    
 
            if(DEVELOPMENT_ENVIRONMENT)
            {
                $container->get('debug')["time"]->startMeasure('initblade','Init Blade Environnment');
            }

             $cachepath = CACHEPATH . DS . 'blade'; 
            $views = $this->getPathArray();
            $blade = new Blade($views, $cachepath); 

            // get blade compiler
            $compiler = $blade->getCompiler();

            // add use directive
            $compiler->directive('use', function($expression){
                preg_match('#\((.*?)\)#', $expression, $match);  
                return "<?php use $match[1]; ?>";
            });   
             
             if(DEVELOPMENT_ENVIRONMENT)
            {
                $container->get('debug')["time"]->stopMeasure('initblade');
            }
            if(DEVELOPMENT_ENVIRONMENT && $disabledebug==false)
            {       
                
                $container->get('debug')["time"]->startMeasure('renderblade','Blade rendering');   
                // render the template file and echo it         
                $ret = $blade->view()->make($template, $params)->render();  
                $container->get('debug')["time"]->stopMeasure('renderblade','Blade rendering');
            }
            else
            {
                $ret =  $blade->view()->make($template, $params)->render();  
            }            
            $event = $stopwatch->stop('render');
               
        } 
        catch (\Exception $e) 
        {
            throw new \Exception($e->getMessage());          
        }
         
        return $ret;
    }
}
