<?php

namespace GL\Core\Twig;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Stopwatch\Stopwatch;
use Assert\Assertion;
use Assert\AssertionFailedException;
/**
 * Load Twig Environment
 *
 */
class TwigService implements \GL\Core\Templating\TemplateServiceInterface
{
     protected $_controller;
     protected $_container;
     protected $_profile;
     
     function __construct($controller = "")
     {
         $this->_controller = $controller;   
         $this->_container = null;
         $this->testTwig();
     }

     private function testTwig()
     {
        try 
        {
            $class = "Twig_Environment";
            Assertion::ClassExists($class);              
        } 
        catch (AssertionFailedException $e) 
        {
            echo "Twig is not installed, add it with 'twig/twig':'1.24' in your composer.json";
            die();
        }          
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
     * Embed DI container in TwigHelper
     * 
     * @param \GL\Core\ContainerInterface $container DI contrainer to embed in TwigHelper
     */
     public function setContainer(\Symfony\Component\DependencyInjection\Container $container = null)
     {
         $this->_container = $container;
     }
    
    
     /**
     * Function for Twig path searching
     * TWIG_PATH , defined in index.php
     * TWIG_PATH / Controller name
     * 
     * @return string
     */
    private function getPathArray()
    {
        

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
     * Function returning the Twig environnment
     * 
     * @return \Twig_Environment
     */
    private function getTwigEnvironment()
    {   
        $arrcache = array();
       
        if(TEMPLATE_CACHE )
        {
            $cachepath = CACHEPATH . DS . 'twig';            
            $arrcache['cache'] =  $cachepath;
            $arrcache['auto_reload'] = AUTORELOADCACHE;
        }
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $arrcache['debug']=true;
        }
        $twigloader = new \Twig_Loader_Filesystem($this->getPathArray());
        $twigenv = new \Twig_Environment($twigloader,$arrcache);        
        $twigenv->addExtension(new \GL\Core\Twig\TwigHelper($this->_container));
        
        // add shared TwigHelper
        $config = new \GL\Core\Config\Config('twig');
        $value = $config->load(); 
        foreach($value as $name => $th)
        {
            $class = $th['class'];
            $twigenv->addExtension(new $class($this->_container));
        }
        //$twigenv->addExtension(new \Application\Shared\SharedTwigHelper($this->_container));
        if(DEVELOPMENT_ENVIRONMENT)
        {
          $twigenv->addExtension(new \GL\Core\Debug\TwigDebugBar($this->_container));
          $twigenv->addExtension(new \Twig_Extension_Debug()); 
          $this->_profile = new \Twig_Profiler_Profile();
          $twigenv->addExtension(new \Twig_Extension_Profiler($this->_profile));
         
        }
        $twigenv->addTokenParser(new \GL\Core\Twig\TwigRenderToken());
        $twigenv->addTokenParser(new \GL\Core\Twig\TwigRouteToken());

        return $twigenv;
    }
   
    /**
     * Render Twig template
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
            $debug = (DEVELOPMENT_ENVIRONMENT==TRUE) ? $this->_container->get('debug') : null;

            $stopwatch = new Stopwatch();
            $stopwatch->start('render');
            
            if(DEVELOPMENT_ENVIRONMENT)
            {
                $debug["time"]->startMeasure('inittwig','Init Twig Environnment');
            }
            $env = $this->getTwigEnvironment();
            if(DEVELOPMENT_ENVIRONMENT)
            {
                $debug["time"]->stopMeasure('inittwig');
            }
            if(DEVELOPMENT_ENVIRONMENT && $disabledebug==false)
            {       
                if(!$debug->hasCollector('twig'))
                {
                    $debug->addCollector(new \GL\Core\Debug\TwigDataCollector($this->_profile));
                } 
                $debug["time"]->startMeasure('rendertwig','Twig rendering');             
                $ret =  $env->render($template, $params);
                $debug["time"]->stopMeasure('rendertwig','Twig rendering');
            }
            else
            {
                $ret =  $env->render($template, $params);
            }
            
            $event = $stopwatch->stop('render');
            /*if(DEVELOPMENT_ENVIRONMENT)
            {
                echo "<!-- generated  twig  ".$event->getDuration()." ms -->\r\n";
            } */       
        } 
        catch (\Twig_Error $e) 
        {
            if($this->_container!=null && DEVELOPMENT_ENVIRONMENT)
            {

                $debug["exceptions"]->addException($e);
            }
           throw new \Exception($e->getMessage());
        }
         
        return $ret;
    }
}
